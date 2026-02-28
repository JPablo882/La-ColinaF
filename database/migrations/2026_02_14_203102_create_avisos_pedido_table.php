<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avisos_pedido', function (Blueprint $table) {

            $table->id();

            // Pedido relacionado
            $table->foreignId('pedido_id')
                ->constrained('pedidos')
                ->onDelete('cascade');

            // Motero que recibirá el aviso
            $table->foreignId('motoquero_id')
                ->constrained('motoqueros')
                ->onDelete('cascade');

            // Tipo de aviso
            $table->enum('tipo', ['ya_sale', 'no_contesta']);

            // Control de lectura
            $table->boolean('leido')->default(false);

            $table->timestamps();

            // Índices para que el polling sea rápido
            $table->index(['motoquero_id', 'leido']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avisos_pedido');
    }
};
