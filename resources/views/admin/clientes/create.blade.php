@extends('adminlte::page')

@section('content_header')
    <h1><b>Clientes / Registro de un nuevo cliente</b></h1>
    <hr>
@stop

@section('content')
<div class="row">
    <div class="col-md-7">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Complete los datos del formulario</h3>
            </div>

            @if(isset($ultimoCliente) && $ultimoCliente)
                <div class="alert alert-info py-2 m-3">
                    <strong>Último cliente registrado:</strong>
                    {{ $ultimoCliente->nombre }}
                </div>
            @endif

            <form action="{{ route('admin.clientes.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="row">

                        {{-- CÓDIGO CLIENTE --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Código Cliente</label><span class="text-danger">*</span>
                                <input type="text"
                                       id="nombre"
                                       class="form-control"
                                       name="nombre"
                                       value="{{ old('nombre', 'LCC ', request('nombre')) }}"
                                      
                                       required>
                                <small id="nombre_feedback" class="text-danger d-none"></small>
                            </div>
                        </div>


                        <input type="hidden" name="cliente_padre_id" id="cliente_padre_id">
                        <div id="extension_container" style="display:none;">
                            <label>Extensión de ubicación</label>
                            <input type="text" class="form-control" name="extension" id="extension"
                                placeholder="Ej: Casa, Trabajo, Oficina">
                        </div>


                        {{-- CELULAR INTERNACIONAL --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Celular (Formato E.164)</label><span class="text-danger">*</span>
                                <input type="tel"
                                       id="celular"
                                       class="form-control"
                                       name="celular"
                                       value="{{ old('celular') }}"
                                       placeholder="Ej: 59178432026 | 5511999999999"
                                       required>
                                <small class="form-text text-muted">
                                    Ingrese el número con código de país. Solo números. (10–15 dígitos)
                                </small>
                                <small id="celular_feedback" class="text-danger d-none"></small>
                            </div>
                        </div>

                        {{-- DIRECCIÓN --}}
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Descripción</label><span class="text-danger">*</span>
                                <input type="text"
                                       class="form-control"
                                       name="direccion"
                                       value="{{ old('direccion', '| # | Botellon normal | Sale afuera |') }}"
                                       placeholder="Ej: | Calle, #Casa|Sin Agarrador|Sale Afuera|Confirmar:no| "
                                       required>
                            </div>
                        </div>

                        {{-- UBICACIÓN GPS --}}
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Ubicación GPS (Google Maps)</label>
                                <input type="text"
                                       class="form-control"
                                       id="ubicacion_gps"
                                       name="ubicacion_gps"
                                       value="{{ old('ubicacion_gps') }}"
                                       placeholder="Ej: https://maps.google.com/?q=-17.7833,-63.1821">
                                <small class="form-text text-muted">
                                    Pegue el enlace de Google Maps.
                                </small>
                            </div>
                        </div>

                        {{-- LAT --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Latitud</label>
                                <input type="text"
                                       class="form-control"
                                       id="latitud"
                                       name="latitud"
                                       value="{{ old('latitud') }}"
                                       placeholder="Ej: -17.7833">
                            </div>
                        </div>

                        {{-- LNG --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Longitud</label>
                                <input type="text"
                                       class="form-control"
                                       id="longitud"
                                       name="longitud"
                                       value="{{ old('longitud') }}"
                                       placeholder="Ej: -63.1821">
                            </div>
                        </div>

                    </div>
                </div>

                <div class="card-footer">
                    <a href="{{ route('admin.clientes.index') }}" class="btn btn-secondary">
                        Cancelar
                    </a>
                    <button type="submit" id="btnGuardar" class="btn btn-primary" disabled>
                        Guardar Cliente
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
                <iframe id="mapa_preview"
                        style="width:100%; aspect-ratio:1/1; border:0; display:none;"
                        loading="lazy"></iframe>

                <div id="mapa_placeholder" class="text-center text-muted p-4">
                    Pegue un enlace o ingrese latitud y longitud
                </div>
            </div>
        </div>
    </div>
</div>
@stop


@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const params = new URLSearchParams(window.location.search);
    const nombreURL = params.get('nombre');

    if (nombreURL) {
        const nombreInput = document.getElementById('nombre');
        nombreInput.value = "LCC " + nombreURL;
    }

    const nombreInput = document.getElementById('nombre');
    const celularInput = document.getElementById('celular');
    const nombreFeedback = document.getElementById('nombre_feedback');
    const celularFeedback = document.getElementById('celular_feedback');
    const btnGuardar = document.getElementById('btnGuardar');

    const inputUbicacion = document.getElementById('ubicacion_gps');
    const latInput = document.getElementById('latitud');
    const lngInput = document.getElementById('longitud');
    const mapa = document.getElementById('mapa_preview');
    const placeholder = document.getElementById('mapa_placeholder');

    const extensionInput = document.getElementById('extension');

    let nombreValido = false;
    let celularValido = false;


    function controlarBoton() {

        const tienePadre = document.getElementById('cliente_padre_id').value;

        // 🔵 Si es cliente hijo, exigir extensión
        if (tienePadre) {

            const extension = extensionInput.value.trim();

            btnGuardar.disabled = !(
                nombreValido &&
                celularValido &&
                extension.length > 0
            );

        } else {

            // 🔵 Cliente normal
            btnGuardar.disabled = !(nombreValido && celularValido);

        }
    }


    /* =========================
       LIMPIAR Y VALIDAR CELULAR
    ========================== */

    function limpiarNumero(valor) {
        return valor.replace(/\D/g, '');
    }

    function validarFormatoInternacional(numero) {
        return numero.length >= 10 && numero.length <= 15;
    }

    celularInput.addEventListener('input', function () {
        let limpio = limpiarNumero(this.value);
        this.value = limpio;

        if (!limpio) {
            celularValido = false;
            controlarBoton();
            return;
        }

        if (!validarFormatoInternacional(limpio)) {
            celularInput.classList.add('is-invalid');
            celularFeedback.classList.remove('d-none');
            celularFeedback.textContent = "Debe tener entre 10 y 15 dígitos e incluir código de país.";
            celularValido = false;
        } else {
            celularInput.classList.remove('is-invalid');
            celularInput.classList.add('is-valid');
            celularFeedback.classList.add('d-none');
            celularValido = true;
        }

        controlarBoton();
    });

    /* =========================
       VALIDACIÓN DUPLICADOS
    ========================== */

    function validarCampo(campo, valor, input, feedback) {

        if (!valor.trim()) return;

        fetch("{{ route('admin.clientes.validar') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            body: JSON.stringify({ campo, valor })
        })
        .then(res => res.json())
        .then(data => {

            if (data.existe) {
                input.classList.remove('is-valid');
                input.classList.add('is-invalid');
                feedback.classList.remove('d-none');
                feedback.textContent = campo === 'nombre'
                    ? 'Este código ya está registrado.'
                    : 'Este número ya está registrado.';

                if (campo === 'nombre') nombreValido = false;
                if (campo === 'celular') celularValido = false;

            } else {
                input.classList.remove('is-invalid');
                input.classList.add('is-valid');
                feedback.classList.add('d-none');

                if (campo === 'nombre') nombreValido = true;
                if (campo === 'celular') celularValido = true;
            }

            controlarBoton();
        });
    }

    nombreInput.addEventListener('blur', function () {
        validarCampo('nombre', this.value, nombreInput, nombreFeedback);
    });


    celularInput.addEventListener('blur', function () {

        if (!celularValido) return;

        let limpio = this.value.replace(/\D/g, '');

        fetch("{{ route('admin.clientes.buscarCelular') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            body: JSON.stringify({ celular: limpio })
        })
        .then(res => res.json())
        .then(data => {

            if (data.existe) {

                Swal.fire({
                    title: 'Cliente ya registrado',
                    text: `Ya existe ${data.cliente.nombre}. ¿Desea agregar nueva ubicación?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí',
                    cancelButtonText: 'No'
                }).then(result => {

            if (result.isConfirmed) {

                // Guardar ID del padre
                document.getElementById('cliente_padre_id').value = data.cliente.id;

                // Bloquear campos
                nombreInput.value = data.cliente.nombre;
                nombreInput.readOnly = true;
                celularInput.readOnly = true;

                // ⚠️ No enviar celular en el submit
                celularInput.removeAttribute('name');

                // 🔵 LIMPIAR ERRORES VISUALES
                nombreInput.classList.remove('is-invalid');
                celularInput.classList.remove('is-invalid');

                nombreInput.classList.add('is-valid');
                celularInput.classList.add('is-valid');

                nombreFeedback.classList.add('d-none');
                celularFeedback.classList.add('d-none');

                // Mostrar extensión
                document.getElementById('extension_container').style.display = 'block';

                // Ahora el botón dependerá SOLO de la extensión
                nombreValido = true;
                celularValido = true;
                // Forzar deshabilitado hasta que escriba extensión
                btnGuardar.disabled = true;

                controlarBoton();
            }
                });

            } else {
                // Si no existe, marcar como válido normal
                celularInput.classList.remove('is-invalid');
                celularInput.classList.add('is-valid');
                celularFeedback.classList.add('d-none');
                celularValido = true;
                controlarBoton();
            }
        });
    });

    /* =========================
       MAPA Y EXTRACCIÓN
    ========================== */

    function mostrarMapa(lat, lng) {
        if (!lat || !lng) return;
        const embedUrl = `https://www.google.com/maps?q=${lat},${lng}&z=15&output=embed`;
        mapa.src = embedUrl;
        mapa.style.display = 'block';
        placeholder.style.display = 'none';
    }

    inputUbicacion.addEventListener('change', function () {

        const url = this.value.trim();
        if (!url) return;

        Swal.fire({
            title: 'Extrayendo latitud y longitud...',
            allowOutsideClick: false,
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
                Swal.fire('Error', 'No se pudo extraer la ubicación.', 'error');
                return;
            }

            latInput.value = data.lat;
            lngInput.value = data.lng;

            mostrarMapa(data.lat, data.lng);

            Swal.fire('Correcto', 'Ubicación obtenida correctamente.', 'success');
        })
        .catch(() => {
            Swal.fire('Error', 'No se pudo procesar el enlace.', 'error');
        });
    });


    extensionInput.addEventListener('input', function() {

        controlarBoton();
    });


    latInput.addEventListener('change', () => mostrarMapa(latInput.value, lngInput.value));
    lngInput.addEventListener('change', () => mostrarMapa(latInput.value, lngInput.value));

});
</script>
@stop