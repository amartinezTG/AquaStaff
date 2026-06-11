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
            ->whereNull('deleted_at')
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
                'servicios' => $this->getServiciosDelDia($startDate, $endDate),
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
                COALESCE(SUM(t1.Total) / NULLIF(COUNT(*), 0), 0) as ticket_promedio,
                COUNT(CASE WHEN t1.TransactionType = 2 AND t1.Total > 0 THEN 1 END) AS lavados_comprados,
                COUNT(CASE WHEN t1.TransactionType = 2 AND t1.Total = 0 THEN 1 END) AS lavados_membresia,
                COUNT(CASE WHEN t1.TransactionType IN (0,1) THEN 1 END) AS lavados_otros,
                COALESCE(SUM(CASE WHEN t1.TransactionType = 2 THEN t1.Total END), 0) AS ingresos_lavados,
                COALESCE(SUM(CASE WHEN t1.TransactionType = 0 THEN t1.Total END), 0) AS ingresos_membresia,
                COALESCE(SUM(CASE WHEN t1.TransactionType = 1 THEN t1.Total END), 0) AS ingresos_renovacion,
                COUNT(CASE WHEN t1.TransactionType = 0 THEN 1 END) AS membresias_nuevas,
                COUNT(CASE WHEN t1.TransactionType = 1 THEN 1 END) AS membresias_renovaciones
            FROM local_transaction AS t1
            WHERE t1.TransationDate >= ?
            AND t1.TransationDate <  ?
            AND t1.deleted_at IS NULL
            GROUP BY CAST(t1.TransationDate AS DATE)
            ORDER BY fecha DESC
        ", [$startDate, $endDate])[0] ?? (object)['total_ingresos' => 0, 'total_ordenes' => 0, 'ticket_promedio' => 0, 'lavados_comprados' => 0, 'lavados_membresia' => 0, 'lavados_otros' => 0, 'ingresos_lavados' => 0, 'ingresos_membresia' => 0, 'ingresos_renovacion' => 0, 'membresias_nuevas' => 0, 'membresias_renovaciones' => 0];
        // Datos del día anterior
        $yesterday = DB::select("
           SELECT
                CAST(t1.TransationDate AS DATE) AS fecha,
                COUNT(*) AS total_ordenes,
                SUM(t1.Total) AS total_ingresos,
                COALESCE(SUM(t1.Total) / NULLIF(COUNT(*), 0), 0) as ticket_promedio,
                COUNT(CASE WHEN t1.TransactionType = 2 AND t1.Total > 0 THEN 1 END) AS lavados_comprados,
                COUNT(CASE WHEN t1.TransactionType = 2 AND t1.Total = 0 THEN 1 END) AS lavados_membresia,
                COUNT(CASE WHEN t1.TransactionType IN (0,1) THEN 1 END) AS lavados_otros,
                COALESCE(SUM(CASE WHEN t1.TransactionType = 2 THEN t1.Total END), 0) AS ingresos_lavados,
                COALESCE(SUM(CASE WHEN t1.TransactionType = 0 THEN t1.Total END), 0) AS ingresos_membresia,
                COALESCE(SUM(CASE WHEN t1.TransactionType = 1 THEN t1.Total END), 0) AS ingresos_renovacion,
                COUNT(CASE WHEN t1.TransactionType = 0 THEN 1 END) AS membresias_nuevas,
                COUNT(CASE WHEN t1.TransactionType = 1 THEN 1 END) AS membresias_renovaciones
            FROM local_transaction AS t1
            WHERE t1.TransationDate >= ?
            AND t1.TransationDate <  ?
            AND t1.deleted_at IS NULL
            GROUP BY CAST(t1.TransationDate AS DATE)
            ORDER BY fecha DESC
        ", [$yesterdayStart, $yesterdayEnd])[0] ?? (object)['total_ingresos' => 0, 'total_ordenes' => 0, 'ticket_promedio' => 0, 'lavados_comprados' => 0, 'lavados_membresia' => 0, 'lavados_otros' => 0, 'ingresos_lavados' => 0, 'ingresos_membresia' => 0, 'ingresos_renovacion' => 0, 'membresias_nuevas' => 0, 'membresias_renovaciones' => 0];

        // Membresías nuevas del día
        $membresiasHoy = DB::select("
            SELECT
                SUM(CASE WHEN t1.TransactionType = 0 THEN 1 ELSE 0 END) AS `total`
            FROM local_transaction AS t1
            WHERE t1.TransationDate between ? AND  ?
            AND t1.deleted_at IS NULL
            GROUP BY CAST(t1.TransationDate AS DATE)
        ",  [$startDate, $endDate])[0]->total ?? 0;

        $membresiasAyer = DB::select("
            SELECT
                SUM(CASE WHEN t1.TransactionType = 0 THEN 1 ELSE 0 END) AS `total`
            FROM local_transaction AS t1
            WHERE t1.TransationDate between ? AND  ?
            AND t1.deleted_at IS NULL
            GROUP BY CAST(t1.TransationDate AS DATE)
        ", [$yesterdayStart, $yesterdayEnd])[0]->total ?? 0;

       

        return [
            'total_ingresos' => floatval($today->total_ingresos),
            'total_ordenes' => intval($today->total_ordenes),
            'total_membresias' => intval($membresiasHoy),
            'ticket_promedio' => floatval($today->ticket_promedio),
            'lavados_comprados' => intval($today->lavados_comprados),
            'lavados_membresia' => intval($today->lavados_membresia),
            'lavados_otros' => intval($today->lavados_otros),
            'ingresos_lavados' => floatval($today->ingresos_lavados),
            'ingresos_membresia' => floatval($today->ingresos_membresia),
            'ingresos_renovacion' => floatval($today->ingresos_renovacion),
            'membresias_nuevas' => intval($today->membresias_nuevas),
            'membresias_renovaciones' => intval($today->membresias_renovaciones),
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
            AND t1.deleted_at IS NULL
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
            AND t1.deleted_at IS NULL
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
            AND t1.deleted_at IS NULL
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
            t1.Atm AS cajero,
            COUNT(*) AS total_ordenes,
            SUM(t1.Total) AS total,
            SUM(CASE WHEN t1.PaymentType = 0 THEN t1.Total ELSE 0 END) AS efectivo,
            SUM(CASE WHEN t1.PaymentType IN (1,2) THEN t1.Total ELSE 0 END) AS tarjeta,
            COUNT(CASE WHEN t1.TransactionType = 2 AND t1.Total > 0 THEN 1 END) AS lavados_paquete,
            COUNT(CASE WHEN t1.TransactionType = 2 AND t1.Total = 0 AND t1.PaymentType != 3 THEN 1 END) AS lavados_membresia,
            COUNT(CASE WHEN t1.TransactionType = 2 AND t1.PaymentType = 3 THEN 1 END) AS garantia,
            COUNT(CASE WHEN t1.TransactionType = 0 THEN 1 END) AS compras_membresia,
            COUNT(CASE WHEN t1.TransactionType = 1 THEN 1 END) AS renovaciones
        FROM local_transaction t1
        WHERE t1.TransationDate BETWEEN ? AND ?
        AND t1.deleted_at IS NULL
        GROUP BY t1.Atm
        HAVING t1.Atm IS NOT NULL
        ORDER BY total DESC
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
            AND t1.deleted_at IS NULL
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

    private function getServiciosDelDia($startDate, $endDate)
    {
        return DB::select("
            SELECT
                CASE
                    WHEN TransactionType = 0 AND COALESCE(Package, Membership) IN ('61344bab37a5f00383106c88','612abcd1c4ce4c141237a356') THEN 'Compra Membresía Deluxe'
                    WHEN TransactionType = 0 AND COALESCE(Package, Membership) IN ('61344ae637a5f00383106c7a','612f057787e473107fda56aa') THEN 'Compra Membresía Express'
                    WHEN TransactionType = 0 AND COALESCE(Package, Membership) IN ('61344b9137a5f00383106c84','612f1c4f30b90803837e7969') THEN 'Compra Membresía Ultra'
                    WHEN TransactionType = 0 AND COALESCE(Package, Membership) IN ('61344b5937a5f00383106c80','612f067387e473107fda56b0') THEN 'Compra Membresía Básico'
                    WHEN TransactionType = 1 AND COALESCE(Package, Membership) IN ('61344bab37a5f00383106c88','612abcd1c4ce4c141237a356') THEN 'Renovación Membresía Deluxe'
                    WHEN TransactionType = 1 AND COALESCE(Package, Membership) IN ('61344ae637a5f00383106c7a','612f057787e473107fda56aa') THEN 'Renovación Membresía Express'
                    WHEN TransactionType = 1 AND COALESCE(Package, Membership) IN ('61344b9137a5f00383106c84','612f1c4f30b90803837e7969') THEN 'Renovación Membresía Ultra'
                    WHEN TransactionType = 1 AND COALESCE(Package, Membership) IN ('61344b5937a5f00383106c80','612f067387e473107fda56b0') THEN 'Renovación Membresía Básico'
                    WHEN TransactionType = 2 AND Total > 0 AND COALESCE(Package, Membership) IN ('61344bab37a5f00383106c88','612abcd1c4ce4c141237a356') THEN 'Lavado Deluxe'
                    WHEN TransactionType = 2 AND Total > 0 AND COALESCE(Package, Membership) IN ('61344ae637a5f00383106c7a','612f057787e473107fda56aa') THEN 'Lavado Express'
                    WHEN TransactionType = 2 AND Total > 0 AND COALESCE(Package, Membership) IN ('61344b9137a5f00383106c84','612f1c4f30b90803837e7969') THEN 'Lavado Ultra'
                    WHEN TransactionType = 2 AND Total > 0 AND COALESCE(Package, Membership) IN ('61344b5937a5f00383106c80','612f067387e473107fda56b0') THEN 'Lavado Básico'
                    WHEN TransactionType = 2 AND PaymentType = 3 THEN 'Cortesía'
                    WHEN TransactionType = 2 AND Total = 0 AND PaymentType != 3 AND COALESCE(Package, Membership) IN ('61344bab37a5f00383106c88','612abcd1c4ce4c141237a356') THEN 'Uso Membresía Deluxe'
                    WHEN TransactionType = 2 AND Total = 0 AND PaymentType != 3 AND COALESCE(Package, Membership) IN ('61344ae637a5f00383106c7a','612f057787e473107fda56aa') THEN 'Uso Membresía Express'
                    WHEN TransactionType = 2 AND Total = 0 AND PaymentType != 3 AND COALESCE(Package, Membership) IN ('61344b9137a5f00383106c84','612f1c4f30b90803837e7969') THEN 'Uso Membresía Ultra'
                    WHEN TransactionType = 2 AND Total = 0 AND PaymentType != 3 AND COALESCE(Package, Membership) IN ('61344b5937a5f00383106c80','612f067387e473107fda56b0') THEN 'Uso Membresía Básico'
                    WHEN TransactionType = 2 AND Total = 0 AND PaymentType != 3 THEN 'Uso Membresía'
                    ELSE 'Otro'
                END AS servicio,
                CASE
                    WHEN TransactionType = 0 AND COALESCE(Package, Membership) IN ('61344bab37a5f00383106c88','612abcd1c4ce4c141237a356') THEN 1
                    WHEN TransactionType = 0 AND COALESCE(Package, Membership) IN ('61344ae637a5f00383106c7a','612f057787e473107fda56aa') THEN 2
                    WHEN TransactionType = 0 AND COALESCE(Package, Membership) IN ('61344b9137a5f00383106c84','612f1c4f30b90803837e7969') THEN 3
                    WHEN TransactionType = 0 AND COALESCE(Package, Membership) IN ('61344b5937a5f00383106c80','612f067387e473107fda56b0') THEN 4
                    WHEN TransactionType = 1 AND COALESCE(Package, Membership) IN ('61344bab37a5f00383106c88','612abcd1c4ce4c141237a356') THEN 5
                    WHEN TransactionType = 1 AND COALESCE(Package, Membership) IN ('61344ae637a5f00383106c7a','612f057787e473107fda56aa') THEN 6
                    WHEN TransactionType = 1 AND COALESCE(Package, Membership) IN ('61344b9137a5f00383106c84','612f1c4f30b90803837e7969') THEN 7
                    WHEN TransactionType = 1 AND COALESCE(Package, Membership) IN ('61344b5937a5f00383106c80','612f067387e473107fda56b0') THEN 8
                    WHEN TransactionType = 2 AND Total > 0 AND COALESCE(Package, Membership) IN ('61344bab37a5f00383106c88','612abcd1c4ce4c141237a356') THEN 9
                    WHEN TransactionType = 2 AND Total > 0 AND COALESCE(Package, Membership) IN ('61344ae637a5f00383106c7a','612f057787e473107fda56aa') THEN 10
                    WHEN TransactionType = 2 AND Total > 0 AND COALESCE(Package, Membership) IN ('61344b9137a5f00383106c84','612f1c4f30b90803837e7969') THEN 11
                    WHEN TransactionType = 2 AND Total > 0 AND COALESCE(Package, Membership) IN ('61344b5937a5f00383106c80','612f067387e473107fda56b0') THEN 12
                    WHEN TransactionType = 2 AND PaymentType = 3 THEN 13
                    WHEN TransactionType = 2 AND Total = 0 AND PaymentType != 3 AND COALESCE(Package, Membership) IN ('61344bab37a5f00383106c88','612abcd1c4ce4c141237a356') THEN 14
                    WHEN TransactionType = 2 AND Total = 0 AND PaymentType != 3 AND COALESCE(Package, Membership) IN ('61344ae637a5f00383106c7a','612f057787e473107fda56aa') THEN 15
                    WHEN TransactionType = 2 AND Total = 0 AND PaymentType != 3 AND COALESCE(Package, Membership) IN ('61344b9137a5f00383106c84','612f1c4f30b90803837e7969') THEN 16
                    WHEN TransactionType = 2 AND Total = 0 AND PaymentType != 3 AND COALESCE(Package, Membership) IN ('61344b5937a5f00383106c80','612f067387e473107fda56b0') THEN 17
                    WHEN TransactionType = 2 AND Total = 0 AND PaymentType != 3 THEN 18
                    ELSE 99
                END AS sort_order,
                COUNT(*) AS pagos,
                SUM(CASE WHEN PaymentType = 0 THEN Total ELSE 0 END) AS efectivo,
                SUM(CASE WHEN PaymentType IN (1,2) THEN Total ELSE 0 END) AS tarjeta,
                SUM(Total) AS total
            FROM local_transaction
            WHERE TransationDate BETWEEN ? AND ?
              AND deleted_at IS NULL
            GROUP BY servicio, sort_order
            ORDER BY sort_order
        ", [$startDate, $endDate]);
    }

    public function duplicateMemberships()
    {
        $rows = DB::select("
            SELECT
                cm.client_id,
                CONCAT(COALESCE(c.first_name,''), ' ', COALESCE(c.last_name,'')) AS cliente,
                c.email,
                c.phone,
                COUNT(*) AS total_vigentes,
                GROUP_CONCAT(
                    CONCAT(
                        CASE cm.membership_id
                            WHEN '612f057787e473107fda56aa' THEN 'Express'
                            WHEN '61344ae637a5f00383106c7a' THEN 'Express'
                            WHEN '612f067387e473107fda56b0' THEN 'Básico'
                            WHEN '61344b5937a5f00383106c80' THEN 'Básico'
                            WHEN '612f1c4f30b90803837e7969' THEN 'Ultra'
                            WHEN '61344b9137a5f00383106c84' THEN 'Ultra'
                            WHEN '61344bab37a5f00383106c88' THEN 'Delux'
                            WHEN '612abcd1c4ce4c141237a356' THEN 'Delux'
                            ELSE 'N/A'
                        END,
                        ' (',
                        DATE(cm.start_date), ' → ', DATE(cm.end_date),
                        ')'
                    )
                    ORDER BY cm.start_date
                    SEPARATOR ' | '
                ) AS membresias
            FROM client_membership cm
            JOIN clients c ON c._id = cm.client_id
            WHERE cm.end_date >= NOW()
            GROUP BY cm.client_id, c.first_name, c.last_name, c.email, c.phone
            HAVING COUNT(*) > 1
            ORDER BY cliente ASC
        ");

        return response()->json($rows);
    }

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
            // Misma lógica que indicadores/clientes/table:
            // parte de la tabla clients, hace LEFT JOIN con la membresía más reciente
            // por cliente (MAX start_date), y cuenta solo las vigentes (end_date >= NOW())
            $membershipsByPackage = DB::select("
                SELECT
                    CASE cm.membership_id
                        WHEN '612f057787e473107fda56aa' THEN 'Express'
                        WHEN '61344ae637a5f00383106c7a' THEN 'Express'
                        WHEN '612f067387e473107fda56b0' THEN 'Basico'
                        WHEN '61344b5937a5f00383106c80' THEN 'Basico'
                        WHEN '612f1c4f30b90803837e7969' THEN 'Ultra'
                        WHEN '61344b9137a5f00383106c84' THEN 'Ultra'
                        WHEN '61344bab37a5f00383106c88' THEN 'Delux'
                        WHEN '612abcd1c4ce4c141237a356' THEN 'Delux'
                        ELSE 'NA'
                    END AS package_name,
                    COUNT(*) AS count
                FROM clients c
                LEFT JOIN (
                    SELECT cm.*
                    FROM client_membership cm
                    INNER JOIN (
                        SELECT client_id, MAX(start_date) AS max_start
                        FROM client_membership
                        WHERE deleted_at IS NULL
                        GROUP BY client_id
                    ) latest ON cm.client_id = latest.client_id
                             AND cm.start_date = latest.max_start
                    WHERE cm.deleted_at IS NULL
                ) cm ON cm.client_id = c._id
                WHERE c.deleted_at IS NULL
                  AND cm.end_date >= NOW()
                GROUP BY package_name
            ");

            $packages = ['express' => 0, 'basico' => 0, 'ultra' => 0, 'delux' => 0];
            $total = 0;

            foreach ($membershipsByPackage as $row) {
                $key = strtolower($row->package_name);
                if (isset($packages[$key])) {
                    $packages[$key] += (int)$row->count;
                }
                if ($key !== 'na') {
                    $total += (int)$row->count;
                }
            }

            return response()->json([
                'total'     => $total,
                'express'   => $packages['express'],
                'basico'    => $packages['basico'],
                'ultra'     => $packages['ultra'],
                'delux'     => $packages['delux'],
                'timestamp' => now()->format('Y-m-d H:i:s'),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'Error al obtener membresías activas',
                'message' => $e->getMessage()
            ], 500);
        }
    }

}

