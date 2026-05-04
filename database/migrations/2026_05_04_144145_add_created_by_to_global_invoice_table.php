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
            $table->unsignedBigInteger('created_by')->nullable()->comment('ID del usuario que generó la factura');
        });
    }

    public function down()
    {
        Schema::table('global_invoice', function (Blueprint $table) {
            $table->dropColumn('created_by');
        });
    }
};
