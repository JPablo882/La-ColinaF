<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DespachoRepartidor;
use App\Models\Motoquero;

class DespachoRepartidorController extends Controller
{
    /**
     * Registrar nuevo despacho
     */
    public function store(Request $request)
    {
        $request->validate([
            'motoquero_id' => 'required|exists:motoqueros,id',
            'botellones_regular' => 'required|integer|min:0',
            'botellones_alcalina' => 'required|integer|min:0',
            'dispensers' => 'required|integer|min:0',
        ]);

        // OPCIONAL: evitar más de un despacho por día
        $existeHoy = DespachoRepartidor::where('motoquero_id', $request->motoquero_id)
            ->whereDate('created_at', today())
            ->exists();

        if ($existeHoy) {
            return back()->with('error', 'Este repartidor ya tiene despacho registrado hoy.');
        }

        DespachoRepartidor::create([
            'motoquero_id' => $request->motoquero_id,
            'botellones_regular' => $request->botellones_regular,
            'botellones_alcalina' => $request->botellones_alcalina,
            'dispensers' => $request->dispensers,
        ]);

        return back()->with('success', 'Despacho registrado correctamente.');
    }
}