<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procepago_domiciliaciones', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('folio')->unique();
            $table->string('titular', 255)->nullable();
            $table->decimal('importe', 10, 2)->nullable();
            $table->string('concepto', 100)->nullable();
            $table->string('referencia', 100)->nullable()->index();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->string('periodicidad', 50)->nullable();
            $table->string('status', 50)->nullable();
            $table->string('tipo', 50)->nullable();
            $table->string('archivo_origen', 255)->nullable();
            $table->timestamp('importado_en')->nullable();
            $table->unsignedInteger('importado_por')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procepago_domiciliaciones');
    }
};
