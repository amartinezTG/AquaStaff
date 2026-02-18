<?php

namespace App\Models;
use Jenssegers\Mongodb\Eloquent\Model;
//comentario
class Promocion extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'specialorders';
    public $timestamps = false;

    protected $fillable = [
        'IsSync',
        'lastSync',
        'promotion_user',
        'purchase_order',
        'code',
        'expiration',
        'package',
        'price',
        'uses',
        'type',
        'status',
        'error',
    ];
} 
