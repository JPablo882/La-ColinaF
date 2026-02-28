<?php

namespace App\Http\Controllers;

use App\Models\AvisoNavegacion;
use App\Models\Pedido;
use App\Models\Notificacion;
use Illuminate\Http\Request;

class AvisoNavegacionController extends Controller
{
    /**
     * Crear aviso cuando el motoquero inicia navegación
     */
    public function crear(Request $request)
    {
        $request->validate([
            'pedido_id'    => 'required|integer',
            'cliente'      => 'required|string',
            'celular'      => 'nullable|string',
            'motoquero_id' => 'nullable|integer'
        ]);

        // Buscar pedido con cliente
        $pedido = Pedido::with('cliente')->find($request->pedido_id);

        if (!$pedido) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'Pedido no encontrado'
            ]);
        }

        // 🔥 Validación centralizada en modelo Notificacion
        if (!Notificacion::debeEnviarInicio($pedido)) {
            return response()->json(['ok' => false]);
        }

        return $this->crearAviso($request);
    }

    /**
     * POLL para administrador
     */
    public function poll()
    {
        $aviso = AvisoNavegacion::with('pedido.cliente')
            ->where('estado', 'pendiente')
            ->latest()
            ->first();

        if (!$aviso) {
            return response()->json([]);
        }

        return response()->json($aviso);
    }

    /**
     * ADMIN acepta el aviso
     */
    public function atender($id)
    {
        $aviso = AvisoNavegacion::findOrFail($id);
        $aviso->estado = 'atendido';
        $aviso->save();

        return response()->json(['ok' => true]);
    }

    /**
     * ADMIN cierra sin atender
     */
    public function cerrar($id)
    {
        $aviso = AvisoNavegacion::findOrFail($id);
        $aviso->estado = 'cerrado';
        $aviso->save();

        return response()->json(['ok' => true]);
    }

    /**
     * Crear registro de aviso
     */
    private function crearAviso($request)
    {
        $aviso = AvisoNavegacion::create([
            'pedido_id'    => $request->pedido_id,
            'cliente'      => $request->cliente,
            'celular'      => $request->celular ?: '0000000000',
            'motoquero_id' => $request->motoquero_id,
            'estado'       => 'pendiente',
        ]);

        return response()->json([
            'ok' => true,
            'aviso' => $aviso
        ]);
    }
}