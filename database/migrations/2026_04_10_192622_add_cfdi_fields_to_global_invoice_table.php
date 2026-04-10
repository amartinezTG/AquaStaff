<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('global_invoice', function (Blueprint $table) {
            $table->string('uuid', 50)->nullable()->after('name')->comment('Folio fiscal UUID del CFDI');
            $table->string('serie', 10)->nullable()->after('uuid');
            $table->string('folio', 20)->nullable()->after('serie');
            $table->string('file_name', 100)->nullable()->after('folio')->comment('Nombre del archivo PDF/XML');
            $table->string('periodicidad', 2)->nullable()->after('paymentType')->comment('01=Diaria 02=Semanal 03=Quincenal 04=Mensual');
        });
    }

    public function down()
    {
        Schema::table('global_invoice', function (Blueprint $table) {
            $table->dropColumn(['uuid', 'serie', 'folio', 'file_name', 'periodicidad']);
        });
    }
};
