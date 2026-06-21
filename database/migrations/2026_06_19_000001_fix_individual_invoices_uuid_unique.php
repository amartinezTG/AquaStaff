<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Paso 1: Identificar qué transacciones tienen UUIDs duplicados.
        // Para cada grupo de UUID duplicado, conservamos el primer registro insertado
        // (id más bajo) y limpiamos los demás.

        $duplicates = DB::select("
            SELECT uuid, MIN(id) as keep_id
            FROM individual_invoices
            GROUP BY uuid
            HAVING COUNT(*) > 1
        ");

        foreach ($duplicates as $dup) {
            // Obtener los ids extras (los que NO se conservan)
            $extraIds = DB::table('individual_invoices')
                ->where('uuid', $dup->uuid)
                ->where('id', '!=', $dup->keep_id)
                ->pluck('local_transaction_id');

            // Limpiar fiscal_invoice en local_transaction para que puedan re-timbrarse
            DB::table('local_transaction')
                ->whereIn('local_transaction_id', $extraIds)
                ->where('fiscal_invoice', $dup->uuid)
                ->update([
                    'fiscal_invoice'    => null,
                    'fiscal_account_id' => null,
                ]);

            // Eliminar los registros duplicados de individual_invoices
            DB::table('individual_invoices')
                ->where('uuid', $dup->uuid)
                ->where('id', '!=', $dup->keep_id)
                ->delete();
        }

        // Paso 2: Reemplazar el unique compuesto (uuid, local_transaction_id)
        // por un unique simple sobre uuid.
        Schema::table('individual_invoices', function (Blueprint $table) {
            $table->dropUnique('individual_invoices_uuid_local_transaction_id_unique');
            $table->unique('uuid');
        });
    }

    public function down(): void
    {
        Schema::table('individual_invoices', function (Blueprint $table) {
            $table->dropUnique('individual_invoices_uuid_unique');
            $table->unique(['uuid', 'local_transaction_id']);
        });
    }
};
