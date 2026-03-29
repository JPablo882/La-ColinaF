@extends('adminlte::page')

@section('content_header')
    <h1><b>Clientes / Editar Cliente</b></h1>
    <hr>
@stop

@section('content')
<div class="row">
    <div class="col-md-7">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title">Editar datos del cliente</h3>
            </div>
            
            <form action="{{ route('admin.clientes.update', $cliente->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="card-body">
                    <div class="row">

                        {{-- === PROMOCIÓN ACTIVA === --}}
                        <div class="col-md-12">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input
                                        type="checkbox"
                                        class="custom-control-input"
                                        id="promo_activa"
                                        name="promo_activa"
                                        value="1"
                                        {{ old('promo_activa', $cliente->promo_activa) ? 'checked' : '' }}
                                    >
                                    <label class="custom-control-label" for="promo_activa">
                                        Cliente con promoción activa
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- === NOMBRE === --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Codigo Cliente</label><span class="text-danger">*</span>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    </div>
                                    <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                                           name="nombre" value="{{ old('nombre', $cliente->nombre) }}" required>
                                </div>
                                @error('nombre')
                                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- === CELULAR === --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Celular</label><span class="text-danger">*</span>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fab fa-whatsapp"></i></span>
                                    </div>
                                    <input type="tel" 
                                        class="form-control @error('celular') is-invalid @enderror" 
                                        name="celular"
                                        value="{{ old('celular', $cliente->celular_real) }}"
                                        @if($cliente->cliente_padre_id !== null) readonly @endif
                                        @if($cliente->cliente_padre_id === null) required @endif
                                    >
                                </div>
                                @error('celular')
                                    @if($cliente->cliente_padre_id === null)
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @endif
                                @enderror
                            </div>
                        </div>



                        {{-- === DIRECCIÓN === --}}
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Descripción</label><span class="text-danger">*</span>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                    </div>
                                    <input type="text" class="form-control @error('direccion') is-invalid @enderror" 
                                           name="direccion" value="{{ old('direccion', $cliente->direccion) }}" required>
                                </div>
                                @error('direccion')
                                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                                @enderror
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
                                    <input type="text" class="form-control @error('ubicacion_gps') is-invalid @enderror" 
                                           id="ubicacion_gps" name="ubicacion_gps" 
                                           value="{{ old('ubicacion_gps', $cliente->ubicacion_gps) }}">
                                    
                                </div>
                                @error('ubicacion_gps')
                                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted">
                                    Si se ingresa un enlace de Google Maps, se intentarán extraer latitud y longitud automáticamente.
                                </small>
                            </div>
                        </div>

                        {{-- === LATITUD === --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Latitud</label>
                                <input type="text" class="form-control" id="latitud" name="latitud" 
                                       value="{{ old('latitud', $cliente->latitud) }}">
                            </div>
                        </div>

                        {{-- === LONGITUD === --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Longitud</label>
                                <input type="text" class="form-control" id="longitud" name="longitud" 
                                       value="{{ old('longitud', $cliente->longitud) }}">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer">
                    <a href="{{ route('admin.clientes.show', $cliente->id) }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Actualizar Cliente
                    </button>
                </div>
            </form>
        </div>
    </div>


    {{-- MAPA --}}
    <div class="col-md-5">
        <div class="card card-outline card-info">
            <div class="card-header py-2">
                <strong>Vista previa del mapa</strong>
            </div>

            <div class="card-body p-0">
                <iframe
                    id="mapa_preview"
                    style="width:100%; aspect-ratio:1/1; border:0; display:none;"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>

                <div id="mapa_placeholder" class="text-center text-muted p-4">
                    Pegue un enlace de Google Maps<br>
                    o ingrese latitud y longitud
                </div>
            </div>
        </div>
    </div>


</div>


@stop

@section('css')
<style>
    .input-group-text { background-color: #f8f9fa; }
</style>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const inputUbicacion = document.getElementById('ubicacion_gps');
    const latInput = document.getElementById('latitud');
    const lngInput = document.getElementById('longitud');

    const mapa = document.getElementById('mapa_preview');
    const placeholder = document.getElementById('mapa_placeholder');

    function mostrarMapa(lat, lng) {
        if (!lat || !lng) return;

        const embedUrl = `https://www.google.com/maps?q=${lat},${lng}&z=13&output=embed`;

        mapa.src = embedUrl;
        mapa.style.display = 'block';
        placeholder.style.display = 'none';
    }

    // 🔹 MOSTRAR MAPA AL CARGAR LA VISTA (SI YA EXISTE)
    if (latInput.value && lngInput.value) {
        mostrarMapa(latInput.value, lngInput.value);
    }

    // 🔹 CUANDO PEGAN UN LINK NUEVO
    if (inputUbicacion) {
        inputUbicacion.addEventListener('change', function () {

            const url = this.value.trim();
            if (!url) return;

            Swal.fire({
                title: 'Extrayendo ubicación…',
                text: 'Por favor espere un momento',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => Swal.showLoading()
            });

            fetch("{{ route('admin.clientes.coords') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                body: JSON.stringify({ url })
            })
            .then(res => res.json())
            .then(data => {

                if (!data.lat || !data.lng) {
                    Swal.fire({
                        icon: 'error',
                        title: 'No se pudo extraer la ubicación',
                        text: 'Pegue otro enlace o ingrese latitud y longitud manualmente.',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                latInput.value = data.lat;
                lngInput.value = data.lng;

                mostrarMapa(data.lat, data.lng);

                Swal.fire({
                    icon: 'success',
                    title: 'Ubicación obtenida',
                    text: 'Mapa actualizado correctamente.',
                    confirmButtonText: 'OK'
                });
            })
            .catch(() => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error inesperado',
                    text: 'No se pudo procesar el enlace.',
                    confirmButtonText: 'OK'
                });
            });
        });
    }

    // 🔹 SI EDITAN LAT/LNG MANUALMENTE
    function actualizarDesdeInputs() {
        const lat = latInput.value.trim();
        const lng = lngInput.value.trim();
        if (lat && lng) {
            mostrarMapa(lat, lng);
        }
    }

    latInput.addEventListener('change', actualizarDesdeInputs);
    lngInput.addEventListener('change', actualizarDesdeInputs);

});
</script>
@stop