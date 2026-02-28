<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();

            $table->string('nombre', 100);
            $table->string('ci', 20)->nullable(); // CI (Cédula de Identidad)
            $table->string('celular', 15)->nullable()->unique();
            $table->string('referencia_celular', 15)->nullable(); // Celular alternativo
            $table->string('direccion', 200);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
