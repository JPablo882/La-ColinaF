@extends('adminlte::page')

@section('title', 'Historial de cierres de venta')

@section('content')
<div class="container-fluid">

    <h4 class="mb-4">Historial de cierres diarios</h4>

    {{-- FILTROS --}}
    <form method="GET" class="card mb-4">
        <div class="card-body row g-3 align-items-end">

            <div class="col-md-4">
                <label>Distribuidor</label>
                <select name="distribuidor_id" class="form-control">
                    <option value="">— Todos —</option>
                    @foreach($distribuidores as $dist)
                        <option value="{{ $dist->id }}"
                            {{ $distribuidorId == $dist->id ? 'selected' : '' }}>
                            {{ $dist->nombres }} {{ $dist->apellidos }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label>Fecha</label>
                <input type="date" name="fecha" class="form-control" value="{{ $fecha }}">
            </div>

            <div class="col-md-2">
                <button class="btn btn-primary w-100">Filtrar</button>
            </div>

        </div>
    </form>

    {{-- TABLA --}}
    <div class="card">
        <div class="card-body table-responsive">

            <table class="table table-bordered table-sm">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Distribuidor</th>
                        <th>Ingreso bruto</th>
                        <th>Gastos</th>
                        <th>Efectivo entregado</th>
                        <th>Qr entregado</th>
                        <th>Detalle</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($cierres as $cierre)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($cierre->fecha)->format('d/m/Y') }}</td>
                        <td>{{ $cierre->motoquero->nombres }} {{ $cierre->motoquero->apellidos }}</td>
                        <td>{{ number_format($cierre->ingreso_bruto, 2) }}</td>
                        <td>{{ number_format($cierre->total_gastos_distribucion, 2) }}</td>
                        <td>{{ number_format($cierre->efectivo_entregado, 2) }}</td>
                        <td>{{ number_format($cierre->ingreso_qr, 2) }}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-info"
                                    data-toggle="modal"
                                    data-target="#detalleModal{{ $cierre->id }}">
                                Ver pedidos
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            No hay cierres registrados
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>

            {{ $cierres->withQueryString()->links() }}

        </div>
    </div>

</div>

{{-- ================= MODALES ================= --}}
@foreach($cierres as $cierre)
<div class="modal fade"
     id="detalleModal{{ $cierre->id }}"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    Pedidos entregados — {{ \Carbon\Carbon::parse($cierre->fecha)->format('d/m/Y') }}
                </h5>
                <button type="button" class="btn-close" data-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                @php
                    $pedidos = \App\Models\Pedido::with('cliente')
                        ->where('motoquero_id', $cierre->motoquero_id)
                        ->whereDate('updated_at', $cierre->fecha)
                        ->where('estado', 'Entregado')
                        ->orderBy('updated_at')
                        ->get();
                @endphp

                @if($pedidos->isEmpty())
                    <p class="text-muted text-center">
                        No hay pedidos entregados ese día.
                    </p>
                @else
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Total</th>
                                <th>Método de pago</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($pedidos as $pedido)
                            <tr>
                                <td>{{ $pedido->cliente->nombre ?? '—' }}</td>
                                <td>{{ number_format($pedido->total_precio, 2) }}</td>

                                <td>

                                    @if($pedido->metodo_pago == 'QR')

                                        <span class="badge badge-success">
                                            QR
                                        </span>

                                    @elseif($pedido->metodo_pago == 'Efectivo')

                                        <span class="badge badge-primary">
                                            Efectivo
                                        </span>

                                    @else

                                        <span class="badge badge-secondary">
                                            No definido
                                        </span>

                                    @endif

                                </td>

                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif

            </div>
        </div>
    </div>
</div>
@endforeach

@endsection