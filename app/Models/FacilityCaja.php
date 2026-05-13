<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacilityCaja extends Model
{
    protected $table    = 'facility_cajas';
    protected $fillable = ['facility_id', 'codigo', 'nombre', 'activo'];

    public function facility()
    {
        return $this->belongsTo(Facilities::class, 'facility_id', 'facility_id');
    } 

    public function cortes()
    {
        return $this->hasMany(CorteCajero::class, 'caja_id');
    }
}
