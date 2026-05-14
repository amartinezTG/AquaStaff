<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('global_invoice', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->decimal('total', 12, 2)->nullable();
            $table->date('start_date_group')->nullable();
            $table->date('end_date_group')->nullable();
            $table->string('paymentType', 20)->nullable();
            $table->timestamp('cancelada_at')->nullable();
            $table->string('cancel_motivo', 10)->nullable();
            $table->date('fecha_emision')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('global_invoice');
    }
};
