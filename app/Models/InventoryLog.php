<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class InventoryLog extends Model
{
    use HasFactory;

    protected $table = 'inventory_logs'; // Asegúrate de que coincida con el nombre de tu tabla
    protected $fillable = [
        'facility_id',
        'product_id',
        'qty_before',
        'qty_after',
        'transfer_id',
        'user_id',
        'movement_type',
        'created_at',
        'updated_at',
    ];

    // Relaciones (si las tienes)
    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function facility()
    {
        return $this->belongsTo(GeneralCatalogs::class, 'facility_id');
    }

    // Puedes agregar más relaciones según tu estructura de base de datos
}