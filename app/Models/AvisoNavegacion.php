<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AvisoNavegacion extends Model
{
    protected $table = 'avisos_navegacion';

    protected $fillable = [
        'pedido_id',
        'cliente',
        'celular',
        'motoquero_id',
        'estado'
    ];

    public function cliente()
    {
        return $this->belongsTo(\App\Models\Cliente::class);
    }

    public function pedido()
    {
        return $this->belongsTo(\App\Models\Pedido::class);
    }

}