<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    protected $table = 'notificaciones';

    protected $fillable = [
        'clave',
        'valor'
    ];

    public static function obtenerModoInicio()
    {
        $registro = self::where('clave', 'inicio_navegacion_modo')->first();

        return $registro ? $registro->valor : 'nadie';
    }

    public static function debeEnviarInicio($pedido)
    {
        $modo = self::obtenerModoInicio();

        // 🔴 NADIE
        if ($modo === 'nadie') {
            return false;
        }

        // 🔵 TODOS
        if ($modo === 'todos') {
            return true;
        }

        // 🟡 SELECCIONADOS
        if ($modo === 'seleccionados') {

            if ($pedido->cliente && $pedido->cliente->inicio_navegacion_siempre) {
                return true;
            }

            if ($pedido->inicio_navegacion_este_pedido) {
                return true;
            }

            return false;
        }

        return false;
    }
}