<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CorteCajeroPaquete extends Model
{
    protected $table = 'cortes_cajero_paquetes';

    protected $fillable = [
        'corte_cajero_id',
        'paquete',
        'autos_sistema',
        'autos_cajero',
        'precio',
        'total',
    ];

    protected $casts = [
        'autos_sistema' => 'integer',
        'autos_cajero'  => 'integer',
        'precio'        => 'decimal:2',
        'total'         => 'decimal:2',
    ];

    public function corteCajero()
    {
        return $this->belongsTo(CorteCajero::class, 'corte_cajero_id');
    }
}
