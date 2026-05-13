<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\CorteCajero;
use App\Models\CorteCajeroDenominacion;
use App\Models\FacilityCaja;
use App\Models\Facilities;
use App\Models\TipoDeCambio;

class CortesController extends Controller
{
    private array $denominacionesMXN = [
        'b500' => 500, 'b200' => 200, 'b100' => 100,
        'b50'  => 50,  'b20'  => 20,  'b10'  => 10,
        'b5'   => 5,   'b2'   => 2,   'b1'   => 1,
        'm10'  => 10,  'm5'   => 5,   'm2'   => 2,  'm1' => 1,
    ]; 

    private array $denominacionesUSD = [
        'usd_b50' => 50, 'usd_b20' => 20, 'usd_b10' => 10,
        'usd_b5'  => 5,  'usd_b2'  => 2,  'usd_b1'  => 1,
    ];

    // GET /cortes  — lista mensual (hoja RESUMEN)
    public function index(Request $request)
    {
        $activePage = 'cortes';
        $mes        = $request->get('mes', now()->format('Y-m'));

        [$anio, $mes_num] = explode('-', $mes);

        $cajas = FacilityCaja::where('activo', 1)
            ->whereHas('facility', fn($q) => $q->where('type', 'SUCURSAL'))
            ->with('facility')
            ->get();

        // Cortes del mes agrupados por fecha y caja
        $cortes = CorteCajero::with('caja')
            ->whereYear('fecha_corte', $anio)
            ->whereMonth('fecha_corte', $mes_num)
            ->orderBy('fecha_corte')
            ->get()
            ->groupBy('fecha_corte');

        // Fechas del mes para las columnas
        $diasEnMes  = cal_days_in_month(CAL_GREGORIAN, (int)$mes_num, (int)$anio);
        $fechas     = collect();
        for ($d = 1; $d <= $diasEnMes; $d++) {
            $fechas->push(sprintf('%04d-%02d-%02d', $anio, $mes_num, $d));
        }

        return view('cortes.index', compact('activePage', 'mes', 'cajas', 'cortes', 'fechas'));
    }

    // GET /cortes/capturar
    public function create(Request $request)
    {
        $activePage = 'cortes';
        $tipoCambio = TipoDeCambio::latest()->first()->tipo_cambio ?? 20.0;

        // Por defecto MISIONES (facility_id=2), extensible a otras sucursales
        $facilityId = $request->get('facility_id', 2);
        $facility   = Facilities::findOrFail($facilityId);
        $cajas      = FacilityCaja::where('facility_id', $facilityId)->where('activo', 1)->get();

        $fechaCorte = $request->get('fecha', now()->format('Y-m-d'));

        // Verificar si ya existen cortes para ese día
        $cortesExistentes = CorteCajero::where('fecha_corte', $fechaCorte)
            ->where('facility_id', $facilityId)
            ->with('caja')
            ->get()
            ->keyBy('caja_id');

        return view('cortes.capturar', compact(
            'activePage', 'tipoCambio', 'facility', 'cajas',
            'fechaCorte', 'cortesExistentes'
        ));
    }

    // POST /cortes
    public function store(Request $request)
    {
        $request->validate([
            'fecha_corte'  => 'required|date',
            'facility_id'  => 'required|integer',
            'cajas'        => 'required|array|min:1',
        ]);

        $facilityId = $request->facility_id;
        $fechaCorte = $request->fecha_corte;
        $tipoCambio = TipoDeCambio::latest()->first()->tipo_cambio ?? 20.0;

        DB::transaction(function () use ($request, $facilityId, $fechaCorte, $tipoCambio) {
            foreach ($request->cajas as $cajaId => $datos) {
                // Calcular totales derivados
                $efectivoDllsImporte = round(
                    ($datos['efectivo_dlls_cantidad'] ?? 0) * ($datos['efectivo_dlls_tc'] ?? $tipoCambio), 2
                );
                $totalEfectivo   = ($datos['efectivo_mxn'] ?? 0) + $efectivoDllsImporte;
                $totalDeVenta    = $totalEfectivo + ($datos['importe_tarjeta'] ?? 0);
                $totalEgresos    = ($datos['dotacion'] ?? 0) + ($datos['pagos_cancelados'] ?? 0);
                $saldoDispensador = ($datos['saldo_inicial_dispensador'] ?? 0) + ($datos['dotacion'] ?? 0);
                $saldoCambio     = ($datos['cambio_entregado'] ?? 0) + ($datos['cambio_no_entregado'] ?? 0);

                $corte = CorteCajero::updateOrCreate(
                    ['fecha_corte' => $fechaCorte, 'caja_id' => $cajaId],
                    [
                        'facility_id'               => $facilityId,
                        'total_ventas'              => $datos['total_ventas']              ?? 0,
                        'num_pagos_tarjeta'         => $datos['num_pagos_tarjeta']         ?? 0,
                        'importe_tarjeta'           => $datos['importe_tarjeta']           ?? 0,
                        'efectivo_mxn'              => $datos['efectivo_mxn']              ?? 0,
                        'efectivo_dlls_cantidad'    => $datos['efectivo_dlls_cantidad']    ?? 0,
                        'efectivo_dlls_tc'          => $datos['efectivo_dlls_tc']          ?? $tipoCambio,
                        'efectivo_dlls_importe'     => $efectivoDllsImporte,
                        'total_efectivo'            => $totalEfectivo,
                        'total_de_venta'            => $totalDeVenta,
                        'dotacion'                  => $datos['dotacion']                  ?? 0,
                        'pagos_cancelados'          => $datos['pagos_cancelados']          ?? 0,
                        'total_egresos'             => $totalEgresos,
                        'saldo_inicial_dispensador' => $datos['saldo_inicial_dispensador'] ?? 0,
                        'dotacion_final'            => $datos['dotacion_final']            ?? 0,
                        'saldo_dispensador'         => $saldoDispensador,
                        'cambio_entregado'          => $datos['cambio_entregado']          ?? 0,
                        'cambio_no_entregado'       => $datos['cambio_no_entregado']       ?? 0,
                        'saldo_cambio_entregado'    => $saldoCambio,
                        'referencia_cambio'         => $datos['referencia_cambio']         ?? null,
                        'corte_total_efectivo'      => $datos['corte_total_efectivo']      ?? 0,
                        'efectivo_entregado'        => $datos['efectivo_entregado']        ?? 0,
                        'capturado_por'             => Auth::id(),
                        'estado'                    => 'cerrado',
                        'observaciones'             => $datos['observaciones']             ?? null,
                    ]
                );

                // Guardar denominaciones MXN
                $this->guardarDenominaciones($corte->id, 'MXN', $datos['den_mxn'] ?? []);

                // Guardar denominaciones USD
                $this->guardarDenominaciones($corte->id, 'USD', $datos['den_usd'] ?? []);
            }
        });

        return redirect()->route('cortes.index')
            ->with('success', 'Cortes del día ' . $fechaCorte . ' guardados correctamente.');
    }

    // GET /cortes/{fecha}
    public function show($fecha)
    {
        $activePage = 'cortes';
        $tipoCambio = TipoDeCambio::latest()->first()->tipo_cambio ?? 20.0;

        $cortes = CorteCajero::with(['caja', 'denominaciones', 'capturadoPor'])
            ->where('fecha_corte', $fecha)
            ->orderBy('caja_id')
            ->get();

        if ($cortes->isEmpty()) {
            return redirect()->route('cortes.index')->with('error', 'No hay cortes para esa fecha.');
        }

        $facility = Facilities::find($cortes->first()->facility_id);

        // Totales del día
        $totalDia = [
            'prosepago'   => $cortes->sum('importe_tarjeta'),
            'efectivo'    => $cortes->sum('total_efectivo'),
            'venta'       => $cortes->sum('total_de_venta'),
            'diferencia'  => $cortes->sum('diferencia'),
        ];

        return view('cortes.show', compact('activePage', 'fecha', 'cortes', 'facility', 'totalDia', 'tipoCambio'));
    }

    // GET /cortes/{id}/editar
    public function edit($id)
    {
        $activePage = 'cortes';
        $tipoCambio = TipoDeCambio::latest()->first()->tipo_cambio ?? 20.0;

        $corte    = CorteCajero::with(['caja', 'denominaciones'])->findOrFail($id);
        $facility = Facilities::findOrFail($corte->facility_id);
        $cajas    = FacilityCaja::where('facility_id', $corte->facility_id)->where('activo', 1)->get();

        $denMxn = $corte->denominaciones->where('moneda', 'MXN')->keyBy('denominacion');
        $denUsd = $corte->denominaciones->where('moneda', 'USD')->keyBy('denominacion');

        return view('cortes.editar', compact(
            'activePage', 'tipoCambio', 'corte', 'facility', 'cajas', 'denMxn', 'denUsd'
        ));
    }

    // PUT /cortes/{id}
    public function update(Request $request, $id)
    {
        $request->validate([
            'efectivo_mxn'     => 'required|numeric',
            'importe_tarjeta'  => 'required|numeric',
            'efectivo_entregado' => 'required|numeric',
        ]);

        $corte      = CorteCajero::findOrFail($id);
        $tipoCambio = TipoDeCambio::latest()->first()->tipo_cambio ?? 20.0;

        $efectivoDllsImporte = round(
            ($request->efectivo_dlls_cantidad ?? 0) * ($request->efectivo_dlls_tc ?? $tipoCambio), 2
        );
        $totalEfectivo   = ($request->efectivo_mxn ?? 0) + $efectivoDllsImporte;
        $totalDeVenta    = $totalEfectivo + ($request->importe_tarjeta ?? 0);
        $totalEgresos    = ($request->dotacion ?? 0) + ($request->pagos_cancelados ?? 0);
        $saldoDispensador = ($request->saldo_inicial_dispensador ?? 0) + ($request->dotacion ?? 0);
        $saldoCambio     = ($request->cambio_entregado ?? 0) + ($request->cambio_no_entregado ?? 0);

        $corte->fill([
            'total_ventas'              => $request->total_ventas              ?? 0,
            'num_pagos_tarjeta'         => $request->num_pagos_tarjeta         ?? 0,
            'importe_tarjeta'           => $request->importe_tarjeta           ?? 0,
            'efectivo_mxn'              => $request->efectivo_mxn              ?? 0,
            'efectivo_dlls_cantidad'    => $request->efectivo_dlls_cantidad    ?? 0,
            'efectivo_dlls_tc'          => $request->efectivo_dlls_tc          ?? $tipoCambio,
            'efectivo_dlls_importe'     => $efectivoDllsImporte,
            'total_efectivo'            => $totalEfectivo,
            'total_de_venta'            => $totalDeVenta,
            'dotacion'                  => $request->dotacion                  ?? 0,
            'pagos_cancelados'          => $request->pagos_cancelados          ?? 0,
            'total_egresos'             => $totalEgresos,
            'saldo_inicial_dispensador' => $request->saldo_inicial_dispensador ?? 0,
            'dotacion_final'            => $request->dotacion_final            ?? 0,
            'saldo_dispensador'         => $saldoDispensador,
            'cambio_entregado'          => $request->cambio_entregado          ?? 0,
            'cambio_no_entregado'       => $request->cambio_no_entregado       ?? 0,
            'saldo_cambio_entregado'    => $saldoCambio,
            'referencia_cambio'         => $request->referencia_cambio         ?? null,
            'corte_total_efectivo'      => $request->corte_total_efectivo      ?? 0,
            'efectivo_entregado'        => $request->efectivo_entregado        ?? 0,
            'estado'                    => 'cerrado',
            'observaciones'             => $request->observaciones             ?? null,
        ]);
        $corte->save();

        $this->guardarDenominaciones($corte->id, 'MXN', $request->input('den_mxn', []));
        $this->guardarDenominaciones($corte->id, 'USD', $request->input('den_usd', []));

        return redirect()->route('cortes.show', $corte->fecha_corte->format('Y-m-d'))
            ->with('success', 'Corte actualizado correctamente.');
    }

    // -----------------------------------------------------------------------
    private function guardarDenominaciones(int $corteId, string $moneda, array $datos): void
    {
        $mapa = $moneda === 'MXN' ? $this->denominacionesMXN : $this->denominacionesUSD;

        foreach ($mapa as $den => $valor) {
            $cantidad = (int) ($datos[$den] ?? 0);
            CorteCajeroDenominacion::updateOrCreate(
                ['corte_cajero_id' => $corteId, 'moneda' => $moneda, 'denominacion' => $den],
                ['cantidad' => $cantidad, 'monto' => $cantidad * $valor]
            );
        }
    }
}
