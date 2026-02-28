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
        Schema::create('despachos_repartidores', function (Blueprint $table) {
            $table->id();

            // Relación con motoquero
            $table->foreignId('motoquero_id')
                  ->constrained('motoqueros')
                  ->onDelete('cascade');

            // Cantidades despachadas
            $table->integer('botellones_regular')->default(0);
            $table->integer('botellones_alcalina')->default(0);
            $table->integer('dispensers')->default(0);

            $table->timestamps(); // created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('despachos_repartidores');
    }
};