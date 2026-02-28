<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Motoquero extends Model
{
    use HasFactory;

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id'); // Ajusta al nombre real de la columna
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'motoquero_id');
    }

    public function cierresVentas()
    {
        return $this->hasMany(\App\Models\CierreVenta::class);
    }

    public function avisos()
    {
        return $this->hasMany(AvisoPedido::class);
    }

    public function despachos()
    {
        return $this->hasMany(DespachoRepartidor::class);
    }

}
