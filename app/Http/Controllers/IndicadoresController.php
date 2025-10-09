<?php
namespace App\Http\Controllers;

use App\Models\GeneralCatalogs;
use App\Models\LocalTransaction;
use App\Models\SpecialOrders;
use App\Models\UsedBusinessCodes;
use App\Models\WeatherLog;
use App\Models\ClientMembership;
use App\Models\Client;
use App\Models\Orders;
use App\Exports\UsersExport;
use App\Exports\SalesTraffic;
use App\Exports\TransactionsList;
use App\Exports\IndicadoresExport;
use App\Exports\MembresiasExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Dompdf\Options;

class IndicadoresController extends Controller
{
    public $catalogs;
    public function __construct(GeneralCatalogs $catalogs){
        $this->catalogs = $catalogs;
    }

    
    function indicadores_cajero(){
        $activePage = 'Indicadores';
        return view('indicadores.indicadores_cajero', compact('activePage'));
    }
    public function indicadores2(){
        $activePage = 'Indicadores';
        return view('indicadores.index', compact('activePage'));
    }

    public function indicadores_table(Request $request){

        $from  = $request->input('fecha_inicio'); // opcional
        $until = $request->input('fecha_final');  // opcional

        $rows = LocalTransaction::resumenPorDia($from, $until);
        $data  = [];
        if ($rows) {
            foreach ($rows as $key => $row) {
                
                $data[] = array(
                    'fecha'                     => $row['fecha'],
                    'total_eventos'             => $row['total_eventos'],
                    'lavados_paquete'           => $row['lavados_paquete'],
                    'lavados_express'           => $row['lavados_express'],
                    'lavados_basico'            => $row['lavados_basico'],
                    'lavados_ultra'             => $row['lavados_ultra'],
                    'lavados_deluxe'            => $row['lavados_deluxe'],
                    'promo150'                   => $row['promo150'],
                    'promo50'                   => $row['promo50'],
                    'suma_total_tipo2'          => $row['suma_total_tipo2'],
                    'lavados_membresia'         => $row['lavados_membresia'],
                    'lavados_express_membresia' => $row['lavados_express_membresia'],
                    'lavados_basico_membresia'  => $row['lavados_basico_membresia'],
                    'lavados_ultra_membresia'   => $row['lavados_ultra_membresia'],
                    'lavados_deluxe_membresia'  => $row['lavados_deluxe_membresia'],
                    'compra_membresia'          => $row['compra_membresia'],
                    'renovacion_membresia'      => $row['renovacion_membresia'],
                    'sum_compra_membresia'      => $row['sum_compra_membresia'],
                    'sum__renovacion_membresia' => $row['sum__renovacion_membresia'], // ojo: doble underscore según tu alias
                    'lavados_cortesia'          => $row['lavados_cortesia'],

                    'suma_total_dia'            => $row['suma_total_dia'],
                    'suma_total_dia_iva'         => $row['suma_total_dia']/1.08


                );
            }
            echo json_encode(array("data" => $data));
        }
    }

    public function indicadores_pagos_table(Request $request){

        try {
            $fechaInicio = $request->input('fecha_inicio');
            $fechaFinal = $request->input('fecha_final');

            // Validar fechas
            if (!$fechaInicio || !$fechaFinal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Las fechas son requeridas'
                ], 400);
            }

            // Convertir fechas
            $fechaInicio = Carbon::parse($fechaInicio)->format('Y-m-d') . ' 00:00:01';
            $fechaFinal = Carbon::parse($fechaFinal)->format('Y-m-d') . ' 23:59:59';
            $rows = LocalTransaction::indicadores_pagos_table($fechaInicio, $fechaFinal);

            // Ejecutar la consulta
            

            // Formatear los resultados para DataTables
            $data = array_map(function($row) {
                return [
                    'fecha' => $row->fecha,
                    'total_eventos' => (int)$row->total_eventos,
                    'suma_total_efectivo' => (float)$row->suma_total_efectivo,
                    'suma_total_cajero1' => (float)$row->suma_total_cajero1,
                    'suma_total_cajero2' => (float)$row->suma_total_cajero2,
                    'suma_targetas_paquetes' => (float)$row->suma_targetas_paquetes,
                    'suma_targetas_cajero_1' => (float)$row->suma_targetas_cajero_1,
                    'suma_targetas_cajero_2' => (float)$row->suma_targetas_cajero_2,
                    'suma_compra_membrecias' => (float)$row->suma_compra_membrecias,
                    'suma_comra_membresia_cajero_1' => (float)$row->suma_comra_membresia_cajero_1,
                    'suma_compra_membresia_cajero_2' => (float)$row->suma_compra_membresia_cajero_2,
                    'suma_renovacion_membresia_cajero_1' => (float)$row->suma_renovacion_membresia_cajero_1,
                    'suma_renovacion_membresia_cajero_2' => (float)$row->suma_renovacion_membresia_cajero_2,
                    'suma_procepago' => (float)$row->suma_procepago,
                    'suma_total_dia' => (float)$row->suma_total_dia
                ];
            }, $rows);

            return response()->json([
                'data' => $data,
                'recordsTotal' => count($data),
                'recordsFiltered' => count($data),
                'success' => true
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los datos: ' . $e->getMessage(),
                'data' => [],
                'recordsTotal' => 0,
                'recordsFiltered' => 0
            ], 500);
        }
    }

    function indicadores_membresias(){
        $activePage = 'indicadores_membresias';
        return view('indicadores.indicadores_membresias', compact('activePage'));
    }

    public function indicadores_membresias_table(Request $request){
        
        try {
            $fechaInicio = $request->input('fecha_inicio');
            $fechaFinal = $request->input('fecha_final');

            // Validar fechas
            if (!$fechaInicio || !$fechaFinal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Las fechas son requeridas'
                ], 400);
            }

            // Convertir fechas
            $fechaInicio = Carbon::parse($fechaInicio)->format('Y-m-d') . ' 00:00:01';
            $fechaFinal = Carbon::parse($fechaFinal)->format('Y-m-d') . ' 23:59:59';
            $rows = LocalTransaction::indicadores_membresias_table($fechaInicio, $fechaFinal);

            // Ejecutar la consulta
            $rows = array_filter($rows, function ($row) {
                return intval($row->total_orders) > 0;
            });
            $precios = [
                'Delux'   => 1000,
                'Ultra'   => 800,
                'Básico'  => 650,
                'Express' => 500
            ];
                    // Formatear los resultados para DataTables
            $data = array_map(function($row)use ($precios)  {

                $client = $row->first_name . ' '.$row->last_name;

                $packageName = $row->package_name ?? 'N/A';
                $totalOrders = intval($row->total_orders);

                // Calcular ticket promedio
                $precio = $precios[$packageName] ?? 0;
                $ticketPromedio = $totalOrders > 0 ? $precio/$totalOrders : 0;
                $ticketPromedio = round($ticketPromedio,2);

                return [
                    'cliente' => $client,
                    'UserId' => $row->client_id,
                    'package' => $row->package_name,
                    'package_name' => $row->package_name,
                    'total' => $row->total_orders,
                    'ticket_promedio'=> $ticketPromedio
                ];
            }, $rows);

            return response()->json([
                'data' => $data,
                'recordsTotal' => count($data),
                'recordsFiltered' => count($data),
                'success' => true
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los datos: ' . $e->getMessage(),
                'data' => [],
                'recordsTotal' => 0,
                'recordsFiltered' => 0
            ], 500);
        }
    }

    /*=============

    =============*/


    public function indicadores(Request $request){

        $catalogs   = new GeneralCatalogs();
        $activePage = 'Indicadores';
        
        // Parsear fechas (o toma un mes fijo si es estático)
        // $startDate = Carbon::parse($request->input('startDate', now()->startOfMonth()));
        // $endDate   = Carbon::parse($request->input('endDate', now()->endOfMonth()));

        //$startDate = Carbon::now()->startOfMonth();
        //$endDate   = Carbon::now()->endOfMonth();

        $startDate = Carbon::parse($request->input('startDate', '2025-07-01'));
        $endDate   = Carbon::parse($request->input('endDate', now()->endOfMonth()));

        // Validar que el startDate no sea menor al 1 julio 2025
        if ($startDate->lt(Carbon::parse('2025-07-01'))) {
            $startDate = Carbon::parse('2025-07-01')->startOfDay();
        }

        



        //############### 
        // LAVADOS POR DIA  TRANSACTIONS

        // 1) preparamos el sub‐query, igual que en el listado
        $sub = DB::table('local_transaction')
            ->select([
                '_id',
                DB::raw("CASE WHEN LENGTH(_id) = 36 THEN 'CARRERA' ELSE 'INTERLOGIC' END as proveedor"),
            ])
            ->whereBetween('TransationDate', [$startDate, $endDate])
            ->whereIn('PaymentType', [0,1,2,3])
            ->whereIn('TransactionType', [0,1,2])
            ->groupBy('_id', 'proveedor');

        // 2) A partir de ahí hacemos el join para quedarnos sólo con INTERLOGIC
        //    y agregamos todas las columnas que necesitas
        $query = DB::table( DB::raw("({$sub->toSql()}) as ag") )
            ->mergeBindings($sub) // importa los where/bindings del sub
            ->where('ag.proveedor', 'INTERLOGIC')
            ->join('local_transaction as lt', 'lt._id', '=', 'ag._id')
            ->selectRaw(<<<SQL
                DATE(lt.TransationDate)                        as fecha,
                COUNT(
                  DISTINCT
                  CASE
                    WHEN lt.PaymentFolio IS NOT NULL AND lt.PaymentFolio <> ''
                      THEN lt.PaymentFolio
                    ELSE ag._id
                  END
                )                                              as total_contados,
                COUNT(*)                                       as total_registros,
                COUNT(DISTINCT lt.PaymentFolio)                 as folios_distintos,
                COUNT(DISTINCT lt._id)                          as ids_distintos,
                SUM(CASE WHEN lt.PaymentType    = 0 THEN 1 ELSE 0 END) as pagos_efectivo,
                SUM(CASE WHEN lt.PaymentType IN (1,2) THEN 1 ELSE 0 END) as pagos_tarjeta,
                SUM(CASE WHEN lt.TransactionType = 0 THEN 1 ELSE 0 END) as tt0,
                SUM(CASE WHEN lt.TransactionType = 1 THEN 1 ELSE 0 END) as tt1,
                SUM(CASE WHEN lt.TransactionType = 2 THEN 1 ELSE 0 END) as tt2
            SQL
            )
            ->whereBetween('lt.TransationDate', [$startDate, $endDate])
            ->groupBy(DB::raw("DATE(lt.TransationDate)"))
            ->orderBy('fecha');

            // 3) primero obtenemos la colección…
            $totTransCollection = $query->get();

            // 4) …y **luego** la `keyBy('fecha')`
            $totTrans = $totTransCollection->keyBy('fecha');




        //############### 
        // LAVADOS POR DIA  TRANSACTIONS

        // Total de lavados por día (OrderType 1 y 2)
        $totLavsXMembOrders = Orders::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('OrderType', [1,2])
            ->selectRaw("DATE(created_at) as fecha, COUNT(*) as total_lavados")
            ->groupBy(DB::raw("DATE(created_at)"))
            ->pluck('total_lavados', 'fecha'); 

        //############### 
        // LAVADOS POR DIA  ORDERS DESGLOSADO POR MEMBRESIA
            $usoMembresiasPorDia = DB::table('orders')
            ->selectRaw("
                DATE(created_at) as fecha,
                SUM(CASE WHEN MembershipId = '61344ae637a5f00383106c7a' THEN 1 ELSE 0 END) AS Uso_Membresia_Express,
                SUM(CASE WHEN MembershipId = '61344b5937a5f00383106c80' THEN 1 ELSE 0 END) AS Uso_Membresia_Basico,
                SUM(CASE WHEN MembershipId = '61344b9137a5f00383106c84' THEN 1 ELSE 0 END) AS Uso_Membresia_Ultra,
                SUM(CASE WHEN MembershipId = '61344bab37a5f00383106c88' THEN 1 ELSE 0 END) AS Uso_Membresia_Delux
            ")
            ->whereBetween('created_at', ['2025-07-01', '2025-07-30'])
            ->where('OrderType', 1)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('fecha')
            ->get()
            ->keyBy('fecha'); // Opcional: para acceder fácilmente por fecha


        /// CONSULTA GENERAL
        $datos = DB::table('local_transaction as lt')
        ->join(DB::raw("(
            SELECT
                _id,
                CASE
                    WHEN LENGTH(_id) = 36 THEN 'CARRERA'
                    ELSE 'INTERLOGIC'
                END AS proveedor
            FROM local_transaction
            WHERE TransationDate BETWEEN '2025-07-01' AND '2025-07-30'
            AND PaymentType IN (0,1,2,3)
            AND TransactionType IN (0,1,2)
            GROUP BY _id
        ) as ag"), 'lt._id', '=', 'ag._id')
        ->selectRaw("
            DATE(lt.TransationDate) as fecha,
            COUNT(DISTINCT CASE WHEN lt.PaymentFolio IS NOT NULL AND lt.PaymentFolio <> '' THEN lt.PaymentFolio ELSE ag._id END) as total_contados,
            COUNT(DISTINCT lt._id) as ids_unicos,
            SUM(CASE WHEN lt.PaymentType = 0 THEN 1 ELSE 0 END) as pagos_efectivo,
            SUM(CASE WHEN lt.PaymentType IN (1,2) THEN 1 ELSE 0 END) as pagos_tarjeta,
            SUM(CASE WHEN lt.TransactionType = 0 THEN 1 ELSE 0 END) as Compra_Membresia,
            SUM(CASE WHEN lt.TransactionType = 1 THEN 1 ELSE 0 END) as Renovacion,
            SUM(CASE WHEN lt.TransactionType = 2 THEN 1 ELSE 0 END) as Compra_Paquete,

            -- Paquetes normales (sin monto promocional)
            SUM(CASE WHEN lt.TransactionType = 2 AND lt.Package = '612f057787e473107fda56aa' AND lt.Total NOT IN (50, 150, 200) THEN 1 ELSE 0 END) AS Paquete_Express,
            SUM(CASE WHEN lt.TransactionType = 2 AND lt.Package = '612f067387e473107fda56b0' AND lt.Total NOT IN (50, 150, 200) THEN 1 ELSE 0 END) AS Paquete_Basico,
            SUM(CASE WHEN lt.TransactionType = 2 AND lt.Package = '612f1c4f30b90803837e7969' AND lt.Total NOT IN (50, 150, 200) THEN 1 ELSE 0 END) AS Paquete_Ultra,
            SUM(CASE WHEN lt.TransactionType = 2 AND lt.Package = '61344bab37a5f00383106c88' AND lt.Total NOT IN (50, 150, 200) THEN 1 ELSE 0 END) AS Paquete_Deluxe,

        
            -- Promociones por monto
            SUM(CASE WHEN lt.TransactionType = 2 AND lt.Total = 50 THEN 1 ELSE 0 END) AS Paquetes_50,
            SUM(CASE WHEN lt.TransactionType = 2 AND lt.Total = 150 THEN 1 ELSE 0 END) AS Paquetes_150,
            SUM(CASE WHEN lt.TransactionType = 2 AND lt.Total = 200 THEN 1 ELSE 0 END) AS Paquetes_200,


            SUM(CASE WHEN lt.TransactionType = 1 THEN lt.Total ELSE 0 END) as Ingresos_Renovacion_Membresia,
            SUM(CASE WHEN lt.TransactionType = 0 AND lt.Package = '61344ae637a5f00383106c7a' THEN lt.Total ELSE 0 END) as Ingresos_Membresia_Express,
            SUM(CASE WHEN lt.TransactionType = 0 AND lt.Package = '61344b5937a5f00383106c80' THEN lt.Total ELSE 0 END) as Ingresos_Membresia_Basico,
            SUM(CASE WHEN lt.TransactionType = 0 AND lt.Package = '61344b9137a5f00383106c84' THEN lt.Total ELSE 0 END) as Ingresos_Membresia_Ultra,
            SUM(CASE WHEN lt.TransactionType = 0 AND lt.Package = '61344bab37a5f00383106c88' THEN lt.Total ELSE 0 END) as Ingresos_Membresia_Deluxe,

            SUM(CASE WHEN lt.TransactionType = 1 AND lt.Package = '61344ae637a5f00383106c7a' THEN lt.Total ELSE 0 END) as Renovacion_Membresia_Express,
            SUM(CASE WHEN lt.TransactionType = 1 AND lt.Package = '61344b5937a5f00383106c80' THEN lt.Total ELSE 0 END) as Renovacion_Membresia_Basico,
            SUM(CASE WHEN lt.TransactionType = 1 AND lt.Package = '61344b9137a5f00383106c84' THEN lt.Total ELSE 0 END) as Renovacion_Membresia_Ultra,
            SUM(CASE WHEN lt.TransactionType = 1 AND lt.Package = '61344bab37a5f00383106c88' THEN lt.Total ELSE 0 END) as Renovacion_Membresia_Deluxe,
            SUM(CASE WHEN lt.TransactionType IN (0,1,2) THEN lt.Total ELSE 0 END) as Total_Ingresos,
            SUM(CASE WHEN lt.TransactionType IN (0,1,2) THEN lt.Total / 1.08 ELSE 0 END) as Ingresos_Sin_IVA,
            AVG(CASE WHEN lt.TransactionType IN (0,1,2) THEN lt.Total ELSE NULL END) as Ticket_Promedio
        ")
        ->where('ag.proveedor', 'INTERLOGIC')
        ->whereBetween('lt.TransationDate', ['2025-07-01', '2025-07-30'])
        ->groupBy(DB::raw('DATE(lt.TransationDate)'))
        ->orderBy('fecha')
        ->get()
        ->keyBy('fecha');

        return view('indicadores.indicadores', compact('activePage', 'startDate', 'endDate', 'totTrans', 'totLavsXMembOrders', 'datos', 'usoMembresiasPorDia'));
    
    }



    public function membresias(Request $request){

        $activePage = 'Membresias';

        $startDate = Carbon::parse($request->input('startDate', '2025-07-01'))->startOfDay();
        $endDate   = Carbon::parse($request->input('endDate', now()->endOfMonth()))->endOfDay();

        if ($startDate->lt(Carbon::parse('2025-07-01'))) {
            $startDate = Carbon::parse('2025-07-01')->startOfDay();
        }


        $paquetes = [
            '61344ae637a5f00383106c7a' => 'Express',
            '61344b5937a5f00383106c80' => 'Basico',
            '61344b9137a5f00383106c84' => 'Ultra',
            '61344bab37a5f00383106c88' => 'Delux',
        ];

        // Subconsulta para obtener el primer uso por cliente
        $sub = DB::table('orders')
            ->select('UserId', DB::raw('MIN(created_at) as FechaPrimerUso'))
            ->where('OrderType', 1)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('UserId');

        // Ahora hacemos join para traer los demás datos del primer uso
        $primerosUsos = DB::table('orders as o')
            ->joinSub($sub, 'primeros', function ($join) {
                $join->on('o.UserId', '=', 'primeros.UserId')
                    ->on('o.created_at', '=', 'primeros.FechaPrimerUso');
            })
            ->join('clients as c', 'o.UserId', '=', 'c._id')
            ->select(
                'o.UserId as Referencia',
                'c.first_name as Nombre',
                'c.last_name as Apellido',
                'o.MembershipId as Paquete',
                'o.created_at as FechaPrimerUso'
            )
            ->orderBy('FechaPrimerUso')
            ->get();

        // Ahora enriquecemos los datos
        $resultados = $primerosUsos->map(function ($item) use ($paquetes) {
            $userId = $item->Referencia;

            // Nombre del paquete
            $item->Nombre_Paquete = $paquetes[$item->Paquete] ?? 'Otro';

            // Total de usos (membresías) sin importar la fecha
            $item->usos = DB::table('orders')
                ->where('UserId', $userId)
                ->where('OrderType', 1)
                ->count();

            // Precio y fecha del primer cobro
            $primerCobro = DB::table('client_membership as cm')
                ->join('local_transaction as lt', 'lt.PaymentFolio', '=', 'cm.prosepago_id')
                ->where('cm.client_id', $userId)
                ->where('lt.TransactionType', 0)
                ->orderBy('lt.TransationDate')
                ->select('lt.Total', 'lt.created_at')
                ->first();

            $item->Precio = $primerCobro->Total ?? null;
            $item->Fecha_PrimerCobro = $primerCobro->created_at ?? null;

            // Ticket promedio
            $item->ticket_promedio = ($item->usos > 0 && $item->Precio)
                ? round($item->Precio / $item->usos, 2)
                : null;

            return $item;
        });


        // Uso de membresias y promedio
        $resultado = DB::table('orders as o')
            ->where('o.OrderType', 1)
            ->whereBetween('o.created_at', [$startDate, $endDate])
            ->selectRaw('
                COUNT(o.UserId) AS Total_Clientes_Con_Membresia,
                COUNT(o.order_id) / NULLIF(COUNT(DISTINCT o.UserId), 0) AS Uso_Promedio
            ')
            ->first();

            return view('indicadores.membresias', compact('activePage', 'startDate', 'endDate', 'resultados', 'resultado'));

    }

    public function generarOperativosPDF(Request $request){

        $startDate = Carbon::parse($request->input('startDate', '2025-07-01'))->startOfDay();
        $endDate   = Carbon::parse($request->input('endDate', now()->endOfMonth()))->endOfDay();

        if ($startDate->lt(Carbon::parse('2025-07-01'))) {
            $startDate = Carbon::parse('2025-07-01')->startOfDay();
        }

        //############### 
        // LAVADOS POR DIA  TRANSACTIONS

        // 1) preparamos el sub‐query, igual que en el listado
        $sub = DB::table('local_transaction')
            ->select([
                '_id',
                DB::raw("CASE WHEN LENGTH(_id) = 36 THEN 'CARRERA' ELSE 'INTERLOGIC' END as proveedor"),
            ])
            ->whereBetween('TransationDate', [$startDate, $endDate])
            ->whereIn('PaymentType', [0,1,2,3])
            ->whereIn('TransactionType', [0,1,2])
            ->groupBy('_id', 'proveedor');

            // 2) A partir de ahí hacemos el join para quedarnos sólo con INTERLOGIC
            //    y agregamos todas las columnas que necesitas
            $query = DB::table( DB::raw("({$sub->toSql()}) as ag") )
                    ->mergeBindings($sub) // importa los where/bindings del sub
                    ->where('ag.proveedor', 'INTERLOGIC')
                    ->join('local_transaction as lt', 'lt._id', '=', 'ag._id')
                    ->selectRaw(<<<SQL
                        DATE(lt.TransationDate)                        as fecha,
                        COUNT(
                        DISTINCT
                        CASE
                            WHEN lt.PaymentFolio IS NOT NULL AND lt.PaymentFolio <> ''
                            THEN lt.PaymentFolio
                            ELSE ag._id
                        END
                        )                                              as total_contados,
                        COUNT(*)                                       as total_registros,
                        COUNT(DISTINCT lt.PaymentFolio)                 as folios_distintos,
                        COUNT(DISTINCT lt._id)                          as ids_distintos,
                        SUM(CASE WHEN lt.PaymentType    = 0 THEN 1 ELSE 0 END) as pagos_efectivo,
                        SUM(CASE WHEN lt.PaymentType IN (1,2) THEN 1 ELSE 0 END) as pagos_tarjeta,
                        SUM(CASE WHEN lt.TransactionType = 0 THEN 1 ELSE 0 END) as tt0,
                        SUM(CASE WHEN lt.TransactionType = 1 THEN 1 ELSE 0 END) as tt1,
                        SUM(CASE WHEN lt.TransactionType = 2 THEN 1 ELSE 0 END) as tt2
                    SQL
                    )
                    ->whereBetween('lt.TransationDate', [$startDate, $endDate])
                    ->groupBy(DB::raw("DATE(lt.TransationDate)"))
                    ->orderBy('fecha');

                    // 3) primero obtenemos la colección…
                    $totTransCollection = $query->get();

                    // 4) …y **luego** la `keyBy('fecha')`
                    $totTrans = $totTransCollection->keyBy('fecha');




                //############### 
                // LAVADOS POR DIA  TRANSACTIONS

                // Total de lavados por día (OrderType 1 y 2)
                $totLavsXMembOrders = Orders::whereBetween('created_at', [$startDate, $endDate])
                    ->whereIn('OrderType', [1,2])
                    ->selectRaw("DATE(created_at) as fecha, COUNT(*) as total_lavados")
                    ->groupBy(DB::raw("DATE(created_at)"))
                    ->pluck('total_lavados', 'fecha'); 




                //############### 
                // LAVADOS POR DIA  ORDERS DESGLOSADO POR MEMBRESIA
                    $usoMembresiasPorDia = DB::table('orders')
                    ->selectRaw("
                        DATE(created_at) as fecha,
                        SUM(CASE WHEN MembershipId = '61344ae637a5f00383106c7a' THEN 1 ELSE 0 END) AS Uso_Membresia_Express,
                        SUM(CASE WHEN MembershipId = '61344b5937a5f00383106c80' THEN 1 ELSE 0 END) AS Uso_Membresia_Basico,
                        SUM(CASE WHEN MembershipId = '61344b9137a5f00383106c84' THEN 1 ELSE 0 END) AS Uso_Membresia_Ultra,
                        SUM(CASE WHEN MembershipId = '61344bab37a5f00383106c88' THEN 1 ELSE 0 END) AS Uso_Membresia_Delux
                    ")
                    ->whereBetween('created_at', ['2025-07-01', '2025-07-30'])
                    ->where('OrderType', 1)
                    ->groupBy(DB::raw('DATE(created_at)'))
                    ->orderBy('fecha')
                    ->get()
                    ->keyBy('fecha'); // Opcional: para acceder fácilmente por fecha


        /// CONSULTA GENERAL
            $datos = DB::table('local_transaction as lt')
            ->join(DB::raw("(
                SELECT
                    _id,
                    CASE
                        WHEN LENGTH(_id) = 36 THEN 'CARRERA'
                        ELSE 'INTERLOGIC'
                    END AS proveedor
                FROM local_transaction
                WHERE TransationDate BETWEEN '2025-07-01' AND '2025-07-30'
                AND PaymentType IN (0,1,2,3)
                AND TransactionType IN (0,1,2)
                GROUP BY _id
            ) as ag"), 'lt._id', '=', 'ag._id')
            ->selectRaw("
                DATE(lt.TransationDate) as fecha,
                COUNT(DISTINCT CASE WHEN lt.PaymentFolio IS NOT NULL AND lt.PaymentFolio <> '' THEN lt.PaymentFolio ELSE ag._id END) as total_contados,
                COUNT(DISTINCT lt._id) as ids_unicos,
                SUM(CASE WHEN lt.PaymentType = 0 THEN 1 ELSE 0 END) as pagos_efectivo,
                SUM(CASE WHEN lt.PaymentType IN (1,2) THEN 1 ELSE 0 END) as pagos_tarjeta,
                SUM(CASE WHEN lt.TransactionType = 0 THEN 1 ELSE 0 END) as Compra_Membresia,
                SUM(CASE WHEN lt.TransactionType = 1 THEN 1 ELSE 0 END) as Renovacion,
                SUM(CASE WHEN lt.TransactionType = 2 THEN 1 ELSE 0 END) as Compra_Paquete,

                -- Paquetes normales (sin monto promocional)
                SUM(CASE WHEN lt.TransactionType = 2 AND lt.Package = '612f057787e473107fda56aa' AND lt.Total NOT IN (50, 150, 200) THEN 1 ELSE 0 END) AS Paquete_Express,
                SUM(CASE WHEN lt.TransactionType = 2 AND lt.Package = '612f067387e473107fda56b0' AND lt.Total NOT IN (50, 150, 200) THEN 1 ELSE 0 END) AS Paquete_Basico,
                SUM(CASE WHEN lt.TransactionType = 2 AND lt.Package = '612f1c4f30b90803837e7969' AND lt.Total NOT IN (50, 150, 200) THEN 1 ELSE 0 END) AS Paquete_Ultra,
                SUM(CASE WHEN lt.TransactionType = 2 AND lt.Package = '61344bab37a5f00383106c88' AND lt.Total NOT IN (50, 150, 200) THEN 1 ELSE 0 END) AS Paquete_Deluxe,

            
                -- Promociones por monto
                SUM(CASE WHEN lt.TransactionType = 2 AND lt.Total = 50 THEN 1 ELSE 0 END) AS Paquetes_50,
                SUM(CASE WHEN lt.TransactionType = 2 AND lt.Total = 150 THEN 1 ELSE 0 END) AS Paquetes_150,
                SUM(CASE WHEN lt.TransactionType = 2 AND lt.Total = 200 THEN 1 ELSE 0 END) AS Paquetes_200,


                SUM(CASE WHEN lt.TransactionType = 1 THEN lt.Total ELSE 0 END) as Ingresos_Renovacion_Membresia,
                SUM(CASE WHEN lt.TransactionType = 0 AND lt.Package = '61344ae637a5f00383106c7a' THEN lt.Total ELSE 0 END) as Ingresos_Membresia_Express,
                SUM(CASE WHEN lt.TransactionType = 0 AND lt.Package = '61344b5937a5f00383106c80' THEN lt.Total ELSE 0 END) as Ingresos_Membresia_Basico,
                SUM(CASE WHEN lt.TransactionType = 0 AND lt.Package = '61344b9137a5f00383106c84' THEN lt.Total ELSE 0 END) as Ingresos_Membresia_Ultra,
                SUM(CASE WHEN lt.TransactionType = 0 AND lt.Package = '61344bab37a5f00383106c88' THEN lt.Total ELSE 0 END) as Ingresos_Membresia_Deluxe,

                SUM(CASE WHEN lt.TransactionType = 1 AND lt.Package = '61344ae637a5f00383106c7a' THEN lt.Total ELSE 0 END) as Renovacion_Membresia_Express,
                SUM(CASE WHEN lt.TransactionType = 1 AND lt.Package = '61344b5937a5f00383106c80' THEN lt.Total ELSE 0 END) as Renovacion_Membresia_Basico,
                SUM(CASE WHEN lt.TransactionType = 1 AND lt.Package = '61344b9137a5f00383106c84' THEN lt.Total ELSE 0 END) as Renovacion_Membresia_Ultra,
                SUM(CASE WHEN lt.TransactionType = 1 AND lt.Package = '61344bab37a5f00383106c88' THEN lt.Total ELSE 0 END) as Renovacion_Membresia_Deluxe,
                SUM(CASE WHEN lt.TransactionType IN (0,1,2) THEN lt.Total ELSE 0 END) as Total_Ingresos,
                SUM(CASE WHEN lt.TransactionType IN (0,1,2) THEN lt.Total / 1.08 ELSE 0 END) as Ingresos_Sin_IVA,
                AVG(CASE WHEN lt.TransactionType IN (0,1,2) THEN lt.Total ELSE NULL END) as Ticket_Promedio
            ")
            ->where('ag.proveedor', 'INTERLOGIC')
            ->whereBetween('lt.TransationDate', ['2025-07-01', '2025-07-30'])
            ->groupBy(DB::raw('DATE(lt.TransationDate)'))
            ->orderBy('fecha')
            ->get()
            ->keyBy('fecha');

        // 4. Generar PDF
        $options = new Options();
        $options->set('defaultFont', 'Helvetica'); // opcional
        $pdf = Pdf::loadView('indicadores.pdf_indicadores_operativos', compact(
            'startDate',
            'endDate',
            'datos',
            'usoMembresiasPorDia',
            'totLavsXMembOrders',
            'totTrans'
        ))
        ->setPaper([0, 0, 1600, 850], 'portrait'); // ancho x alto en puntos

        return $pdf->download('indicadores_operativos.pdf');
    }


    public function generarMembresiasPDF(Request $request){ 
        $startDate = Carbon::parse($request->input('startDate', '2025-07-01'));
        $endDate   = Carbon::parse($request->input('endDate', now()->endOfMonth()));
        

        if ($startDate->lt(Carbon::parse('2025-07-01'))) {
            $startDate = Carbon::parse('2025-07-01')->startOfDay();
        }

        $paquetes = [
            '61344ae637a5f00383106c7a' => 'Express',
            '61344b5937a5f00383106c80' => 'Basico',
            '61344b9137a5f00383106c84' => 'Ultra',
            '61344bab37a5f00383106c88' => 'Delux',
        ];

        // Subconsulta para obtener el primer uso por cliente
        $sub = DB::table('orders')
            ->select('UserId', DB::raw('MIN(created_at) as FechaPrimerUso'))
            ->where('OrderType', 1)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('UserId');

        // Ahora hacemos join para traer los demás datos del primer uso
        $primerosUsos = DB::table('orders as o')
            ->joinSub($sub, 'primeros', function ($join) {
                $join->on('o.UserId', '=', 'primeros.UserId')
                    ->on('o.created_at', '=', 'primeros.FechaPrimerUso');
            })
            ->join('clients as c', 'o.UserId', '=', 'c._id')
            ->select(
                'o.UserId as Referencia',
                'c.first_name as Nombre',
                'c.last_name as Apellido',
                'o.MembershipId as Paquete',
                'o.created_at as FechaPrimerUso'
            )
            ->orderBy('FechaPrimerUso')
            ->get();

        // Ahora enriquecemos los datos
        $resultados = $primerosUsos->map(function ($item) use ($paquetes) {
            $userId = $item->Referencia;

            // Nombre del paquete
            $item->Nombre_Paquete = $paquetes[$item->Paquete] ?? 'Otro';

            // Total de usos (membresías) sin importar la fecha
            $item->usos = DB::table('orders')
                ->where('UserId', $userId)
                ->where('OrderType', 1)
                ->count();

            // Precio y fecha del primer cobro
            $primerCobro = DB::table('client_membership as cm')
                ->join('local_transaction as lt', 'lt.PaymentFolio', '=', 'cm.prosepago_id')
                ->where('cm.client_id', $userId)
                ->where('lt.TransactionType', 0)
                ->orderBy('lt.TransationDate')
                ->select('lt.Total', 'lt.created_at')
                ->first();

            $item->Precio = $primerCobro->Total ?? null;
            $item->Fecha_PrimerCobro = $primerCobro->created_at ?? null;

            // Ticket promedio
            $item->ticket_promedio = ($item->usos > 0 && $item->Precio)
                ? round($item->Precio / $item->usos, 2)
                : null;

            return $item;
        });

        // Uso de memebresias y promedio
        $resultado = DB::table('orders as o')
            ->where('o.OrderType', 1)
            ->whereBetween('o.created_at', [$startDate, $endDate])
            ->selectRaw('
                COUNT(o.UserId) AS Total_Clientes_Con_Membresia,
                COUNT(o.order_id) / NULLIF(COUNT(DISTINCT o.UserId), 0) AS Uso_Promedio
            ')
            ->first();

            //return view('indicadores.membresias', compact('activePage', 'startDate', 'endDate', 'resultados', 'resultado'));

            // 4. Generar PDF
        $options = new Options();
        $options->set('defaultFont', 'Helvetica'); // opcional
        $pdf = Pdf::loadView('indicadores.pdf_indicadores_membresias', compact(
            'startDate',
            'endDate',
            'resultados',
            'resultado',
            'primerosUsos'
            
        ))->setPaper('A4', 'landscape');

        return $pdf->download('indicadores_membresias.pdf');
    }


    public function exportarIndicadores(Request $request){
        $startDate = Carbon::parse($request->input('startDate', '2025-07-01'));
        $endDate   = Carbon::parse($request->input('endDate', now()->endOfMonth()));

        // Validar que el startDate no sea menor al 1 julio 2025
        if ($startDate->lt(Carbon::parse('2025-07-01'))) {
            $startDate = Carbon::parse('2025-07-01')->startOfDay();
        }

        return Excel::download(new IndicadoresExport($startDate, $endDate), 'indicadores-' . now()->format('Y-m-d_H-i') . '.xlsx');
    
    }

    public function exportMembresias(Request $request){
        // Aquí puedes regenerar los datos o pasarlos desde sesión
        $startDate = Carbon::parse($request->input('startDate', '2025-07-01'));
        $endDate   = Carbon::parse($request->input('endDate', now()->endOfMonth()));

        // Validar que el startDate no sea menor al 1 julio 2025
        if ($startDate->lt(Carbon::parse('2025-07-01'))) {
            $startDate = Carbon::parse('2025-07-01')->startOfDay();
        }

        return Excel::download(
            new MembresiasExport($startDate, $endDate),
            'membresias-' . now()->format('Y-m-d_H-i') . '.xlsx'
        );
    }



}