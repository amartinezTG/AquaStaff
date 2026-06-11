<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CorteCajeroMembresia extends Model
{
    protected $table = 'cortes_cajero_membresias';

    protected $fillable = [
        'corte_cajero_id',
        'paquete',
        'autos_sistema',
        'autos_cajero',
    ];

    protected $casts = [
        'autos_sistema' => 'integer',
        'autos_cajero'  => 'integer',
    ];

    public function corteCajero()
    {
        return $this->belongsTo(CorteCajero::class, 'corte_cajero_id');
    }
}
