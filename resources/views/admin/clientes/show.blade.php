@extends('adminlte::page')

@section('content_header')
    <h1><b>Clientes / Detalles del Cliente</b></h1>
    <hr>
@stop

@section('content')
    <div class="row">
        <div class="col-md-7">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Información del Cliente</h3>
                </div>
                
                <div class="card-body">
                    <div class="row">

                        {{-- === PROMOCIÓN ACTIVA === --}}
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Promoción</label>
                                <div class="custom-control custom-checkbox">
                                    <input
                                        type="checkbox"
                                        class="custom-control-input"
                                        id="promo_activa_show"
                                        {{ $cliente->promo_activa ? 'checked' : '' }}
                                        disabled
                                    >
                                    <label class="custom-control-label" for="promo_activa_show">
                                        Cliente con promoción activa
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- === NOMBRE === --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Codigo Cliente</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    </div>
                                    <input type="text" class="form-control" value="{{ $cliente->nombre }}" readonly>
                                </div>
                            </div>
                        </div>

                        {{-- === CELULAR === --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Celular</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fab fa-whatsapp"></i></span>
                                    </div>
                                    <input type="text" class="form-control" value="{{ $cliente->celular_real }}" readonly>
                                </div>
                            </div>
                        </div>


                        {{-- === DIRECCIÓN === --}}
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Descripción</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                    </div>
                                    <input type="text" class="form-control" value="{{ $cliente->direccion }}" readonly>
                                </div>
                            </div>
                        </div>

                        {{-- === UBICACIÓN GPS === --}}
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Ubicación GPS (Enlace de Google Maps)</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-globe-americas"></i></span>
                                    </div>
                                    <input type="text" class="form-control" value="{{ $cliente->ubicacion_gps ?? 'Sin registrar' }}" readonly>

                                </div>
                            </div>
                        </div>

                        {{-- === LATITUD Y LONGITUD === --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Latitud</label>
                                <input type="text" class="form-control" value="{{ $cliente->latitud ?? 'No disponible' }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Longitud</label>
                                <input type="text" class="form-control" value="{{ $cliente->longitud ?? 'No disponible' }}" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    <div class="text-center">
                        <a href="{{ route('admin.clientes.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>


        {{-- MAPA (SOLO VISUALIZACIÓN) --}}
        <div class="col-md-5">
            <div class="card card-outline card-info">
                <div class="card-header py-2">
                    <strong>Ubicación del Cliente</strong>
                </div>

                <div class="card-body p-0">
                    @if($cliente->latitud && $cliente->longitud)
                        <iframe
                            src="https://www.google.com/maps?q={{ $cliente->latitud }},{{ $cliente->longitud }}&z=13&output=embed"
                            style="width:100%; aspect-ratio:1/1; border:0;"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    @else
                        <div class="text-center text-muted p-4">
                            No hay ubicación registrada
                        </div>
                    @endif
                </div>
            </div>
        </div>


    </div>

@stop

@section('css')
    <style>
        .form-control[readonly] {
            background-color: #f8f9fa;
            opacity: 1;
        }
        .input-group-text {
            background-color: #e9ecef;
        }
    </style>
@stop

@section('js')

@stop