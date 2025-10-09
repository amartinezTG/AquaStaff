<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LocalTransaction;
use App\Models\GeneralCatalogs;
use App\Models\ClientMembership;
use App\Models\Client;
use App\Models\Orders;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public $catalogs;
    public function __construct(GeneralCatalogs $catalogs){
        $this->catalogs = $catalogs;
    }

    // Función de ayuda que recibe un rango y devuelve totales por proveedor
    private function getTotalsByProvider($from, $to)
    {
        $sub = DB::table('local_transaction')
            ->select([
                '_id',
                DB::raw("CASE WHEN LENGTH(_id) = 36 THEN 'CARRERA' ELSE 'INTERLOGIC' END AS proveedor"),
                DB::raw('SUM(Total) as tot'),
            ])
            ->whereBetween('TransationDate', [$from, $to])
            ->whereIn('PaymentType', [0,1,2,3])
            ->whereIn('TransactionType', [0,1,2])
            ->groupBy('_id');

        return DB::query()
            ->fromSub($sub, 'Unicas')
            ->select([
                'proveedor',
                DB::raw('SUM(tot) as total'),
            ])
            ->groupBy('proveedor')
            ->get()
            ->keyBy('proveedor');
    }
    public function dashboard(){
        $activePage = 'dashboard';
        return view('dashboard.dashboard', compact('activePage'));
    }


    public function index(Request $request)
    {
        $catalogs = new GeneralCatalogs();
        $activePage = 'dashboard';

        $timezone = 'America/Mexico_City';

        // Establecer el inicio de la semana antes de cualquier cálculo
        Carbon::setWeekStartsAt(Carbon::SUNDAY);

        $fechaEnZonaHoraria = Carbon::now($timezone);
        $today = Carbon::today($timezone);

        $todayStart    = $today->startOfDay();
        $todayEnd      = $fechaEnZonaHoraria->copy()->endOfDay();

        $dayOfWeek     = $fechaEnZonaHoraria->dayOfWeek;
        $DayOfMonth    = $fechaEnZonaHoraria->format('d');
        $LetterOfMonth = $fechaEnZonaHoraria->format('M');

        // Calcular el inicio y el fin de la semana correctamente
        $inicioDeSemana = $fechaEnZonaHoraria->copy()->startOfWeek();
        $finDeSemana    = $fechaEnZonaHoraria->copy()->endOfWeek();

        $startOfWeekAndEndWeek = 'Del ' . $inicioDeSemana->format('d') . ' al ' . $finDeSemana->format('d');

        $MonthStart = $fechaEnZonaHoraria->copy()->startOfMonth();
        $MonthEnd   = $fechaEnZonaHoraria->copy()->endOfMonth();

        $numberOfMonth = $fechaEnZonaHoraria->month;

        $TodayTotalTransactions = Orders::whereBetween('created_at', [$todayStart, $todayEnd])
                //->whereIn('OrderType', [1, 2])
                ->selectRaw('COUNT(*) as total')
                ->first();

        $WeekTotalTransactions = Orders::whereBetween('created_at', [$inicioDeSemana, $finDeSemana])
                //->whereIn('OrderType', [1, 2])
                ->selectRaw('COUNT(*) as total')
                ->first();
        $MonthTotalTransactions = Orders::whereBetween('created_at', [$MonthStart, $MonthEnd])
                //->whereIn('OrderType', [1, 2])
                ->selectRaw('COUNT(*) as total')
                ->first();


        // SALES
        $TodayTotalSales = LocalTransaction::whereBetween('TransationDate', [$todayStart, $todayEnd])->sum('Total');
        $WeektotalSales = LocalTransaction::whereBetween('TransationDate', [$inicioDeSemana, $finDeSemana])->sum('Total');
        $MonthTotalSales = LocalTransaction::whereBetween('TransationDate', [$MonthStart, $MonthEnd])->sum('Total');


        // Totales por proveedor
        $dailyByProv   = $this->getTotalsByProvider($todayStart, $todayEnd);
        $weeklyByProv  = $this->getTotalsByProvider($inicioDeSemana, $finDeSemana);
        $monthlyByProv = $this->getTotalsByProvider($MonthStart, $MonthEnd);

        // MEMBERSHIPS
        $TodayMemberships = ClientMembership::whereBetween('created_at', [$todayStart, $todayEnd])->count();
        $WeekMemberships = ClientMembership::whereBetween('created_at', [$inicioDeSemana, $finDeSemana])->count();
        $MonthMemberships = ClientMembership::whereBetween('created_at', [$MonthStart, $MonthEnd])->count();

        return view('dashboard', compact(
            'activePage',
            'catalogs',
            'TodayTotalTransactions',
            'WeekTotalTransactions',
            'MonthTotalTransactions',
            'TodayTotalSales',
            'WeektotalSales',
            'MonthTotalSales',
            'TodayMemberships',
            'WeekMemberships',
            'MonthMemberships',
            'dayOfWeek',
            'DayOfMonth',
            'LetterOfMonth',
            'numberOfMonth',
            'startOfWeekAndEndWeek',
            'dailyByProv',
            'weeklyByProv',
            'monthlyByProv'

        ));
    }


    public function info_dashboard(Request $request)
    {
        try {
            $date = $request->input('date', now()->toDateString());

            $startDate = $date . ' 00:00:00';
            $endDate = $date . ' 23:59:59';

            // Calcular datos del día anterior para comparaciones
            $yesterday = Carbon::parse($date)->subDay();
            $yesterdayStart = $yesterday->format('Y-m-d') . ' 00:00:00';
            $yesterdayEnd = $yesterday->format('Y-m-d') . ' 23:59:59';
            
            $data = [
                'summary' => $this->getDailySummary($startDate, $endDate, $yesterdayStart, $yesterdayEnd),
                'hourly' => $this->getHourlyData($startDate, $endDate),
                'membership_distribution' => $this->getMembershipDistribution($startDate, $endDate),
                'cajeros' => $this->getTopCajeros($startDate, $endDate),
                'payment_methods' => $this->getPaymentMethods($startDate, $endDate),
                // 'detailed_summary' => $this->getDetailedSummary($startDate, $endDate)
            ];

            
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener datos del dashboard: ' . $e->getMessage()
            ], 500);
        }
    }
    
    private function getDailySummary($startDate, $endDate, $yesterdayStart, $yesterdayEnd)
    {
        // Datos del día actual
        $today = DB::select("
           SELECT 
                CAST(t1.TransationDate AS DATE) AS fecha,
                COUNT(*) AS total_ordenes,
                SUM(t1.Total) AS total_ingresos,
                COALESCE(AVG(t1.Total), 0) as ticket_promedio
            FROM local_transaction AS t1

            WHERE t1.TransationDate >= ?
            AND t1.TransationDate <  ?
            GROUP BY CAST(t1.TransationDate AS DATE)
            ORDER BY fecha DESC
        ", [$startDate, $endDate])[0] ?? (object)['total_ingresos' => 0, 'total_ordenes' => 0, 'ticket_promedio' => 0];
        // Datos del día anterior
        $yesterday = DB::select("
           SELECT 
                CAST(t1.TransationDate AS DATE) AS fecha,
                COUNT(*) AS total_ordenes,
                SUM(t1.Total) AS total_ingresos,
                COALESCE(AVG(t1.Total), 0) as ticket_promedio
            FROM local_transaction AS t1
            WHERE t1.TransationDate >= ?
            AND t1.TransationDate <  ?
            GROUP BY CAST(t1.TransationDate AS DATE)
            ORDER BY fecha DESC
        ", [$yesterdayStart, $yesterdayEnd])[0] ?? (object)['total_ingresos' => 0, 'total_ordenes' => 0, 'ticket_promedio' => 0];

        // Membresías nuevas del día
        $membresiasHoy = DB::select("
            SELECT
                SUM(CASE WHEN t1.TransactionType = 0 THEN 1 ELSE 0 END) AS `total`
            FROM local_transaction AS t1
            WHERE t1.TransationDate between ? AND  ?
            GROUP BY CAST(t1.TransationDate AS DATE)
        ",  [$startDate, $endDate])[0]->total ?? 0;

        $membresiasAyer = DB::select("
            SELECT
                SUM(CASE WHEN t1.TransactionType = 0 THEN 1 ELSE 0 END) AS `total`
            FROM local_transaction AS t1
            WHERE t1.TransationDate between ? AND  ?
            GROUP BY CAST(t1.TransationDate AS DATE)
        ", [$yesterdayStart, $yesterdayEnd])[0]->total ?? 0;

       

        return [
            'total_ingresos' => floatval($today->total_ingresos),
            'total_ordenes' => intval($today->total_ordenes),
            'total_membresias' => intval($membresiasHoy),
            'ticket_promedio' => floatval($today->ticket_promedio),
            'ingresos_change' => $this->calculatePercentageChange($today->total_ingresos, $yesterday->total_ingresos),
            'ordenes_change' => $this->calculatePercentageChange($today->total_ordenes, $yesterday->total_ordenes),
            'membresias_change' => $this->calculatePercentageChange($membresiasHoy, $membresiasAyer),
            'ticket_change' => $this->calculatePercentageChange($today->ticket_promedio, $yesterday->ticket_promedio)
        ];
    }
    
    private function getHourlyData($startDate, $endDate)
    {
        return DB::select("
           SELECT 
                HOUR(t1.TransationDate)+1 AS `hour`,
                SUM(t1.Total) AS ingresos,
                COUNT(*) as ordenes
            FROM `local_transaction` t1
            WHERE
            t1.TransationDate BETWEEN ? AND ?
            GROUP BY hour
        ", [$startDate, $endDate]);
    }

    private function getMembershipDistribution($startDate, $endDate)
    {
        // Aquí necesitarías adaptar según tu lógica de negocio
        $memberships = DB::select("
            SELECT
            CASE COALESCE(t1.Package, t1.Membership)
                WHEN '612f057787e473107fda56aa' THEN 'Express'
                WHEN '61344ae637a5f00383106c7a' THEN 'Express'
                WHEN '612f067387e473107fda56b0' THEN 'Básico'
                WHEN '61344b5937a5f00383106c80' THEN 'Básico'
                WHEN '612f1c4f30b90803837e7969' THEN 'Ultra'
                WHEN '61344b9137a5f00383106c84' THEN 'Ultra'
                WHEN '61344bab37a5f00383106c88' THEN 'Delux'
                WHEN '612abcd1c4ce4c141237a356' THEN 'Delux'
                ELSE 'N/A'
            END AS package_name,
            COUNT(*) AS total
            FROM local_transaction t1
            WHERE
            t1.TransationDate >= ?
            AND t1.TransationDate <  ?
            AND (
                (t1.TransactionType = 2 AND t1.Total = 0 AND t1.PaymentType <> 3)
                OR t1.TransactionType IN (0,1)
            )
            GROUP BY package_name
            ORDER BY total DESC
        ", [$startDate, $endDate]) ?? 0;

        $packages = DB::select("
            SELECT
            CASE COALESCE(t1.Package, t1.Membership)
                WHEN '612f057787e473107fda56aa' THEN 'Express'
                WHEN '61344ae637a5f00383106c7a' THEN 'Express'
                WHEN '612f067387e473107fda56b0' THEN 'Básico'
                WHEN '61344b5937a5f00383106c80' THEN 'Básico'
                WHEN '612f1c4f30b90803837e7969' THEN 'Ultra'
                WHEN '61344b9137a5f00383106c84' THEN 'Ultra'
                WHEN '61344bab37a5f00383106c88' THEN 'Delux'
                WHEN '612abcd1c4ce4c141237a356' THEN 'Delux'
                ELSE 'N/A'
            END AS package_name,
            COUNT(*) AS total
            FROM local_transaction t1
            WHERE
            t1.TransationDate >= ?
            AND t1.TransationDate <  ?
            AND (
               (t1.TransactionType = 2 AND t1.Total != 0 AND t1.PaymentType <> 3)
            )
            GROUP BY package_name
            ORDER BY total DESC
        ", [$startDate, $endDate]) ?? 0;
        
       return [
            ['type' => 'Membresías', 'data' => $memberships, 'color' => '#6f42c1'],
            ['type' => 'Paquetes',   'data' => $packages,   'color' => '#17a2b8'],
        ];
    }
    
    private function getTopCajeros($startDate, $endDate)
    {
        return DB::select("
        SELECT 
            COUNT(*) AS total_ordenes,
            sum(t1.Total) AS total,
            t1.atm as `cajero`
        FROM local_transaction t1
        WHERE
        t1.TransationDate BETWEEN ? AND  ?
        GROUP BY atm
        ", [$startDate, $endDate]);
    }
    
    private function getPaymentMethods($startDate, $endDate)
    {
        // 1) Ejecuta la consulta, agrupando tipos 1 y 2 como “Tarjeta”
        $rows = DB::select("
            SELECT
            CASE
                WHEN t1.PaymentType = 0 THEN 'Efectivo'
                WHEN t1.PaymentType IN (1,2) THEN 'Tarjeta'
                WHEN t1.PaymentType = 3 THEN 'Garantía'
                ELSE 'N/A'
            END AS method,
            COUNT(*) AS total,
            SUM(t1.Total) AS total_ingreso
            FROM local_transaction t1
            WHERE
            t1.TransationDate >= ?
            AND t1.TransationDate <  ?
            GROUP BY method
        ", [$startDate, $endDate]);

        // 2) Define los métodos que siempre quieres mostrar
        $defaults = [
            'Efectivo'     => ['method' => 'Efectivo',  'total' => 0],
            'Tarjeta'      => ['method' => 'Tarjeta',   'total' => 0],
            'Garantía'     => ['method' => 'Garantía',  'total' => 0],
            // si quieres Transferencia, añádela aquí:
            //'Transferencia'=> ['method' => 'Transferencia', 'total' => 0],
        ];

        // 3) Combina resultados reales con los defaults
        foreach ($rows as $r) {
            // Laravel convierte cada fila en stdClass
            $m = $r->method;
            $defaults[$m]['total'] = (float)$r->total;
        }

        // 4) Devuelve como arreglo indexado
        return array_values($defaults);
    }
    
    // private function getDetailedSummary($startDate, $endDate)
    // {
    //     return DB::select("
    //         SELECT 
    //             CASE 
    //                 WHEN EXISTS (SELECT 1 FROM client_membership cm WHERE cm.client_id = o.UserId) 
    //                 THEN 'Membresías' 
    //                 ELSE 'Paquetes' 
    //             END as concepto,
    //             COUNT(*) as cantidad,
    //             COALESCE(SUM(Price), 0) as total
    //         FROM orders o
    //         WHERE o.created_at BETWEEN ? AND ? 
    //         AND o.OrderType = 1
    //         GROUP BY concepto
    //         ORDER BY total DESC
    //     ", [$startDate, $endDate]);
    // }
    
    private function calculatePercentageChange($current, $previous)
    {
        if (!$previous || $previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        
        return (($current - $previous) / $previous) * 100;
    }

    public function active_memberships()
    {
        try {
            // Query para obtener el total de membresías activas
            $totalMemberships = DB::table('client_membership')
                ->where('end_date', '>=', DB::raw('NOW()'))
                ->count();

            // Query para obtener el desglose por paquete
            $membershipsByPackage = DB::table('client_membership')
                ->select(
                    DB::raw('COUNT(*) as count'),
                    'membership_id',
                    DB::raw("CASE membership_id
                        WHEN '612f057787e473107fda56aa' THEN 'Express'
                        WHEN '61344ae637a5f00383106c7a' THEN 'Express'
                        WHEN '612f067387e473107fda56b0' THEN 'Básico'
                        WHEN '61344b5937a5f00383106c80' THEN 'Básico'
                        WHEN '612f1c4f30b90803837e7969' THEN 'Ultra'
                        WHEN '61344b9137a5f00383106c84' THEN 'Ultra'
                        WHEN '61344bab37a5f00383106c88' THEN 'Delux'
                        WHEN '612abcd1c4ce4c141237a356' THEN 'Delux'
                        ELSE 'N/A'
                    END AS package_name")
                )
                ->where('end_date', '>=', DB::raw('NOW()'))
                ->groupBy('membership_id')
                ->get();

            // Inicializar contadores
            $packages = [
                'express' => 0,
                'basico' => 0,
                'ultra' => 0,
                'delux' => 0
            ];

            // Sumar las cantidades por paquete
            foreach ($membershipsByPackage as $membership) {
                $packageKey = strtolower($membership->package_name);
                
                // Normalizar nombre del paquete
                if ($packageKey === 'básico') {
                    $packageKey = 'basico';
                }
                
                if (isset($packages[$packageKey])) {
                    $packages[$packageKey] += $membership->count;
                }
            }

            // Preparar respuesta
            $response = [
                'total' => $totalMemberships,
                'express' => $packages['express'],
                'basico' => $packages['basico'],
                'ultra' => $packages['ultra'],
                'delux' => $packages['delux'],
                'breakdown' => $membershipsByPackage,
                'timestamp' => now()->format('Y-m-d H:i:s')
            ];


            return response()->json($response);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener membresías activas',
                'message' => $e->getMessage()
            ], 500);
        }
    }

}

