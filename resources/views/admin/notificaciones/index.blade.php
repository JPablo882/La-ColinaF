@extends('adminlte::page')

@section('content')
<div class="container">
    <h2>Configuración de Notificaciones</h2>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('admin.notificaciones.update') }}" method="POST">
        @csrf

        <div class="card p-4">

            <div class="form-check mb-3">
                <input class="form-check-input" type="radio"
                    name="modo_global"
                    value="todos"
                    {{ $config->valor === 'todos' ? 'checked' : '' }}>
                <label class="form-check-label">
                    Activadas para todos los clientes
                </label>
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="radio"
                    name="modo_global"
                    value="nadie"
                    {{ $config->valor === 'nadie' ? 'checked' : '' }}>
                <label class="form-check-label">
                    Desactivadas para todos
                </label>
            </div>

            <div class="form-check mb-4">
                <input class="form-check-input" type="radio"
                    name="modo_global"
                    value="seleccionados"
                    {{ $config->valor === 'seleccionados' ? 'checked' : '' }}>
                <label class="form-check-label">
                    Solo clientes seleccionados
                </label>
            </div>

            <button type="submit" class="btn btn-primary">
                Guardar configuración
            </button>

        </div>
    </form>
</div>
@endsection