<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AvisoPedido extends Model
{
    protected $table = 'avisos_pedido';

    protected $fillable = [
        'pedido_id',
        'motoquero_id',
        'tipo',
        'leido'
    ];

    // ================= RELACIONES =================

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function motoquero()
    {
        return $this->belongsTo(Motoquero::class);
    }
}
