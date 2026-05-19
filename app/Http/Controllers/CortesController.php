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
use Carbon\Carbon;
  
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
        $orden      = $request->get('orden', 'desc'); // desc por defecto: más reciente arriba

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
            ->groupBy(fn($c) => $c->fecha_corte->format('Y-m-d'));

        // Fechas del mes: si es el mes actual, no mostrar días futuros
        $hoy       = now()->startOfDay();
        $esMesActual = $mes === now()->format('Y-m');
        $diasEnMes = cal_days_in_month(CAL_GREGORIAN, (int)$mes_num, (int)$anio);
        $fechas    = collect();
        for ($d = 1; $d <= $diasEnMes; $d++) {
            $fecha = sprintf('%04d-%02d-%02d', $anio, $mes_num, $d);
            if ($esMesActual && $fecha > $hoy->format('Y-m-d')) {
                continue;
            }
            $fechas->push($fecha);
        }
        if ($orden === 'desc') {
            $fechas = $fechas->reverse()->values();
        }

        $ordenOpuesto = $orden === 'asc' ? 'desc' : 'asc';

        return view('cortes.index', compact('activePage', 'mes', 'cajas', 'cortes', 'fechas', 'orden', 'ordenOpuesto'));
    } 

    // GET /cortes/capturar
    public function create(Request $request)
    {
        $activePage = 'cortes';
        $tipoCambio = TipoDeCambio::latest()->first()->tipo_cambio ?? 20.0;

        $facilityId = $request->get('facility_id', 2);
        $facility   = Facilities::findOrFail($facilityId);
        $cajas      = FacilityCaja::where('facility_id', $facilityId)->where('activo', 1)->get();

        $fechaCorte = $request->get('fecha', now()->format('Y-m-d'));

        // Cajero seleccionado — por defecto el primero
        $cajaId         = $request->get('caja_id', $cajas->first()->id ?? null);
        $cajaSeleccionada = $cajas->firstWhere('id', $cajaId) ?? $cajas->first();

        // Si ya existe corte para esta caja/fecha, pre-cargar datos
        $corteExistente = CorteCajero::with('denominaciones')
            ->where('fecha_corte', $fechaCorte)
            ->where('caja_id', $cajaSeleccionada->id)
            ->first();

        $denMxn = $corteExistente
            ? $corteExistente->denominaciones->where('moneda', 'MXN')->keyBy('denominacion')
            : collect();
        $denUsd = $corteExistente
            ? $corteExistente->denominaciones->where('moneda', 'USD')->keyBy('denominacion')
            : collect();

        $sistemaData = $this->sistemaDatos($cajaSeleccionada->codigo, $fechaCorte);

        return view('cortes.capturar', compact(
            'activePage', 'tipoCambio', 'facility', 'cajas',
            'fechaCorte', 'cajaSeleccionada', 'corteExistente', 'denMxn', 'denUsd',
            'sistemaData'
        ));
    }

    // POST /cortes
    public function store(Request $request)
    {
        $request->validate([
            'fecha_corte' => 'required|date',
            'facility_id' => 'required|integer',
            'caja_id'     => 'required|integer',
        ]);

        $facilityId = $request->facility_id;
        $fechaCorte = $request->fecha_corte;
        $cajaId     = $request->caja_id;
        $tipoCambio = TipoDeCambio::latest()->first()->tipo_cambio ?? 20.0;

        $efectivoDllsImporte = round(
            ($request->efectivo_dlls_cantidad ?? 0) * ($request->efectivo_dlls_tc ?? $tipoCambio), 2
        );
        $totalEfectivo    = ($request->efectivo_mxn ?? 0) + $efectivoDllsImporte;
        $totalDeVenta     = $totalEfectivo + ($request->importe_tarjeta ?? 0);
        $totalEgresos     = ($request->dotacion ?? 0) + ($request->pagos_cancelados ?? 0);
        $saldoDispensador = ($request->saldo_inicial_dispensador ?? 0) + ($request->dotacion ?? 0);
        $saldoCambio      = ($request->cambio_entregado ?? 0) + ($request->cambio_no_entregado ?? 0);

        $corte = CorteCajero::updateOrCreate(
            ['fecha_corte' => $fechaCorte, 'caja_id' => $cajaId],
            [
                'facility_id'               => $facilityId,
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
                'capturado_por'             => Auth::id(),
                'estado'                    => 'cerrado',
                'observaciones'             => $request->observaciones             ?? null,
            ]
        );

        $this->guardarDenominaciones($corte->id, 'MXN', $request->input('den_mxn', []));
        $this->guardarDenominaciones($corte->id, 'USD', $request->input('den_usd', []));

        return redirect()->route('cortes.show', $fechaCorte)
            ->with('success', 'Corte guardado correctamente.');
    }

    // GET /cortes/datos-cajero  — JSON para el select dinámico
    public function datosCajero(Request $request)
    {
        $cajaId     = $request->get('caja_id');
        $fechaCorte = $request->get('fecha_corte');

        $caja = FacilityCaja::find($cajaId);
        $sistema = $caja ? $this->sistemaDatos($caja->codigo, $fechaCorte) : [];

        $corte = CorteCajero::with('denominaciones')
            ->where('fecha_corte', $fechaCorte)
            ->where('caja_id', $cajaId)
            ->first();

        if (!$corte) {
            return response()->json(['existe' => false, 'sistema' => $sistema]);
        }

        $denMxn = $corte->denominaciones->where('moneda', 'MXN')->keyBy('denominacion')
            ->map(fn($d) => $d->cantidad);
        $denUsd = $corte->denominaciones->where('moneda', 'USD')->keyBy('denominacion')
            ->map(fn($d) => $d->cantidad);

        return response()->json([
            'existe'                    => true,
            'sistema'                   => $sistema,
            'updated_at'                => $corte->updated_at->format('d/m/Y H:i'),
            'total_ventas'              => $corte->total_ventas,
            'num_pagos_tarjeta'         => $corte->num_pagos_tarjeta,
            'importe_tarjeta'           => $corte->importe_tarjeta,
            'efectivo_mxn'              => $corte->efectivo_mxn,
            'efectivo_dlls_cantidad'    => $corte->efectivo_dlls_cantidad,
            'efectivo_dlls_tc'          => $corte->efectivo_dlls_tc,
            'dotacion'                  => $corte->dotacion,
            'pagos_cancelados'          => $corte->pagos_cancelados,
            'saldo_inicial_dispensador' => $corte->saldo_inicial_dispensador,
            'dotacion_final'            => $corte->dotacion_final,
            'cambio_entregado'          => $corte->cambio_entregado,
            'cambio_no_entregado'       => $corte->cambio_no_entregado,
            'referencia_cambio'         => $corte->referencia_cambio,
            'corte_total_efectivo'      => $corte->corte_total_efectivo,
            'efectivo_entregado'        => $corte->efectivo_entregado,
            'observaciones'             => $corte->observaciones,
            'den_mxn'                   => $denMxn,
            'den_usd'                   => $denUsd,
        ]);
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

    // GET /cortes/{fecha}/json — datos del día para el modal en index
    public function showJson($fecha)
    {
        $tipoCambio = TipoDeCambio::latest()->first()->tipo_cambio ?? 20.0;

        $cortes = CorteCajero::with(['caja', 'denominaciones', 'capturadoPor'])
            ->where('fecha_corte', $fecha)
            ->orderBy('caja_id')
            ->get();

        if ($cortes->isEmpty()) {
            return response()->json(['error' => 'No hay cortes para esa fecha.'], 404);
        }

        $denMap = [
            'MXN' => ['b500'=>500,'b200'=>200,'b100'=>100,'b50'=>50,'b20'=>20,'b10'=>10,'b5'=>5,'b2'=>2,'b1'=>1,'m10'=>10,'m5'=>5,'m2'=>2,'m1'=>1],
            'USD' => ['usd_b50'=>50,'usd_b20'=>20,'usd_b10'=>10,'usd_b5'=>5,'usd_b2'=>2,'usd_b1'=>1],
        ];

        $data = $cortes->map(function ($c) use ($tipoCambio, $denMap) {
            $denMxn = $c->denominaciones->where('moneda','MXN')->keyBy('denominacion');
            $denUsd = $c->denominaciones->where('moneda','USD')->keyBy('denominacion');

            $billetes = []; $totalBilletes = 0;
            foreach ($denMap['MXN'] as $k => $val) {
                $cant = $denMxn[$k]->cantidad ?? 0;
                if ($cant > 0) { $billetes[] = ['lbl' => $k, 'cant' => $cant, 'monto' => $cant * $val]; }
                $totalBilletes += $cant * $val;
            }
            $usdItems = []; $totalUsd = 0;
            foreach ($denMap['USD'] as $k => $val) {
                $cant = $denUsd[$k]->cantidad ?? 0;
                if ($cant > 0) { $usdItems[] = ['lbl' => $k, 'cant' => $cant, 'monto' => $cant * $val]; }
                $totalUsd += $cant * $val;
            }

            return [
                'id'                      => $c->id,
                'caja_nombre'             => $c->caja->nombre,
                'caja_codigo'             => $c->caja->codigo,
                'diferencia'              => (float) $c->diferencia,
                'total_ventas'            => (float) $c->total_ventas,
                'num_pagos_tarjeta'       => $c->num_pagos_tarjeta,
                'importe_tarjeta'         => (float) $c->importe_tarjeta,
                'efectivo_mxn'            => (float) $c->efectivo_mxn,
                'efectivo_dlls_cantidad'  => (float) $c->efectivo_dlls_cantidad,
                'efectivo_dlls_tc'        => (float) $c->efectivo_dlls_tc,
                'efectivo_dlls_importe'   => (float) $c->efectivo_dlls_importe,
                'total_efectivo'          => (float) $c->total_efectivo,
                'total_de_venta'          => (float) $c->total_de_venta,
                'dotacion'                => (float) $c->dotacion,
                'pagos_cancelados'        => (float) $c->pagos_cancelados,
                'total_egresos'           => (float) $c->total_egresos,
                'saldo_inicial_dispensador' => (float) $c->saldo_inicial_dispensador,
                'dotacion_final'          => (float) $c->dotacion_final,
                'saldo_dispensador'       => (float) $c->saldo_dispensador,
                'cambio_entregado'        => (float) $c->cambio_entregado,
                'cambio_no_entregado'     => (float) $c->cambio_no_entregado,
                'saldo_cambio_entregado'  => (float) $c->saldo_cambio_entregado,
                'referencia_cambio'       => $c->referencia_cambio,
                'corte_total_efectivo'    => (float) $c->corte_total_efectivo,
                'efectivo_entregado'      => (float) $c->efectivo_entregado,
                'observaciones'           => $c->observaciones,
                'capturado_por'           => $c->capturadoPor->name ?? 'N/D',
                'updated_at'              => $c->updated_at->format('d/m/Y H:i'),
                'billetes_mxn'            => $billetes,
                'total_billetes'          => $totalBilletes,
                'usd_items'               => $usdItems,
                'total_usd'               => $totalUsd,
                'total_usd_mxn'           => round($totalUsd * $tipoCambio, 2),
                'tc'                      => $tipoCambio,
            ];
        });

        return response()->json([
            'fecha'      => $fecha,
            'tc'         => $tipoCambio,
            'total_dia'  => [
                'prosepago'  => (float) $cortes->sum('importe_tarjeta'),
                'efectivo'   => (float) $cortes->sum('total_efectivo'),
                'venta'      => (float) $cortes->sum('total_de_venta'),
                'diferencia' => (float) $cortes->sum('diferencia'),
            ],
            'cortes' => $data,
        ]);
    }

    // GET /cortes/{fecha}/modal — HTML parcial para inyectar en modal
    public function showModal($fecha)
    {
        $tipoCambio = TipoDeCambio::latest()->first()->tipo_cambio ?? 20.0;

        $cortes = CorteCajero::with(['caja', 'denominaciones', 'capturadoPor'])
            ->where('fecha_corte', $fecha)
            ->orderBy('caja_id')
            ->get();

        if ($cortes->isEmpty()) {
            return response('<div class="text-center py-4 text-danger">No hay cortes para esta fecha.</div>');
        }

        $totalDia = [
            'prosepago'  => (float) $cortes->sum('importe_tarjeta'),
            'efectivo'   => (float) $cortes->sum('total_efectivo'),
            'venta'      => (float) $cortes->sum('total_de_venta'),
            'diferencia' => (float) $cortes->sum('diferencia'),
        ];

        $billetesMxn = ['b500'=>['lbl'=>'B. $500','val'=>500],'b200'=>['lbl'=>'B. $200','val'=>200],'b100'=>['lbl'=>'B. $100','val'=>100],'b50'=>['lbl'=>'B. $50','val'=>50],'b20'=>['lbl'=>'B. $20','val'=>20],'b10'=>['lbl'=>'B. $10','val'=>10],'b5'=>['lbl'=>'B. $5','val'=>5],'b2'=>['lbl'=>'B. $2','val'=>2],'b1'=>['lbl'=>'B. $1','val'=>1],'m10'=>['lbl'=>'M. $10','val'=>10],'m5'=>['lbl'=>'M. $5','val'=>5],'m2'=>['lbl'=>'M. $2','val'=>2],'m1'=>['lbl'=>'M. $1','val'=>1]];
        $billetesUsd = ['usd_b50'=>['lbl'=>'B. $50','val'=>50],'usd_b20'=>['lbl'=>'B. $20','val'=>20],'usd_b10'=>['lbl'=>'B. $10','val'=>10],'usd_b5'=>['lbl'=>'B. $5','val'=>5],'usd_b2'=>['lbl'=>'B. $2','val'=>2],'usd_b1'=>['lbl'=>'B. $1','val'=>1]];

        return view('cortes.modal.contenido-dia', compact(
            'fecha', 'cortes', 'totalDia', 'tipoCambio', 'billetesMxn', 'billetesUsd'
        ));
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

    // ── Datos del sistema para un cajero/fecha concretos ────────────────────
    private function sistemaDatos(string $codigoCaja, string $fecha): array
    {
        $inicio = Carbon::parse($fecha)->startOfDay();
        $fin    = Carbon::parse($fecha)->endOfDay();

        $row = DB::selectOne("
            SELECT
                -- Total real del cajero: lavados (T2) + compra/renovación membresía (T0,T1), excluye cortesías (Total=0)
                SUM(CASE WHEN Total > 0 THEN Total ELSE 0 END)                                                               AS total_ventas,
                -- Tarjeta: cualquier tipo de transacción pagada con tarjeta (PaymentType 1=débito, 2=crédito)
                SUM(CASE WHEN PaymentType != 0 AND Total > 0 THEN Total ELSE 0 END)                                         AS importe_tarjeta,
                COUNT(CASE WHEN PaymentType != 0 AND Total > 0 THEN 1 END)                                                  AS num_pagos_tarjeta,
                -- Efectivo: cualquier tipo de transacción pagada en efectivo (PaymentType=0)
                SUM(CASE WHEN PaymentType = 0 AND Total > 0 THEN Total ELSE 0 END)                                          AS efectivo_mxn,
                -- Conteos desglosados
                COUNT(CASE WHEN TransactionType = 2 AND Total > 0 THEN 1 END)                                               AS num_lavados,
                SUM(CASE WHEN TransactionType IN (0,1) AND PaymentType != 0 AND Total > 0 THEN Total ELSE 0 END)            AS importe_membresias_tarjeta,
                COUNT(CASE WHEN TransactionType IN (0,1) AND Total > 0 THEN 1 END)                                          AS num_membresias
            FROM local_transaction
            WHERE TransationDate BETWEEN ? AND ?
              AND Atm = ?
              AND deleted_at IS NULL
        ", [$inicio, $fin, $codigoCaja]);

        return [
            'total_ventas'              => (float) ($row->total_ventas              ?? 0),
            'importe_tarjeta'           => (float) ($row->importe_tarjeta           ?? 0),
            'num_pagos_tarjeta'         => (int)   ($row->num_pagos_tarjeta         ?? 0),
            'efectivo_mxn'              => (float) ($row->efectivo_mxn              ?? 0),
            'num_lavados'               => (int)   ($row->num_lavados               ?? 0),
            'importe_membresias_tarjeta'=> (float) ($row->importe_membresias_tarjeta?? 0),
            'num_membresias'            => (int)   ($row->num_membresias            ?? 0),
        ];
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
