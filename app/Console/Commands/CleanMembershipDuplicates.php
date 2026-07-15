<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanMembershipDuplicates extends Command
{
    protected $signature   = 'membership:clean-duplicates';
    protected $description = 'Soft-delete membresías duplicadas en client_membership (mismo _id), conservando la de id más alto';

    public function handle()
    {
        $affected = DB::update("
            UPDATE client_membership cm
            JOIN (
                SELECT _id, MAX(id) AS keep_id
                FROM client_membership
                WHERE deleted_at IS NULL
                GROUP BY _id
                HAVING COUNT(*) > 1
            ) dupes
                ON cm._id = dupes._id
                AND cm.id < dupes.keep_id
            SET cm.deleted_at = NOW()
            WHERE cm.deleted_at IS NULL
        ");

        $this->info("Membresías duplicadas eliminadas (soft delete): {$affected}");

        return 0;
    }
}
