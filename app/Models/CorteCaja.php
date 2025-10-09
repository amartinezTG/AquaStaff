<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class CorteCaja extends Model
{
    protected $table = 'corte_de_caja';

    // CorteCaja.php
    public function usuario()
    {
        return $this->belongsTo(\App\Models\StaffUser::class, 'usuario_que_hizo_corte');
    }

    public function detallesArqueo()
    {
        return $this->hasMany(\App\Models\DetalleArqueo::class, 'id_corte');
    }
    
}