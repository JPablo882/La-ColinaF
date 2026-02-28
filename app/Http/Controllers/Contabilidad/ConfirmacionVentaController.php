<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Motoquero;
use App\Models\Pedido;
use Carbon\Carbon;

use App\Models\CierreVenta;
use App\Models\CierreVentaGasto;
use Illuminate\Support\Facades\DB;
use App\Models\DespachoRepartidor;

class ConfirmacionVentaController extends Controller
{
    public function create(Request $request)
    {
        $distribuidores = Motoquero::orderBy('nombres')->get();

        $fecha = $request->fecha ?? now()->toDateString();
        $distribuidorId = $request->distribuidor_id;

        // Si aún no seleccionó distribuidor
        if (!$distribuidorId) {
            return view('admin.contabilidad.confirmar_venta', compact(
                'distribuidores',
                'fecha'
            ));
        }

        $inicio = Carbon::parse($fecha)->startOfDay();
        $fin    = Carbon::parse($fecha)->endOfDay();

        $pedidos = Pedido::with(['cliente', 'detalles'])
            ->where('motoquero_id', $distribuidorId)
            ->where('estado', 'Entregado')
            ->whereBetween('updated_at', [$inicio, $fin])
            ->orderBy('updated_at', 'asc')
            ->get();

        // ===============================
        // 💰 INGRESOS
        // ===============================

        $ingresoBruto = $pedidos->sum('total_precio');

        $ingresoEfectivo = $pedidos
            ->where('metodo_pago', 'Efectivo')
            ->sum('total_precio');

        $ingresoQR = $pedidos
            ->where('metodo_pago', 'QR')
            ->sum('total_precio');

        // ===============================
        // 🚛 DESPACHO
        // ===============================

        $despacho = DespachoRepartidor::where('motoquero_id', $distribuidorId)
            ->whereDate('created_at', $fecha)
            ->first();

        $regularDespachado = $despacho->botellones_regular ?? 0;
        $alcalinaDespachado = $despacho->botellones_alcalina ?? 0;

        // ===============================
        // 🟠 VENDIDOS
        // ===============================

        $vendidosRegular = 0;
        $vendidosAlcalina = 0;

        foreach ($pedidos as $pedido) {
            foreach ($pedido->detalles as $detalle) {

                if ($detalle->producto === 'Agua Regular') {
                    $vendidosRegular += $detalle->cantidad;
                }

                if ($detalle->producto === 'Agua Alcalina') {
                    $vendidosAlcalina += $detalle->cantidad;
                }
            }
        }

        // ===============================
        // 🟢 RESTANTES
        // ===============================

        $restanteRegular = $regularDespachado - $vendidosRegular;
        $restanteAlcalina = $alcalinaDespachado - $vendidosAlcalina;

        // ===============================
        // 🔒 CIERRE EXISTENTE
        // ===============================

        $cierreExistente = CierreVenta::where('fecha', $fecha)
            ->where('motoquero_id', $distribuidorId)
            ->first();

        return view('admin.contabilidad.confirmar_venta', compact(
            'distribuidores',
            'pedidos',
            'fecha',
            'distribuidorId',
            'ingresoBruto',
            'ingresoEfectivo',
            'ingresoQR',
            'cierreExistente',
            'regularDespachado',
            'alcalinaDespachado',
            'vendidosRegular',
            'vendidosAlcalina',
            'restanteRegular',
            'restanteAlcalina'
        ));
    }




    public function store(Request $request)
    {
        $request->validate([
            'fecha'           => 'required|date',
            'distribuidor_id' => 'required|exists:motoqueros,id',
            'ingreso_bruto'   => 'required|numeric|min:0',
            'gastos'          => 'nullable|array',
            'gastos.*.concepto' => 'required_with:gastos|string',
            'gastos.*.monto'    => 'required_with:gastos|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {

            $totalGastos = collect($request->gastos)
                ->sum(fn($g) => $g['monto']);

            // 🔒 evitar doble cierre
            $cierre = CierreVenta::updateOrCreate(
                [
                    'fecha' => $request->fecha,
                    'motoquero_id' => $request->distribuidor_id,
                ],
                [
                    'ingreso_bruto' => $request->ingreso_bruto,
                    'ingreso_efectivo' => $request->ingreso_efectivo ?? 0,
                    'ingreso_qr' => $request->ingreso_qr ?? 0,
                    'total_gastos_distribucion' => $totalGastos,
                    'efectivo_entregado' =>
                        ($request->ingreso_efectivo ?? 0) - $totalGastos,
                ]
            );

            // limpiar gastos anteriores (si re-confirmo)
            $cierre->gastos()->delete();

            foreach ($request->gastos ?? [] as $gasto) {
                CierreVentaGasto::create([
                    'cierre_venta_id' => $cierre->id,
                    'concepto' => $gasto['concepto'],
                    'monto' => $gasto['monto'],
                ]);
            }
        });

        return redirect()
            ->route('admin.contabilidad.confirmar_venta.create', [
                'fecha' => $request->fecha,
                'distribuidor_id' => $request->distribuidor_id
            ])
            ->with('success', 'Venta diaria confirmada correctamente');
    }


    public function historial(Request $request)
    {
        $distribuidores = Motoquero::orderBy('nombres')->get();

        $distribuidorId = $request->distribuidor_id;
        $fecha          = $request->fecha;

        $query = CierreVenta::with(['motoquero', 'gastos'])
            ->when($distribuidorId, function ($q) use ($distribuidorId) {
                $q->where('motoquero_id', $distribuidorId);
            })
            ->when($fecha, function ($q) use ($fecha) {
                $q->whereDate('fecha', $fecha);
            })
            ->orderBy('fecha', 'desc')
            ->orderBy('id', 'desc');

        // 🔁 paginamos
        $cierres = $query->paginate(10);

        // 🔥 Forzar a que cargue la ÚLTIMA página (más reciente)
        if (!$request->page) {
            return redirect()->route('admin.contabilidad.historial_cierres', array_merge(
                $request->all(),
                ['page' => $cierres->lastPage()]
            ));
        }

        return view('admin.contabilidad.historial_cierres', compact(
            'cierres',
            'distribuidores',
            'distribuidorId',
            'fecha'
        ));
    }


}