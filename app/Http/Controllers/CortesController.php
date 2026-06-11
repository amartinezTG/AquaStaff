<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\CorteCajero;
use App\Models\CorteCajeroDenominacion;
use App\Models\CorteCajeroPaquete;
use App\Models\CorteCajeroMembresia;
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

    // Mapeo de plan _id (MongoDB) → nombre de paquete
    private array $membershipPlanMap = [
        '61344ae637a5f00383106c7a' => 'Express',
        '61344b5937a5f00383106c80' => 'Basico',
        '61344b9137a5f00383106c84' => 'Ultra',
        '61344bab37a5f00383106c88' => 'Deluxe',
    ];

    // Orden fijo de paquetes en el corte
    private array $ordenPaquetes = ['Express', 'Ultra', 'Deluxe', 'Basico', 'Otro'];

    // ── GET /cortes  — lista mensual ────────────────────────────────────────
    public function index(Request $request)
    {
        $activePage = 'cortes';
        $mes        = $request->get('mes', now()->format('Y-m'));
        $orden      = $request->get('orden', 'desc');

        [$anio, $mes_num] = explode('-', $mes);

        $cajas = FacilityCaja::where('activo', 1)
            ->whereHas('facility', fn($q) => $q->where('type', 'SUCURSAL'))
            ->with('facility')
            ->get();

        $cortes = CorteCajero::with('caja')
            ->whereYear('fecha_corte', $anio)
            ->whereMonth('fecha_corte', $mes_num)
            ->orderBy('fecha_corte')
            ->get()
            ->groupBy(fn($c) => $c->fecha_corte->format('Y-m-d'));

        $hoy         = now()->startOfDay();
        $esMesActual = $mes === now()->format('Y-m');
        $diasEnMes   = cal_days_in_month(CAL_GREGORIAN, (int)$mes_num, (int)$anio);
        $fechas      = collect();
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

    // ── GET /cortes/capturar ────────────────────────────────────────────────
    public function create(Request $request)
    {
        $activePage = 'cortes';
        $tipoCambio = TipoDeCambio::latest()->first()->tipo_cambio ?? 20.0;

        $facilityId = $request->get('facility_id', 2);
        $facility   = Facilities::findOrFail($facilityId);
        $cajas      = FacilityCaja::where('facility_id', $facilityId)->where('activo', 1)->get();

        $fechaCorte = $request->get('fecha', now()->format('Y-m-d'));

        $cajaId           = $request->get('caja_id', $cajas->first()->id ?? null);
        $cajaSeleccionada = $cajas->firstWhere('id', $cajaId) ?? $cajas->first();

        $corteExistente = CorteCajero::with(['denominaciones', 'paquetes', 'membresias'])
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

    // ── POST /cortes ────────────────────────────────────────────────────────
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

        $caja = FacilityCaja::find($cajaId);
        $sistemaData = $caja ? $this->sistemaDatos($caja->codigo, $fechaCorte) : [];

        $efectivoDllsImporte = round(
            ($request->efectivo_dlls_cantidad ?? 0) * ($request->efectivo_dlls_tc ?? $tipoCambio), 2
        );
        $totalEfectivo    = ($request->efectivo_mxn ?? 0) + $efectivoDllsImporte;
        $totalDeVenta     = $totalEfectivo + ($request->importe_tarjeta ?? 0);
        $totalEgresos     = ($request->dotacion ?? 0) + ($request->pagos_cancelados ?? 0);
        $saldoDispensador = ($request->saldo_inicial_dispensador ?? 0) + ($request->dotacion ?? 0);
        $saldoCambio      = ($request->cambio_entregado ?? 0) + ($request->cambio_no_entregado ?? 0);

        $interlogicId = $sistemaData['interlogic']['id'] ?? null;

        $corte = CorteCajero::updateOrCreate(
            ['fecha_corte' => $fechaCorte, 'caja_id' => $cajaId],
            [
                'corte_interlogic_id'       => $interlogicId,
                'interlogic_cargado'        => $interlogicId !== null,
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
        $this->guardarPaquetes($corte->id, $sistemaData, $request->input('paq_cajero', []), $request->input('paq_precio', []));
        $this->guardarMembresias($corte->id, $sistemaData, $request->input('mem_cajero', []));

        return redirect()->route('cortes.show', $fechaCorte)
            ->with('success', 'Corte guardado correctamente.');
    }

    // ── GET /cortes/datos-cajero  — JSON para el select dinámico ───────────
    public function datosCajero(Request $request)
    {
        $cajaId     = $request->get('caja_id');
        $fechaCorte = $request->get('fecha_corte');

        $caja    = FacilityCaja::find($cajaId);
        $sistema = $caja ? $this->sistemaDatos($caja->codigo, $fechaCorte) : [];

        $corte = CorteCajero::with(['denominaciones', 'paquetes', 'membresias'])
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

        $paqCajero  = $corte->paquetes->keyBy('paquete')
            ->map(fn($p) => ['autos_cajero' => $p->autos_cajero, 'precio' => $p->precio]);
        $memCajero  = $corte->membresias->keyBy('paquete')
            ->map(fn($m) => $m->autos_cajero);

        return response()->json([
            'existe'                    => true,
            'sistema'                   => $sistema,
            'updated_at'                => $corte->updated_at->format('d/m/Y H:i'),
            'interlogic_cargado'        => (bool) $corte->interlogic_cargado,
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
            'paq_cajero'                => $paqCajero,
            'mem_cajero'                => $memCajero,
        ]);
    }

    // ── GET /cortes/{fecha} ─────────────────────────────────────────────────
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

        $totalDia = [
            'prosepago'  => $cortes->sum('importe_tarjeta'),
            'efectivo'   => $cortes->sum('total_efectivo'),
            'venta'      => $cortes->sum('total_de_venta'),
            'diferencia' => $cortes->sum('diferencia'),
        ];

        return view('cortes.show', compact('activePage', 'fecha', 'cortes', 'facility', 'totalDia', 'tipoCambio'));
    }

    // ── GET /cortes/{fecha}/json ────────────────────────────────────────────
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
                'id'                        => $c->id,
                'caja_nombre'               => $c->caja->nombre,
                'caja_codigo'               => $c->caja->codigo,
                'diferencia'                => (float) $c->diferencia,
                'total_ventas'              => (float) $c->total_ventas,
                'num_pagos_tarjeta'         => $c->num_pagos_tarjeta,
                'importe_tarjeta'           => (float) $c->importe_tarjeta,
                'efectivo_mxn'              => (float) $c->efectivo_mxn,
                'efectivo_dlls_cantidad'    => (float) $c->efectivo_dlls_cantidad,
                'efectivo_dlls_tc'          => (float) $c->efectivo_dlls_tc,
                'efectivo_dlls_importe'     => (float) $c->efectivo_dlls_importe,
                'total_efectivo'            => (float) $c->total_efectivo,
                'total_de_venta'            => (float) $c->total_de_venta,
                'dotacion'                  => (float) $c->dotacion,
                'pagos_cancelados'          => (float) $c->pagos_cancelados,
                'total_egresos'             => (float) $c->total_egresos,
                'saldo_inicial_dispensador' => (float) $c->saldo_inicial_dispensador,
                'dotacion_final'            => (float) $c->dotacion_final,
                'saldo_dispensador'         => (float) $c->saldo_dispensador,
                'cambio_entregado'          => (float) $c->cambio_entregado,
                'cambio_no_entregado'       => (float) $c->cambio_no_entregado,
                'saldo_cambio_entregado'    => (float) $c->saldo_cambio_entregado,
                'referencia_cambio'         => $c->referencia_cambio,
                'corte_total_efectivo'      => (float) $c->corte_total_efectivo,
                'efectivo_entregado'        => (float) $c->efectivo_entregado,
                'observaciones'             => $c->observaciones,
                'capturado_por'             => $c->capturadoPor->name ?? 'N/D',
                'updated_at'                => $c->updated_at->format('d/m/Y H:i'),
                'billetes_mxn'              => $billetes,
                'total_billetes'            => $totalBilletes,
                'usd_items'                 => $usdItems,
                'total_usd'                 => $totalUsd,
                'total_usd_mxn'             => round($totalUsd * $tipoCambio, 2),
                'tc'                        => $tipoCambio,
            ];
        });

        return response()->json([
            'fecha'     => $fecha,
            'tc'        => $tipoCambio,
            'total_dia' => [
                'prosepago'  => (float) $cortes->sum('importe_tarjeta'),
                'efectivo'   => (float) $cortes->sum('total_efectivo'),
                'venta'      => (float) $cortes->sum('total_de_venta'),
                'diferencia' => (float) $cortes->sum('diferencia'),
            ],
            'cortes' => $data,
        ]);
    }

    // ── GET /cortes/{fecha}/modal ───────────────────────────────────────────
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

    // ── GET /cortes/{id}/editar ─────────────────────────────────────────────
    public function edit($id)
    {
        $activePage = 'cortes';
        $tipoCambio = TipoDeCambio::latest()->first()->tipo_cambio ?? 20.0;

        $corte    = CorteCajero::with(['caja', 'denominaciones', 'paquetes', 'membresias'])->findOrFail($id);
        $facility = Facilities::findOrFail($corte->facility_id);
        $cajas    = FacilityCaja::where('facility_id', $corte->facility_id)->where('activo', 1)->get();

        $denMxn = $corte->denominaciones->where('moneda', 'MXN')->keyBy('denominacion');
        $denUsd = $corte->denominaciones->where('moneda', 'USD')->keyBy('denominacion');

        $sistemaData = $this->sistemaDatos($corte->caja->codigo, $corte->fecha_corte->format('Y-m-d'));

        return view('cortes.editar', compact(
            'activePage', 'tipoCambio', 'corte', 'facility', 'cajas', 'denMxn', 'denUsd', 'sistemaData'
        ));
    }

    // ── PUT /cortes/{id} ────────────────────────────────────────────────────
    public function update(Request $request, $id)
    {
        $request->validate([
            'efectivo_mxn'       => 'required|numeric',
            'importe_tarjeta'    => 'required|numeric',
            'efectivo_entregado' => 'required|numeric',
        ]);

        $corte      = CorteCajero::findOrFail($id);
        $tipoCambio = TipoDeCambio::latest()->first()->tipo_cambio ?? 20.0;

        $caja        = FacilityCaja::find($corte->caja_id);
        $sistemaData = $caja ? $this->sistemaDatos($caja->codigo, $corte->fecha_corte->format('Y-m-d')) : [];

        $efectivoDllsImporte = round(
            ($request->efectivo_dlls_cantidad ?? 0) * ($request->efectivo_dlls_tc ?? $tipoCambio), 2
        );
        $totalEfectivo    = ($request->efectivo_mxn ?? 0) + $efectivoDllsImporte;
        $totalDeVenta     = $totalEfectivo + ($request->importe_tarjeta ?? 0);
        $totalEgresos     = ($request->dotacion ?? 0) + ($request->pagos_cancelados ?? 0);
        $saldoDispensador = ($request->saldo_inicial_dispensador ?? 0) + ($request->dotacion ?? 0);
        $saldoCambio      = ($request->cambio_entregado ?? 0) + ($request->cambio_no_entregado ?? 0);

        $interlogicId = $sistemaData['interlogic']['id'] ?? $corte->corte_interlogic_id;

        $corte->fill([
            'corte_interlogic_id'       => $interlogicId,
            'interlogic_cargado'        => $interlogicId !== null,
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
        $this->guardarPaquetes($corte->id, $sistemaData, $request->input('paq_cajero', []), $request->input('paq_precio', []));
        $this->guardarMembresias($corte->id, $sistemaData, $request->input('mem_cajero', []));

        return redirect()->route('cortes.show', $corte->fecha_corte->format('Y-m-d'))
            ->with('success', 'Corte actualizado correctamente.');
    }

    // ── Datos del sistema + interlogic para un cajero/fecha ─────────────────
    private function sistemaDatos(string $codigoCaja, string $fecha): array
    {
        $inicio = Carbon::parse($fecha)->startOfDay();
        $fin    = Carbon::parse($fecha)->endOfDay();

        // ── Totales generales de local_transaction (amarillo) ──
        $row = DB::selectOne("
            SELECT
                SUM(CASE WHEN Total > 0 THEN Total ELSE 0 END)                                              AS total_ventas,
                SUM(CASE WHEN PaymentType != 0 AND Total > 0 THEN Total ELSE 0 END)                         AS importe_tarjeta,
                COUNT(CASE WHEN PaymentType != 0 AND Total > 0 THEN 1 END)                                  AS num_pagos_tarjeta,
                SUM(CASE WHEN PaymentType = 0 AND Total > 0 THEN Total ELSE 0 END)                          AS efectivo_mxn,
                COUNT(CASE WHEN TransactionType = 2 AND Total > 0 THEN 1 END)                               AS num_lavados,
                SUM(CASE WHEN TransactionType IN (0,1) AND PaymentType != 0 AND Total > 0 THEN Total END)   AS importe_membresias_tarjeta,
                COUNT(CASE WHEN TransactionType IN (0,1) AND Total > 0 THEN 1 END)                          AS num_membresias
            FROM local_transaction
            WHERE TransationDate BETWEEN ? AND ?
              AND Atm = ?
              AND deleted_at IS NULL
        ", [$inicio, $fin, $codigoCaja]);

        // ── Paquetes comprados (TransactionType=2) por nombre de paquete ──
        $paquetesRows = DB::select("
            SELECT p.name AS paquete,
                   COUNT(*) AS autos,
                   p.price  AS precio,
                   SUM(lt.Total) AS total
            FROM local_transaction lt
            INNER JOIN packages p ON lt.Package = p._id
            WHERE lt.TransationDate BETWEEN ? AND ?
              AND lt.Atm = ?
              AND lt.TransactionType = 2
              AND lt.Total > 0
              AND lt.deleted_at IS NULL
            GROUP BY p.name, p.price
            ORDER BY p.price DESC
        ", [$inicio, $fin, $codigoCaja]);

        $paquetes = [];
        foreach ($paquetesRows as $r) {
            $paquetes[$r->paquete] = [
                'paquete' => $r->paquete,
                'autos'   => (int) $r->autos,
                'precio'  => (float) $r->precio,
                'total'   => (float) $r->total,
            ];
        }

        // ── Membresías utilizadas (TransactionType=1) por tipo de plan ──
        $memRows = DB::select("
            SELECT
                COALESCE(cm.membership_id, lt.Membership) AS plan_id,
                COUNT(*) AS autos
            FROM local_transaction lt
            LEFT JOIN client_membership cm ON lt.Membership = cm._id
            WHERE lt.TransationDate BETWEEN ? AND ?
              AND lt.Atm = ?
              AND lt.TransactionType = 1
              AND lt.Membership IS NOT NULL
              AND lt.Membership NOT IN ('0','')
              AND lt.Total > 0
              AND lt.deleted_at IS NULL
            GROUP BY plan_id
        ", [$inicio, $fin, $codigoCaja]);

        $membresias = [];
        foreach ($memRows as $r) {
            $nombre = $this->membershipPlanMap[$r->plan_id] ?? 'Otro';
            $membresias[$nombre] = ($membresias[$nombre] ?? 0) + (int) $r->autos;
        }
        // Convertir a array con estructura uniforme
        $membresiasArr = [];
        foreach ($membresias as $nombre => $autos) {
            $membresiasArr[$nombre] = ['paquete' => $nombre, 'autos' => $autos];
        }

        // ── Corte interlogic correspondiente (gris, puede ser null) ──
        $interlogic = $this->cargarInterlogic($codigoCaja, $fecha);

        return [
            // Totales generales (backward-compatible)
            'total_ventas'               => (float) ($row->total_ventas               ?? 0),
            'importe_tarjeta'            => (float) ($row->importe_tarjeta            ?? 0),
            'num_pagos_tarjeta'          => (int)   ($row->num_pagos_tarjeta          ?? 0),
            'efectivo_mxn'               => (float) ($row->efectivo_mxn               ?? 0),
            'num_lavados'                => (int)   ($row->num_lavados                ?? 0),
            'importe_membresias_tarjeta' => (float) ($row->importe_membresias_tarjeta ?? 0),
            'num_membresias'             => (int)   ($row->num_membresias             ?? 0),
            // Desglose por paquete/membresía
            'paquetes'   => $paquetes,
            'membresias' => $membresiasArr,
            // Datos del cajero interlogic (null si no llegó)
            'interlogic' => $interlogic,
        ];
    }

    // ── Carga completa del corte interlogic para cajero+fecha ───────────────
    private function cargarInterlogic(string $codigoCaja, string $fecha): ?array
    {
        $codigoUpper = strtoupper($codigoCaja);

        $ci = DB::selectOne("
            SELECT * FROM cortes_interlogic
            WHERE UPPER(id_cajero) = ?
              AND DATE(fin_sesion) = ?
            ORDER BY id DESC
            LIMIT 1
        ", [$codigoUpper, $fecha]);

        if (!$ci) {
            return null;
        }

        $paquetes  = DB::select("SELECT paquete, autos, precio, total FROM cortes_interlogic_paquetes_comprados WHERE corte_id = ?", [$ci->id]);
        $membresias = DB::select("SELECT paquete, autos FROM cortes_interlogic_membresias_utilizadas WHERE corte_id = ?", [$ci->id]);
        $pagosError = DB::select("SELECT fecha_hora, cuenta, a_pagar, ingresado, cambio_entregado, cambio_faltante FROM cortes_interlogic_pagos_error WHERE corte_id = ?", [$ci->id]);
        $cancelados = DB::select("SELECT fecha_hora, cuenta, a_pagar, ingresado, cambio_entregado, cambio_faltante FROM cortes_interlogic_cancelados WHERE corte_id = ?", [$ci->id]);
        $denominaciones = DB::select("SELECT tipo, valor, cantidad, importe FROM cortes_interlogic_denominaciones WHERE corte_id = ? ORDER BY tipo, valor DESC", [$ci->id]);
        $totalesEfectivo = DB::select("SELECT moneda, tc, cantidad, total FROM cortes_interlogic_totales_efectivo WHERE corte_id = ?", [$ci->id]);
        $saldoDispensadores = DB::select("SELECT denominacion, divisa, total FROM cortes_interlogic_saldo_dispensadores WHERE corte_id = ? ORDER BY divisa, denominacion DESC", [$ci->id]);

        return [
            'id'                                => $ci->id,
            'id_cajero'                         => $ci->id_cajero,
            'id_sesion'                         => $ci->id_sesion,
            'operador'                          => $ci->operador,
            'inicio_sesion'                     => $ci->inicio_sesion,
            'fin_sesion'                        => $ci->fin_sesion,
            'corte_total_ingresado_correctos'   => (float) $ci->corte_total_ingresado_correctos,
            'corte_total_ingresado_error'       => (float) $ci->corte_total_ingresado_error,
            'corte_total_ingresado_cancelados'  => (float) $ci->corte_total_ingresado_cancelados,
            'corte_total_ingresado'             => (float) $ci->corte_total_ingresado,
            'corte_total_cambio_entregado_correctos' => (float) $ci->corte_total_cambio_entregado_correctos,
            'corte_total_cambio_entregado_error'     => (float) $ci->corte_total_cambio_entregado_error,
            'corte_total_devuelto_cancelados'        => (float) $ci->corte_total_devuelto_cancelados,
            'corte_total_cambio_entregado'           => (float) $ci->corte_total_cambio_entregado,
            'corte_total_dotaciones'            => (float) $ci->corte_total_dotaciones,
            'balance_total_ingresado'           => (float) $ci->balance_total_ingresado,
            'balance_total_venta'               => (float) $ci->balance_total_venta,
            'balance_cambio_entregado'          => (float) $ci->balance_cambio_entregado,
            'balance_cambio_no_entregado'       => (float) $ci->balance_cambio_no_entregado,
            'balance_resultado'                 => (float) $ci->balance_resultado,
            'aceptado_cajero_total'             => (float) $ci->aceptado_cajero_total,
            'conteo_operador_total'             => (float) $ci->conteo_operador_total,
            'diferencia_registrado_cajero'      => (float) $ci->diferencia_registrado_cajero,
            'diferencia_total'                  => (float) $ci->diferencia_total,
            'tarjeta_importe_pagado'            => (float) $ci->tarjeta_importe_pagado,
            'tarjeta_numero_pagos'              => (int)   $ci->tarjeta_numero_pagos,
            'promociones_qr_aplicadas'          => (int)   $ci->promociones_qr_aplicadas,
            'cortesias_aplicadas'               => (int)   $ci->cortesias_aplicadas,
            // Subtablas
            'paquetes'           => array_map(fn($r) => (array) $r, $paquetes),
            'membresias'         => array_map(fn($r) => (array) $r, $membresias),
            'pagos_error'        => array_map(fn($r) => (array) $r, $pagosError),
            'cancelados'         => array_map(fn($r) => (array) $r, $cancelados),
            'denominaciones'     => array_map(fn($r) => (array) $r, $denominaciones),
            'totales_efectivo'   => array_map(fn($r) => (array) $r, $totalesEfectivo),
            'saldo_dispensadores'=> array_map(fn($r) => (array) $r, $saldoDispensadores),
        ];
    }

    // ── Guardar denominaciones ──────────────────────────────────────────────
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

    // ── Guardar paquetes comprados ──────────────────────────────────────────
    private function guardarPaquetes(int $corteId, array $sistemaData, array $cajeroAutos, array $cajeroPrecio): void
    {
        // Unir paquetes del sistema con los que el operador capturó
        $todosPaquetes = collect($sistemaData['paquetes'] ?? [])
            ->keys()
            ->merge(array_keys($cajeroAutos))
            ->unique();

        foreach ($todosPaquetes as $nombre) {
            $autosSistema = $sistemaData['paquetes'][$nombre]['autos'] ?? 0;
            $precioSistema = $sistemaData['paquetes'][$nombre]['precio'] ?? 0;
            $autosCajero  = (int) ($cajeroAutos[$nombre] ?? 0);
            $precio       = (float) ($cajeroPrecio[$nombre] ?? $precioSistema);
            $total        = $autosCajero * $precio;

            CorteCajeroPaquete::updateOrCreate(
                ['corte_cajero_id' => $corteId, 'paquete' => $nombre],
                [
                    'autos_sistema' => $autosSistema,
                    'autos_cajero'  => $autosCajero,
                    'precio'        => $precio,
                    'total'         => $total,
                ]
            );
        }
    }

    // ── Guardar membresías utilizadas ───────────────────────────────────────
    private function guardarMembresias(int $corteId, array $sistemaData, array $cajeroAutos): void
    {
        $todasMembresias = collect($sistemaData['membresias'] ?? [])
            ->keys()
            ->merge(array_keys($cajeroAutos))
            ->unique();

        foreach ($todasMembresias as $nombre) {
            $autosSistema = $sistemaData['membresias'][$nombre]['autos'] ?? 0;
            $autosCajero  = (int) ($cajeroAutos[$nombre] ?? 0);

            CorteCajeroMembresia::updateOrCreate(
                ['corte_cajero_id' => $corteId, 'paquete' => $nombre],
                [
                    'autos_sistema' => $autosSistema,
                    'autos_cajero'  => $autosCajero,
                ]
            );
        }
    }
}
