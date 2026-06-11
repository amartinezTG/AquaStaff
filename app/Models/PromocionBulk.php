<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;
use MongoDB\BSON\ObjectId;

class PromocionBulk extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'specialorders';
    public $timestamps    = false;

    protected $fillable = [
        'IsSync', 'lastSync', 'promotion_user', 'purchase_order',
        'code', 'expiration', 'package', 'price', 'uses',
        'type', 'proyecto', 'status', 'error',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('bulk', fn ($q) => $q->where('promotion_user', '!=', new ObjectId('678ab435ee1026a922940d5b')));
    }
}
