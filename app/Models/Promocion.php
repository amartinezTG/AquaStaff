<?php

namespace App\Models;
use Jenssegers\Mongodb\Eloquent\Model;
//coment
class Promocion extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'specialorders';

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
