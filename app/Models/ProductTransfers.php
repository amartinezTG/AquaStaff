<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductTransfers extends Model
{
    use HasFactory;
    protected $table      = 'product_transfers';

    protected $fillable = [
        'transfer_id',
        'product_id',
        'qty',
    ];
    

    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id', 'product_id');
    }

}
