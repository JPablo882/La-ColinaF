@extends('adminlte::page')

@section('title', 'Crear pedidos')

@section('content_header')
    <h1><b>Crear pedidos (por asignar)</b></h1>
@stop

@section('content')

<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- ================= MAPA ================= --}}
<div class="card card-outline card-success mb-3">
    <div class="card-header">
        <h3 class="card-title"><b>Mapa de clientes seleccionados</b></h3>
    </div>
    <div class="card-body">
        <div id="mapaClientes" style="height:300px; border-radius:8px;"></div>
    </div>
</div>

{{-- ================= TABLA CLIENTES ================= --}}
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">Listado de clientes</h3>
    </div>

    <div class="card-body">

        <table id="tablaClientes" class="table table-bordered table-striped table-sm">
            <thead>
                <tr>
                    <th style="text-align:center">#</th>
                    <th>Cliente</th>
                    <th>Celular</th>
                    <th>Dirección</th>
                    <th>Ubicación GPS</th>
                    <th>Lat</th>
                    <th>Lng</th>
                    <th style="text-align:center">Seleccionar</th>
                </tr>
            </thead>

            <tbody>
                @foreach($clientes as $i => $cliente)
                <tr>
                    <td style="text-align:center">{{ $i + 1 }}</td>
                    <td>{{ $cliente->nombre }}</td>
                    <td>{{ $cliente->celular_real }}</td>
                    <td>{{ $cliente->direccion }}</td>

                    <td>
                        @if($cliente->ubicacion_gps)
                            <a href="{{ $cliente->ubicacion_gps }}" target="_blank">Ver</a>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>

                    <td>{{ $cliente->latitud ?? '—' }}</td>
                    <td>{{ $cliente->longitud ?? '—' }}</td>

                    {{-- CHECKBOX + SELECTORES --}}
                    <td style="text-align:center; width:240px">

                        <input type="checkbox"
                            class="chk-cliente"
                            data-id="{{ $cliente->id }}"
                            data-lat="{{ $cliente->latitud }}"
                            data-lng="{{ $cliente->longitud }}"
                        >

                        <div class="selectores d-none mt-2">

                            <select class="form-control form-control-sm mb-1 motoquero">
                                <option value="">Distribuidor</option>
                                @foreach($motoqueros as $mq)
                                    <option value="{{ $mq->id }}">
                                        {{ $mq->apellidos }} {{ $mq->nombres }}
                                    </option>
                                @endforeach
                            </select>

                            <select class="form-control form-control-sm ruta">
                                <option value="">Ruta</option>
                                <option value="A">Ruta A</option>
                                <option value="B">Ruta B</option>
                                <option value="C">Ruta C</option>
                                <option value="D">Ruta D</option>
                            </select>

                        </div>

                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- BOTÓN ÚNICO --}}
        <div class="text-right mt-3">
            <button id="btnCrearPedidos" class="btn btn-success">
                <i class="fas fa-plus"></i> Crear pedido(s)
            </button>
        </div>

    </div>
</div>

@stop

{{-- ================= CSS ================= --}}
@section('css')

<style>
.selectores select {
    font-size: 0.8rem;
}
</style>
@stop

{{-- ================= JS ================= --}}
@section('js')

<script async
    src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&callback=initMap&loading=async">
</script>

<script>
let map;

let markers = [];

function initMap() {

    map = new google.maps.Map(document.getElementById("mapaClientes"), {
        center: { lat: -17.7833, lng: -63.1821 }, // Santa Cruz
        zoom: 12,
    });
   
}

$(document).ready(function () {

    // DataTable
    if ($.fn.DataTable) {
        $('#tablaClientes').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
            },
            pageLength: 10,
            responsive: true
        });
    }

    // Checkbox cliente
    $(document).on('change', '.chk-cliente', function () {

        const contenedor = $(this).closest('td');
        const selectores = contenedor.find('.selectores');

        const lat = parseFloat($(this).data('lat'));
        const lng = parseFloat($(this).data('lng'));

        if (this.checked) {
            selectores.removeClass('d-none');

            if (lat && lng) {

                const marker = new google.maps.Marker({
                    position: { lat: lat, lng: lng },
                    map: map
                });

                markers.push(marker);

                map.setCenter({ lat: lat, lng: lng });
                map.setZoom(15);
            }

        } else {

            selectores.addClass('d-none');
            selectores.find('select').val('');
            limpiarMapa();
        }
    });

    // Crear pedidos (NO se toca)
    $('#btnCrearPedidos').on('click', function () {

        let pedidos = [];

        $('.chk-cliente:checked').each(function () {

            const td = $(this).closest('td');

            const cliente_id = $(this).data('id');
            const motoquero_id = td.find('.motoquero').val();
            const ruta = td.find('.ruta').val();

            if (!motoquero_id || !ruta) {
                Swal.fire(
                    'Atención',
                    'Todos los pedidos deben tener motoquero y ruta',
                    'warning'
                );
                pedidos = [];
                return false;
            }

            pedidos.push({
                cliente_id: cliente_id,
                motoquero_id: motoquero_id,
                ruta: ruta
            });
        });

        if (pedidos.length === 0) return;

        $.ajax({
            url: "{{ url('/admin/pedidos/mover-por-asignar') }}",
            type: "POST",
            data: {
                pedidos: pedidos,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function () {
                Swal.fire({
                    icon: 'success',
                    title: 'Pedidos creados correctamente',
                    timer: 1000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = "{{ route('admin.pedidos.index') }}";
                });
            },
            error: function (xhr) {
                Swal.fire('Error', xhr.responseText, 'error');
            }
        });

    });

});

// Limpia marcadores
function limpiarMapa() {
    markers.forEach(m => m.setMap(null));
    markers = [];
}
</script>


@stop