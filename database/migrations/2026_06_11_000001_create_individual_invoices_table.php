<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('individual_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 50)->index();
            $table->integer('local_transaction_id')->index();
            $table->integer('fiscal_account_id')->nullable()->index();
            $table->string('serie', 10)->nullable();
            $table->string('folio', 20)->nullable();
            $table->decimal('subtotal', 10, 2)->nullable();
            $table->decimal('iva', 10, 2)->nullable();
            $table->decimal('total', 10, 2)->nullable();
            $table->string('file_name', 100)->nullable();
            $table->dateTime('fecha_emision')->nullable();
            // vigente | cancelada
            $table->string('status', 20)->default('vigente')->index();
            $table->dateTime('cancelada_at')->nullable();
            $table->string('cancel_motivo', 2)->nullable();
            // staff | cliente | backfill
            $table->string('origen', 20)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->unique(['uuid', 'local_transaction_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('individual_invoices');
    }
};
