<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsedBusinessCodes extends Model
{
    use HasFactory;
    protected $table = 'used_business_codes';
    protected $primaryKey = 'id';
      
}