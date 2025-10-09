<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class DetalleArqueo extends Model
{
    protected $table = 'detalle_de_arqueo';

    protected $fillable = [
        'id_corte',
        'denominacion',
        'cantidad',
    ];
    
}