<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncIndividualInvoices extends Command
{
    protected $signature = 'facturas:sync-individuales {--origen=backfill : Valor de origen para los registros nuevos (backfill|cliente)}';

    protected $description = 'Registra en individual_invoices las facturas timbradas en local_transaction que aún no tienen registro (backfill inicial y sync de autofacturas del portal cliente)';

    public function handle(): int
    {
        $origen = $this->option('origen');

        $rows = DB::select("
            SELECT lt.local_transaction_id, lt.fiscal_invoice, lt.fiscal_account_id,
                   lt.CadenaFacturacion, lt.Total
            FROM local_transaction lt
            LEFT JOIN individual_invoices ii
                   ON ii.uuid = lt.fiscal_invoice
                  AND ii.local_transaction_id = lt.local_transaction_id
            WHERE lt.fiscal_invoice IS NOT NULL
              AND lt.deleted_at IS NULL
              AND ii.id IS NULL
        ");

        if (empty($rows)) {
            $this->info('Sin facturas nuevas que registrar.');
            return self::SUCCESS;
        }

        $now      = now();
        $inserted = 0;

        foreach (array_chunk($rows, 500) as $chunk) {
            $batch = array_map(function ($r) use ($origen, $now) {
                $base = round($r->Total / 1.08, 2);
                return [
                    'uuid'                 => $r->fiscal_invoice,
                    'local_transaction_id' => $r->local_transaction_id,
                    'fiscal_account_id'    => $r->fiscal_account_id,
                    'serie'                => 'AB',
                    'subtotal'             => $base,
                    'iva'                  => round($r->Total - $base, 2),
                    'total'                => $r->Total,
                    'file_name'            => $r->CadenaFacturacion ?: 'IND_' . $r->local_transaction_id,
                    'status'               => 'vigente',
                    'origen'               => $origen,
                    'created_at'           => $now,
                    'updated_at'           => $now,
                ];
            }, $chunk);

            DB::table('individual_invoices')->insertOrIgnore($batch);
            $inserted += count($batch);
        }

        $this->info("Registradas {$inserted} factura(s) en individual_invoices (origen: {$origen}).");
        return self::SUCCESS;
    }
}
