<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;

class ProductoController extends Controller
{

    // LISTAR PRODUCTOS
    public function index()
    {
        $productos = Producto::all();
        return view('admin.productos.index', compact('productos'));
    }


    // FORMULARIO CREAR PRODUCTO
    public function create()
    {
        return view('admin.productos.create');
    }


    // GUARDAR PRODUCTO
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0',
        ]);

        Producto::create([
            'nombre' => $request->nombre,
            'precio' => $request->precio,
        ]);

        return redirect()
            ->route('admin.productos.index')
            ->with('success', 'Producto creado correctamente.');
    }


    // FORMULARIO EDITAR PRODUCTO
    public function edit(Producto $producto)
    {
        return view('admin.productos.edit', compact('producto'));
    }


    // ACTUALIZAR PRODUCTO
    public function update(Request $request, Producto $producto)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0',
        ]);

        $producto->update([
            'nombre' => $request->nombre,
            'precio' => $request->precio,
        ]);

        return redirect()
            ->route('admin.productos.index')
            ->with('success', 'Producto actualizado correctamente.');
    }


    // ELIMINAR PRODUCTO
    public function destroy(Producto $producto)
    {
        $producto->delete();

        return redirect()
            ->route('admin.productos.index')
            ->with('success', 'Producto eliminado correctamente.');
    }

}