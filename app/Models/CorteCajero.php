<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CorteCajero extends Model
{
    protected $table = 'cortes_cajero';

    protected $fillable = [
        'fecha_corte', 'facility_id', 'caja_id',
        'total_ventas', 'num_pagos_tarjeta', 'importe_tarjeta',
        'efectivo_mxn', 'efectivo_dlls_cantidad', 'efectivo_dlls_tc',
        'efectivo_dlls_importe', 'total_efectivo', 'total_de_venta',
        'dotacion', 'pagos_cancelados', 'total_egresos',
        'saldo_inicial_dispensador', 'dotacion_final', 'saldo_dispensador',
        'cambio_entregado', 'cambio_no_entregado', 'saldo_cambio_entregado',
        'referencia_cambio',
        'corte_total_efectivo', 'efectivo_entregado',
        'capturado_por', 'estado', 'observaciones',
    ];

    protected $casts = [
        'fecha_corte' => 'date',
    ];
 
    public function caja()
    {
        return $this->belongsTo(FacilityCaja::class, 'caja_id');
    }

    public function facility()
    {
        return $this->belongsTo(Facilities::class, 'facility_id', 'facility_id');
    }

    public function denominaciones()
    {
        return $this->hasMany(CorteCajeroDenominacion::class, 'corte_cajero_id');
    }

    public function capturadoPor()
    {
        return $this->belongsTo(StaffUser::class, 'capturado_por');
    }
}
