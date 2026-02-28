<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DespachoRepartidor extends Model
{
    use HasFactory;

    protected $table = 'despachos_repartidores';

    protected $fillable = [
        'motoquero_id',
        'botellones_regular',
        'botellones_alcalina',
        'dispensers',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    public function motoquero()
    {
        return $this->belongsTo(Motoquero::class);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES ÚTILES (Opcional pero PRO)
    |--------------------------------------------------------------------------
    */

    // Despachos de hoy
    public function scopeHoy($query)
    {
        return $query->whereDate('created_at', today());
    }
}