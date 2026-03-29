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
        Schema::table('pedidos', function (Blueprint $table) {

            // 🔹 Estado de pago QR
            // null = no pagado
            // distribuidor = pagado al motoquero
            // central = pagado a la empresa
            $table->string('qr_pago_estado')
                  ->nullable()
                  ->after('metodo_pago')
                  ->comment('Estado del pago QR: distribuidor o central');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {

            $table->dropColumn('qr_pago_estado');

        });
    }
};