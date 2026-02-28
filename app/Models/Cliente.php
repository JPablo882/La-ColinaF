<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\Promocion;
use App\Models\Producto;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'ci',
        'celular',
        'referencia_celular',
        'direccion',
        'ubicacion_gps',
        'latitud',
        'longitud',

        // 🔥 promo
        'promo_activa',
        'promo_desde',
        'promo_hasta',
        'inicio_navegacion_siempre',
    ];

    protected $casts = [
        'promo_activa' => 'boolean',
        'promo_desde'  => 'datetime',
        'promo_hasta'  => 'datetime',
    ];

    /* ============================
     * Relaciones
     * ============================ */

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'cliente_id');
    }

    public function preciosEspeciales()
    {
        return $this->hasMany(PrecioEspecial::class);
    }

    /* ============================
     * LÓGICA DE PRECIO CENTRALIZADA
     * ============================ */

    public function getPrecioProducto($producto)
    {
        // Si viene un ID, buscamos el producto
        if (is_numeric($producto)) {
            $producto = Producto::find($producto);
        }

        if (!$producto) {
            return null;
        }

        /* ----------------------------
         * 1️⃣ PROMO (máxima prioridad)
         * ---------------------------- */
        if ($this->promoVigente()) {

            $promo = Promocion::where('producto_id', $producto->id)
                ->where('activa', true)
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

            if ($promo) {
                return $promo->precio_promo;
            }
        }

        /* ----------------------------
         * 2️⃣ PRECIO ESPECIAL
         * ---------------------------- */
        $precioEspecial = $this->preciosEspeciales()
            ->where('producto_id', $producto->id)
            ->first();

        if ($precioEspecial) {
            return $precioEspecial->precio_especial;
        }

        /* ----------------------------
         * 3️⃣ PRECIO NORMAL
         * ---------------------------- */
        return $producto->precio;
    }

    /* ============================
     * HELPERS DE PROMO
     * ============================ */

    public function promoVigente()
    {
        if (!$this->promo_activa) {
            return false;
        }

        $ahora = Carbon::now();

        return
            (!$this->promo_desde || $this->promo_desde <= $ahora) &&
            (!$this->promo_hasta || $this->promo_hasta >= $ahora);
    }



    public function padre()
    {
        return $this->belongsTo(Cliente::class, 'cliente_padre_id');
    }

    public function hijos()
    {
        return $this->hasMany(Cliente::class, 'cliente_padre_id');
    }

    /* 👇 Esto será CLAVE para WhatsApp */
    public function getCelularRealAttribute()
    {
        if ($this->celular) {
            return $this->celular;
        }

        return $this->padre ? $this->padre->celular : null;
    }


}