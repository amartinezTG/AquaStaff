<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CorteCajero extends Model
{
    protected $table = 'cortes_cajero';

    protected $fillable = [
        'corte_interlogic_id', 'interlogic_cargado',
        'fecha_corte', 'facility_id', 'caja_id',
        'total_ventas', 'num_pagos_tarjeta', 'importe_tarjeta',
        'efectivo_mxn', 'efectivo_dlls_cantidad', 'efectivo_dlls_tc',
        'efectivo_dlls_importe', 'total_efectivo', 'total_de_venta',
        'dotacion', 'pagos_cancelados', 'total_egresos',
        'saldo_inicial_dispensador', 'dotacion_final', 'saldo_dispensador',
        'dotacion_determinada', 'dotacion_diferencia',
        'cambio_entregado', 'cambio_no_entregado', 'saldo_cambio_entregado',
        'referencia_cambio',
        'corte_total_efectivo', 'efectivo_entregado',
        'capturado_por', 'estado', 'observaciones',
    ];

    protected $casts = [
        'fecha_corte' => 'date',
    ];
 
    /**
     * Diferencia recalculada, igual que el card "Diferencia (Cajero Interlogic)"
     * de la vista de captura:
     *   Diferencia = Reportado por Operador − Registrado en Cajero
     *   · Reportado por Operador = efectivo físico contado (denominaciones
     *     MXN + USD·TC).
     *   · Registrado en Cajero   = Total Efectivo (= Total de Venta del Balance
     *     General) − Dotación + Cambio Entregado + Cambio No Entregado.
     */
    public function getDiferenciaRealAttribute(): float
    {
        $tc  = (float) ($this->efectivo_dlls_tc ?: 20);
        $mxn = (float) $this->denominaciones->where('moneda', 'MXN')->sum('monto');
        $usd = (float) $this->denominaciones->where('moneda', 'USD')->sum('monto');

        $reportadoOperador = $mxn + $usd * $tc;
        $registradoCajero  = (float) $this->total_efectivo
                           - (float) $this->dotacion
                           + (float) $this->cambio_entregado
                           + (float) $this->cambio_no_entregado;

        return round($reportadoOperador - $registradoCajero, 2);
    }

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

    public function paquetes()
    {
        return $this->hasMany(CorteCajeroPaquete::class, 'corte_cajero_id');
    }

    public function membresias()
    {
        return $this->hasMany(CorteCajeroMembresia::class, 'corte_cajero_id');
    }
}
