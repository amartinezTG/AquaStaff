<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpecialOrders extends Model
{
    use HasFactory;
    protected $table = 'special_orders';
    protected $primaryKey = 'id';
      
}