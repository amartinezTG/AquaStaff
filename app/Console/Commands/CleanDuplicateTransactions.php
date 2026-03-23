<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanDuplicateTransactions extends Command
{
    protected $signature   = 'transactions:clean-duplicates';
    protected $description = 'Soft-delete renovaciones y compras de membresía duplicadas del día en curso';

    public function handle()
    {
        $affected = DB::update("
            UPDATE local_transaction lt
            JOIN (
                SELECT _id, TransactionType, Atm, MAX(local_transaction_id) AS keep_id
                FROM local_transaction
                WHERE deleted_at IS NULL
                  AND TransactionType IN (0, 1)
                  AND DATE(TransationDate) = CURDATE()
                GROUP BY _id, TransactionType, Atm
                HAVING COUNT(*) > 1
            ) dupes
                ON  lt._id              = dupes._id
                AND lt.TransactionType  = dupes.TransactionType
                AND lt.Atm              = dupes.Atm
                AND lt.local_transaction_id < dupes.keep_id
            SET lt.deleted_at = NOW()
            WHERE lt.deleted_at IS NULL
        ");

        $this->info("Duplicados eliminados (soft delete): {$affected}");

        return 0; 
    }
}
