<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transfers extends Model
{
    use HasFactory;
    protected $table = 'transfers';
    protected $primaryKey = 'transfer_id';
    protected $attributes = [
    'status' => 0, // O el valor por defecto que desees
];


    public function facilityDeparture(){
        return $this->belongsTo(Facilities::class, 'facility_departure', 'facility_id');
    }

    public function facilityArrive(){
        return $this->belongsTo(Facilities::class, 'facility_arrive', 'facility_id');
    }


}
