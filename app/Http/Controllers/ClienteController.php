<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Promocion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class ClienteController extends Controller
{
    /* ============================
     * LISTADO
     * ============================ */
    public function index()
    {
        $clientes = Cliente::all();
        return view('admin.clientes.index', compact('clientes'));
    }

    /* ============================
     * FORM CREAR
     * ============================ */
public function create()
{
    $promoVigente = Promocion::where('activa', true)
        ->where(function ($q) {
            $q->whereNull('fecha_inicio')
              ->orWhere('fecha_inicio', '<=', now());
        })
        ->where(function ($q) {
            $q->whereNull('fecha_fin')
              ->orWhere('fecha_fin', '>=', now());
        })
        ->orderBy('precio_promo', 'asc') // 👈 la más barata
        ->first();

        $ultimoCliente = Cliente::orderBy('id', 'desc')->first();

    return view('admin.clientes.create', compact('promoVigente', 'ultimoCliente'));
}

    /* ============================
     * GUARDAR CLIENTE
     * ============================ */
    public function store(Request $request)
    {
        /* ============================
        * LIMPIAR TELÉFONOS
        * ============================ */
        $celularLimpio = preg_replace('/\D/', '', $request->celular);
        $referenciaLimpio = $request->referencia_celular
            ? preg_replace('/\D/', '', $request->referencia_celular)
            : null;

        $request->merge([
            'celular' => $celularLimpio,
            'referencia_celular' => $referenciaLimpio
        ]);

        /* ============================
        * VALIDACIÓN BASE
        * ============================ */
        $request->validate([
            'nombre' => 'required|string|max:100',
            'celular' => 'nullable|digits_between:10,15',
            'direccion' => 'required|string|max:200',
        ]);

        $clientePadreId = $request->cliente_padre_id ?? null;

        /* ============================
        * CREAR CLIENTE (PADRE O HIJO)
        * ============================ */
        if ($clientePadreId) {

            // 🔵 CLIENTE HIJO (NUEVA UBICACIÓN)
            $padre = Cliente::findOrFail($clientePadreId);

            $cliente = new Cliente();
            $cliente->nombre = $padre->nombre . ' ' . $request->extension;
            $cliente->celular = null; // 👈 CLAVE
            $cliente->cliente_padre_id = $padre->id;

        } else {

            // 🔵 CLIENTE NORMAL
            $request->validate([
                'celular' => 'required|unique:clientes,celular'
            ]);

            $cliente = new Cliente();
            $cliente->nombre = $request->nombre;
            $cliente->celular = $celularLimpio;
            $cliente->cliente_padre_id = null;
        }

        /* ============================
        * UBICACIÓN
        * ============================ */
        $lat = $request->latitud;
        $lng = $request->longitud;

        if ($request->ubicacion_gps && (!$lat || !$lng)) {
            [$lat, $lng] = $this->extraerLatLng($request->ubicacion_gps);
        }

        /* ============================
        * PROMO VIGENTE
        * ============================ */
        $promo = Promocion::where('activa', true)
            ->where(function ($q) {
                $q->whereNull('fecha_inicio')
                ->orWhere('fecha_inicio', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('fecha_fin')
                ->orWhere('fecha_fin', '>=', now());
            })
            ->orderBy('precio_promo', 'asc')
            ->first();

        /* ============================
        * COMPLETAR DATOS
        * ============================ */
        $cliente->referencia_celular = $referenciaLimpio;
        $cliente->direccion          = $request->direccion;
        $cliente->ubicacion_gps      = $request->ubicacion_gps;
        $cliente->latitud            = $lat;
        $cliente->longitud           = $lng;

        if ($promo) {
            $cliente->promo_activa = true;
            $cliente->promo_desde  = $promo->fecha_inicio;
            $cliente->promo_hasta  = $promo->fecha_fin;
        } else {
            $cliente->promo_activa = false;
        }

        $cliente->save();

        return redirect()->route('admin.clientes.index')
            ->with('mensaje', 'Cliente creado correctamente')
            ->with('icono', 'success');
    }

    /* ============================
     * VER CLIENTE
     * ============================ */
    public function show($id)
    {
        $cliente = Cliente::findOrFail($id);
        return view('admin.clientes.show', compact('cliente'));
    }

    /* ============================
     * FORM EDITAR
     * ============================ */
    public function edit($id)
    {
        $cliente = Cliente::findOrFail($id);
        return view('admin.clientes.edit', compact('cliente'));
    }

    /* ============================
     * ACTUALIZAR
     * ============================ */
    public function update(Request $request, $id)
{
    $cliente = Cliente::findOrFail($id);

    /* =========================================
     * 1️⃣ PROTEGER CELULAR SI ES CLIENTE HIJO
     * ========================================= */
    if (!is_null($cliente->cliente_padre_id)) {
        // 🔒 Es cliente hijo → mantener celular original
        $celularLimpio = $cliente->celular;
    } else {
        // ✅ Es cliente padre → permitir edición
        $celularLimpio = preg_replace('/\D/', '', $request->celular);
    }

    $referenciaLimpio = $request->referencia_celular
        ? preg_replace('/\D/', '', $request->referencia_celular)
        : null;

    $request->merge([
        'celular' => $celularLimpio,
        'referencia_celular' => $referenciaLimpio
    ]);

    /* =========================================
     * 2️⃣ VALIDACIÓN
     * ========================================= */
    $request->validate([
        'nombre' => [
            'required',
            'string',
            'max:100',
            Rule::unique('clientes')->ignore($cliente->id),
        ],


        'celular' => is_null($cliente->cliente_padre_id)
        ? [
        'required',
        'digits_between:10,15',
        Rule::unique('clientes')->ignore($cliente->id),
        ]
        :[
        'nullable'
        ],


        'referencia_celular' => [
            'nullable',
            'digits_between:10,15'
        ],

        'direccion'     => 'required|string|max:200',
        'ubicacion_gps' => 'nullable|string|max:500',
        'latitud'       => 'nullable|numeric',
        'longitud'      => 'nullable|numeric',
    ]);

    /* =========================================
     * 3️⃣ UBICACIÓN GPS
     * ========================================= */
    $lat = $request->latitud;
    $lng = $request->longitud;

    if ($request->ubicacion_gps && (!$lat || !$lng)) {
        [$lat, $lng] = $this->extraerLatLng($request->ubicacion_gps);
    }

    /* =========================================
     * 4️⃣ ACTUALIZAR DATOS
     * ========================================= */

    $cliente->promo_activa = $request->has('promo_activa') ? 1 : 0;
    $cliente->nombre       = $request->nombre;

    // 🔒 Solo si es cliente padre permitir cambiar celular
    if (is_null($cliente->cliente_padre_id)) {
        $cliente->celular = $celularLimpio;
    }

    $cliente->referencia_celular = $referenciaLimpio;
    $cliente->direccion          = $request->direccion;
    $cliente->ubicacion_gps      = $request->ubicacion_gps;
    $cliente->latitud            = $lat;
    $cliente->longitud           = $lng;

    $cliente->save();

    return redirect()->route('admin.clientes.index')
        ->with('mensaje', 'Cliente actualizado correctamente')
        ->with('icono', 'success');
}

    /* ============================
     * ELIMINAR
     * ============================ */
    public function destroy($id)
    {
        Cliente::findOrFail($id)->delete();

        return redirect()->route('admin.clientes.index')
            ->with('mensaje', 'Cliente eliminado correctamente')
            ->with('icono', 'success');
    }

    /* ============================
     * EXTRAER COORDS (AJAX)
     * ============================ */
    public function obtenerCoords(Request $request)
{
    $url = $request->input('url');

    if (!$url) {
        return response()->json(['error' => 'URL vacía'], 400);
    }

    try {

        /* =====================================================
         * 1️⃣ MÉTODO ORIGINAL (NO lo modificamos)
         * ===================================================== */
        $response = Http::withOptions([
            'allow_redirects' => true,
            'timeout' => 10,
        ])->get($url);

        $finalUrl = (string) $response->effectiveUri();

        if (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $finalUrl, $m)) {
            return response()->json(['lat' => $m[1], 'lng' => $m[2]]);
        }

        if (preg_match('/q=(-?\d+\.\d+),(-?\d+\.\d+)/', $finalUrl, $m)) {
            return response()->json(['lat' => $m[1], 'lng' => $m[2]]);
        }

        if (preg_match('/ll=(-?\d+\.\d+),(-?\d+\.\d+)/', $finalUrl, $m)) {
            return response()->json(['lat' => $m[1], 'lng' => $m[2]]);
        }

        /* =====================================================
         * 2️⃣ SOLO SI FALLA → USAR API (si existe)
         * ===================================================== */
        $apiKey = env('GOOGLE_MAPS_API_KEY');

        if ($apiKey) {

            $geoResponse = Http::get(
                'https://maps.googleapis.com/maps/api/geocode/json',
                [
                    'address' => $url,
                    'key' => $apiKey,
                ]
            );

            $geoData = $geoResponse->json();

            if (isset($geoData['status']) && $geoData['status'] === 'OK') {

                $location = $geoData['results'][0]['geometry']['location'];

                return response()->json([
                    'lat' => $location['lat'],
                    'lng' => $location['lng']
                ]);
            }
        }

        return response()->json(['error' => 'No se encontraron coordenadas'], 404);

    } catch (\Exception $e) {

        return response()->json([
            'error' => 'Error procesando el enlace',
            'detalle' => $e->getMessage()
        ], 500);
    }
}

    /* ============================
     * HELPER PRIVADO
     * ============================ */
    private function extraerLatLng($url)
    {
        $lat = $lng = null;

        if (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $m)) {
            $lat = $m[1];
            $lng = $m[2];
        } elseif (preg_match('/[?&]ll=(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $m)) {
            $lat = $m[1];
            $lng = $m[2];
        }

        return [$lat, $lng];
    }

    public function validarCampo(Request $request)
    {
        $campo = $request->campo;
        $valor = $request->valor;

        $existe = \App\Models\Cliente::where($campo, $valor)->exists();

        return response()->json([
            'existe' => $existe
        ]);
    }

    public function guardarImagen(Request $request, Cliente $cliente)
    {
        $request->validate([
            'imagen_casa' => 'required|image|max:1024' 
            // 1024 KB = 1MB máximo permitido
        ]);

        if ($cliente->imagen_casa) {
            Storage::disk('public')->delete($cliente->imagen_casa);
        }

        $ruta = $request->file('imagen_casa')
                        ->store('clientes', 'public');

        $cliente->imagen_casa = $ruta;
        $cliente->save();

        return response()->json([
            'success' => true,
            'ruta' => asset('storage/'.$ruta)
        ]);
    }


    public function buscarPorCelular(Request $request)
    {
        $celular = preg_replace('/\D/', '', $request->celular);

        $cliente = Cliente::where('celular', $celular)
            ->whereNull('cliente_padre_id')
            ->first();

        if ($cliente) {
            return response()->json([
                'existe' => true,
                'cliente' => [
                    'id' => $cliente->id,
                    'nombre' => $cliente->nombre,
                    'celular' => $cliente->celular
                ]
            ]);
        }

        return response()->json(['existe' => false]);
    }


    public function toggleNotificacion(Request $request, $clienteId)
    {
        $cliente = \App\Models\Cliente::findOrFail($clienteId);
        $cliente->inicio_navegacion_siempre = $request->valor;
        $cliente->save();

        return response()->json(['ok' => true]);
    }


}