<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Llamada extends Model
{
    protected $fillable = [
        'cliente_id',
        'nombre_cliente',
        'celular_cliente',
        'motoquero_id',
        'nombre_motoquero',
        'estado'
    ];

    public function cliente()
    {
        return $this->belongsTo(\App\Models\Cliente::class);
    }
    
}
