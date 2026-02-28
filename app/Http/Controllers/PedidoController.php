<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Cliente;
use App\Models\Tarifa;
use App\Models\Motoquero;
use App\Models\TmpPedido;
use App\Models\DetallePedido;
use App\Models\Configuracion;
use App\Models\Producto;
use App\Models\WhatsAppMessage;   // modelo para mensajes entrantes de WhatsApp
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\AvisoPedido;
use App\Models\DespachoRepartidor;

use Illuminate\Http\Request;

class PedidoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $despachosHoy = DespachoRepartidor::whereDate('created_at', today())
            ->get()
            ->keyBy('motoquero_id');

        $fecha = $request->get('fecha', date('Y-m-d'));

        $pedidos = Pedido::with(['cliente', 'motoquero', 'detalles'])
            ->whereDate('updated_at', $fecha)
            ->get();

        // 🔥 PRODUCTOS FIJOS (solo consulta una vez)
        $productoRegular = Producto::find(1);   // Agua Regular
        $productoAlcalina = Producto::find(2);  // Agua Alcalina

        foreach ($pedidos as $pedido) {

            if ($pedido->cliente) {

                $cliente = $pedido->cliente;

                // 🔹 Precio Regular
                $pedido->precio_regular_ref = $cliente->getPrecioProducto($productoRegular);

                // 🔹 Precio Alcalina
                $pedido->precio_alcalina_ref = $cliente->getPrecioProducto($productoAlcalina);

            } else {
                $pedido->precio_regular_ref = null;
                $pedido->precio_alcalina_ref = null;
            }
        }

        $motoqueros = Motoquero::all();

        $clientes = Cliente::select(
            'id',
            'nombre',
            'ubicacion_gps',
            'latitud',
            'longitud'
        )->get();

        $hoyInicio = Carbon::today()->startOfDay();
        $hoyFin = Carbon::today()->endOfDay();

        $contactosHoy = WhatsAppMessage::select(
                'from',
                DB::raw('MAX(name) as name'),
                DB::raw('MAX(created_at) as last_at')
            )
            ->whereBetween('created_at', [$hoyInicio, $hoyFin])
            ->groupBy('from')
            ->orderByDesc('last_at')
            ->get();

        return view('admin.pedidos.index', compact(
            'pedidos',
            'motoqueros',
            'fecha',
            'contactosHoy',
            'clientes',
            'despachosHoy'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $motoqueros = Motoquero::all();

        $session_id = session()->getId();
        $tmpPedidos = TmpPedido::where('session_id' , $session_id)->get();
        $clientes = Cliente::all();
        $tarifas = Tarifa::all();
        return view('admin.pedidos.create', compact('tmpPedidos', 'clientes', 'tarifas', 'motoqueros'));
    }

    /**
     * Store a newly created resource in storage.
     */
  public function store(Request $request)
{
    try {
        $session_id = session()->getId();

        // NUEVO: Validación de cliente desde WhatsApp
        if ($request->filled('nombre_whatsapp') || $request->filled('celular_whatsapp')) {

            $cliente = null;

            // Primero buscamos por celular
            if ($request->filled('celular_whatsapp')) {
                $cliente = Cliente::where('celular', $request->celular_whatsapp)->first();
            }

            // Si no encontramos por celular, buscamos por nombre/código
            if (!$cliente && $request->filled('nombre_whatsapp')) {
                $cliente = Cliente::where('nombre', $request->nombre_whatsapp)->first();
            }

            if (!$cliente) {
                return response()->json([
                    'success' => false,
                    'message' => 'El cliente no está registrado',
                ], 404);
            }

            $cliente_id = $cliente->id;

        } else {
            // Flujo normal
            $request->validate([
                'cliente_id' => 'required|integer',
            ]);
            $cliente_id = $request->cliente_id;
        }

        // Crear el pedido
        $pedido = new Pedido();
        $pedido->cliente_id = $cliente_id;
        $pedido->estado = 'Pendiente';
        $pedido->total_precio = $request->input('total_precio', 0);
        $pedido->tarifa_id = $request->input('tarifa_id', null);
        $pedido->save();

        // Guardar detalles desde tmp_pedidos
        $tmp_pedidos = TmpPedido::where('session_id', $session_id)->get();
        if ($tmp_pedidos->count() > 0) {
            foreach ($tmp_pedidos as $tmp_pedido) {
                $detalle_pedido = new DetallePedido();
                $detalle_pedido->pedido_id = $pedido->id;
                $detalle_pedido->producto = $tmp_pedido->producto;
                $detalle_pedido->detalle = $tmp_pedido->detalle;
                $detalle_pedido->cantidad = $tmp_pedido->cantidad;
                $detalle_pedido->precio_unitario = $tmp_pedido->precio_unitario;
                $detalle_pedido->precio_total = $tmp_pedido->precio_total;
                $detalle_pedido->save();
            }

            TmpPedido::where('session_id', $session_id)->delete();
        }


        // ===============================
        // CREACIÓN RÁPIDA DESDE INDEX
        // ===============================
        if ($request->filled('motoquero_id') && $request->filled('ruta')) {

            $request->validate([
                'motoquero_id' => 'required|integer|exists:motoqueros,id',
                'ruta' => 'required|in:A,B,C,D',
            ]);

            // Último orden para ese motoquero + ruta + hoy
            $ultimoOrden = Pedido::where('motoquero_id', $request->motoquero_id)
                ->where('ruta', $request->ruta)
                ->where('estado', 'Por asignar')
                ->whereDate('created_at', today())
                ->whereNotNull('orden')
                ->max('orden');


            // Si no hay pedidos aún, empezamos desde 0 y sumamos 1 => orden = 1

            $pedido->orden = ($ultimoOrden ?? 0) + 1;


            $pedido->update([
                'motoquero_id' => $request->motoquero_id,
                'ruta'         => $request->ruta,
                'estado'       => 'Por asignar',
                'orden'        => $ultimoOrden + 1,
                'updated_at'   => now()
            ]);
        }



        return response()->json([
            'success' => true,
            'message' => 'Pedido registrado correctamente',
            'pedido' => $pedido,
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al registrar el pedido: '.$e->getMessage(),
            'error' => $e->getMessage(),
        ], 500);
    }
}

    
        public function actualizarOrden(Request $request)
    {
        foreach ($request->orden as $item) {
            Pedido::where('id', $item['id'])
                ->update(['orden' => $item['posicion']]);
        }

        return response()->json(['success' => true]);
    }


    public function moverPorAsignar(Request $request)
{
    $request->validate([
        'pedidos' => 'required|array|min:1',
        'pedidos.*.cliente_id'   => 'required|integer|exists:clientes,id',
        'pedidos.*.motoquero_id' => 'required|integer|exists:motoqueros,id',
        'pedidos.*.ruta'         => 'required|in:A,B,C,D',
    ]);

    DB::beginTransaction();

    try {

        foreach ($request->pedidos as $data) {

            $clienteId   = $data['cliente_id'];
            $motoqueroId = $data['motoquero_id'];
            $ruta        = $data['ruta'];

            /**
             * 1️⃣ Crear pedido en estado Pendiente
             */
            $pedido = Pedido::create([
                'cliente_id'  => $clienteId,
                'estado'      => 'Pendiente',
                'total_precio'=> 0,
            ]);

            /**
             * 2️⃣ Calcular último orden para ese motoquero + ruta + hoy
             */
            $ultimoOrden = Pedido::where('motoquero_id', $motoqueroId)
                ->where('ruta', $ruta)
                ->where('estado', 'Por asignar')
                ->whereDate('created_at', today())
                ->whereNotNull('orden')
                ->max('orden');


                $pedido->orden = ($ultimoOrden ?? 0) + 1;
            /**
             * 3️⃣ Asignar motoquero, ruta, orden y cambiar estado
             */
            $pedido->update([
                'motoquero_id' => $motoqueroId,
                'ruta'         => $ruta,
                'estado'       => 'Por asignar',
                'orden'        => $ultimoOrden + 1,
                'updated_at'   => now(),
            ]);
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Pedidos creados y asignados correctamente'
        ]);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
}


    public function asignar_motoquero(Request $request)
    {
        $request->validate([
            'pedido_id' => 'required|integer',
            'motoquero_id' => 'required|integer',
        ]);

        try {   
            $pedido = Pedido::find($request->pedido_id);
            $pedido->motoquero_id = $request->motoquero_id;
            $pedido->save();

            return response()->json([
                'success' => true,
                'message' => 'Motoquero asignado correctamente',
                'pedido' => $pedido,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar el motoquero',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function cambiar_motoquero(Request $request)
    {
        $request->validate([
            'pedido_id' => 'required|integer',
        ]);

        try {
            $pedido = Pedido::find($request->pedido_id);
            $pedido->motoquero_id = null;
            $pedido->estado = 'Pendiente';
            $pedido->save();

            return response()->json([
                'success' => true,
                'message' => 'Motoquero cambiado correctamente',
                'pedido' => $pedido,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el motoquero',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

public function ver_pedidos_motoquero($id, Request $request)
{
    $configuracion = Configuracion::first();
    $motoquero = Motoquero::find($id);

    // Determinamos si el usuario envió manualmente la fecha
    $userFiltered = $request->has('fecha');

    // Si el usuario filtró → usar su fecha; si no → usar hoy
    $fecha = $userFiltered ? $request->get('fecha') : Carbon::today()->toDateString();

    // Pedidos del día (Asignado y En camino usan today() — así siguen mostrando sólo pedidos de hoy)
    $pedidos = Pedido::with(['cliente'])
        ->where('motoquero_id', $id)
        ->where('estado', 'Asignado')
        ->whereDate('created_at', today())
        ->orderBy('orden', 'asc')
        ->get();

    $pedidos_en_camino = Pedido::with(['cliente'])
        ->where('motoquero_id', $id)
        ->where('estado', 'En camino')
        ->whereDate('created_at', today())
        ->orderBy('updated_at', 'desc')
        ->get();

    // Pedidos entregados filtrados por la fecha elegida ($fecha)
    $pedidos_entregados = Pedido::with(['cliente', 'detalles'])
        ->where('motoquero_id', $id)
        ->where('estado', 'Entregado')
        ->whereDate('updated_at', $fecha)
        ->orderBy('updated_at', 'desc')
        ->get();

    // Cargamos productos
    $productos = Producto::all();


    /**
     * NUEVO: obtener la última compra ENTREGADA por cada cliente que aparece
     * en $pedidos o $pedidos_en_camino.
     */
    $clienteIds = $pedidos->pluck('cliente_id')
        ->merge($pedidos_en_camino->pluck('cliente_id'))
        ->unique()
        ->filter()
        ->values()
        ->all();

    $ultimasCompras = collect();
    if (!empty($clienteIds)) {
        // Obtener pedidos entregados de esos clientes, ordenados desc por id (más reciente primero)
        $entregados = Pedido::with('detalles')
            ->whereIn('cliente_id', $clienteIds)
            ->where('estado', 'Entregado') // ajusta si tu estado final es otro
            ->orderBy('id', 'desc')
            ->get()
            ->groupBy('cliente_id');

        // Para cada cliente tomar el primer pedido (más reciente)
        foreach ($entregados as $clienteId => $coleccionPedidos) {
            $ultimasCompras->put($clienteId, $coleccionPedidos->first());
        }
    }

    return view('admin.pedidos.ver_pedidos_motoquero', compact(
        'pedidos',
        'motoquero',
        'configuracion',
        'pedidos_en_camino',
        'pedidos_entregados',
        'productos',
        'fecha',
        'userFiltered',
        'ultimasCompras' // <-- nueva variable pasada a la vista
    ));
}

    public function tomar_pedido(Request $request)
    {
        $pedido = Pedido::find($request->pedido_id);
        $pedido->estado = 'En camino';
        $pedido->save();
        return redirect()->back()
            ->with('mensaje', 'Pedido tomado correctamente')
            ->with('icono', 'success');
    }

    public function rechazar_pedido(Request $request)
    {
        $pedido = Pedido::find($request->pedido_id);
        $pedido->estado = 'Pendiente';
        $pedido->motoquero_id = null;
        $pedido->save();
        return redirect()->back()
            ->with('mensaje', 'Pedido rechazado correctamente')
            ->with('icono', 'success');
    }


   public function finalizar_pedido(Request $request)
    {
        $request->validate([
            'pedido_id'     => 'required|integer|exists:pedidos,id',
            'producto_id.*' => 'required|integer|exists:productos,id',
            'cantidad.*'    => 'required|numeric|min:1',
            'metodo_pago'   => 'required|in:Efectivo,QR',
        ]);

        DB::beginTransaction();

        try {

            $pedido = Pedido::with('cliente')->findOrFail($request->pedido_id);
            $cliente = $pedido->cliente;

            // Limpiar detalles anteriores
            DetallePedido::where('pedido_id', $pedido->id)->delete();

            $total = 0;

            foreach ($request->producto_id as $i => $producto_id) {

                $producto = Producto::find($producto_id);
                if (!$producto) continue;

                $cantidad = $request->cantidad[$i];

                // 🔥 PRECIO CENTRALIZADO
                $precio_unitario = $cliente->getPrecioProducto($producto);

                $subtotal = $precio_unitario * $cantidad;
                $total += $subtotal;

                DetallePedido::create([
                    'pedido_id'       => $pedido->id,
                    'producto'        => $producto->nombre,
                    'detalle'         => '',
                    'cantidad'        => $cantidad,
                    'precio_unitario' => $precio_unitario,
                    'precio_total'    => $subtotal,
                ]);
            }

            $pedido->update([
                'estado'        => 'Entregado',
                'metodo_pago'   => $request->metodo_pago,
                'total_precio'  => $total,
            ]);

            DB::commit();

            return redirect()->back()
                ->with('mensaje', 'Pedido finalizado correctamente')
                ->with('icono', 'success');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('mensaje', 'Error: '.$e->getMessage())
                ->with('icono', 'error');
        }
    }


    public function asignarMotoqueroMultiple(Request $request)
{
    $motoquero_id = $request->motoquero_id;
    $ruta = $request->ruta; // ⭐ NUEVO

    DB::beginTransaction();

    try {

        // ⭐ VALIDACIÓN SIMPLE (opcional pero recomendado)
        if (!$ruta) {
            return response()->json([
                'success' => false,
                'message' => 'Ruta no definida'
            ], 422);
        }

        // Obtenemos los pedidos por asignar **en el orden actual** (orden asc)
        $pedidosPorAsignar = Pedido::where('motoquero_id', $motoquero_id)
            ->where('ruta', $ruta)                // ⭐ FILTRO CLAVE
            ->where('estado', 'Por asignar')
            ->whereDate('created_at', today())
            ->orderBy('orden', 'asc')
            ->get();

        // Obtener el último número de orden ya existente para este motoquero (solo Asignado)
        $ultimoOrden = Pedido::where('motoquero_id', $motoquero_id)
            ->where('estado', 'Asignado')
            ->whereDate('created_at', today())
            ->max('orden') ?? 0;

        foreach ($pedidosPorAsignar as $pedido) {
            $ultimoOrden++; // siguiente número consecutivo
            $pedido->orden = $ultimoOrden;
            $pedido->estado = 'Asignado';
            $pedido->save();
        }

        DB::commit();

        return response()->json(['success' => true]);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
}


    public function show(Pedido $pedido)
    {
        //
    }

    public function edit(Pedido $pedido)
    {
        //
    }

    public function update(Request $request, Pedido $pedido)
    {
        //
    }

    public function destroy($id)
    {
        $pedido = Pedido::find($id);
        $pedido->delete();
        return redirect()->back()
            ->with('mensaje', 'Pedido eliminado correctamente')
            ->with('icono', 'success');
    }

    public function reporte_motoquero(Request $request)
{
    // Validación
    $request->validate([
        'motoquero_id' => 'required|integer|exists:motoqueros,id', // tabla correcta
        'fecha' => 'required|date',
    ]);

    try {
        $motoquero_id = $request->motoquero_id;
        $fecha = $request->fecha;

        // Obtener pedidos entregados del motoquero en la fecha indicada
        $pedidos = Pedido::with('detalles')
            ->where('motoquero_id', $motoquero_id)
            ->where('estado', 'Entregado')
            ->whereDate('updated_at', $fecha)
            ->get();

        $resumen = collect();

        foreach ($pedidos as $pedido) {
            foreach ($pedido->detalles as $detalle) {
                $key = $detalle->producto . '-' . $pedido->metodo_pago;

                // Obtener elemento actual si existe
                $item = $resumen->get($key, [
                    'producto' => $detalle->producto,
                    'metodo' => $pedido->metodo_pago,
                    'cantidad' => 0,
                    'total' => 0
                ]);

                $item['cantidad'] += $detalle->cantidad;
                $item['total'] += $detalle->precio_total;

                $resumen->put($key, $item);
            }
        }

        $total_efectivo = $resumen->where('metodo', 'Efectivo')->sum('total');
        $total_qr = $resumen->where('metodo', 'QR')->sum('total');
        $total_general = $total_efectivo + $total_qr;

        // Retornar vista parcial para Ajax
        return view('admin.pedidos.ajax.reporte_motoquero_tabla', compact(
            'resumen', 'total_efectivo', 'total_qr', 'total_general', 'fecha'
        ));

    } catch (\Exception $e) {
        // Retornar error en JSON para manejarlo en frontend
        return response()->json([
            'success' => false,
            'message' => 'Ocurrió un error al generar el reporte del motoquero',
            'error' => $e->getMessage()
        ], 500);
    }
}



    public function cliente()
{
    return $this->belongsTo(Cliente::class);
}



public function estadoMotoquero($motoqueroId)
{
    $lastUpdate = Pedido::where('motoquero_id', $motoqueroId)
        ->whereDate('updated_at', now()->toDateString())
        ->max('updated_at');

    return response()->json([
        'last_update' => optional($lastUpdate)->timestamp
    ]);
}



public function marcarEmergencia(Pedido $pedido)
{
    $pedido->emergencia = true;
    $pedido->save();

    return response()->json(['ok' => true]);
}


public function checkEmergencia()
{
    $motoqueroId = auth()->user()->motoquero->id;

    $pedido = Pedido::where('motoquero_id', $motoqueroId)
        ->where('emergencia', true)
        ->whereNull('emergencia_notificada')
        ->where('estado', 'Asignado')
        ->first();

    if (!$pedido) {
        return response()->json(['emergencia' => false]);
    }

    $pedido->emergencia_notificada = now();
    $pedido->save();

    return response()->json([
        'emergencia' => true,
        'pedido_id' => $pedido->id,
        'cliente' => $pedido->cliente->nombre
    ]);
}


public function mapaPorAsignar(Request $request)
{
    $request->validate([
        'motoquero_id' => 'required|integer',
        'ruta' => 'required|in:A,B,C,D'
    ]);

    $pedidos = Pedido::with('cliente')
        ->where('estado', 'Por asignar')
        ->where('motoquero_id', $request->motoquero_id)
        ->where('ruta', $request->ruta)
        ->whereDate('updated_at', Carbon::today())   // 👈 FILTRAR SOLO HOY
        ->whereHas('cliente', function ($q) {
            $q->whereNotNull('latitud')
              ->whereNotNull('longitud');
        })
        ->orderBy('orden')
        ->get();

    return response()->json(
        $pedidos->map(fn($p) => [
            'id'        => $p->id,
            'orden'     => $p->orden,
            'nombre'    => $p->cliente->nombre,
            'latitud'   => (float) $p->cliente->latitud,
            'longitud'  => (float) $p->cliente->longitud,
        ])
    );
}


public function precioCliente(Cliente $cliente, Producto $producto)
{
    return response()->json([
        'precio' => $cliente->getPrecioProducto($producto)
    ]);
}


public function editarPedido($id)
{
    $pedido = Pedido::with('cliente')->findOrFail($id);

    return response()->json([
        'id'           => $pedido->id,
        'estado'       => $pedido->estado,
        'cliente'      => $pedido->cliente->nombre,
        'ubicacion'    => $pedido->cliente->ubicacion_gps,
        'motoquero_id' => $pedido->motoquero_id,
        'ruta'         => $pedido->ruta,
        'promo_activa' => (bool)$pedido->cliente->promo_activa,
    ]);
}

public function actualizarEdicion(Request $request)
{
    $request->validate([
        'pedido_id'   => 'required|integer|exists:pedidos,id',
        'promo_activa'=> 'required|boolean',
        'ruta'        => 'required|in:A,B,C,D',
        'motoquero_id'=> 'required|integer|exists:motoqueros,id',
    ]);

    DB::beginTransaction();

    try {

        $pedido = Pedido::with('cliente')->findOrFail($request->pedido_id);

        // ✅ ACTUALIZAR PROMO DEL CLIENTE
        $pedido->cliente->update([
            'promo_activa' => $request->promo_activa
        ]);

        // ✅ RECALCULAR ORDEN (al final de la ruta)
        $ultimoOrden = Pedido::where('motoquero_id', $request->motoquero_id)
            ->where('ruta', $request->ruta)
            ->where('estado', $pedido->estado)
            ->whereDate('created_at', today())
            ->max('orden') ?? 0;

        // ✅ ACTUALIZAR PEDIDO
        $pedido->update([
            'descripcion' => $request->descripcion,
            'motoquero_id'=> $request->motoquero_id,
            'ruta'        => $request->ruta,
            'orden'       => $ultimoOrden + 1,
            'updated_at'  => now(),
        ]);

        DB::commit();

        return response()->json(['success' => true]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}


public function avisarMotoquero(Request $request, $pedidoId)
{
    $request->validate([
        'tipo' => 'required|in:ya_sale,no_contesta'
    ]);

    $pedido = Pedido::findOrFail($pedidoId);

    // Verificar que el pedido tenga motero asignado
    if (!$pedido->motoquero_id) {
        return response()->json([
            'error' => 'El pedido no tiene motero asignado.'
        ], 400);
    }

    AvisoPedido::create([
        'pedido_id' => $pedido->id,
        'motoquero_id' => $pedido->motoquero_id,
        'tipo' => $request->tipo,
        'leido' => false
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Aviso enviado correctamente'
    ]);
}


public function obtenerAvisosMotoquero()
{
    if (!auth()->check() || !auth()->user()->motoquero) {
        return response()->json([]);
    }

    $motoqueroId = auth()->user()->motoquero->id;

    $avisos = AvisoPedido::where('motoquero_id', $motoqueroId)
        ->where('leido', false)
        ->with('pedido.cliente')
        ->get();

    if ($avisos->isEmpty()) {
        return response()->json([]);
    }

    // Preparar respuesta limpia
    $respuesta = $avisos->map(function ($aviso) {
        return [
            'id' => $aviso->id,
            'tipo' => $aviso->tipo,
            'pedido_id' => $aviso->pedido->id,
            'cliente' => $aviso->pedido->cliente->nombre ?? 'Cliente',
        ];
    });

    // Marcar como leídos
    AvisoPedido::whereIn('id', $avisos->pluck('id'))
        ->update(['leido' => true]);

    return response()->json($respuesta);
}


public function toggleNotificacion(Request $request, $pedidoId)
{
    $pedido = \App\Models\Pedido::findOrFail($pedidoId);
    $pedido->inicio_navegacion_este_pedido = $request->valor;
    $pedido->save();

    return response()->json(['ok' => true]);
}


}