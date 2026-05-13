<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CorteCajeroDenominacion extends Model
{
    protected $table = 'cortes_cajero_denominaciones';

    protected $fillable = [
        'corte_cajero_id', 'moneda', 'denominacion', 'cantidad', 'monto',
    ]; 

    public function corte()
    {
        return $this->belongsTo(CorteCajero::class, 'corte_cajero_id');
    }
}
