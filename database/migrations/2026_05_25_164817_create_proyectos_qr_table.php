<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proyectos_qr', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->string('promotion_user', 24)->unique();
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proyectos_qr');
    }
};
