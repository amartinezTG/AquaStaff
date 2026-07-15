<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Agrega 'DOT' al enum de moneda para guardar el desglose de la
     * dotación (billetes/monedas que se dejan en caja) en la misma
     * tabla de denominaciones del corte.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE cortes_cajero_denominaciones MODIFY moneda ENUM('MXN','USD','DOT') NOT NULL DEFAULT 'MXN'");
    }

    public function down(): void
    {
        DB::table('cortes_cajero_denominaciones')->where('moneda', 'DOT')->delete();
        DB::statement("ALTER TABLE cortes_cajero_denominaciones MODIFY moneda ENUM('MXN','USD') NOT NULL DEFAULT 'MXN'");
    }
};
