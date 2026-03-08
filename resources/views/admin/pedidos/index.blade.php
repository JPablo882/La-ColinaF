@extends('adminlte::page')

@section('content_header')
<h1><b>Listado de pedidos</b></h1>

{{-- SELECTOR DE FECHA GLOBAL --}}
<form method="GET" class="mt-3 mb-3 d-flex align-items-center" style="gap: 10px;">
    <label for="fecha"><b>Fecha:</b></label>
    <input type="date"
           name="fecha"
           id="fecha"
           class="form-control"
           style="width: 200px"
           value="{{ request('fecha', now()->format('Y-m-d')) }}">
    <button class="btn btn-primary btn-sm" type="submit">Filtrar</button>
</form>

<hr>
@stop

@section('content')

<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="container-fluid">


             {{-- ============================= --}}
            {{-- MAPA GENERAL DE PEDIDOS --}}
            {{-- ============================= --}}
            <div class="card card-outline card-secondary mt-3">
                <div class="card-header">
                    <h3 class="card-title">
                        🗺️ Mapa general de pedidos registrados
                    </h3>
                </div>

                <div class="card-body p-0">
                    <div id="mapa-general-pedidos"
                        style="height:280px; width:100%; background:#eee;">
                    </div>
                </div>
            </div>


            <div class="card card-outline card-info mb-3">
                <div class="card-body">

                    <div class="row align-items-end">

                        {{-- BUSCADOR CLIENTE --}}
                        <div class="col-md-4">
                            <label>Cliente</label>
                            <input type="text" id="buscadorCliente" class="form-control" placeholder="Buscar cliente...">
                            <div id="resultadosClientes" class="list-group mt-1"></div>
                        </div>

                        {{-- MOTOQUERO --}}
                        <div class="col-md-3">
                            <label>Distribuidor</label>
                            <select id="motoqueroSelect" class="form-control">
                                <option value="">Seleccionar</option>
                                @foreach($motoqueros as $m)
                                    <option value="{{ $m->id }}">{{ $m->apellidos }} {{ $m->nombres }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- RUTA --}}
                        <div class="col-md-2">
                            <label>Ruta</label>
                            <select id="rutaSelect" class="form-control">
                                <option value="">Ruta</option>
                                <option>A</option>
                                <option>B</option>
                                <option>C</option>
                                <option>D</option>
                            </select>
                        </div>

                        {{-- BOTÓN --}}
                        <div class="col-md-3">
                            <button id="btnCrearPedidoRapido" class="btn btn-success w-100">
                                ➕ Crear Pedido Rapido
                            </button>
                        </div>

                    </div>
                </div>
            </div>



<div class="row">


    {{-- ======================================================= --}}
    {{-- COLUMNA IZQUIERDA → LISTA DE WHATSAPP (NUEVA SECCIÓN AGREGADA) --}}
    {{-- ======================================================= --}}
    {{-- NUEVO: Se crea una columna de 3 para mostrar los contactos de WhatsApp --}}
    <div class="col-md-2">

        {{-- NUEVO: TARJETA DE CONTACTOS WHATSAPP --}}
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fab fa-whatsapp"></i> Contactos que escribieron hoy
                </h3>
            </div>

            <div class="card-body" style="max-height: 450px; overflow-y: auto;">
                
                {{-- NUEVO: Aquí mostramos los contactos del día --}}
                @if($contactosHoy->isEmpty())
                    <p class="text-muted text-center">Nadie escribió hoy</p>
                @else
                    @foreach($contactosHoy as $c)

                    @php
                        $clienteWhatsapp = \App\Models\Cliente::where('celular', $c->from)
                            ->orWhere('nombre', $c->name)
                            ->first();

                        $latitud  = $clienteWhatsapp->latitud ?? null;
                        $longitud = $clienteWhatsapp->longitud ?? null;

                        $pedidoExistente = \App\Models\Pedido::whereHas('cliente', function($q) use ($c) {
                            $q->where('celular', $c->from)
                            ->orWhere('nombre', $c->name);
                        })->whereDate('created_at', now()->format('Y-m-d'))->exists();
                    @endphp

                    <div
                    class="border rounded p-2 mb-2 whatsapp-contact"
                    style="background:#e9ffe9; font-size:0.8rem;"
                    data-lat="{{ $latitud }}"
                    data-lng="{{ $longitud }}"
                    >

                        {{-- INFO CLIENTE --}}
                        <div class="mb-1">
                            <b>{{ $c->name }}</b><br>
                            <small>{{ $c->from }}</small>
                        </div>

                        {{-- SELECTOR MOTOQUERO --}}
                        <select class="form-control form-control-sm mb-1 selector-motoquero">
                            <option value="">Distribuidor</option>
                            @foreach($motoqueros as $m)
                                <option value="{{ $m->id }}">
                                    {{ $m->apellidos }} {{ $m->nombres }}
                                </option>
                            @endforeach
                        </select>

                        {{-- SELECTOR RUTA --}}
                        <select class="form-control form-control-sm mb-1 selector-ruta">
                            <option value="">Ruta</option>
                            <option value="A">Ruta A</option>
                            <option value="B">Ruta B</option>
                            <option value="C">Ruta C</option>
                            <option value="D">Ruta D</option>
                        </select>

                        {{-- BOTÓN REGISTRAR --}}
                        <button
                            class="btn btn-sm w-100 {{ $pedidoExistente ? 'btn-secondary' : 'btn-success' }} btn-registrar-pedido"
                            data-nombre="{{ $c->name }}"
                            data-celular="{{ $c->from }}"
                            {{ $pedidoExistente ? 'disabled' : '' }}
                        >
                            {{ $pedidoExistente ? 'Pedido registrado' : 'Registrar pedido' }}
                        </button>
                    </div>


                    @endforeach
                @endif

            </div>
        </div>

    </div> {{-- FIN COLUMNA IZQUIERDA --}}


        {{-- ===================================== --}}
        {{-- ========== TABLA GENERAL ============ --}}
        {{-- ===================================== --}}
    <div class="col-md-10">

    

        <div class="card card-outline card-primary">


            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="card-title mb-0">Pedidos registrados</h3>
                    <small class="text-muted">
                        Mostrando {{ $pedidos->count() }} pedidos
                    </small>
                </div>

                <a href="{{ url('/admin/pedidos/create') }}" class="btn btn-primary">
                    ➕ Crear Varios Pedidos
                </a>
            </div>




            <div class="card-body">

                <div class="tabla-scroll">

                    <table id="example1" class="table table-bordered table-hover table-striped table-sm">
                        <thead>
                            <tr>
                                <th style="text-align:center">Nro</th>
                                <th style="text-align:center">Cliente</th>
                                <th style="text-align:center">Promo</th>
                                <th style="text-align:center">Celular</th>
                                <th style="text-align:center">Descripción</th>
                                <th style="text-align:center">Ubicación GPS</th>
                                <th style="text-align:center">Latitud</th>
                                <th style="text-align:center">Longitud</th>
                                <th style="text-align:center">Precios</th>
                                <th style="text-align:center">Distribuidor</th>
                                <th style="text-align:center">Estado</th>
                                <th style="text-align:center">Notificación</th>
                                <th style="text-align:center">Acción</th>
                            </tr>
                        </thead>

                        <tbody>
                        @php
                            $pedidosOrdenados = $pedidos->sortByDesc('updated_at')->values();
                            $total = $pedidosOrdenados->count();
                        @endphp

                        @foreach($pedidosOrdenados as $index => $pedido)
                            <tr>

                                <td style="text-align:center">{{ $total - $index }}</td>

                                <td>{{ $pedido->cliente->nombre ?? '—' }}</td>

                                <td style="text-align:center">
                                    @if($pedido->cliente && $pedido->cliente->promo_activa)
                                        <span class="badge bg-success">Tiene</span>
                                    @else
                                        <span class="text-muted">No</span>
                                    @endif
                                </td>

                                <td>{{ $pedido->cliente->celular_real ?? '—' }}</td>
                                <td>{{ $pedido->cliente->direccion ?? $pedido->direccion_entrega ?? '—' }}</td>

                                <td>
                                    @if(!empty($pedido->cliente->ubicacion_gps))
                                        <a href="{{ $pedido->cliente->ubicacion_gps }}" target="_blank">Ver enlace</a>
                                    @else
                                        <span class="text-muted">No registrado</span>
                                    @endif
                                </td>

                                <td>{{ $pedido->cliente->latitud ?? '-' }}</td>
                                <td>{{ $pedido->cliente->longitud ?? '-' }}</td>


                                <td>
                                    @if($pedido->precio_regular_ref)
                                        <small>
                                            <strong>Reg:</strong> Bs {{ number_format($pedido->precio_regular_ref, 2) }}<br>
                                            <strong>Alc:</strong> Bs {{ number_format($pedido->precio_alcalina_ref, 2) }}
                                        </small>
                                    @else
                                        -
                                    @endif
                                </td>


                                <td style="text-align:center">
                                    @if($pedido->motoquero)
                                        {{ $pedido->motoquero->nombres }} {{ $pedido->motoquero->apellidos }}
                                        <small class="text-muted">
                                            ({{ $pedido->ruta ?? '-' }})
                                        </small>
                                    @else
                                        <span class="text-muted">Sin asignar</span>
                                    @endif
                                </td>

                                <td style="text-align:center">
                                    @switch($pedido->estado)
                                        @case('Pendiente')
                                            <span class="badge bg-secondary">Pendiente</span>
                                            @break
                                        @case('Por asignar')
                                            <span class="badge bg-warning text-dark">Por asignar</span>
                                            @break
                                        @case('Asignado')
                                            <span class="badge bg-primary">Asignado</span>
                                            @break
                                        @case('En camino')
                                            <span class="badge bg-orange">En camino</span>
                                            @break
                                        @case('Entregado')
                                            <span class="badge bg-success">Entregado</span>
                                            @break
                                    @endswitch
                                </td>


                                <td style="text-align:center">

                                    {{-- Siempre (Cliente) --}}
                                    <div>
                                        <input type="checkbox"
                                            class="toggle-siempre"
                                            data-cliente-id="{{ $pedido->cliente->id ?? '' }}"
                                            {{ $pedido->cliente && $pedido->cliente->inicio_navegacion_siempre ? 'checked' : '' }}>
                                        <small>Siempre</small>
                                    </div>

                                    {{-- Este pedido --}}
                                    <div>
                                        <input type="checkbox"
                                            class="toggle-este-pedido"
                                            data-pedido-id="{{ $pedido->id }}"
                                            {{ $pedido->inicio_navegacion_este_pedido ? 'checked' : '' }}>
                                        <small>Este pedido</small>
                                    </div>

                                </td>


                                {{-- ACCIÓN --}}
                                <td style="text-align:center">

                                    <div class="d-flex justify-content-center gap-2">

                                        <!-- BOTÓN EDITAR -->
                                        <button
                                            class="btn btn-sm btn-outline-primary btn-editar-pedido"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEditarPedido"
                                            data-pedido-id="{{ $pedido->id }}"
                                        >
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <!-- BOTÓN ELIMINAR -->
                                        @if($pedido->estado !== 'Entregado')
                                            <form
                                                action="{{ url('/admin/pedidos/'.$pedido->id) }}"
                                                method="post"
                                                id="formEliminar{{ $pedido->id }}"
                                                onsubmit="confirmEliminar{{ $pedido->id }}(event)"
                                            >
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif

                                    </div>

                                    <!-- SWEETALERT -->
                                    <script>
                                        function confirmEliminar{{ $pedido->id }}(e) {
                                            e.preventDefault();

                                            Swal.fire({
                                                title: '¿Eliminar pedido?',
                                                text: 'Esta acción no se puede deshacer',
                                                icon: 'warning',
                                                showCancelButton: true,
                                                confirmButtonColor: '#d33',
                                                confirmButtonText: 'Eliminar',
                                                cancelButtonText: 'Cancelar'
                                            }).then((result) => {
                                                if (result.isConfirmed) {
                                                    document.getElementById('formEliminar{{ $pedido->id }}').submit();
                                                }
                                            });
                                        }
                                    </script>

                                </td>

                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                </div>

            </div>



        </div>
    </div>
</div>    

        {{-- ===================================== --}}
        {{-- ========== PANEL MOTOQUEROS ========= --}}
        {{-- ===================================== --}}
        <hr>
        <h3 class="text-center mt-4 mb-4"><b>Panel de control por Distribuidor</b></h3>

    <div class="row">
            @foreach($motoqueros as $motoquero)
            <div class="col-md-3 mb-4" id="panel-motoquero-{{ $motoquero->id }}">
                <div class="motoquero-card">
                    <h4 class="text-center"><b>#{{ $motoquero->id }}, {{ $motoquero->apellidos }} {{ $motoquero->nombres }}</b></h4>
                    <hr>


                    {{-- ================== --}}
                    {{-- MINI TABLA DESPACHO --}}
                    {{-- ================== --}}

                    @php
                        $despacho = $despachosHoy[$motoquero->id] ?? null;

                        $regularDespachado = $despacho->botellones_regular ?? 0;
                        $alcalinaDespachado = $despacho->botellones_alcalina ?? 0;

                        $vendidosRegular = $pedidos
                            ->where('estado','Entregado')
                            ->where('motoquero_id',$motoquero->id)
                            ->sum(function($p){
                                return $p->detalles->where('producto','Agua Regular')->sum('cantidad');
                            });

                        $vendidosAlcalina = $pedidos
                            ->where('estado','Entregado')
                            ->where('motoquero_id',$motoquero->id)
                            ->sum(function($p){
                                return $p->detalles->where('producto','Agua Alcalina')->sum('cantidad');
                            });

                        $restanteRegular = $regularDespachado - $vendidosRegular;
                        $restanteAlcalina = $alcalinaDespachado - $vendidosAlcalina;
                    @endphp

                    <div class="table-responsive mb-3">
                        <table class="table table-sm table-bordered text-center" style="font-size:12px;">
                            <thead class="table-light">
                                <tr>
                                    <th></th>
                                    <th>Regular</th>
                                    <th>Alcalina</th>
                                </tr>
                            </thead>
                            <tbody>

                                {{-- FILA 1 - DESPACHO --}}
                                <tr>
                                    <td><b>Despacho</b></td>
                                    <td>
                                        <form action="{{ route('admin.despachos.store') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="motoquero_id" value="{{ $motoquero->id }}">
                                            <input type="number" name="botellones_regular"
                                                value="{{ $regularDespachado }}"
                                                class="form-control form-control-sm text-center"
                                                style="font-size:11px; padding:2px;">
                                    </td>
                                    <td>
                                            <input type="number" name="botellones_alcalina"
                                                value="{{ $alcalinaDespachado }}"
                                                class="form-control form-control-sm text-center"
                                                style="font-size:11px; padding:2px;">

                                            <input type="hidden" name="dispensers" value="0">

                                            <button type="submit"
                                                class="btn btn-success btn-sm mt-1 w-100"
                                                style="font-size:10px; padding:2px;">
                                                Guardar
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                {{-- FILA 2 - VENDIDO --}}
                                <tr class="table-warning">
                                    <td><b>Vendido</b></td>
                                    <td>{{ $vendidosRegular }}</td>
                                    <td>{{ $vendidosAlcalina }}</td>
                                </tr>

                                {{-- FILA 3 - RESTANTE --}}
                                <tr class="table-success">
                                    <td><b>Restante</b></td>
                                    <td>{{ $restanteRegular }}</td>
                                    <td>{{ $restanteAlcalina }}</td>
                                </tr>

                            </tbody>
                        </table>
                    </div>



                    {{-- BOTÓN REPORTE --}}
                    <div class="text-center mb-2">
                        <button class="btn btn-dark btn-sm abrirReporte" data-motoquero="{{ $motoquero->id }}">
                            Reporte de ventas
                        </button>

                        
                        
                    </div>


            {{-- ===================================== --}}
            {{-- POR ASIGNAR – RUTAS A B C D (VISUAL) --}}
            {{-- ===================================== --}}
            <div class="estado-section">

                <div class="estado-title asignar">🟨 Por asignar</div>


                    {{-- MAPA POR ASIGNAR --}}
                    <div
                        class="mapa-por-asignar mt-2 mb-2"
                        id="mapa-por-asignar-{{ $motoquero->id }}"
                        data-motoquero="{{ $motoquero->id }}"
                        data-ruta="A"
                        style="height:280px; border-radius:8px; background:#eee;">
                    </div>


                    <div class="d-flex justify-content-between gap-2 mb-2">
                        <button
                            class="btn btn-sm btn-outline-primary btn-ordenar-mapa"
                            data-motoquero="{{ $motoquero->id }}">
                            📍 Ordenar en mapa
                        </button>

                        <button
                            class="btn btn-sm btn-success btn-guardar-orden-mapa"
                            data-motoquero="{{ $motoquero->id }}"
                            disabled>
                            💾 Guardar orden
                        </button>
                    </div>


                {{-- SELECTOR DE RUTA --}}
                <div class="ruta-selector mt-2">
                    <button class="btn btn-sm btn-outline-primary btn-ruta active" data-ruta="A">A</button>
                    <button class="btn btn-sm btn-outline-primary btn-ruta" data-ruta="B">B</button>
                    <button class="btn btn-sm btn-outline-primary btn-ruta" data-ruta="C">C</button>
                    <button class="btn btn-sm btn-outline-primary btn-ruta" data-ruta="D">D</button>
                </div>

                {{-- CONTENEDOR DE RUTAS --}}
                <div class="rutas-container mt-2">

                    @foreach(['A','B','C','D'] as $ruta)

                        @php
                            $porAsignarRuta = $pedidos
                                ->where('estado', 'Por asignar')
                                ->where('motoquero_id', $motoquero->id)
                                ->where('ruta', $ruta)
                                ->sortBy(function($it) {
                                    return $it->orden === null ? PHP_INT_MAX : $it->orden;
                                })
                                ->values();
                        @endphp

                        <div class="ruta ruta-{{ $ruta }} {{ $ruta === 'A' ? 'active' : '' }}" data-ruta="{{ $ruta }}">

                            @if($porAsignarRuta->count() > 0)
                                <button
                                    class="btn btn-primary btn-sm btn_asignar_todos mb-2"
                                    data-motoquero="{{ $motoquero->id }}"
                                    data-ruta="{{ $ruta }}"
                                >
                                    Asignar todos (Ruta {{ $ruta }})
                                </button>
                            @endif

                            <div
                                class="lista-por-asignar lista-ruta"
                                data-motoquero="{{ $motoquero->id }}"
                                data-ruta="{{ $ruta }}"
                            >
                                @if($porAsignarRuta->isEmpty())
                                    <div class="pedido-item text-muted">
                                        No hay pedidos en ruta {{ $ruta }}
                                    </div>
                                @else
                                    @foreach($porAsignarRuta as $p)
                                        <div class="pedido-item" data-id="{{ $p->id }}">
                                            <b>#{{ $p->orden }}</b> – {{ $p->cliente->nombre }}

                                            <div>
                                                <small>
                                                    @if($p->cliente->ubicacion_gps)
                                                        <a href="{{ $p->cliente->ubicacion_gps }}" target="_blank">Ver enlace</a>
                                                    @else
                                                        <span class="text-muted">No registrado</span>
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>

                        </div>

                    @endforeach

                </div>
            </div>

            {{-- ===================================== --}}
            {{-- ASIGNADO --}}
            {{-- ===================================== --}}
            <div class="estado-section">
                <div class="estado-title asignado">🟦 Asignado</div>


                <div class="text-center mt-2 mb-2">
                    <button 
                        class="btn btn-sm btn-dark btn-ver-mapa-asignados"
                        data-motoquero="{{ $motoquero->id }}">
                        🗺 Ver mapa
                    </button>
                </div>


                @php
                    $asignados = $pedidos
                        ->where('estado','Asignado')
                        ->where('motoquero_id',$motoquero->id)
                        ->sortBy(function($it) { return $it->orden === null ? PHP_INT_MAX : $it->orden; })
                        ->values();
                

                @endphp

                <div class="mt-2 lista-asignado" data-motoquero="{{ $motoquero->id }}">
                    @if($asignados->isEmpty())
                        <div class="pedido-item text-muted">No hay pedidos asignados.</div>
                    @else

                        @foreach($asignados as $p)
                            @php
                                $ultimaCompra = \App\Models\Pedido::with('detalles')
                                    ->where('cliente_id', $p->cliente_id)
                                    ->where('estado', 'Entregado')
                                    ->where('id', '!=', $p->id)
                                    ->orderBy('updated_at', 'desc')
                                    ->first();
                            @endphp

                            <div class="pedido-item pedido-card" data-id="{{ $p->id }}">

                                <b>#{{ $p->orden }}</b> - {{ $p->cliente->nombre }}

                                {{-- GPS --}}
                                <div>
                                    <small>
                                        @if($p->cliente->ubicacion_gps)
                                            <a href="{{ $p->cliente->ubicacion_gps }}" target="_blank">Ver enlace</a>
                                        @else
                                            <span class="text-muted">No registrado</span>
                                        @endif
                                    </small>
                                </div>

                                {{-- ÚLTIMA COMPRA --}}
                                <div class="mt-1">
                                    <small><b>Última compra:</b></small>
                                    @if($ultimaCompra && $ultimaCompra->detalles->count() > 0)
                                        <ul class="mb-0 ps-3">
                                            @foreach($ultimaCompra->detalles as $d)
                                                <li>
                                                    <small>{{ $d->producto }} × {{ $d->cantidad }}</small>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <small class="text-muted">Sin compras anteriores</small>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                    @endif
                </div>
            </div>

            {{-- ===================================== --}}
            {{-- EN CAMINO --}}
            {{-- ===================================== --}}
            <div class="estado-section">
                <div class="estado-title camino">🟧 En camino</div>

                @php
                    $enCamino = $pedidos
                        ->where('estado','En camino')
                        ->where('motoquero_id',$motoquero->id)
                        ->sortByDesc('updated_at')
                        ->values();
                @endphp

                <div class="mt-2 lista-en-camino" data-motoquero="{{ $motoquero->id }}">
                    @if($enCamino->isEmpty())
                        <div class="pedido-item text-muted">No hay pedidos en camino.</div>
                    @else
                        @foreach($enCamino as $index => $p)

                            <div class="pedido-item mb-3 p-3 border rounded" data-id="{{ $p->id }}">
                                
                                <b>{{ $p->cliente->nombre }}</b><br>

                                <div>
                                    <small>
                                        @if($p->cliente->ubicacion_gps)
                                            <a href="{{ $p->cliente->ubicacion_gps }}" target="_blank">Ver enlace</a>
                                        @else
                                            <span class="text-muted">No registrado</span>
                                        @endif
                                    </small>
                                </div>

                                @php
                                    $msgVoy = urlencode("Hola, su pedido ya está en camino a su ubicación. El distribuidor llegará pronto.");
                                    $msgLlegada = urlencode("Hola, el distribuidor LLEGÓ a su ubicación. Por favor acérquese para recibir el pedido.");
                                @endphp


                                {{-- ================= ACCIONES ================= --}}
                                <div class="mt-3 p-2 border rounded bg-light">

                                    <div class="row g-2">

                                        {{-- COLUMNA CLIENTE --}}
                                        <div class="col-6">
                                            <div class="fw-bold small text-success mb-1">Cliente</div>

                                            <div class="d-grid gap-1">

                                                <a href="https://wa.me/{{ $p->cliente->celular_real }}?text={{ $msgVoy }}"
                                                target="_blank"
                                                class="btn btn-success btn-sm py-1"
                                                style="font-size: 12px;">
                                                    🚚 En Camino
                                                </a>

                                                <a href="https://wa.me/{{ $p->cliente->celular_real }}?text={{ $msgLlegada }}"
                                                target="_blank"
                                                class="btn btn-warning btn-sm py-1"
                                                style="font-size: 12px;">
                                                    📍 Llegada
                                                </a>

                                            </div>
                                        </div>

                                        {{-- COLUMNA DISTRIBUIDOR --}}
                                        <div class="col-6">
                                            <div class="fw-bold small text-primary mb-1">Distribuidor</div>

                                            <div class="d-grid gap-1">

                                                <button 
                                                    class="btn btn-primary btn-sm py-1 btn-avisar"
                                                    style="font-size: 12px;"
                                                    data-id="{{ $p->id }}"
                                                    data-tipo="ya_sale">
                                                    🚀 Ya Sale
                                                </button>

                                                <button 
                                                    class="btn btn-danger btn-sm py-1 btn-avisar"
                                                    style="font-size: 12px;"
                                                    data-id="{{ $p->id }}"
                                                    data-tipo="no_contesta">
                                                    📞 No Contesta
                                                </button>

                                            </div>
                                        </div>

                                    </div>

                                </div>

                            </div>

                        @endforeach
                    @endif
                </div>
            </div>


                    {{-- ENTREGADO --}}
                    <div class="estado-section entregado-scroll">
                        <div class="estado-title entregado">🟩 Entregado</div>

                        @php
                            $entregados = $pedidos->where('estado','Entregado')->where('motoquero_id',$motoquero->id)->sortByDesc('updated_at');
                        @endphp

                        @if($entregados->isEmpty())
                            <div class="pedido-item text-muted">No hay pedidos entregados.</div>
                        @else
                            @foreach($entregados as $p)
                            <div class="pedido-item">

                                
                                <div class="d-flex justify-content-between align-items-center">

                                    <b>{{ $p->cliente->nombre }}</b>

                                    <button 
                                        class="btn btn-sm btn-success px-2 py-0 btnEditarEntrega"
                                        style="font-size:12px;"
                                        data-id="{{ $p->id }}"
                                    >
                                        Editar
                                    </button>

                                </div>
                                    


                                <small><b>Total:</b> Bs {{ number_format($p->total_precio ?? 0,2) }}</small><br>
                                <small><b>Pago:</b> {{ $p->metodo_pago ?? 'No definido' }}</small>
                            
                                {{-- BOTON - RECIBO --}}
                                @php
                                   $lineasProductos = "";

                                foreach ($p->detalles as $detalle) {
                                    $nombre = $detalle->producto;            // Nombre del producto
                                    $cantidad = $detalle->cantidad;          // Cantidad
                                    $precio = $detalle->precio_total;        // Precio total del detalle

                                    // Construimos cada línea del detalle
                                    $lineasProductos .= "• $nombre — Cant: $cantidad — Bs $precio\n";
                                }

                                    $total = $p->total_precio;
                                    $metodo = ucfirst($p->metodo_pago);

                                    // Recibo base
                                    $mensajeBase =
                                        "Hola, gracias por su compra 🙌\n\n".
                                        "🧾 *RECIBO DE COMPRA*\n\n".
                                    $lineasProductos . "\n".
                                        "💰 *TOTAL*: Bs $total\n".
                                        "💳 *Método de pago*: $metodo\n\n".
                                        "¡Gracias por confiar en nosotros!";

                                    // Si pagó en efectivo → mensaje normal
                                    if (strtolower($metodo) === 'efectivo') {
                                        $msgEntrega = urlencode($mensajeBase);
                                    }

                                    // Si pagó con QR → mensaje + línea + “/” para abrir respuestas rápidas de WhatsApp Business
                                    else if (strtolower($metodo) === 'qr') {
                                        $mensajeQR = $mensajeBase . "\n\n/";
                                        $msgEntrega = urlencode($mensajeQR);
                                    }

                                    // Cualquier otro método, por si acaso
                                    else {
                                        $msgEntrega = urlencode($mensajeBase);
                                    }
                                @endphp

                                    <a href="https://wa.me/{{ $p->cliente->celular_real }}?text={{ $msgEntrega }}"
                                    target="_blank"
                                    class="btn btn-primary btn-sm mt-1 btn-recibo">
                                        <i class="fab fa-whatsapp"></i> Enviar Recibo
                                    </a>



                            </div>
                            @endforeach
                        @endif
                    </div>

                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>



<form
    id="formPedidoWhatsapp"
    action="{{ route('admin.pedidos.store') }}"
    method="POST"
    style="display:none;"
>
    @csrf
    <input type="hidden" name="nombre" id="wp_nombre">
    <input type="hidden" name="celular" id="wp_celular">
    <input type="hidden" name="motoquero_id" id="wp_motoquero">
    <input type="hidden" name="ruta" id="wp_ruta">
    <input type="hidden" name="origen" value="whatsapp">
</form>


@stop

@section('css')
<style>
.motoquero-card { border:2px solid #007bff; border-radius:10px; padding:15px; background:#f8f9fa; }
.estado-section { border:2px dashed #ccc; border-radius:8px; padding:10px; margin-bottom:15px; }
.estado-section.entregado-scroll { max-height:300px; overflow-y:auto; }
.estado-title { font-weight:bold; padding:6px; border-radius:5px; text-align:center; }
.estado-title.asignar { background:#f0ad4e; color:#000; } 
.estado-title.asignado { background:#17a2b8; color:#fff; } 
.estado-title.camino { background:#fd7e14; color:#fff; } 
.estado-title.entregado { background:#28a745; color:#fff; }
.pedido-item { background:#fff; border:1px solid #dee2e6; padding:8px; border-radius:5px; margin-bottom:8px; cursor: grab; }
.pedido-item.dragging { opacity: 0.5; }
.badge.bg-orange { background: #fd7e14; color: #fff; }



.btn-emergencia-bar {width: 100%; border: none; background-color: #dc3545; color: #fff; font-size: 12px; padding: 6px 0; margin-bottom: 6px; border-radius: 4px; font-weight: bold; letter-spacing: 0.5px; cursor: pointer; }

/* Cuando ya está marcado */
.btn-emergencia-bar.emergencia-activa { background-color: #6c757d; /* gris */ cursor: not-allowed; }

/* Hover solo si NO está activo */
.btn-emergencia-bar:not(.emergencia-activa):hover { background-color: #b52a37; }


.ruta-selector { display: flex; gap: 6px; }

.ruta { display: none; }

.ruta.active { display: block; }

.lista-ruta { min-height: 80px; border: 1px dashed #ccc; padding: 6px; }


/* ===== SCROLL SOLO PARA TABLA ===== */
.tabla-scroll {
    max-height: 420px;      /* puedes ajustar: 350 / 400 / 500 */
    overflow-y: auto;
    border: 1px solid #dee2e6;
    border-radius: 6px;
}

/* Header de la tabla fijo */
.tabla-scroll thead th {
    position: sticky;
    top: 0;
    background: #f8f9fa;
    z-index: 2;
}

/* Footer de acciones fijo visualmente */
.sticky-footer-acciones {
    background: #fff;
    padding-top: 10px;
    border-top: 1px solid #ddd;
}


#mapa-general-pedidos {
    border-radius: 6px;
}

/* Oculta la X del InfoWindow */
.gm-ui-hover-effect {
    display: none !important;
}

</style>

@stop


@section('js')


<meta name="csrf-token" content="{{ csrf_token() }}">

<script async
  src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}">
</script>




<script>
const mapas = {};
const marcadores = {};
const ordenMapa = {};
const polilineas = {};
let modoOrdenMapa = {};


let mapaGeneral = null;
let mapaGeneralListo = false;
let marcadoresGeneral = [];
let marcadorTemporal = null;
let marcadorDestacado = null;
let marcadorConfirmado = null;



let clientePendienteMapa = null;
let clienteSeleccionado = null;
const clientes = @json($clientes);


let mapaModalAsignados = null;
let marcadoresModalAsignados = [];
let ordenModalAsignados = [];
let modoOrdenModalAsignados = false;
let modoOrdenar = false;


</script>


<script>
function mostrarClienteEnMapa(cliente, tipo = 'temporal') {

    if (!cliente?.latitud || !cliente?.longitud) return;

    // 🔥 Si el mapa aún no está listo, guardamos el cliente
    if (!mapaGeneralListo) {
        clientePendienteMapa = { cliente, tipo };
        return;
    }

    if (tipo === 'temporal' && marcadorTemporal) {
        marcadorTemporal.setMap(null);
    }

    if (tipo === 'confirmado' && marcadorConfirmado) {
        marcadorConfirmado.setMap(null);
    }

    const iconos = {
        temporal: 'http://maps.google.com/mapfiles/ms/icons/yellow-dot.png',
        confirmado: 'http://maps.google.com/mapfiles/ms/icons/ltblue-dot.png'
    };

    const marker = new google.maps.Marker({
        map: mapaGeneral,
        position: {
            lat: parseFloat(cliente.latitud),
            lng: parseFloat(cliente.longitud)
        },
        icon: {
            url: iconos[tipo],
            scaledSize: new google.maps.Size(40, 40)
        },
        title: cliente.nombre
    });

    mapaGeneral.panTo(marker.getPosition());
    mapaGeneral.setZoom(15);

    if (tipo === 'temporal') marcadorTemporal = marker;
    if (tipo === 'confirmado') marcadorConfirmado = marker;
}
</script>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const buscador = document.getElementById('buscadorCliente');
    if (!buscador) {
        console.warn('buscadorCliente no existe en el DOM');
        return;
    }

    buscador.addEventListener('keyup', function () {

        const q = this.value.toLowerCase().trim();
        const contenedor = document.getElementById('resultadosClientes');
        contenedor.innerHTML = '';

        if (q.length < 2) {
            if (marcadorTemporal) marcadorTemporal.setMap(null);
            return;
        }

        const resultados = clientes
            .filter(c =>
                c.nombre.toLowerCase().includes(q) ||
                (c.celular && c.celular.includes(q))
            )
            .slice(0, 8);

        // ==========================
        // SI NO HAY RESULTADOS
        // ==========================
        if (resultados.length === 0) {

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'list-group-item list-group-item-action text-danger';
            btn.innerHTML = `❌ Cliente no registrado: <b>${q}</b>`;

            btn.onclick = function () {

                Swal.fire({
                    title: 'Cliente no registrado',
                    text: '¿Desea registrar este cliente?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, registrar',
                    cancelButtonText: 'No'
                }).then(result => {

                    if (result.isConfirmed) {

                        // Ir al create enviando el nombre
                        window.location.href =
                            "{{ route('admin.clientes.create') }}?nombre=" + encodeURIComponent(q);

                    }

                });

            };

            contenedor.appendChild(btn);
            return;
        }

        // ==========================
        // MOSTRAR RESULTADOS
        // ==========================
        resultados.forEach(c => {

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'list-group-item list-group-item-action';
            btn.dataset.id = c.id;
            btn.innerHTML = `<b>${c.nombre}</b>`;

            contenedor.appendChild(btn);

        });

        const primerCliente = resultados.find(c => c.latitud && c.longitud);
        if (primerCliente) {
            mostrarClienteEnMapa(primerCliente, 'temporal');
        }

    });


});
</script>


<script>
document.addEventListener('click', function (e) {

    const btn = e.target.closest('#resultadosClientes button');
    if (!btn) return;

    clienteSeleccionado = btn.dataset.id;
    document.getElementById('buscadorCliente').value = btn.innerText;
    document.getElementById('resultadosClientes').innerHTML = '';

    if (marcadorTemporal) {
        marcadorTemporal.setMap(null);
        marcadorTemporal = null;
    }

    const cliente = clientes.find(c => String(c.id) === String(clienteSeleccionado));
    if (cliente) {
        mostrarClienteEnMapa(cliente, 'confirmado');
    }
});
</script>

<script>
function initMapaGeneralPedidos() {

    const cont = document.getElementById('mapa-general-pedidos');
    if (!cont) return;

    mapaGeneral = new google.maps.Map(cont, {
        zoom: 13,
        center: { lat: -17.7833, lng: -63.1821 }
    });

    mapaGeneralListo = true;

    
    // Dibujar cola si había datos pendientes
    if (colaMotoqueros.length) {
        colaMotoqueros.forEach(actualizarMotoqueroEnMapa);
        colaMotoqueros = [];
    }


    cargarPedidosEnMapaGeneral();

    // 🔥 Si había un cliente pendiente, lo dibujamos ahora
    if (clientePendienteMapa) {
        mostrarClienteEnMapa(
            clientePendienteMapa.cliente,
            clientePendienteMapa.tipo
        );
        clientePendienteMapa = null;
    }

}

function cargarPedidosEnMapaGeneral() {

    marcadoresGeneral.forEach(m => m.setMap(null));
    marcadoresGeneral = [];

    document.querySelectorAll('tbody tr').forEach(row => {

        const lat = row.querySelector('td:nth-child(7)')?.innerText;
        const lng = row.querySelector('td:nth-child(8)')?.innerText;
        const nombreCliente = row.querySelector('td:nth-child(2)')?.innerText;

        if (!lat || !lng || isNaN(lat) || isNaN(lng)) return;

        const marker = new google.maps.Marker({
            map: mapaGeneral,
            position: {
                lat: parseFloat(lat),
                lng: parseFloat(lng)
            },
            icon: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png',
            title: nombreCliente || 'Cliente'
        });


        // 🔵 Click en fila → centrar mapa
        row.addEventListener('click', () => {
            mapaGeneral.panTo(marker.getPosition());
            mapaGeneral.setZoom(15);
        });

        marcadoresGeneral.push(marker);
    });
}
</script>


<script>

// ==========================
// CREAR PEDIDO RÁPIDO
// ==========================
$('#btnCrearPedidoRapido').on('click', function () {

    if (!clienteSeleccionado) {
        alert('Selecciona un cliente');
        return;
    }

    const motoquero = $('#motoqueroSelect').val();
    const ruta = $('#rutaSelect').val();

    if (!motoquero || !ruta) {
        alert('Selecciona motoquero y ruta');
        return;
    }

    $.post("{{ route('admin.pedidos.store') }}", {
        _token: $('meta[name="csrf-token"]').attr('content'),
        cliente_id: clienteSeleccionado,
        motoquero_id: motoquero,
        ruta: ruta
    })
    .done(() => {
        location.reload();
    })
    .fail(err => {
        alert(err.responseJSON?.message || 'Error al crear pedido');
    });
});
</script>






<script>
function cargarMapaPorAsignar(motoqueroId, ruta) {

    const cont = document.getElementById('mapa-por-asignar-' + motoqueroId);
    if (!cont) return;

    // 🔥 LIMPIAR CONTENEDOR
    cont.innerHTML = '';

    fetch(`/admin/pedidos/mapa-por-asignar?motoquero_id=${motoqueroId}&ruta=${ruta}`)
        .then(r => r.json())
        .then(pedidos => {

            // ❗ Sin pedidos
            if (!Array.isArray(pedidos) || pedidos.length === 0) {
                cont.innerHTML = '<small class="text-muted">Sin pedidos en esta ruta</small>';
                return;
            }

            // ❗ Filtrar pedidos con lat/lng válidos
            const validos = pedidos.filter(p =>
                p.latitud && p.longitud &&
                !isNaN(p.latitud) && !isNaN(p.longitud)
            );

            if (!validos.length) {
                cont.innerHTML = '<small class="text-danger">Pedidos sin ubicación válida</small>';
                return;
            }

            const map = new google.maps.Map(cont, {
                zoom: 13,
                center: {
                    lat: parseFloat(validos[0].latitud),
                    lng: parseFloat(validos[0].longitud)
                }
            });

            // ✅ GUARDAR MAPA
            mapas[motoqueroId] = map;
            ordenMapa[motoqueroId] = [];
            marcadores[motoqueroId] = [];
            modoOrdenMapa[motoqueroId] = false;

            validos.forEach(p => {

                const marker = new google.maps.Marker({
                    map,
                    position: {
                        lat: parseFloat(p.latitud),
                        lng: parseFloat(p.longitud)
                    },
                    label: p.orden ? String(p.orden) : '',
                    title: p.nombre ?? ''
                });

                marker.pedidoId = p.id;
                marker.latlng = {
                    lat: parseFloat(p.latitud),
                    lng: parseFloat(p.longitud)
                };

                marker.addListener('click', () => {
                    if (!modoOrdenMapa[motoqueroId]) return;

                    if (ordenMapa[motoqueroId].includes(marker)) return;

                    ordenMapa[motoqueroId].push(marker);
                    marker.setLabel(String(ordenMapa[motoqueroId].length));

                    dibujarPolilinea(motoqueroId);
                });

                marcadores[motoqueroId].push(marker);
            });



               // 🔵 ORDENAR pedidos por campo "orden"
                const pedidosOrdenados = validos
                    .filter(p => p.orden !== null)
                    .sort((a, b) => a.orden - b.orden);

                // 🔵 Dibujar polilínea SI YA EXISTE ORDEN
                if (pedidosOrdenados.length > 1) {

                    const path = pedidosOrdenados.map(p =>
                        new google.maps.LatLng(
                            parseFloat(p.latitud),
                            parseFloat(p.longitud)
                        )
                    );

                    polilineas[motoqueroId] = new google.maps.Polyline({
                        path: path,
                        map: map,
                        strokeOpacity: 0.9,
                        strokeWeight: 4
                    });
                }


        })
        .catch(err => {
            console.error('Error cargando mapa:', err);
            cont.innerHTML = '<small class="text-danger">Error cargando mapa</small>';
        });
}

</script>



<script>
function dibujarPolilinea(motoqueroId) {

    if (polilineas[motoqueroId]) {
        polilineas[motoqueroId].setMap(null);
    }

    const path = ordenMapa[motoqueroId].map(m =>
    new google.maps.LatLng(m.latlng.lat, m.latlng.lng)
    );

    polilineas[motoqueroId] = new google.maps.Polyline({
        path,
        map: mapas[motoqueroId],
        strokeOpacity: 0.9,
        strokeWeight: 4
    });
}


document.addEventListener('click', e => {
    const btn = e.target.closest('.btn-ordenar-mapa');
    if (!btn) return;

    const motoqueroId = btn.dataset.motoquero;

    modoOrdenMapa[motoqueroId] = true;
    ordenMapa[motoqueroId] = [];

    if (polilineas[motoqueroId]) {
        polilineas[motoqueroId].setMap(null);
    }

    marcadores[motoqueroId].forEach(m => m.setLabel(''));

    document
        .querySelector(`.btn-guardar-orden-mapa[data-motoquero="${motoqueroId}"]`)
        .disabled = false;

    Swal.fire('Modo orden activado', 'Seleccione los puntos en el mapa', 'info');
});



document.addEventListener('click', e => {
    const btn = e.target.closest('.btn-guardar-orden-mapa');
    if (!btn) return;

    const motoqueroId = btn.dataset.motoquero;

    const orden = ordenMapa[motoqueroId].map((m, i) => ({
        id: m.pedidoId,
        posicion: i + 1
    }));

    fetch("{{ url('/admin/pedidos/ordenar') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ orden })
    })
    .then(r => r.json())
    .then(() => {
        Swal.fire('Guardado', 'Orden actualizado correctamente', 'success');
        location.reload();
    });
});



function cargarMapaAsignadosModal(motoqueroId){

    modoOrdenModalAsignados = false;

    const cont = document.getElementById('mapa-asignados-modal');
    if(!cont) return;

    cont.innerHTML = '';

    fetch(`/admin/pedidos/mapa-asignados?motoquero_id=${motoqueroId}`)
    .then(r => r.json())
    .then(pedidos => {

        if(!pedidos.length){
            cont.innerHTML = '<p class="text-muted">Sin pedidos asignados</p>';
            return;
        }

        mapaModalAsignados = new google.maps.Map(cont,{
            zoom:13,
            center:{
                lat: parseFloat(pedidos[0].latitud),
                lng: parseFloat(pedidos[0].longitud)
            }
        });

        marcadoresModalAsignados = [];
        ordenModalAsignados = [];

        pedidos.forEach(p => {

            if(!p.latitud || !p.longitud) return;

            const marker = new google.maps.Marker({
                map: mapaModalAsignados,
                position:{
                    lat: parseFloat(p.latitud),
                    lng: parseFloat(p.longitud)
                },
                label: p.orden ? String(p.orden) : '',
                title: p.nombre
            });

            marker.pedidoId = p.id;


            marker.addListener('click',function(){

                if(!modoOrdenModalAsignados) return;

                if(ordenModalAsignados.includes(marker)) return;

                ordenModalAsignados.push(marker);
                marker.setLabel(String(ordenModalAsignados.length));

            });

            marcadoresModalAsignados.push(marker);

        });

    });
}


document.addEventListener('click',function(e){

    const btn = e.target.closest('#btnOrdenarMapaAsignados');
    if(!btn) return;

    modoOrdenar = true;
    modoOrdenModalAsignados = true;

    document.getElementById('btnGuardarOrdenAsignados').disabled = false;

    ordenModalAsignados = [];

    marcadoresModalAsignados.forEach(m=>{
        m.setLabel('');
    });

    Swal.fire(
        'Modo orden activado',
        'Seleccione los puntos en el mapa en el orden deseado',
        'info'
    );

});




</script>





<script>
document.addEventListener('click', function (e) {

    const btn = e.target.closest('.btn-ruta');
    if (!btn) return;

    // 🔑 Encontrar el panel del motoquero donde se hizo click
    const motoqueroCard = btn.closest('.motoquero-card');
    if (!motoqueroCard) return;

    const ruta = btn.dataset.ruta;

    // 🔹 Activar botón SOLO en este motoquero
    motoqueroCard.querySelectorAll('.btn-ruta').forEach(b => {
        b.classList.remove('active');
    });
    btn.classList.add('active');

    // 🔹 Mostrar SOLO la ruta correspondiente en este motoquero
    motoqueroCard.querySelectorAll('.ruta').forEach(r => {
        r.classList.toggle('active', r.dataset.ruta === ruta);


    });


    const motoqueroId =
        motoqueroCard
            .querySelector('.mapa-por-asignar')
            .dataset.motoquero;

    cargarMapaPorAsignar(motoqueroId, ruta);


});

</script>



<script>

document.addEventListener('click',function(e){

    const btn = e.target.closest('.btn-ver-mapa-asignados');
    if(!btn) return;

    const motoqueroId = btn.dataset.motoquero;

    const modal = new bootstrap.Modal(document.getElementById('modalMapaAsignados'));
    modal.show();

    setTimeout(()=>{
        cargarMapaAsignadosModal(motoqueroId);
    },300);

});


document.addEventListener('click',function(e){

    const btn = e.target.closest('#btnGuardarOrdenAsignados');
    if(!btn) return;

    const orden = ordenModalAsignados.map((m,i)=>({
        id: m.pedidoId,
        posicion: i+1
    }));

    fetch("{{ url('/admin/pedidos/ordenar') }}",{
        method:'POST',
        headers:{
            'Content-Type':'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({orden})
    })
    .then(()=>{

        Swal.fire(
            'Orden guardado',
            'Los pedidos fueron reordenados',
            'success'
        ).then(()=>{
            location.reload();
        });

    });

});



document.addEventListener('click', function(e){

    const boton = e.target.closest('.btnEditarEntrega');

    if(!boton) return;

    const id = boton.dataset.id;

    document.getElementById('edit_entregado_id').value = id;

    fetch(`/admin/pedidos/${id}/metodo-pago`)
    .then(res => res.json())
    .then(data => {

        document.getElementById('metodo_actual').innerText = data.metodo_pago;

        document.getElementById('edit_entregado_pago').value = data.metodo_pago;

        $('#modalEditarEntrega').modal('show');

    });

});


document.addEventListener('click', function(e){

    const btn = e.target.closest('#btnGuardarEntrega');
    if(!btn) return;

    const id = document.getElementById('edit_entregado_id').value;
    const metodo = document.getElementById('edit_entregado_pago').value;

    fetch(`/admin/pedidos/${id}/actualizar-entrega`,{

        method:'POST',

        headers:{
            'Content-Type':'application/json',
            'X-CSRF-TOKEN':document
                .querySelector('meta[name="csrf-token"]').content
        },

        body:JSON.stringify({
            metodo_pago:metodo
        })

    })
    .then(res=>res.json())
    .then(data=>{

        if(data.success){

            Swal.fire(
                'Actualizado',
                'Método de pago actualizado',
                'success'
            ).then(()=>{
                location.reload();
            });

        }

    });

});



</script>



<script>
document.addEventListener('click', function (e) {

    const btn = e.target.closest('.btn-emergencia-bar');
    if (!btn) return;

    if (btn.classList.contains('emergencia-activa')) return;

    const pedidoId = btn.dataset.pedidoId;

    fetch(`/admin/pedidos/${pedidoId}/emergencia`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document
                .querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(r => r.json())
    .then(() => {
        btn.classList.add('emergencia-activa');
        btn.textContent = '🚨 PEDIDO DE EMERGENCIA ACTIVADO';
        btn.disabled = true;
    })
    .catch(err => {
        console.error('Error emergencia:', err);
    });
});
</script>


<script>
function actualizarBotonEmergencia() {

    // Quitar todos los botones existentes
    document.querySelectorAll('.btn-emergencia-bar').forEach(b => b.remove());

    // Recorremos cada lista de ASIGNADOS
    document.querySelectorAll('.lista-asignado').forEach(lista => {

        const primerPedido = lista.querySelector('.pedido-item');

        if (!primerPedido) return;

        const pedidoId = primerPedido.dataset.id;

        const boton = document.createElement('button');
        boton.className = 'btn-emergencia-bar';
        boton.dataset.pedidoId = pedidoId;
        boton.textContent = '🚨 PEDIDO DE EMERGENCIA';

        primerPedido.prepend(boton);
    });
}
</script>



<script>
document.addEventListener('click', function (e) {

    const btn = e.target.closest('.btn-recibo');
    if (!btn) return;

    btn.classList.remove('btn-primary');
    btn.classList.add('btn-success');
    btn.innerHTML = '<i class="fab fa-whatsapp"></i> Recibo enviado';

});
</script>


<script>
function refrescarPanelMotoquero(motoqueroId) {

    const panel = document.getElementById('panel-motoquero-' + motoqueroId);
    if (!panel) {
        console.warn('Panel no encontrado para motoquero:', motoqueroId);
        return;
    }

    // 🔑 conservar fecha actual
    const params = new URLSearchParams(window.location.search);
    const fecha = params.get('fecha') || '';

    const url = fecha
        ? window.location.pathname + '?fecha=' + fecha
        : window.location.pathname;

    fetch(url, {
        method: 'GET',
        credentials: 'same-origin', // 🔥 CLAVE
        cache: 'no-store',          // 🔥 CLAVE
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.text())
    .then(html => {

        const temp = document.createElement('div');
        temp.innerHTML = html;

        const nuevoPanel = temp.querySelector('#panel-motoquero-' + motoqueroId);
        if (!nuevoPanel) {
            console.error('No se encontró el panel en la respuesta HTML');
            return;
        }

        panel.innerHTML = nuevoPanel.innerHTML;
        console.log('Panel actualizado correctamente:', motoqueroId);
    })
    .catch(err => {
        console.error('Error al refrescar panel:', err);
    });
}
</script>


<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {

    document.querySelectorAll('.lista-por-asignar, .lista-asignado').forEach(function(el){

        // 🛑 No inicializar si no hay pedidos
        if (el.querySelectorAll('.pedido-item').length === 0) {
            return;
        }

        new Sortable(el, {

            // ✅ SOLO se pueden arrastrar pedidos
            draggable: '.pedido-item',

            group: {
                name: 'solo-orden',
                pull: false,
                put: false
            },

            animation: 150,
            ghostClass: 'dragging',

            // 📱 Mejor comportamiento en celular
            delay: 200,
            delayOnTouchOnly: true,
            touchStartThreshold: 6,

            onMove: function (evt) {

                // 🔥 Si intenta moverse a otra lista → cancelar
                if (evt.from !== evt.to) {
                    return false;
                }

            },

            onEnd: function(evt){

                // 🛑 Ignorar si no es pedido
                if (!evt.item.classList.contains('pedido-item')) {
                    return;
                }

                // 🛑 Seguridad extra
                if (evt.from !== evt.to) {
                    evt.from.insertBefore(evt.item, evt.from.children[evt.oldIndex]);
                    return;
                }

                let lista = evt.to;

                lista.querySelectorAll('.pedido-item').forEach((item,index)=>{
                    const numero = item.querySelector('b');
                    if(numero){
                        numero.textContent = '#' + (index + 1);
                    }
                });

                let orden = [];

                lista.querySelectorAll('.pedido-item').forEach((item,index)=>{
                    orden.push({
                        id: item.dataset.id,
                        posicion: index + 1
                    });
                });

                fetch("{{ url('/admin/pedidos/ordenar') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({orden: orden})
                });

                actualizarBotonEmergencia();

                const motoqueroId = lista.dataset.motoquero;
                const ruta = lista.dataset.ruta;

                if (mapas[motoqueroId]) {
                    cargarMapaPorAsignar(motoqueroId, ruta);
                }

            }

        });

    });

    actualizarBotonEmergencia();

});



    // === BOTÓN: "Por asignar → Asignado" dentro de POR ASIGNAR (admin)
        $(document).on('click', '.btn_asignar_todos', function () {

        let motoquero_id = $(this).data('motoquero');
        let ruta = $(this).data('ruta'); // 🔥 CLAVE
        let _token = $('meta[name="csrf-token"]').attr('content');


        if (!ruta) {
        Swal.fire('Error', 'Ruta no definida', 'error');
        return;
        }


        $.ajax({
            url: "{{ url('/admin/pedidos/asignar-motoquero-multiple') }}",
            type: "POST",
            data: { motoquero_id: motoquero_id, ruta: ruta, _token: _token },
            success: function () {
             Swal.fire({
                icon: 'success',
                title: `Pedidos de ruta ${ruta} asignados`,
                timer: 900,
                showConfirmButton: false
                }).then(() => location.reload());
            },
                error: function (xhr) {
             Swal.fire('Error', 'No se pudo asignar: ' + (xhr.responseText || ''), 'error');
            }
        });
    });



////////
document.addEventListener('DOMContentLoaded', function() {

    // ========================
    // Variables globales
    // ========================
    let motoqueroSeleccionado = null;
    const modalReporteEl = document.getElementById('modalReporte');
    let modalReporteInstance = null;

    // ========================
    // Inicializar modal (Bootstrap 5)
    // ========================
    if(modalReporteEl) {
        modalReporteInstance = new bootstrap.Modal(modalReporteEl, {
            keyboard: true,   // permite ESC
            backdrop: true    // permite clic afuera
        });
    }

    // ========================
    // Abrir modal y cargar reporte
    // ========================
    document.querySelectorAll('.abrirReporte').forEach(button => {
        button.addEventListener('click', function() {
            motoqueroSeleccionado = this.dataset.motoquero;
            if(modalReporteInstance) {
                modalReporteInstance.show();
                cargarReporte();
            }
        });
    });

    // ========================
    // Cambiar fecha dentro del modal
    // ========================
    document.addEventListener('change', function(e) {
        if(e.target && e.target.id === 'fecha_reporte') {
            cargarReporte();
        }
    });

    // ========================
    // Función para cargar reporte vía AJAX
    // ========================
    function cargarReporte() {
        const fechaGlobal = document.getElementById('fecha_reporte')?.value || document.getElementById('fecha')?.value || '';
        const _token = document.querySelector('meta[name="csrf-token"]').content;

        if(!motoqueroSeleccionado) return;

        const contenidoEl = document.getElementById('contenidoReporte');
        if(!contenidoEl) return;

        contenidoEl.innerHTML = '<p class="text-center">Cargando...</p>';

        fetch("{{ route('admin.pedidos.reporte_motoquero') }}?motoquero_id=" + motoqueroSeleccionado + "&fecha=" + fechaGlobal, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': _token,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => contenidoEl.innerHTML = html)
        .catch(err => contenidoEl.innerHTML = '<p class="text-danger">Error al cargar datos.<br>' + err + '</p>');
    }

    // ========================
    // Delegación de eventos para botones dinámicos
    // ========================
    document.addEventListener('click', function(e) {
        // Ejemplo: botón dentro del reporte dinámico
        if(e.target && e.target.classList.contains('btn-dinamico')) {
            e.preventDefault();
            // Aquí colocas la acción que necesites
            console.log('Botón dinámico clickeado:', e.target);
        }
    });

    // ========================
    // Cerrar modal con botones de cierre (X o Footer)
    // ========================
    document.querySelectorAll('[data-bs-dismiss="modal"]').forEach(btn => {
        btn.addEventListener('click', function() {
            if(modalReporteInstance) modalReporteInstance.hide();
        });
    });

});

</script>





{{-- ======================================================= --}}
{{-- JS: Registrar pedido desde WhatsApp --}}
{{-- ======================================================= --}}

<script>
document.addEventListener('click', function (e) {

    /* ===============================
       CENTRAR MAPA AL HACER CLICK
       =============================== */
    const cardMapa = e.target.closest('.whatsapp-contact');
    if (cardMapa && !e.target.classList.contains('btn-registrar-pedido')) {
        const lat = parseFloat(cardMapa.dataset.lat);
        const lng = parseFloat(cardMapa.dataset.lng);

        if (!isNaN(lat) && !isNaN(lng)) {
            mapaGeneral.setCenter({ lat, lng });
            mapaGeneral.setZoom(17);
        }
    }

    /* ===============================
       REGISTRAR PEDIDO
       =============================== */
    if (!e.target.classList.contains('btn-registrar-pedido')) return;

    const btn  = e.target;
    const card = btn.closest('.whatsapp-contact');

    const motoquero = card.querySelector('.selector-motoquero').value;
    const ruta      = card.querySelector('.selector-ruta').value;

    if (!motoquero || !ruta) {
        Swal.fire({
            icon: 'warning',
            title: 'Faltan datos',
            text: 'Seleccione motoquero y ruta antes de registrar el pedido'
        });
        return;
    }

    // Cargamos datos en el formulario oculto
    document.getElementById('wp_nombre').value    = btn.dataset.nombre;
    document.getElementById('wp_celular').value   = btn.dataset.celular;
    document.getElementById('wp_motoquero').value = motoquero;
    document.getElementById('wp_ruta').value      = ruta;

    // Enviamos como formulario normal
    document.getElementById('formPedidoWhatsapp').submit();
});
</script>





<script>
let llamadaActual = null;
let modalLlamada = null;

// ==============================
// Inicializar modal (Bootstrap 5)
// ==============================
document.addEventListener("DOMContentLoaded", () => {
    const modalEl = document.getElementById("modalLlamada");
    if (modalEl) {
        modalLlamada = new bootstrap.Modal(modalEl, {
            backdrop: true,
            keyboard: true
        });
    }
});

// ==============================
// POLLING seguro cada 5 segundos
// ==============================
setInterval(() => {
    fetch("{{ route('llamadas.poll') }}", {
        headers: {
            'Accept': 'application/json'
        },
        redirect: 'manual'
    })
    .then(res => {
        // ❌ NO seguir redirecciones
        if (res.status === 302) return null;
        if (res.status === 204) return null;
        if (res.status === 401) return null;

        return res.json();
    })
    .then(llamadas => {
        if (!Array.isArray(llamadas)) return;
        if (llamadas.length === 0) return;

        mostrarModal(llamadas[0]);
    })
    .catch(() => {});
}, 5000);


// ==============================
// Cerrar modal
// ==============================
function cerrarModalLlamada() {
    if (!modalLlamada || !llamadaActual) return;

    const audio = document.getElementById("sonidoAlerta");
    if (audio) {
        audio.pause();
        audio.currentTime = 0;
    }

    if (navigator.vibrate) navigator.vibrate(0);

    fetch("/admin/llamadas/" + llamadaActual.id + "/cerrar", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        }
    })
    .finally(() => {
        modalLlamada.hide();
        llamadaActual = null;
    });
}


// ==============================
// Mostrar modal
// ==============================
function mostrarModal(data) {

    // Evitar duplicados
    if (llamadaActual && llamadaActual.id === data.id) return;

    llamadaActual = data;

    const cont = document.getElementById("contenidoLlamada");
    if (!cont) return;

    cont.innerHTML = `
        <p><strong>Cliente:</strong> ${data.nombre_cliente}</p>
        <p><strong>Celular:</strong> ${data.celular_cliente}</p>
        <p><strong>Descripción:</strong> ${data.cliente?.direccion ?? 'Sin descripción'}</p>
        <p><strong>Motoquero:</strong> ${data.motoquero_id}, ${data.nombre_motoquero}</p>
        <p class="text-danger fw-bold">
            El distribuidor solicita contactar al cliente.
        </p>
    `;

    // 🔊 Sonido
    const audio = document.getElementById("sonidoAlerta");
    if (audio) {
        audio.currentTime = 0;
        audio.play().catch(() => {});
    }

    // 📳 Vibración
    if (navigator.vibrate) {
        navigator.vibrate([200, 100, 200]);
    }

    modalLlamada.show();

    // Acción botón aceptar
    const btnAceptar = document.getElementById("btnAceptarLlamada");
    if (btnAceptar) {
        btnAceptar.onclick = () => {
            atenderLlamada(data.id, data.celular_cliente);
        };
    }
}


// ==============================
// Atender llamada
// ==============================
function atenderLlamada(id, celular) {

    fetch("/admin/llamadas/" + id + "/atender", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        }
    })
    .finally(() => {

        const audio = document.getElementById("sonidoAlerta");
        if (audio) {
            audio.pause();
            audio.currentTime = 0;
        }

        if (navigator.vibrate) navigator.vibrate(0);

        if (modalLlamada) modalLlamada.hide();

        // WhatsApp
        const url =
            "https://wa.me/" +
            celular +
            "?text=" +
            encodeURIComponent("El distribuidor ya llegó a su ubicación.");

        window.open(url, "_blank");

        llamadaActual = null;
    });
}
</script>




<script>
let avisoActual = null;
let modalAviso = null;

// Inicializar modal
document.addEventListener("DOMContentLoaded", () => {
    modalAviso = new bootstrap.Modal(
        document.getElementById("modalAvisoNavegacion"),
        { backdrop: true, keyboard: true }
    );
});

// 🔄 Poll cada 5 segundos (SEGURO)
setInterval(() => {
    fetch("{{ route('avisos.navegacion.poll') }}", {
        headers: {
            'Accept': 'application/json'
        },
        redirect: 'manual'
    })
    .then(res => {
        // ❌ nunca seguir redirects
        if (res.status === 302) return null;
        if (res.status === 204) return null;
        if (res.status === 401) return null;

        return res.json();
    })
    .then(aviso => {
        if (!aviso || !aviso.id) return;
        mostrarAviso(aviso);
    })
    .catch(() => {});
}, 5000);


function cerrarModalAviso() {
    if (!modalAviso || !avisoActual) return;

    detenerSonido();

    fetch("/admin/avisos-navegacion/" + avisoActual.id + "/cerrar", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Accept": "application/json"
        }
    }).finally(() => {
        modalAviso.hide();
        avisoActual = null;
    });
}


function mostrarAviso(data) {

    if (avisoActual && avisoActual.id === data.id) return;

    avisoActual = data;

    document.getElementById("contenidoAvisoNavegacion").innerHTML = `
        <p><strong>Cliente:</strong> ${data.cliente}</p>
        <p><strong>Celular:</strong> ${data.celular}</p>
        <p><strong>Descripción:</strong> ${data.pedido?.cliente?.direccion ?? 'Sin descripción'}</p>
        <p><strong>Motoquero ID:</strong> ${data.motoquero_id}</p>
        <p class="text-primary fw-bold">
            El distribuidor inició la navegación hacia la ubicación del cliente.
        </p>
    `;

    reproducirSonido();
    modalAviso.show();

    if (avisoActual.motoquero_id) {
        refrescarPanelMotoquero(avisoActual.motoquero_id);
    }

    document.getElementById("btnAvisarCliente").onclick = () => {
        atenderAviso(data.id, data.celular);
    };
}


function atenderAviso(id, celular) {

    fetch("/admin/avisos-navegacion/" + id + "/atender", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Accept": "application/json"
        }
    })
    .then(() => {
        detenerSonido();
        modalAviso.hide();
        avisoActual = null;

        const url =
            "https://wa.me/" +
            celular +
            "?text=" +
            encodeURIComponent(
                "El distribuidor está en camino a su ubicación. Por favor manténgase atento."
            );

        window.open(url, "_blank");
    });
}


function reproducirSonido() {
    const audio = document.getElementById("sonidoEnCamino");
    audio.currentTime = 0;
    audio.play().catch(() => {});
    if (navigator.vibrate) navigator.vibrate([200, 100, 200]);
}

function detenerSonido() {
    const audio = document.getElementById("sonidoEnCamino");
    audio.pause();
    audio.currentTime = 0;
    if (navigator.vibrate) navigator.vibrate(0);
}

</script>



<script>
/**
 * ================================
 * TRACKING DE MOTOQUEROS (ADMIN)
 * ================================
 * - Usa SOLO motoquero.id
 * - No depende de usuario
 * - Dibuja recorrido
 * - Actualiza cada 5 segundos
 */

const marcadoresMotoqueros = {};
const recorridosMotoqueros = {};
const MAX_PUNTOS = 50; // límite de puntos del recorrido (performance)
let colaMotoqueros = []; // temporal

// 🔄 Polling cada 5 segundos
setInterval(() => {
    fetch("{{ route('admin.pedidos.motoqueros.ubicaciones') }}", {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(r => {
        if (!r.ok) throw new Error('Error al obtener ubicaciones');
        return r.json();
    })
    .then(data => {
        if (!Array.isArray(data)) return;

        if (!mapaGeneralListo) {
            colaMotoqueros.push(...data);
        } else {
            data.forEach(actualizarMotoqueroEnMapa);
        }

    })
    .catch(err => {
        console.error('Error tracking motoqueros:', err);
    });
}, 5000);


/**
 * =================================
 * Actualizar / crear marcador
 * =================================
 */
function actualizarMotoqueroEnMapa(m) {

    // 🛡️ Validaciones fuertes
    if (
        !m ||
        !m.motoquero_id ||
        m.latitud === null ||
        m.longitud === null
    ) return;

    if (!mapaGeneralListo || !window.google || !google.maps) return;

    // ✅ LatLng REAL (no objeto plano)
    const pos = new google.maps.LatLng(
        parseFloat(m.latitud),
        parseFloat(m.longitud)
    );

    // 🚚 Icono camioncito
    const icono = {
        url: '/img/camioncito.png',
        scaledSize: new google.maps.Size(36, 36)
    };

    // 🆕 Crear marcador si no existe
    if (!marcadoresMotoqueros[m.motoquero_id]) {

        marcadoresMotoqueros[m.motoquero_id] = new google.maps.Marker({
            map: mapaGeneral,
            position: pos, // ✅ OK
            icon: icono,
            title: `DIS: ${m.motoquero_id} | Últ. act: ${formatearFecha(m.registrado_en)}`
        });

        // 📍 Crear recorrido (Polyline SOLO acepta LatLng)
        recorridosMotoqueros[m.motoquero_id] = new google.maps.Polyline({
            map: mapaGeneral,
            path: [pos], // ✅ OK
            strokeOpacity: 0.9,
            strokeWeight: 4
        });

    } else {

        // 🔄 Actualizar posición
        marcadoresMotoqueros[m.motoquero_id].setPosition(pos);
        marcadoresMotoqueros[m.motoquero_id].setTitle(
            `DIS: ${m.motoquero_id} | Últ. act: ${formatearFecha(m.registrado_en)}`
        );

        // ➕ Agregar punto al recorrido
        const path = recorridosMotoqueros[m.motoquero_id].getPath();
        path.push(pos); // ✅ SOLO LatLng

        // 🧹 Limitar cantidad de puntos
        if (path.getLength() > MAX_PUNTOS) {
            path.removeAt(0);
        }
    }
}


/**
 * ================================
 * Formatear fecha amigable
 * ================================
 */
function formatearFecha(fecha) {
    if (!fecha) return 'N/D';
    const d = new Date(fecha);
    return d.toLocaleTimeString('es-BO', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
}
</script>


<script>
let modalEditarInstance = null;

// ===============================
// Inicializar modal UNA VEZ
// ===============================
document.addEventListener('DOMContentLoaded', () => {

    const modalEl = document.getElementById('modalEditarPedido');
    if (!modalEl) {
        console.error('❌ Modal editar no encontrado');
        return;
    }

    modalEditarInstance = new bootstrap.Modal(modalEl, {
        backdrop: true,
        keyboard: true
    });
});

// ===============================
// Abrir modal (FUNCIÓN GLOBAL)
// ===============================
function abrirModalEditar() {
    if (!modalEditarInstance) return;
    modalEditarInstance.show();
}

// ===============================
// Cerrar modal (FUNCIÓN GLOBAL)
// ===============================
function cerrarModalEditar() {
    if (!modalEditarInstance) return;
    modalEditarInstance.hide();
}
</script>


<script>
document.addEventListener('click', function (e) {

    const btn = e.target.closest('.btn-editar-pedido');
    if (!btn) return;

    // 🛑 CLAVES ABSOLUTAS
    e.preventDefault();
    e.stopPropagation();

    const pedidoId = btn.dataset.pedidoId;
    if (!pedidoId) return;

    fetch(`/admin/pedidos/${pedidoId}/editar`)
        .then(res => res.json())
        .then(data => {

            if (!['Por asignar', 'Asignado'].includes(data.estado)) {
                Swal.fire(
                    'No permitido',
                    'Este pedido no se puede editar',
                    'warning'
                );
                return;
            }

            // Cargar datos
            document.getElementById('edit_pedido_id').value = data.id;
            document.getElementById('edit_cliente').value = data.cliente;
            document.getElementById('edit_motoquero').value = data.motoquero_id ?? '';
            document.getElementById('edit_ruta').value = data.ruta ?? 'A';
            document.getElementById('edit_promo_activa').checked = data.promo_activa == 1;

            const link = document.getElementById('edit_ubicacion');
            if (data.ubicacion) {
                link.href = data.ubicacion;
                link.textContent = 'Ver ubicación';
            } else {
                link.href = '#';
                link.textContent = 'No registrada';
            }

            abrirModalEditar();
        })
        .catch(() => {
            Swal.fire('Error', 'No se pudo cargar el pedido', 'error');
        });

});
</script>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const btnGuardar = document.getElementById('btnGuardarEdicion');

    if (!btnGuardar) {
        console.error('❌ btnGuardarEdicion NO existe en el DOM');
        return;
    }

    btnGuardar.addEventListener('click', function () {

        const pedidoId = document.getElementById('edit_pedido_id')?.value;
        if (!pedidoId) {
            console.warn('⚠️ No hay pedido_id');
            return;
        }

        const data = {
            pedido_id: pedidoId,
            promo_activa: document.getElementById('edit_promo_activa').checked ? 1 : 0,
            motoquero_id: document.getElementById('edit_motoquero').value,
            ruta: document.getElementById('edit_ruta').value,
            _token: '{{ csrf_token() }}'
        };

        fetch('/admin/pedidos/actualizar-edicion', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        })
        .then(res => {
            if (!res.ok) throw new Error('Error HTTP');
            return res.json();
        })
        .then(() => {
            cerrarModalEditar();

            Swal.fire({
                icon: 'success',
                title: 'Pedido actualizado',
                timer: 900,
                showConfirmButton: false
            }).then(() => location.reload());
        })
        .catch(err => {
            console.error(err);
            Swal.fire('Error', 'No se pudo guardar el pedido', 'error');
        });

    });

});
</script>

<script>
document.addEventListener('click', function (e) {

    const boton = e.target.closest('.btn-avisar');
    if (!boton) return;

    let pedidoId = boton.dataset.id;
    let tipo = boton.dataset.tipo;

    fetch(`/admin/pedidos/${pedidoId}/avisar`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ tipo: tipo })
    })
    .then(response => response.json())
    .then(data => {

        if(data.success){

            boton.disabled = true;
            boton.innerText = "✔ Enviado";

            setTimeout(() => {
                boton.disabled = false;
                boton.innerText = tipo === 'ya_sale'
                    ? "🚀 Ya Sale"
                    : "📞 No Contesta";
            }, 3000);

        } else {
            alert('Error al enviar aviso');
        }

    })
    .catch(error => {
        alert('Error de conexión');
    });

});
</script>

<script>
document.addEventListener('change', function(e) {

    // Siempre
    if (e.target.classList.contains('toggle-siempre')) {

        let clienteId = e.target.dataset.clienteId;
        let valor = e.target.checked ? 1 : 0;

        fetch(`/admin/clientes/${clienteId}/toggle-notificacion`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ valor: valor })
        });
    }

    // Este pedido
    if (e.target.classList.contains('toggle-este-pedido')) {

        let pedidoId = e.target.dataset.pedidoId;
        let valor = e.target.checked ? 1 : 0;

        fetch(`/admin/pedidos/${pedidoId}/toggle-notificacion`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ valor: valor })
        });
    }

});
</script>

<!-- Modal de llamada de llegada-->
<div class="modal fade" id="modalLlamada" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-warning">
      <div class="modal-header bg-warning">
        <h5 class="modal-title">
            🚨 Nueva Solicitud de Llamada
        </h5>
        <button type="button" class="btn-close" onclick="cerrarModalLlamada()"></button>
      </div>

      <div class="modal-body" id="contenidoLlamada">
        <!-- Aquí se cargará la info -->
      </div>

      <div class="modal-footer">
        <button id="btnAceptarLlamada" class="btn btn-primary">
            Aceptar y Contactar
        </button>

        <!-- CORRECTO EN BS5 -->
        <button type="button" class="btn btn-secondary" onclick="cerrarModalLlamada()">
            Cerrar
        </button>
      </div>
    </div>
  </div>
</div>

<audio id="sonidoAlerta" src="/sounds/alertallegada.mp3" preload="auto"></audio>



<!-- Modal de llamada en camino -->
<div class="modal fade" id="modalAvisoNavegacion" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-primary">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">
            🚚 Aviso de Navegación
        </h5>
        <button type="button" class="btn-close" onclick="cerrarModalAviso()"></button>
      </div>

      <div class="modal-body" id="contenidoAvisoNavegacion"></div>

      <div class="modal-footer">
        <button id="btnAvisarCliente" class="btn btn-success">Aceptar y Avisar</button>

        <button type="button" class="btn btn-secondary" onclick="cerrarModalAviso()">
            Cerrar
        </button>
      </div>
    </div>
  </div>
</div>
<audio id="sonidoEnCamino" src="/sounds/alertaencamino.mp3" preload="auto"></audio>


<!-- Modal Reporte de Ventas (Bootstrap 5) -->
<div class="modal fade" id="modalReporte" tabindex="-1" aria-labelledby="modalReporteLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      
      <!-- Header -->
      <div class="modal-header">
        <h5 class="modal-title" id="modalReporteLabel">Reporte de Ventas - Motoquero</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      
      <!-- Body -->
      <div class="modal-body">
        <div class="mb-3 d-flex align-items-center" style="gap: 10px;">
          <label for="fecha_reporte" class="form-label mb-0"><b>Fecha:</b></label>
          <input type="date" id="fecha_reporte" class="form-control" value="{{ now()->format('Y-m-d') }}">
        </div>

        <div id="contenidoReporte">
          <p class="text-center">Seleccione un motoquero y una fecha para cargar el reporte.</p>
        </div>
      </div>
      
      <!-- Footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>

    </div>
  </div>
</div>



<!-- MODAL EDITAR PEDIDO -->
<div class="modal fade" id="modalEditarPedido" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Editar pedido</h5>
                <!-- ❌ SIN data-bs-dismiss -->
                <button type="button" class="btn-close" onclick="cerrarModalEditar()"></button>
            </div>

            <div class="modal-body">

                <input type="hidden" id="edit_pedido_id">

                <!-- DATOS DEL CLIENTE -->
                <div class="mb-3">
                    <label class="form-label">Cliente</label>
                    <input type="text" class="form-control" id="edit_cliente" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Ubicación</label>
                    <a href="#" target="_blank" id="edit_ubicacion">Ver ubicación</a>
                </div>

                <hr>

                <!-- PROMOCIÓN -->
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" id="edit_promo_activa">
                    <label class="form-check-label" for="edit_promo_activa">
                        Cliente con promoción activa
                    </label>
                </div>

                <!-- DISTRIBUIDOR -->
                <div class="mb-3">
                    <label class="form-label">Distribuidor</label>
                    <select class="form-select" id="edit_motoquero">
                        @foreach($motoqueros as $m)
                            <option value="{{ $m->id }}">
                                {{ $m->apellidos }} {{ $m->nombres }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- RUTA -->
                <div class="mb-3">
                    <label class="form-label">Ruta</label>
                    <select class="form-select" id="edit_ruta">
                        <option value="A">Ruta A</option>
                        <option value="B">Ruta B</option>
                        <option value="C">Ruta C</option>
                        <option value="D">Ruta D</option>
                    </select>
                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="cerrarModalEditar()">Cancelar</button>
                <button class="btn btn-primary" id="btnGuardarEdicion">
                    Guardar cambios
                </button>
            </div>

        </div>
    </div>
</div>



<div class="modal fade" id="modalMapaAsignados" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Mapa de pedidos asignados</h5>
                <button type="button" class="btn-close" data-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div id="mapa-asignados-modal" style="height:500px;"></div>

                <div class="mt-3 text-center">
                    <button class="btn btn-warning btn-sm" id="btnOrdenarMapaAsignados">
                        Ordenar pedidos
                    </button>

                    <button class="btn btn-success btn-sm" id="btnGuardarOrdenAsignados" disabled>
                        Guardar orden
                    </button>
                </div>

            </div>

        </div>
    </div>
</div>




<div class="modal fade" id="modalEditarEntrega" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    Editar método de pago
                </h5>

                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">

                <!-- ID del pedido -->
                <input type="hidden" id="edit_entregado_id">

                <!-- Metodo actual -->
                <div class="form-group">
                    <label><b>Método actual</b></label>

                    <div style="
                        padding:8px;
                        background:#f1f1f1;
                        border-radius:6px;
                        font-weight:bold;
                    ">
                        <span id="metodo_actual">...</span>
                    </div>
                </div>

                <!-- Cambiar metodo -->
                <div class="form-group">
                    <label>Cambiar método de pago</label>

                    <select class="form-control" id="edit_entregado_pago">
                        <option value="">Seleccione método</option>
                        <option value="Efectivo">Efectivo</option>
                        <option value="QR">QR</option>
                    </select>
                </div>

            </div>

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-dismiss="modal">
                    Cancelar
                </button>

                <button
                    type="button"
                    class="btn btn-success"
                    id="btnGuardarEntrega">
                    Guardar cambio
                </button>

            </div>

        </div>
    </div>
</div>



@stop
<script>
window.addEventListener("load", function () {

    if (typeof google === "undefined") {
        console.error("Google Maps no cargó");
        return;
    }

    // 🔥 INICIAR MAPA GENERAL
    initMapaGeneralPedidos();

    // 🔥 MAPAS POR ASIGNAR
    document.querySelectorAll('.mapa-por-asignar').forEach(div => {
        cargarMapaPorAsignar(
            div.dataset.motoquero,
            div.dataset.ruta || 'A'
        );
    });

});
</script>