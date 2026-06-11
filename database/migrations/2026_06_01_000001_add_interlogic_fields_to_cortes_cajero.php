<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cortes_cajero', function (Blueprint $table) {
            $table->unsignedBigInteger('corte_interlogic_id')->nullable()->after('id');
            $table->boolean('interlogic_cargado')->default(false)->after('corte_interlogic_id');
        });
    }

    public function down(): void
    {
        Schema::table('cortes_cajero', function (Blueprint $table) {
            $table->dropColumn(['corte_interlogic_id', 'interlogic_cargado']);
        });
    }
};
