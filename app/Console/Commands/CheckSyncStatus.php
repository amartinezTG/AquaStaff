<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

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

        $index = [];
        foreach ($rows as $row) {
            $index[$row->estructura] = $row;
        }

        $alertas = [];

        foreach ($structures as $structure) {
            $rowData         = $index[$structure] ?? null;
            $ultimaRecepcion = $rowData ? $rowData->ultima_recepcion : null;
            $minutos         = $rowData ? (int)$rowData->minutos_desde : null;

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

        $destinatarios = explode(',', env('SYNC_ALERT_EMAILS', ''));
        $destinatarios = array_filter(array_map('trim', $destinatarios));

        if (empty($destinatarios)) {
            $this->warn('Hay alertas pero SYNC_ALERT_EMAILS no está configurado en .env');
            return 1;
        }

        $enviado = $this->enviarCorreo($alertas, $destinatarios);

        if ($enviado) {
            $this->info('Alerta enviada a: ' . implode(', ', $destinatarios));
        } else {
            $this->error('Error al enviar el correo. Revisa el log.');
        }

        $this->table(['Estructura', 'Última Recepción', 'Minutos'], array_map(function ($a) {
            return [$a['estructura'], $a['ultima_recepcion'], $a['minutos_desde'] ?? '—'];
        }, $alertas));

        return 0;
    }

    private function enviarCorreo(array $alertas, array $destinatarios): bool
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->SMTPDebug  = SMTP::DEBUG_OFF;
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;
            $mail->Username   = 'no-reply@totalgas.com';
            $mail->Password   = 'sysdhepknmlkigbs';

            $mail->CharSet    = 'UTF-8';
            $mail->Encoding   = 'base64';
            $mail->isHTML(true);
            $mail->setLanguage('es');

            $mail->setFrom('no-reply@totalgas.com', 'AquaStaff | Monitor de Sincronización');

            foreach ($destinatarios as $email) {
                $mail->addAddress($email);
            }

            $mail->Subject = '[AquaStaff] Alerta: estructuras sin sincronizar — ' . now()->format('d/m/Y');
            $mail->Body    = $this->buildHtml($alertas);
            $mail->AltBody = $this->buildPlainText($alertas);

            return $mail->send();

        } catch (Exception $e) {
            error_log('SyncAlert mailer error: ' . $mail->ErrorInfo);
            return false;
        }
    }

    private function buildHtml(array $alertas): string
    {
        $filas = '';
        foreach ($alertas as $a) {
            $min    = $a['minutos_desde'];
            $tiempo = $this->formatMinutos($min);
            $badge  = $min === null
                ? '<span style="background:#7f8c8d;color:#fff;padding:3px 9px;border-radius:12px;font-size:11px;">Sin datos</span>'
                : '<span style="background:#e74c3c;color:#fff;padding:3px 9px;border-radius:12px;font-size:11px;">+24h</span>';

            $filas .= "
                <tr>
                    <td style='padding:9px 12px;border-bottom:1px solid #eee;'><code>{$a['estructura']}</code></td>
                    <td style='padding:9px 12px;border-bottom:1px solid #eee;'>{$a['ultima_recepcion']}</td>
                    <td style='padding:9px 12px;border-bottom:1px solid #eee;'>{$tiempo}</td>
                    <td style='padding:9px 12px;border-bottom:1px solid #eee;'>{$badge}</td>
                </tr>";
        }

        $fecha   = now()->format('d/m/Y H:i');
        $url     = config('app.url') . '/administracion/sync-monitor';

        return "
        <!DOCTYPE html>
        <html lang='es'>
        <head><meta charset='UTF-8'></head>
        <body style='font-family:Arial,sans-serif;background:#f4f4f4;margin:0;padding:20px;'>
        <div style='max-width:620px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.1);'>
            <div style='background:#c0392b;color:#fff;padding:24px 30px;'>
                <h1 style='margin:0;font-size:20px;'>⚠️ Alerta de Sincronización</h1>
                <p style='margin:6px 0 0;font-size:13px;opacity:.85;'>{$fecha} — AquaStaff</p>
            </div>
            <div style='padding:24px 30px;'>
                <p style='color:#444;font-size:14px;'>Las siguientes estructuras llevan <strong>más de 24 horas sin recibir información</strong> en <code>transactions_log</code>:</p>
                <table style='width:100%;border-collapse:collapse;font-size:13px;margin-top:16px;'>
                    <thead>
                        <tr>
                            <th style='background:#2c3e50;color:#fff;padding:10px 12px;text-align:left;'>Estructura</th>
                            <th style='background:#2c3e50;color:#fff;padding:10px 12px;text-align:left;'>Última Recepción</th>
                            <th style='background:#2c3e50;color:#fff;padding:10px 12px;text-align:left;'>Tiempo</th>
                            <th style='background:#2c3e50;color:#fff;padding:10px 12px;text-align:left;'>Estado</th>
                        </tr>
                    </thead>
                    <tbody>{$filas}</tbody>
                </table>
                <p style='margin-top:20px;font-size:14px;color:#444;'>
                    Ver detalles: <a href='{$url}'>Monitor de Sincronización</a>
                </p>
            </div>
            <div style='padding:16px 30px;background:#f9f9f9;font-size:12px;color:#999;text-align:center;border-top:1px solid #eee;'>
                Correo generado automáticamente por AquaStaff. No responder.
            </div>
        </div>
        </body></html>";
    }

    private function buildPlainText(array $alertas): string
    {
        $lines = ["Alerta AquaStaff — " . now()->format('d/m/Y H:i'), ""];
        $lines[] = "Estructuras con más de 24h sin sincronizar:";
        $lines[] = str_repeat('-', 50);
        foreach ($alertas as $a) {
            $tiempo = $this->formatMinutos($a['minutos_desde']);
            $lines[] = "- {$a['estructura']} | Última: {$a['ultima_recepcion']} | Tiempo: {$tiempo}";
        }
        return implode("\n", $lines);
    }

    private function formatMinutos(?int $min): string
    {
        if ($min === null) return '—';
        if ($min < 60)    return $min . ' min';
        if ($min < 1440)  return floor($min / 60) . 'h ' . ($min % 60) . 'min';
        return floor($min / 1440) . 'd ' . floor(($min % 1440) / 60) . 'h';
    }
}
