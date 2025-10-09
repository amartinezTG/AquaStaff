<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LocalTransaction;
use App\Models\GeneralCatalogs;
use App\Models\Client;
use App\Models\CorteCaja;
use App\Models\DetalleArqueo;
use App\Models\CorteCajaComentarios;
use App\Models\FondoDeCaja;
use App\Models\Orders;
use App\Models\TipoDeCambio;
use Illuminate\Support\Facades\Auth;
use App\Exports\CorteCajaExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class CorteCajaController extends Controller
{
    public function __construct(GeneralCatalogs $catalogs){
        $this->catalogs = $catalogs;
    }

    public function index(Request $request)
    {
        $activePage   = 'corteœ_caja_sucursal';
        //$cortesDeCaja = CorteCaja::orderBy('id', 'desc')->get();
        $cortesDeCaja = CorteCaja::with('usuario')->orderBy('id', 'desc')->get();


        $FondoDeCaja  = FondoDeCaja::orderBy('id', 'desc')->get();

        return view('corte_caja.corte_caja', compact('activePage', 'cortesDeCaja', 'FondoDeCaja'));
    }

    public function exportExcel($corte_id)
    {
        return Excel::download(new CorteCajaExport($corte_id), 'detalle_corte_'.$corte_id.'.xlsx');
    }

    public function validarFechaCorte(Request $request)
    {
        $fechaCorte = $request->fecha_corte;

        // Verificar si existe un registro en la tabla corte_de_caja con la fecha seleccionada
        $existeCorte = CorteCaja::where('fecha_corte', $fechaCorte)->exists();

        if ($existeCorte) {
            return response()->json(['error' => 'Ya se ha realizado el corte de caja para esta fecha.']);
        } else {
            // Obtener el número total de ventas y el monto total
            $ventas = LocalTransaction::whereDate('TransationDate', $fechaCorte)
                ->selectRaw('count(*) as total_ventas, sum(Total) as total_monto,
                    sum(case when PaymentType = 0 then Total else 0 end) as total_efectivo,
                    sum(case when PaymentType in (1, 2) then Total else 0 end) as total_tarjeta')
                ->first();

            // Ventas por Tipo de Pago
            $ventasTipoPago = LocalTransaction::whereDate('TransationDate', $fechaCorte)
            ->selectRaw('PaymentType, COUNT(*) as total_transacciones, SUM(Total) as total_monto')
            ->groupBy('PaymentType')
            ->get();

            // Convertir los resultados a un array asociativo
            $ventasPorTipoPago = [];
                foreach ($ventasTipoPago as $venta) {
                    $ventasPorTipoPago[$venta->PaymentType] = [
                        'total_transacciones' => $venta->total_transacciones,
                        'total_monto'         => number_format($venta->total_monto,2),
                    ];
                }

            // Ventas por Tipo de Paquete
            $ventasPaquete = LocalTransaction::whereDate('TransationDate', $fechaCorte)
                ->selectRaw('Package, COUNT(*) as total_transacciones, SUM(Total) as total_monto')
                ->groupBy('Package')
                ->get();

            $ventasPorPaquete = [];
                foreach ($ventasPaquete as $venta) {
                    $ventasPorPaquete[$venta->Package] = [
                        'total_transacciones' => $venta->total_transacciones,
                        'total_monto'         => $venta->total_monto,
                    ];
                }

            return response()->json([
                'success' => true,
                'total_ventas'         => $ventas->total_ventas ?? 0,
                'total_monto'          => $ventas->total_monto ?? 0,
                'total_efectivo'       => $ventas->total_efectivo,
                'total_tarjeta'        => $ventas->total_tarjeta,
                'ventas_por_tipo_pago' => $ventasPorTipoPago,
                'ventas_por_paquete'   => $ventasPorPaquete
            ]);
        }
    }

    public function SubmitCorte(Request $request)
    {
        $user          = Auth::user();

        $tipoCambio = TipoDeCambio::latest()->first()->tipo_cambio ?? 17.0; // valor default si no hay registros

        $request->validate([
            'fecha_corte'              => 'required',
            'sucursal'                 => 'required',
            'total_ventas'             => 'required',
            'total_tickets'            => 'required',
            'dinero_acumulado_efectivo' => 'required',
            'dinero_acumulado_tarjeta' => 'required',
            'dinero_recibido'          => 'required',
        ], [
            'fecha_corte.required'              => 'Campo requerido',
            'sucursal.required'                 => 'Campo requerido',
            'total_ventas.required'             => 'RFC ya ha sido agregado, debes de usar otro',
            'total_tickets.required'            => 'Campo requerido',
            'dinero_acumulado_efectivo.required' => 'Campo requerido',
            'dinero_acumulado_tarjeta.required' => 'Campo requerido',
            'dinero_recibido.required'          => 'Campo requerido'
        ]);

        $usdDenominations = ['usd_100', 'usd_50', 'usd_20', 'usd_10', 'usd_1', 'usd_1c', 'usd_5c', 'usd_10c', 'usd_25c'];
        $totalUsd = 0;
        foreach ($usdDenominations as $den) {
            $qty = (float) $request->$den;
            $valor = (float) str_replace('usd_', '', $den);
            $valor = str_contains($den, 'c') ? ($valor / 100) : $valor;
            $totalUsd += $qty * $valor;
        }
        $totalUsdEnMxn = round($totalUsd * $tipoCambio, 2);


        $CorteCaja = new CorteCaja;
        $CorteCaja->sucursal                 = $request->sucursal;
        $CorteCaja->fecha_corte              = $request->fecha_corte;
        
        $CorteCaja->total_ventas             = $request->total_ventas;
        $CorteCaja->total_tickets            = $request->total_tickets;
        $CorteCaja->dinero_acumulado_efectivo = $request->dinero_acumulado_efectivo;
        $CorteCaja->dinero_acumulado_tarjeta = $request->dinero_acumulado_tarjeta;
        $CorteCaja->dinero_recibido          = $request->dinero_recibido;
        $CorteCaja->usuario_que_hizo_corte   = $request->usuario_que_hizo_corte;
        $CorteCaja->estado                   = 'Cerrado';
        $CorteCaja->usuario_que_hizo_corte   = $user->id;

        $CorteCaja->dinero_acumulado_usd    = $totalUsd;
        $CorteCaja->tipo_cambio             = $tipoCambio;
        $CorteCaja->total_efectivo_en_mxn   = $totalUsdEnMxn + $request->dinero_acumulado_efectivo;


    
            if($CorteCaja->save()){
                
                $DetalleArqueo = new DetalleArqueo;
                $arr = array('mxn_1000', 'mxn_500', 'mxn_200', 'mxn_100', 'mxn_50', 'mxn_20', 'mxn_10', 'mxn_5', 'mxn_2', 'mxn_1');
                foreach ($arr as $denominacion) {
                    $cantidad = $request->$denominacion;

                    if ($cantidad > 0) { // Solo si la cantidad es mayor a cero
                        $detalle               = new DetalleArqueo;
                        $detalle->id_corte     = $CorteCaja->id;
                        $detalle->denominacion = $denominacion;
                        $detalle->cantidad     = $cantidad;
                        $detalle->save();
                    }
                }



                $arr_usd = array('usd_100', 'usd_50', 'usd_20', 'usd_10', 'usd_1', 'usd_1c', 'usd_5c', 'usd_10c', 'usd_25c');
                foreach ($arr_usd as $denominacion) {
                    $cantidad = $request->$denominacion;
                        if ($cantidad > 0) { // Solo si la cantidad es mayor a cero
                            $detalle               = new DetalleArqueo;
                            $detalle->id_corte     = $CorteCaja->id;
                            $detalle->denominacion = $denominacion;
                            $detalle->cantidad     = $cantidad;
                            $detalle->save();
                        }
                }

                if($request->comentarios){
                    $Comentarios = new CorteCajaComentarios;

                    $Comentarios->id_corte = $CorteCaja->id;
                    //$Comentarios->fecha    = date('Y-m-d');
                    $Comentarios->comentarios    = $request->comentarios;
                    $Comentarios->save();  
                }
                

                ////s
          
                return redirect('corte_caja')->with('success',  'Corte de Caja Agregado')->withInput();
            }else{
          
                return redirect('corte_caja')->with('error', 'Error')->withInput();
            }   
    }

    public function DetalleCorte($corte_id)
    {
        $user           = Auth::user();
        $corteCaja      = CorteCaja::where('id', $corte_id)->first();
        $detallesArqueo = DetalleArqueo::where('id_corte', $corte_id)->get();
        $catalogs       = $this->catalogs;
        $tipoCambio     = TipoDeCambio::latest()->first()->tipo_cambio ?? 17.0; // valor default si no hay registros

        $Comentarios    = CorteCajaComentarios::where('id_corte', $corte_id)->get();


        $ventas = LocalTransaction::whereDate('TransationDate', $corteCaja->fecha_corte)
                ->selectRaw('count(*) as total_ventas, sum(Total) as total_monto,
                    sum(case when PaymentType = 0 then Total else 0 end) as total_efectivo,
                    sum(case when PaymentType in (1, 2) then Total else 0 end) as total_tarjeta')
                ->first();

        // Ventas por Tipo de Pago
        $ventasTipoPago = LocalTransaction::whereDate('TransationDate', $corteCaja->fecha_corte)
            ->selectRaw('PaymentType, COUNT(*) as total_transacciones, SUM(Total) as total_monto')
            ->groupBy('PaymentType')
            ->get();

            // Convertir los resultados a un array asociativo
            $ventasPorTipoPago = [];
                foreach ($ventasTipoPago as $venta) {
                    $ventasPorTipoPago[$venta->PaymentType] = [
                        'total_transacciones' => $venta->total_transacciones,
                        'total_monto'         => number_format($venta->total_monto,2),
                    ];
                }

        // Ventas por Tipo de Paquete
            $ventasPaquete = LocalTransaction::whereDate('TransationDate', $corteCaja->fecha_corte)
                ->selectRaw('Package, COUNT(*) as total_transacciones, SUM(Total) as total_monto')
                ->groupBy('Package')
                ->get();

            $ventasPorPaquete = [];
                foreach ($ventasPaquete as $venta) {
                    $ventasPorPaquete[$venta->Package] = [
                        'total_transacciones' => $venta->total_transacciones,
                        'total_monto'         => $venta->total_monto,
                    ];
                }

        $activePage = 'corte_caja';

        $denominacionesMXN = [
            'mxn_1000' => 1000,
            'mxn_500'  => 500,
            'mxn_200'  => 200,
            'mxn_100'  => 100,
            'mxn_50'   => 50,
            'mxn_20'   => 20,
            'mxn_10'   => 10,
            'mxn_5'    => 5,
            'mxn_2'    => 2,
            'mxn_1'    => 1,
        ];

        $denominacionesUSD = [
            'usd_100' => 100,
            'usd_50'  => 50,
            'usd_20'  => 20,
            'usd_10'  => 10,
            'usd_1'   => 1,
            'usd_1c'  => 0.01,
            'usd_5c'  => 0.05,
            'usd_10c' => 0.10,
            'usd_25c' => 0.25,
        ];

        // Obtener cantidades
        $detalles = $detallesArqueo->pluck('cantidad', 'denominacion');

        // Calcular totales
        $total_mxn = 0;
        foreach ($denominacionesMXN as $key => $valor) {
            $cantidad = $detalles[$key] ?? 0;
            $total_mxn += $cantidad * $valor;
        }

        $total_usd = 0;
        foreach ($denominacionesUSD as $key => $valor) {
            $cantidad = $detalles[$key] ?? 0;
            $total_usd += $cantidad * $valor;
        }

        $total_efectivo_en_mxn = ($total_usd * $tipoCambio) + $total_mxn;


        return view('corte_caja.detalle_corte', compact('activePage',
    'corteCaja',
    'corte_id',
    'detallesArqueo',
    'Comentarios',
    'catalogs',
    'tipoCambio',
    'ventas',
    'ventasPorTipoPago',
    'ventasPorPaquete',
    'total_usd',
    'total_mxn',
    'total_efectivo_en_mxn'));

    }

    public function edit($corte_id)
    {
        $activePage     = 'corte_caja';
        $catalogs       = $this->catalogs;
        $corteCaja      = CorteCaja::findOrFail($corte_id);
        $detallesArqueo = DetalleArqueo::where('id_corte', $corte_id)->get();
        $comentarios    = CorteCajaComentarios::where('id_corte', $corte_id)->first(); // Suponiendo solo un comentario por corte
        $tipoCambio = TipoDeCambio::latest()->first()->tipo_cambio ?? 17.0; // valor default si no hay registros


        return view('corte_caja.editar_corte', compact('activePage', 'corteCaja', 'detallesArqueo', 'comentarios', 'catalogs', 'tipoCambio'));
    }

    public function update(Request $request, $corte_id)
    {
        $user = Auth::user();

        $request->validate([
            'fecha_corte'               => 'required',
            'sucursal'                  => 'required',
            'total_ventas'              => 'required',
            'total_tickets'             => 'required',
            'dinero_acumulado_efectivo' => 'required',
            'dinero_acumulado_tarjeta'  => 'required',
            'dinero_recibido'           => 'required',
        ], [
            'fecha_corte.required'               => 'Campo requerido',
            'sucursal.required'                  => 'Campo requerido',
            'total_ventas.required'              => 'Campo requerido',
            'total_tickets.required'             => 'Campo requerido',
            'dinero_acumulado_efectivo.required' => 'Campo requerido',
            'dinero_acumulado_tarjeta.required'  => 'Campo requerido',
            'dinero_recibido.required'           => 'Campo requerido'
        ]);


        $CorteCaja = CorteCaja::findOrFail($corte_id);
        $CorteCaja->sucursal                    = $request->sucursal;
        $CorteCaja->fecha_corte                 = $request->fecha_corte;
        $CorteCaja->total_ventas                = $request->total_ventas;
        $CorteCaja->total_tickets               = $request->total_tickets;
        $CorteCaja->dinero_acumulado_efectivo   = $request->dinero_acumulado_efectivo;
        $CorteCaja->dinero_acumulado_tarjeta  = $request->dinero_acumulado_tarjeta;
        $CorteCaja->dinero_recibido             = $request->dinero_recibido;
        $CorteCaja->usuario_que_edito          = $user->id; // Opcional: registrar quién editó
        $CorteCaja->tipo_cambio                = $request->tipo_cambio;
        $CorteCaja->total_efectivo_mxn       = $request->total_efectivo_mxn;
        $CorteCaja->estado                      = 'Editado'; // Opcional: cambiar el estado a 'Editado'

        if ($CorteCaja->save()) {
            // Actualizar o crear los detalles del arqueo
            $arr = array('mxn_1000', 'mxn_500', 'mxn_200', 'mxn_100', 'mxn_50', 'mxn_20', 'mxn_10', 'mxn_5', 'mxn_2', 'mxn_1');
            foreach ($arr as $denominacion) {
                if($request->$denominacion){
                    $cantidad = $request->$denominacion;
                    //die($corte_id.' - ' . $denominacion . ' - ' .$cantidad);
                    DetalleArqueo::updateOrCreate(
                        ['id_corte' => $corte_id, 'denominacion' => $denominacion],
                        ['cantidad' => $cantidad]
                    );
                }
                
            }

            $arr_usd = array('usd_100', 'usd_50', 'usd_20', 'usd_10', 'usd_1', 'usd_1c', 'usd_5c', 'usd_10c', 'usd_25c');
            foreach ($arr_usd as $denominacion) {
                $cantidad = $request->$denominacion;
                DetalleArqueo::updateOrCreate(
                    ['id_corte' => $corte_id, 'denominacion' => $denominacion],
                    ['cantidad' => $cantidad]
                );
            }

            // Actualizar o crear el comentario
            if ($request->comentarios) {
                CorteCajaComentarios::updateOrCreate(
                    ['id_corte' => $corte_id],
                    ['comentarios' => $request->comentarios]
                );
            } else {
                // Si no hay comentario, asegúrate de que no exista uno (opcional)
                CorteCajaComentarios::where('id_corte', $corte_id)->delete();
            }

            return redirect()->route('corte_caja')->with('success', 'Corte de Caja Actualizado');
        } else {
            return redirect()->route('corte_caja.detalle_corte', $corte_id)->with('error', 'Error al actualizar el Corte de Caja');
        }
    }

    public function exportPDF($corte_id){
        $corteCaja = CorteCaja::with('detallesArqueo')->findOrFail($corte_id);
        $pdf       = Pdf::loadView('pdf.detalle_corte', compact('corteCaja'));

        return $pdf->download('corte_' . $corte_id . '.pdf');
    }

    public function generarPDF($corte_id)
    {

        $membershipType = [
            '61344ae637a5f00383106c7a' => 'Express',
            '61344b5937a5f00383106c80' => 'Básico',
            '61344b9137a5f00383106c84' => 'Ultra',
            '61344bab37a5f00383106c88' => 'Delux',
        ];
        
       $corteCaja = CorteCaja::with('detallesArqueo')->findOrFail($corte_id);

        $responsable = $corteCaja->usuario->name ?? 'No asignado';

        $detallesArqueo = $corteCaja->detallesArqueo;
        $tipoCambio     = TipoDeCambio::latest()->first()?->tipo_cambio ?? 17.0;
        $Comentarios    = CorteCajaComentarios::where('id_corte', $corte_id)->first()?->comentarios ?? '';
        $catalogs       = $this->catalogs;

        // Denominaciones
        $denominacionesMXN = [
            'mxn_1000' => 1000, 'mxn_500' => 500, 'mxn_200' => 200,
            'mxn_100'  => 100,  'mxn_50' => 50,   'mxn_20' => 20,
            'mxn_10'   => 10,    'mxn_5' => 5,     'mxn_2' => 2, 'mxn_1' => 1,
        ];
        $denominacionesUSD = [
            'usd_100' => 100, 'usd_50' => 50, 'usd_20' => 20, 'usd_10' => 10,
            'usd_1'   => 1, 'usd_25c' => 0.25, 'usd_10c' => 0.10, 'usd_5c' => 0.05, 'usd_1c' => 0.01,
        ];

        $detalles = $detallesArqueo->pluck('cantidad', 'denominacion');

        $total_mxn = collect($denominacionesMXN)->reduce(fn($carry, $valor, $key) =>
            $carry + ($detalles[$key] ?? 0) * $valor, 0);

        $total_usd = collect($denominacionesUSD)->reduce(fn($carry, $valor, $key) =>
            $carry + ($detalles[$key] ?? 0) * $valor, 0);

        $total_efectivo_en_mxn = ($total_usd * $tipoCambio) + $total_mxn;

        // Ventas por tipo de pago
        $ventasTipoPago = LocalTransaction::whereDate('TransationDate', $corteCaja->fecha_corte)
            ->selectRaw('PaymentType, COUNT(*) as total_transacciones, SUM(Total) as total_monto')
            ->groupBy('PaymentType')
            ->get()
            ->keyBy('PaymentType')
            ->map(fn($v) => ['total_transacciones' => $v->total_transacciones, 'total_monto' => $v->total_monto]);

        // Ventas por paquete
        $ventasPorPaquete = LocalTransaction::whereDate('TransationDate', $corteCaja->fecha_corte)
            ->selectRaw('Package, COUNT(*) as total_transacciones, SUM(Total) as total_monto')
            ->groupBy('Package')
            ->get()
            ->keyBy('Package')
            ->map(fn($v) => ['total_transacciones' => $v->total_transacciones, 'total_monto' => $v->total_monto]);

        // Nuevas membresías
        $nuevasMembresias = Orders::whereBetween('created_at', [
            $corteCaja->fecha_corte . ' 00:00:00',
            $corteCaja->fecha_corte . ' 23:59:59',
        ])
        ->whereNotNull('MembershipId')
        ->whereNotNull('UserId')
        ->whereIn('OrderType', [1])
        ->select('OrderType', DB::raw('count(*) as total'))
        ->groupBy('OrderType')
        ->pluck('total', 'OrderType');

        // consulta Eloquent que agrupa y cuenta
        $LavadosXMembresias = LocalTransaction::query()
        ->whereDate('TransationDate', $corteCaja->fecha_corte)
        ->whereNotNull('Membership')
        ->select('Membership', DB::raw('COUNT(*) as total'))
        ->groupBy('Membership')
        ->get()
        // remapeamos para poner nombre en vez de ID
        ->map(function($row) use($membershipType) {
            return [
                'membership' => $membershipType[$row->Membership] ?? $row->Membership,
                'total'      => $row->total,
            ];
        });

        $MembresiasNuevas = LocalTransaction::query()
        ->whereDate('TransationDate', $corteCaja->fecha_corte)
        ->where('TransactionType', 0)       // solo nuevas membresías
        ->whereNotNull('Membership')        // con membresía asignada
        ->selectRaw('Membership, COUNT(*) as qty, SUM(Total) as total')
        ->groupBy('Membership')
        ->get()
        ->map(function($row) use($membershipType) {
            return [
                'membership' => $membershipType[$row->Membership] ?? 'N/D',
                'qty'        => $row->qty,
                'total'      => $row->total,
            ];
        });

        // Lavados por membresía
        $lavadoConMembresia = Orders::whereBetween('created_at', [
            $corteCaja->fecha_corte . ' 00:00:00',
            $corteCaja->fecha_corte . ' 23:59:59',
        ])
        ->whereNotNull('MembershipId')
        ->whereNotNull('UserId')
        ->whereIn('OrderType', [2])
        ->select('OrderType', DB::raw('count(*) as total'))
        ->groupBy('OrderType')
        ->pluck('total', 'OrderType');


        // consulta
    $rawVentasTipoPago = LocalTransaction::whereDate('TransationDate', $corteCaja->fecha_corte)
        ->selectRaw('PaymentType, COUNT(*) as total_transacciones, SUM(Total) as total_monto')
        ->groupBy('PaymentType')
        ->get();

    // inicializa array
    $ventasPorTipoPago = [
        0 => ['label'=>'Efectivo',           'total_transacciones'=>0, 'total_monto'=>0],
        1 => ['label'=>'Tarjeta de Débito',  'total_transacciones'=>0, 'total_monto'=>0],
        2 => ['label'=>'Tarjeta de Crédito', 'total_transacciones'=>0, 'total_monto'=>0],
        //3 => ['label'=>'Cortesía',           'total_transacciones'=>0, 'total_monto'=>0],
    ];

    // rellena datos
    foreach ($rawVentasTipoPago as $row) {
        $pt = $row->PaymentType;
        $ventasPorTipoPago[$pt]['total_transacciones'] = $row->total_transacciones;
        $ventasPorTipoPago[$pt]['total_monto']         = $row->total_monto;
    }


        /*return view('corte_caja.plantilla_pdf', compact('corteCaja',
            'responsable',
            'ventasPorPaquete',
            'LavadosXMembresias',
            'nuevasMembresias',
            'MembresiasNuevas',
            'lavadoConMembresia',
            'ventasTipoPago',
            'ventasPorTipoPago',
            'denominacionesMXN',
            'denominacionesUSD',
            'detallesArqueo',
            'total_usd',
            'total_mxn',
            'tipoCambio',
            'total_efectivo_en_mxn',
            'Comentarios',
            'catalogs'));*/

        return Pdf::loadView('corte_caja.plantilla_pdf', compact('corteCaja',
            'responsable',
            'ventasPorPaquete',
            'LavadosXMembresias',
            'nuevasMembresias',
            'MembresiasNuevas',
            'lavadoConMembresia',
            'ventasTipoPago',
            'ventasPorTipoPago',
            'denominacionesMXN',
            'denominacionesUSD',
            'detallesArqueo',
            'total_usd',
            'total_mxn',
            'tipoCambio',
            'total_efectivo_en_mxn',
            'Comentarios',
            'catalogs'))->stream("corte-caja-{$corteCaja->id}.pdf");
       
    }


    

}



