<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sat_descarga_peticiones', function (Blueprint $table) {
            $table->id();
            $table->string('request_id', 50)->unique();
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            // pendiente | descargada | rechazada | fallida
            $table->string('estado', 20)->default('pendiente')->index();
            $table->json('paquetes')->nullable();
            $table->integer('xmls_extraidos')->nullable();
            $table->text('mensaje_error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sat_descarga_peticiones');
    }
};
