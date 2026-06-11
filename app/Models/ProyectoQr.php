<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProyectoQr extends Model
{
    protected $table    = 'proyectos_qr';
    protected $fillable = ['nombre', 'promotion_user', 'created_by'];
}
