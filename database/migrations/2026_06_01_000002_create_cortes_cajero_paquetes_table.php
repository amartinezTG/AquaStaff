<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cortes_cajero_paquetes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('corte_cajero_id');
            $table->string('paquete', 100);
            $table->unsignedInteger('autos_sistema')->default(0); // calculado de local_transaction
            $table->unsignedInteger('autos_cajero')->default(0);  // del operador (pre-llenado de interlogic)
            $table->decimal('precio', 12, 2)->default(0.00);
            $table->decimal('total', 12, 2)->default(0.00);
            $table->timestamps();

            $table->foreign('corte_cajero_id')->references('id')->on('cortes_cajero')->onDelete('cascade');
            $table->unique(['corte_cajero_id', 'paquete']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cortes_cajero_paquetes');
    }
};
