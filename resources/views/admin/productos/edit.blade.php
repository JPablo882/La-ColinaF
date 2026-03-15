@extends('adminlte::page')

@section('title', 'Editar Producto')

@section('content_header')
    <h1><b>Editar Producto</b></h1>
    <hr>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.productos.update', $producto->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group mb-3">
                    <label for="nombre">Nombre del Producto</label>
                    <input type="text" name="nombre" id="nombre" class="form-control" required
                        value="{{ $producto->nombre }}">
                </div>

                <div class="form-group mb-3">
                    <label for="precio">Precio (Bs)</label>
                    <input type="number" step="0.01" name="precio" id="precio" class="form-control" required
                        value="{{ $producto->precio }}">
                </div>

                <button type="submit" class="btn btn-primary">💾 Actualizar</button>
                <a href="{{ route('admin.productos.index') }}" class="btn btn-secondary">↩️ Volver</a>
            </form>
        </div>
    </div>
@stop