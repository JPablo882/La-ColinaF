<?php

namespace App\Models;

use App\Models\DetallePedido;
use App\Models\Motoquero;
use App\Models\Cliente;
use App\Models\Tarifa;
use App\Models\AvisoPedido;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $table = 'pedidos';
    protected $fillable = ['cliente_id', 'estado', 'motoquero_id', 'direccion_entrega', 'telefono_contacto', 'observaciones', 'ubicacion_gps', 'ruta', 'metodo_pago', 'total_precio', 'inicio_navegacion_este_pedido' ];

    use HasFactory;

     // Relación: Un pedido puede tener muchos detalles
     public function detalles()
     {
         return $this->hasMany(DetallePedido::class, 'pedido_id');
     }
 
     // Relación: Un pedido puede ser asignado a un motoquero
     public function motoquero()
     {
         return $this->belongsTo(Motoquero::class, 'motoquero_id');
     }
 
     public function cliente()
     {
         return $this->belongsTo(Cliente::class, 'cliente_id');
     }
 
     public function tarifa()
     {
         return $this->belongsTo(Tarifa::class, 'tarifa_id');
     }



     public function ubicaciones()
    {
        return $this->hasMany(MotoqueroUbicacion::class);
    }

    public function ultimaUbicacion()
    {
        return $this->hasOne(MotoqueroUbicacion::class)
            ->latestOfMany('registrado_en');
    }

    public function avisos()
    {
        return $this->hasMany(AvisoPedido::class);
    }


}
