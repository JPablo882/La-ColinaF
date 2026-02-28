<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {

            $table->unsignedBigInteger('cliente_padre_id')
                  ->nullable()
                  ->after('id');

            $table->foreign('cliente_padre_id')
                  ->references('id')
                  ->on('clientes')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {

            $table->dropForeign(['cliente_padre_id']);
            $table->dropColumn('cliente_padre_id');
        });
    }
};