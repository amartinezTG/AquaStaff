<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacilityInventory extends Model
{
    use HasFactory;
    protected $table      = 'facility_inventory';
    protected $primaryKey = 'id';
    protected $fillable  = ['facility_id', 'product_id', 'qty', 'reorder'];

    // Definir la relación con el modelo Product
    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id');
    }

    public function facility()
    {
        return $this->belongsTo(Facilities::class, 'facility_id', 'facility_id');
    }

   

}
