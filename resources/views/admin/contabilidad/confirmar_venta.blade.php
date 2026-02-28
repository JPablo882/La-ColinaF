@extends('adminlte::page')

@section('title', 'Confirmar venta')

@section('content')
<div class="container-fluid">

    <h4 class="mb-4">Confirmación de venta diaria</h4>

    
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    

    {{-- =======================
        SELECTOR DISTRIBUIDOR / FECHA
    ======================= --}}
    <form method="GET" action="{{ route('admin.contabilidad.confirmar_venta.create') }}" class="card mb-4">
        <div class="card-body row g-3 align-items-end">

            <div class="col-md-4">
                <label class="form-label">Distribuidor</label>
                <select name="distribuidor_id" class="form-control" required>
                    <option value="">— Seleccionar —</option>
                    @foreach($distribuidores as $dist)
                        <option value="{{ $dist->id }}"
                            {{ ($distribuidorId ?? '' ) == $dist->id ? 'selected' : '' }}>
                            {{ $dist->nombres }} {{ $dist->apellidos }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Fecha</label>
                <input type="date"
                       name="fecha"
                       class="form-control"
                       value="{{ $fecha ?? now()->toDateString() }}"
                       required>
            </div>

            <div class="col-md-2">
                <button class="btn btn-primary w-100">
                    Buscar
                </button>
            </div>

        </div>
    </form>

    @if(isset($pedidos))

    {{-- =======================
        RESUMEN DE PEDIDOS
    ======================= --}}
    <div class="card mb-4">
        <div class="card-body table-responsive">

            <h5 class="mb-3">Pedidos del día</h5>

            <table class="table table-bordered table-sm">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Total</th>
                        <th>Hora</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pedidos as $pedido)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>#{{ $pedido->cliente->nombre ?? 'Sin cliente' }}</td>
                            <td>{{ number_format($pedido->total_precio, 2) }}</td>
                            <td>{{ $pedido->updated_at->format('H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                No hay pedidos para esta fecha
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="row text-end">
                <div class="col-md-4">
                    <strong>Total efectivo:</strong><br>
                    {{ number_format($ingresoEfectivo, 2) }} Bs
                </div>
                <div class="col-md-4">
                    <strong>Total QR:</strong><br>
                    {{ number_format($ingresoQR, 2) }} Bs
                </div>
                <div class="col-md-4">
                    <strong>Ingreso bruto:</strong><br>
                    {{ number_format($ingresoBruto, 2) }} Bs
                </div>
            </div>

        </div>
    </div>


    @if($cierreExistente)
        <div class="alert alert-success">
            ✅ La venta de este distribuidor para esta fecha ya fue confirmada.
        </div>
    @endif


    {{-- =======================
        CONFIRMACIÓN + GASTOS
    ======================= --}}
    <form method="POST" action="{{ route('admin.contabilidad.confirmar_venta.store') }}" class="card">
        @csrf

        <input type="hidden" name="fecha" value="{{ $fecha }}">
        <input type="hidden" name="distribuidor_id" value="{{ $distribuidorId }}">
        <input type="hidden" name="ingreso_bruto" value="{{ $ingresoBruto }}">
        <input type="hidden" name="ingreso_efectivo" value="{{ $ingresoEfectivo }}">
        <input type="hidden" name="ingreso_qr" value="{{ $ingresoQR }}">

        <div class="card-body">

            <h5 class="mb-3">Gastos de distribución</h5>

            <table class="table table-bordered" id="tabla-gastos">
                <thead class="table-light">
                    <tr>
                        <th>Concepto</th>
                        <th width="200">Monto</th>
                        <th width="60"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <input type="text"
                                   name="gastos[0][concepto]"
                                   class="form-control"
                                   placeholder="Ej: combustible">
                        </td>
                        <td>
                            <input type="number"
                                   step="0.01"
                                   name="gastos[0][monto]"
                                   class="form-control monto-gasto"
                                   value="0">
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-danger eliminar-fila">
                                ✕
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <button type="button" class="btn btn-sm btn-secondary mb-3" id="agregar-gasto">
                + Agregar gasto
            </button>



            <div class="row">

                {{-- ================= BOTELLONES ================= --}}
                <div class="col-md-6">

                    <div class="card border-primary">
                        <div class="card-body">

                            <h6 class="mb-3 text-primary"><b>Control de Botellones</b></h6>

                            <table class="table table-sm table-bordered text-center">
                                <thead class="table-light">
                                    <tr>
                                        <th></th>
                                        <th>Regular</th>
                                        <th>Alcalina</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><b>Despachado</b></td>
                                        <td>{{ $regularDespachado }}</td>
                                        <td>{{ $alcalinaDespachado }}</td>
                                    </tr>

                                    <tr class="table-warning">
                                        <td><b>Vendido</b></td>
                                        <td>{{ $vendidosRegular }}</td>
                                        <td>{{ $vendidosAlcalina }}</td>
                                    </tr>

                                    <tr class="{{ ($restanteRegular < 0 || $restanteAlcalina < 0) ? 'table-danger' : 'table-success' }}">
                                        <td><b>Restante</b></td>
                                        <td>{{ $restanteRegular }}</td>
                                        <td>{{ $restanteAlcalina }}</td>
                                    </tr>
                                </tbody>
                            </table>

                        </div>
                    </div>

                </div>


                {{-- ================= FINANZAS ================= --}}
                <div class="col-md-6 text-end">

                    <p>
                        <strong>Total gastos:</strong>
                        <span id="total-gastos">0.00</span> Bs
                    </p>

                    <p>
                        <strong>Efectivo a entregar:</strong>
                        <span id="efectivo-a-entregar" class="text-success">
                            {{ number_format($ingresoEfectivo, 2) }}
                        </span> Bs
                    </p>

                    <p>
                        <strong>QR a entregar:</strong>
                        <span id="qr-a-entregar" class="text-primary">
                            {{ number_format($ingresoQR, 2) }}
                        </span> Bs
                    </p>

                    <p>
                        <strong>Ingreso bruto:</strong>
                        <span id="ingreso-bruto" class="text-dark">
                            {{ number_format($ingresoBruto, 2) }}
                        </span> Bs
                    </p>

                </div>

            </div>


        </div>

        <div class="card-footer text-end">

           @if($cierreExistente)
                <button class="btn btn-danger" disabled>
                    Venta ya confirmada
                </button>
            @else
                <button class="btn btn-success">
                    Confirmar venta del día
                </button>
            @endif

        </div>

    </form>

    @endif

</div>
@endsection

{{-- =======================
    JS CORREGIDO
======================= --}}
@section('js')
<script>
let index = 1;

// Agregar nueva fila de gasto
document.getElementById('agregar-gasto')?.addEventListener('click', () => {
    const tbody = document.querySelector('#tabla-gastos tbody');

    const fila = document.createElement('tr');
    fila.innerHTML = `
        <td>
            <input type="text" name="gastos[${index}][concepto]" class="form-control" placeholder="Ej: combustible">
        </td>
        <td>
            <input type="number" step="0.01" name="gastos[${index}][monto]" class="form-control monto-gasto" value="0">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-danger eliminar-fila">✕</button>
        </td>
    `;

    tbody.appendChild(fila);
    index++;
});

// Eliminar fila de gasto
document.addEventListener('click', e => {
    if (e.target.classList.contains('eliminar-fila')) {
        e.target.closest('tr').remove();
        calcularTotales();
    }
});

// Recalcular totales al cambiar monto
document.addEventListener('input', e => {
    if (e.target.classList.contains('monto-gasto')) {
        calcularTotales();
    }
});

function calcularTotales() {
    let totalGastos = 0;
    document.querySelectorAll('.monto-gasto').forEach(input => {
        totalGastos += parseFloat(input.value || 0);
    });

    document.getElementById('total-gastos').innerText = totalGastos.toFixed(2);

    const ingresoEfectivo = {{ $ingresoEfectivo ?? 0 }};
    const ingresoQR       = {{ $ingresoQR ?? 0 }};
    const ingresoBruto    = {{ $ingresoBruto ?? 0 }};

    // Solo restamos gastos al efectivo
    document.getElementById('efectivo-a-entregar').innerText =
        (ingresoEfectivo - totalGastos).toFixed(2);

    // QR permanece igual
    document.getElementById('qr-a-entregar').innerText =
        ingresoQR.toFixed(2);

    // Ingreso bruto total
    document.getElementById('ingreso-bruto').innerText =
        ingresoBruto.toFixed(2);
}
</script>
@endsection