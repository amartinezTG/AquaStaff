<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\FacilityInventory;

class Products extends Model
{
    use HasFactory;
    protected $table = 'products';
    protected $primaryKey = 'product_id';

    public function facilityInventories()
    {
        return $this->hasMany(FacilityInventory::class, 'product_id', 'product_id');
    }

}
