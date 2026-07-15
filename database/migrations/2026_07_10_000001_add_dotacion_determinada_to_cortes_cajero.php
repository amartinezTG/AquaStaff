<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cortes_cajero', function (Blueprint $table) {
            $table->decimal('dotacion_determinada', 12, 2)->default(0)->after('dotacion_final');
            $table->decimal('dotacion_diferencia', 12, 2)->default(0)->after('dotacion_determinada');
        });
    }

    public function down(): void
    {
        Schema::table('cortes_cajero', function (Blueprint $table) {
            $table->dropColumn(['dotacion_determinada', 'dotacion_diferencia']);
        });
    }
};
