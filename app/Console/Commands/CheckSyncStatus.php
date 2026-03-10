<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\SyncAlertMail;

class CheckSyncStatus extends Command
{
    protected $signature   = 'sync:check-status';
    protected $description = 'Revisa estructuras con más de 24h sin sincronizar y envía alerta por correo';

    public function handle()
    {
        $structures = [
            'local_transactions',
            'orders',
            'recurrent_logs',
            'used_business_codes',
            'client_memberships',
            'clients',
            'special_orders',
        ];

        $year         = now()->year;
        $placeholders = implode(',', array_fill(0, count($structures), '?'));

        $rows = DB::select("
            SELECT
                JSON_UNQUOTE(JSON_EXTRACT(`data`, '$.structure')) AS estructura,
                MAX(created_at) AS ultima_recepcion,
                TIMESTAMPDIFF(MINUTE, MAX(created_at), NOW()) AS minutos_desde
            FROM transactions_log
            WHERE YEAR(created_at) = ?
              AND JSON_UNQUOTE(JSON_EXTRACT(`data`, '$.structure')) IN ({$placeholders})
            GROUP BY JSON_UNQUOTE(JSON_EXTRACT(`data`, '$.structure'))
        ", array_merge([$year], $structures));

        // Indexar por estructura
        $index = [];
        foreach ($rows as $row) {
            $index[$row->estructura] = $row;
        }

        $alertas = [];

        foreach ($structures as $structure) {
            $rowData         = $index[$structure] ?? null;
            $ultimaRecepcion = $rowData ? $rowData->ultima_recepcion : null;
            $minutos         = $rowData ? (int)$rowData->minutos_desde : null;

            // Sin datos o más de 1440 minutos (24h) = alerta
            if ($minutos === null || $minutos > 1440) {
                $alertas[] = [
                    'estructura'       => $structure,
                    'ultima_recepcion' => $ultimaRecepcion ?? 'Sin registros',
                    'minutos_desde'    => $minutos,
                ];
            }
        }

        if (empty($alertas)) {
            $this->info('Todo sincronizado. No se envía correo.');
            return 0;
        }

        $destinatarios = explode(',', config('sync.alert_emails', env('SYNC_ALERT_EMAILS', '')));
        $destinatarios = array_filter(array_map('trim', $destinatarios));

        if (empty($destinatarios)) {
            $this->warn('Hay alertas pero SYNC_ALERT_EMAILS no está configurado en .env');
            return 1;
        }

        foreach ($destinatarios as $email) {
            Mail::to($email)->send(new SyncAlertMail($alertas));
        }

        $this->info('Alerta enviada a: ' . implode(', ', $destinatarios));
        $this->table(['Estructura', 'Última Recepción', 'Minutos'], array_map(function($a) {
            return [$a['estructura'], $a['ultima_recepcion'], $a['minutos_desde'] ?? '—'];
        }, $alertas));

        return 0;
    }
}
