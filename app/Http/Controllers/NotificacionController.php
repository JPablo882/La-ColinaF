<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{


public function index()
{
    $config = Notificacion::where('clave', 'inicio_navegacion_modo')->first();

    if (!$config) {
        $config = Notificacion::create([
            'clave' => 'inicio_navegacion_modo',
            'valor' => 'nadie'
        ]);
    }

    return view('admin.notificaciones.index', compact('config'));
}

public function update(Request $request)
{
    $request->validate([
        'modo_global' => 'required|in:todos,nadie,seleccionados'
    ]);

    $config = Notificacion::where('clave', 'inicio_navegacion_modo')->first();

    if (!$config) {
        $config = new Notificacion();
        $config->clave = 'inicio_navegacion_modo';
    }

    $config->valor = $request->modo_global;
    $config->save();

    return redirect()->back()->with('success', 'Configuración actualizada');
}

}
