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
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class CajeroController extends Controller
{
    public function __construct(GeneralCatalogs $catalogs){
        $this->catalogs = $catalogs;
    }

    public function exportCsv(Request $request){
        $startDate = $this->getStartDate($request->start_date);
        $endDate   = $this->getEndDate($request->end_date);

        return Excel::download(new UsersExport($startDate, $endDate), 'indicadores-' . Carbon::now()->format('Y-m-d H-i-s') . '.xlsx');
    }

    public function exportSalesTraffic(Request $request){
        $startDate = $this->getStartDate($request->startDate);
        $endDate   = $this->getEndDate($request->endDate);

       // die($request->startDate.' '.$request->endDate);

        return Excel::download(new SalesTraffic($startDate, $endDate), 'ventas_trafico-' . Carbon::now()->format('Y-m-d H-i-s') . '.xlsx');
    }

    public function exportTransactionsList(Request $request, $startDate, $endDate)
    {
        $catalogs = new GeneralCatalogs();

        // 1) Construimos el sub‐query que agrupa por _id y calcula el total por transacción
        $sub = DB::table('local_transaction')
            ->select([
                '_id',
                DB::raw("CASE WHEN LENGTH(_id) = 36 THEN 'CARRERA' ELSE 'INTERLOGIC' END as proveedor"),
                DB::raw('SUM(Total) as Total'),
            ])
            ->whereBetween('TransationDate', [$startDate, $endDate])
            ->whereIn('PaymentType', [0,1,2,3])
            ->whereIn('TransactionType', [0,1,2])
            ->groupBy('_id');

        // 2) Me quedo sólo con INTERLOGIC y vuelvo a unir con la tabla original
        $totalTransactionsList = DB::table( DB::raw("({$sub->toSql()}) as ag") )
            ->mergeBindings($sub)
            ->where('ag.proveedor', 'INTERLOGIC')
            ->join('local_transaction as lt', 'lt._id', '=', 'ag._id')
            ->select([
                'lt.TransationDate',
                'lt.created_at',
                'lt.TransactionType',
                'lt.PaymentType',
                'lt.Membership',
                'lt.Package',
                'lt.Atm',
                'lt.PaymentFolio',
                'lt._id as Folio',
                'ag.Total'
            ])
            ->whereBetween('lt.TransationDate', [$startDate, $endDate])
            ->orderBy('lt.created_at','desc')
            ->get();

        return Excel::download(new TransactionsList($totalTransactionsList, $catalogs, $startDate, $endDate), 'listado-transacciones-' . Carbon::now()->format('Y-m-d H-i-s') . '.xlsx');

    }

    public function index(Request $request){

        $catalogs   = new GeneralCatalogs();
        $activePage = 'cajero';

        // Obtener las fechas de inicio y fin
        /*$startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate   = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));*/

        // Obtener las fechas de inicio y fin
        $startDate = $this->getStartDate($request->start_date);
        $endDate   = $this->getEndDate($request->end_date);


        $startDateCarbon    = Carbon::parse($startDate);
        $endDateCarbon      = Carbon::parse($endDate);

        $startDateFormatted = $startDateCarbon->format('Y-m-d H:i:s');
        $endDateFormatted   = $endDateCarbon->format('Y-m-d H:i:s');


        /*######
        1. Número total vehículos lavados
        #######*/
        $groupedSummary = Orders::whereBetween('created_at', [$startDate, $endDate])
                //->whereIn('OrderType', [1, 2])
                ->selectRaw('COUNT(*) as total')
                ->first();

        /*######
        2. Valor total de ventas
        #######*/
        $totalSales2            = LocalTransaction::whereBetween('TransationDate', [$startDate, $endDate])->sum('Total');

        // 1) Sub‐query “Unicas”
        $sub = DB::table('local_transaction')
            ->select([
                '_id',
                DB::raw("CASE WHEN LENGTH(_id) = 36 THEN 'CARRERA' ELSE 'INTERLOGIC' END as proveedor"),
                DB::raw('SUM(Total) as tot'),
            ])
            ->whereBetween('TransationDate', [$startDate, $endDate])
            ->whereIn('PaymentType', [0,1,2,3])
            ->whereIn('TransactionType', [0,1,2])  // <- Usamos aquí el campo correcto
            ->groupBy('_id');

        // 2) Usamos fromSub para convertirlo en la tabla “Unicas”
        $totalSales = DB::query()
            ->fromSub($sub, 'Unicas')
            ->select([
                'proveedor',
                DB::raw('COUNT(*) as trans_unicas'),
                DB::raw("CONCAT('$', FORMAT(SUM(tot), 0)) as ingreso_unico"),
                DB::raw('SUM(CASE WHEN tot > 0 THEN 1 ELSE 0 END) as registros_mayor_zero'),
            ])
            ->groupBy('proveedor')
            ->get()
            ->keyBy('proveedor');



        /*######
        3. Valor promedio por transacción
        #######*/
        $totalTransactions     = LocalTransaction::whereBetween('TransationDate', [$startDate, $endDate])->count();
        
        $averageSale           = $totalTransactions > 0 ? $totalSales2 / $totalTransactions : 0;


        /*######
        4. Tipo de pago más utilizado
        #######*/
        $mostUsedPaymentType= LocalTransaction::whereBetween('TransationDate', [$startDateFormatted, $endDateFormatted])->groupBy('PaymentType')->selectRaw('PaymentType, count(*) as total')->orderByDesc('total')->first();
        if (is_null($mostUsedPaymentType)) {

            $mostUsedPaymentType = (object) [
                'PaymentType' => 0, 
                'total'       => 0
            ];
        }

        /*######
        5. Lavados por Membresía
        #######*/
            // total membership washes (used for the “Lavados por Membresía” big tile)
            $totalMembershipUses = Orders::whereBetween('OrderDate', [$startDate, $endDate])
                ->where('OrderType', 1)        // only MembershipUse
                ->count();

            $packageType = [
                '612f057787e473107fda56aa' => 'Express',
                '612f067387e473107fda56b0' => 'Básico',
                '612f1c4f30b90803837e7969' => 'Ultra',
                '61344bab37a5f00383106c88' => 'Delux',
            ];

            // 2) Obtenemos solo las keys para usarlas en el whereIn:
            $allowed     = array_keys($packageType);



            // breakdown by membership package
            $packageData = Orders::query()
              ->where('OrderType', 1)
              ->whereBetween('orders.created_at', [$startDate, $endDate]) // <-- prefijo aquí
              ->leftJoin('packages', 'orders.package_id', '=', 'packages._id')
              ->groupBy('packages._id','packages.name')
              ->select([
                DB::raw("COALESCE(packages.name,'Sin paquete') AS name"),
                DB::raw('COUNT(*) AS total_lavados'),
              ])
              ->orderByDesc('total_lavados')
              ->get();

         /*######
        6. Lavados sin Membresia
        #######*/

           
            // breakdown by membership package
            $package1timeData = Orders::query()
              ->where('OrderType', 2)
              ->whereBetween('orders.created_at', [$startDate, $endDate]) // <-- prefijo aquí
              ->leftJoin('packages', 'orders.package_id', '=', 'packages._id')
              ->groupBy('packages._id','packages.name')
              ->select([
                DB::raw("COALESCE(packages.name,'Sin paquete') AS name"),
                DB::raw('COUNT(*) AS total_lavados'),
              ])
              ->orderByDesc('total_lavados')
              ->get();








        $DateVisual         = $catalogs->day_of_week[$startDateCarbon->dayOfWeek].', '.$startDateCarbon->format('d') .' de '. $catalogs->month[$startDateCarbon->month].' del '.$startDateCarbon->format('Y') .' '. $startDateCarbon->format('H:i:s') .' al '. $catalogs->day_of_week[$endDateCarbon->dayOfWeek].', '.$endDateCarbon->format('d') .' de '. $catalogs->month[$endDateCarbon->month].' del '.$endDateCarbon->format('Y') .' '. $endDateCarbon->format('H:i:s');
        

        $startDate          = $startDateFormatted;
        $endDate            = $endDateFormatted;
        //die($startDateCarbon.' '. $endDateCarbon);

        
        $totalInvoices         = LocalTransaction::whereBetween('TransationDate', [$startDateFormatted, $endDate])->whereNotNull('fiscal_invoice')->count();

        $ordersByPeriod = LocalTransaction::whereBetween('TransationDate', [$startDate, $endDate])
            ->selectRaw('DATE_FORMAT(TransationDate, "%H") as hour, COUNT(*) as total_orders')
            ->groupBy('hour')
            ->get();

        $chartData = [
            'labels' => [],
            'datasets' => [
                [
                    'name' => 'Transacciones',
                    'data' => []
                ]
            ]
        ];

        foreach ($ordersByPeriod as $order) {
            $chartData['labels'][] = $order->hour . ':00'; // Agregar ":00" para indicar la hora
            $chartData['datasets'][0]['data'][] = $order->total_orders;
        }


        $ordersByHour = LocalTransaction::whereBetween('TransationDate', [$startDate, $endDate])
            ->selectRaw('DATE_FORMAT(TransationDate, "%H") as hour, COUNT(*) as total_orders, SUM(Total) as total_sales') 
            ->groupBy('hour') 
            ->get();


        $timePeriods = [
            'Mañana 9am a 12pm' => 0,
            'Tarde 12pm a 6pm'  => 0,
            'Tarde después 6pm' => 0
        ];

        $timePeriodsData = [
            /*'Mañana 9am a 12pm' => ['total_orders' => 0, 'total_sales' => 0],
            'Tarde 12pm a 6pm'  => ['total_orders' => 0, 'total_sales' => 0],
            'Tarde después 6pm' => ['total_orders' => 0, 'total_sales' => 0],*/

            '6:00 am' => ['total_orders' => 0, 'total_sales' => 0, 'Express' => 0, 'Básico' => 0, 'Ultra' => 0, 'Deluxe' => 0],
            '7:00 am' => ['total_orders' => 0, 'total_sales' => 0, 'Express' => 0, 'Básico' => 0, 'Ultra' => 0, 'Deluxe' => 0],
            '8:00 am' => ['total_orders' => 0, 'total_sales' => 0, 'Express' => 0, 'Básico' => 0, 'Ultra' => 0, 'Deluxe' => 0],
            '9:00 am' => ['total_orders' => 0, 'total_sales' => 0, 'Express' => 0, 'Básico' => 0, 'Ultra' => 0, 'Deluxe' => 0],
            '10:00 am' => ['total_orders' => 0, 'total_sales' => 0, 'Express' => 0, 'Básico' => 0, 'Ultra' => 0, 'Deluxe' => 0],
            '11:00 am' => ['total_orders' => 0, 'total_sales' => 0, 'Express' => 0, 'Básico' => 0, 'Ultra' => 0, 'Deluxe' => 0],
            '12:00 pm' => ['total_orders' => 0, 'total_sales' => 0, 'Express' => 0, 'Básico' => 0, 'Ultra' => 0, 'Deluxe' => 0],
            '1:00 pm' => ['total_orders' => 0, 'total_sales' => 0, 'Express' => 0, 'Básico' => 0, 'Ultra' => 0, 'Deluxe' => 0],
            '2:00 pm' => ['total_orders' => 0, 'total_sales' => 0, 'Express' => 0, 'Básico' => 0, 'Ultra' => 0, 'Deluxe' => 0],
            '3:00 pm' => ['total_orders' => 0, 'total_sales' => 0, 'Express' => 0, 'Básico' => 0, 'Ultra' => 0, 'Deluxe' => 0],
            '4:00 pm' => ['total_orders' => 0, 'total_sales' => 0, 'Express' => 0, 'Básico' => 0, 'Ultra' => 0, 'Deluxe' => 0],
            '5:00 pm' => ['total_orders' => 0, 'total_sales' => 0, 'Express' => 0, 'Básico' => 0, 'Ultra' => 0, 'Deluxe' => 0],
            '6:00 pm' => ['total_orders' => 0, 'total_sales' => 0, 'Express' => 0, 'Básico' => 0, 'Ultra' => 0, 'Deluxe' => 0],
            '7:00 pm' => ['total_orders' => 0, 'total_sales' => 0, 'Express' => 0, 'Básico' => 0, 'Ultra' => 0, 'Deluxe' => 0],
            '8:00 pm' => ['total_orders' => 0, 'total_sales' => 0, 'Express' => 0, 'Básico' => 0, 'Ultra' => 0, 'Deluxe' => 0],
            '9:00 pm' => ['total_orders' => 0, 'total_sales' => 0, 'Express' => 0, 'Básico' => 0, 'Ultra' => 0, 'Deluxe' => 0],
        ];

       
        foreach ($ordersByHour as $order) {
            $hour = (int)$order->hour; // Convertir a entero

            if ($hour >= 9 && $hour < 12) {
                $timePeriods['Mañana 9am a 12pm'] += $order->total_orders;
            } elseif ($hour >= 12 && $hour < 18) {
                $timePeriods['Tarde 12pm a 6pm'] += $order->total_orders;
            } else {
                $timePeriods['Tarde después 6pm'] += $order->total_orders;   
            }


            if ($hour >= 6 && $hour < 7) {
                $timePeriodsData['6:00 am']['total_orders']  += $order->total_orders;
                $timePeriodsData['6:00 am']['total_sales']   += $order->total_sales;
            } else if ($hour >= 7 && $hour < 8) {
                $timePeriodsData['7:00 am']['total_orders']  += $order->total_orders;
                $timePeriodsData['7:00 am']['total_sales']   += $order->total_sales;
            } elseif ($hour >= 8 && $hour < 9) {
                $timePeriodsData['8:00 am']['total_orders']  += $order->total_orders;
                $timePeriodsData['8:00 am']['total_sales']   += $order->total_sales;

            } elseif ($hour >= 9 && $hour < 10) {
                $timePeriodsData['9:00 am']['total_orders']  += $order->total_orders;
                $timePeriodsData['9:00 am']['total_sales']   += $order->total_sales;

            } elseif ($hour >= 10 && $hour < 11) {
                $timePeriodsData['10:00 am']['total_orders'] += $order->total_orders;
                $timePeriodsData['10:00 am']['total_sales']  += $order->total_sales;

            } elseif ($hour >= 11 && $hour < 12) {
                $timePeriodsData['11:00 am']['total_orders'] += $order->total_orders;
                $timePeriodsData['11:00 am']['total_sales']  += $order->total_sales;
            } elseif ($hour >= 12 && $hour < 13) {
                $timePeriodsData['12:00 pm']['total_orders'] += $order->total_orders;
                $timePeriodsData['12:00 pm']['total_sales']  += $order->total_sales;
            } elseif ($hour >= 13 && $hour < 14) {
                $timePeriodsData['1:00 pm']['total_orders']  += $order->total_orders;
                $timePeriodsData['1:00 pm']['total_sales']   += $order->total_sales;
            } elseif ($hour >= 14 && $hour < 15) {
                $timePeriodsData['2:00 pm']['total_orders']  += $order->total_orders;
                $timePeriodsData['2:00 pm']['total_sales']   += $order->total_sales;
            } elseif ($hour >= 15 && $hour < 16) {
                $timePeriodsData['3:00 pm']['total_orders']  += $order->total_orders;
                $timePeriodsData['3:00 pm']['total_sales']   += $order->total_sales;
            } elseif ($hour >= 16 && $hour < 17) {
                $timePeriodsData['4:00 pm']['total_orders']  += $order->total_orders;
                $timePeriodsData['4:00 pm']['total_sales']   += $order->total_sales;
            } elseif ($hour >= 17 && $hour < 18) {
                $timePeriodsData['5:00 pm']['total_orders']  += $order->total_orders;
                $timePeriodsData['5:00 pm']['total_sales']   += $order->total_sales;
            } elseif ($hour >= 18 && $hour < 19) {
                $timePeriodsData['6:00 pm']['total_orders']  += $order->total_orders;
                $timePeriodsData['6:00 pm']['total_sales']   += $order->total_sales;
            } elseif ($hour >= 19 && $hour < 20) {
                $timePeriodsData['7:00 pm']['total_orders']  += $order->total_orders;
                $timePeriodsData['7:00 pm']['total_sales']   += $order->total_sales;
            } elseif ($hour >= 20 && $hour < 21) {
                $timePeriodsData['8:00 pm']['total_orders']  += $order->total_orders;
                $timePeriodsData['8:00 pm']['total_sales']   += $order->total_sales;
            } else {
                $timePeriodsData['9:00 pm']['total_orders']  += $order->total_orders;
                $timePeriodsData['9:00 pm']['total_sales']   += $order->total_sales;

            }
        }

        /*########
            Tráfico de ventas (promedio por hora)
        ########*/

        /*
        $ordersData = Orders::whereBetween('created_at', [$startDate, $endDate])
            //->whereIn('OrderType', [1, 2])
            ->selectRaw('DATE_FORMAT(created_at, "%H") as hour, package_id, COUNT(*) as total_orders')
            ->groupBy('hour', 'package_id')
            ->get()
            ->groupBy('hour');

        $salesData = LocalTransaction::whereBetween('TransationDate', [$startDate, $endDate])
            ->selectRaw('DATE_FORMAT(TransationDate, "%H") as hour, Package, SUM(Total) as total_sales')
            ->groupBy('hour', 'Package')
            ->get()
            ->groupBy('hour');

        $packages = [
            '612f057787e473107fda56aa', // Express
            '612f067387e473107fda56b0', // Básico
            '612f1c4f30b90803837e7969', // Ultra
            '612abcd1c4ce4c141237a356', // Deluxe
            null, // Membresía u otros
        ];

        $totals = [
            '612f057787e473107fda56aa_orders' => 0,
            '612f057787e473107fda56aa_sales' => 0,
            '612f067387e473107fda56b0_orders' => 0,
            '612f067387e473107fda56b0_sales' => 0,
            '612f1c4f30b90803837e7969_orders' => 0,
            '612f1c4f30b90803837e7969_sales' => 0,
            '612abcd1c4ce4c141237a356_orders' => 0,
            '612abcd1c4ce4c141237a356_sales' => 0,
            'null_orders' => 0,
            'null_sales' => 0,
            'total_orders' => 0,
            'total_sales' => 0,
        ];

        $combinedData = [];

        foreach (range(6, 21) as $hour) {
            $hourKey = str_pad($hour, 2, '0', STR_PAD_LEFT); // Ej: 06, 07, ..., 21
            $combinedData[$hourKey] = [];

            foreach ($packages as $pkg) {
                $ordersHour = $ordersData[$hourKey] ?? collect();
                $salesHour = $salesData[$hourKey] ?? collect();

                $orderItem = $ordersHour->firstWhere('package_id', $pkg);
                $saleItem = $salesHour->firstWhere('Package', $pkg);

                $combinedData[$hourKey][$pkg ?? 'null'] = [
                    'orders' => $orderItem->total_orders ?? 0,
                    'sales' => $saleItem->total_sales ?? 0,
                ];
            }
        }   

        foreach ($combinedData as $hourData) {
            foreach ($hourData as $packageId => $metrics) {
                $totals["{$packageId}_orders"] += $metrics['orders'] ?? 0;
                $totals["{$packageId}_sales"] += $metrics['sales'] ?? 0;
                $totals['total_orders'] += $metrics['orders'] ?? 0;
                $totals['total_sales'] += $metrics['sales'] ?? 0;
            }
        }*/



        // 2) Traigo los pedidos por hora y paquete
        $ordersData = Orders::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE_FORMAT(created_at, "%H") as hour, package_id, COUNT(*) as total_orders')
            ->groupBy('hour', 'package_id')
            ->get()
            ->groupBy('hour');

        // 3) Traigo las ventas únicas por proveedor (_id) y monto
        $sub = DB::table('local_transaction')
            ->select([
                '_id',
                DB::raw("CASE WHEN LENGTH(_id) = 36 THEN 'CARRERA' ELSE 'INTERLOGIC' END as proveedor"),
                DB::raw('SUM(Total) as tot'),
            ])
            ->whereBetween('TransationDate', [$startDate, $endDate])
            ->whereIn('PaymentType', [0,1,2,3])
            ->whereIn('TransactionType', [0,1,2])
            ->groupBy('_id');

        // 3bis) Ahora agrupo sólo los de INTERLOGIC por hora y paquete
        $salesData = DB::table(DB::raw("({$sub->toSql()}) as uniques"))
            ->mergeBindings($sub)                        // importa los bindings de fechas/tipos
            ->where('uniques.proveedor', 'INTERLOGIC')   // <— filtrar sólo INTERLOGIC
            ->join('local_transaction as lt', 'lt._id', '=', 'uniques._id')
            ->selectRaw("
                DATE_FORMAT(lt.TransationDate, '%H') as hour,
                lt.Package         as package_id,
                SUM(uniques.tot)   as total_sales
            ")
            ->whereBetween('lt.TransationDate', [$startDate, $endDate])
            ->groupBy('hour', 'package_id')
            ->get()
            ->groupBy('hour');

        // 4) Defino los paquetes en el orden en que los muestro en la vista
        $packages = [
            '612f057787e473107fda56aa', // Express
            '612f067387e473107fda56b0', // Básico
            '612f1c4f30b90803837e7969', // Ultra
            '612abcd1c4ce4c141237a356', // Deluxe
            null,                        // Membresía u otros
        ];

        // 5) Inicializo totales
        $totals = [
            '612f057787e473107fda56aa_orders' => 0,
            '612f057787e473107fda56aa_sales'  => 0,
            '612f067387e473107fda56b0_orders' => 0,
            '612f067387e473107fda56b0_sales'  => 0,
            '612f1c4f30b90803837e7969_orders' => 0,
            '612f1c4f30b90803837e7969_sales'  => 0,
            '612abcd1c4ce4c141237a356_orders' => 0,
            '612abcd1c4ce4c141237a356_sales'  => 0,
            'null_orders' => 0,
            'null_sales'  => 0,
            'total_orders' => 0,
            'total_sales'  => 0,
        ];

        // 6) Construyo la matriz combinada por hora
        $combinedData = [];
        foreach (range(6, 21) as $h) {
            $hourKey = str_pad($h, 2, '0', STR_PAD_LEFT); // "06", "07", ..., "21"
            $combinedData[$hourKey] = [];

            // Colecciones (vacías si no hay registros)
            $ordersHour = $ordersData->get($hourKey, collect());
            $salesHour  = $salesData->get($hourKey,  collect());

            foreach ($packages as $pkg) {
                $key = $pkg ?? 'null';

                // Encuentro en cada hora el registro correspondiente al paquete
                $orderItem = $ordersHour->firstWhere('package_id', $pkg);
                $saleItem  = $salesHour->firstWhere('package_id', $pkg);

                $combinedData[$hourKey][$key] = [
                    'orders' => $orderItem->total_orders ?? 0,
                    'sales'  => $saleItem->total_sales  ?? 0,
                ];
            }
        }

        // 7) Acumulo totales
        foreach ($combinedData as $hourMetrics) {
            foreach ($hourMetrics as $pkgKey => $metrics) {
                $totals["{$pkgKey}_orders"] += $metrics['orders'];
                $totals["{$pkgKey}_sales"]  += $metrics['sales'];
                $totals['total_orders']     += $metrics['orders'];
                $totals['total_sales']      += $metrics['sales'];
            }
        }

        // 8) (Opcional) si quieres el total “real” de todas las transacciones ignorando paquetes:
        $realTotalOrders = Orders::whereBetween('created_at', [$startDate, $endDate])->count();
        
        // 1) Sub‐query “Unicas”
        $sub = DB::table('local_transaction')
            ->select([
                '_id',
                DB::raw("CASE WHEN LENGTH(_id) = 36 THEN 'CARRERA' ELSE 'INTERLOGIC' END as proveedor"),
                DB::raw('SUM(Total) as tot'),
            ])
            ->whereBetween('TransationDate', [$startDate, $endDate])
            ->whereIn('PaymentType', [0,1,2,3])
            ->whereIn('TransactionType', [0,1,2])
            ->groupBy('_id');

        // 2) Total real para CARRERA
        $carreraTotalSales = DB::table(DB::raw("({$sub->toSql()}) as uniques"))
            ->mergeBindings($sub)            // trae los bindings de fechas, filtros, etc.
            ->where('proveedor', 'CARRERA')
            ->sum('tot');

        // 3) Total real para INTERLOGIC
        $interlogicTotalSales = DB::table(DB::raw("({$sub->toSql()}) as uniques"))
            ->mergeBindings($sub)
            ->where('proveedor', 'INTERLOGIC')
            ->sum('tot');




        /*########
           / ---- Tráfico de ventas (promedio por hora)
        ########*/



        /*########
             ---- Listado de transacciones
        ########*/

          // $totalTransactionsList = LocalTransaction::whereBetween('TransationDate', [$startDate, $endDate])->where('Package', '!=', '')->get();

           //$totalTransactionsList = Orders::whereBetween('created_at', [$startDate, $endDate])
            //->whereIn('OrderType', [1, 2])
            //->get();

            // 1) Construimos el sub‐query que agrupa por _id y calcula el total por transacción
            $sub = DB::table('local_transaction')
                ->select([
                    '_id',
                    DB::raw("CASE WHEN LENGTH(_id) = 36 THEN 'CARRERA' ELSE 'INTERLOGIC' END as proveedor"),
                    DB::raw('SUM(Total) as tot'),
                ])
                ->whereBetween('TransationDate', [$startDate, $endDate])
                ->whereIn('PaymentType', [0,1,2,3])
                ->whereIn('TransactionType', [0,1,2])
                ->groupBy('_id');
            
            // 2) A partir de ahí, me quedo sólo con INTERLOGIC y vuelvo a unir con la tabla original
            $totalTransactionsList = DB::table( DB::raw("({$sub->toSql()}) as ag") )
                ->mergeBindings($sub) // para que respete los where del sub
                ->where('ag.proveedor', 'INTERLOGIC')
                ->join('local_transaction as lt', function($join){
                    $join->on('lt._id', '=', 'ag._id');
                })
                ->distinct('ag._id')
                ->select([
                    'lt.created_at',    
                    'lt.TransationDate',
                    'lt.TransactionType',
                    'lt.PaymentType',
                    'lt.Package',
                    'lt.Atm',
                    'ag.tot as Total',
                    'lt.Membership',
                    'lt.Package as package_id',
                    'lt.PaymentFolio',
                    'lt._id'
                ])
                ->whereBetween('lt.TransationDate', [$startDate, $endDate])
                ->orderBy('lt.created_at', 'desc')
                ->get();



        /*########
            /  ---- Listado de transacciones
        ########*/

           


        $chartDataPie = [
            'labels' => array_keys($timePeriods),
            'series' => array_values($timePeriods)
        ];

        $mostUsedPaymentTypes = LocalTransaction::whereBetween('TransationDate', [$startDate, $endDate])
                                ->groupBy('PaymentType')
                                ->selectRaw('PaymentType, count(*) as total')
                                ->orderByDesc('total')
                                ->get();


        // Crear un array para almacenar los datos del gráfico
        $chartUsedPayment = [
            'series' => [],
            'labels' => []
        ];

        if ($mostUsedPaymentTypes->isEmpty()) {
            // Si no hay registros, agregar un conjunto de datos predeterminado
            $chartUsedPayment['series'][] = 0;
            $chartUsedPayment['labels'][] = 'Sin datos'; 
        } else {
            foreach ($mostUsedPaymentTypes as $paymentType) {
                if (isset($paymentType->PaymentType) && isset($catalogs->folio_payment_type[$paymentType->PaymentType])) {
                    $chartUsedPayment['series'][] = $paymentType->total;
                    $chartUsedPayment['labels'][] = $catalogs->folio_payment_type[$paymentType->PaymentType];
                } else {
                    $chartUsedPayment['series'][] = 0; 
                    $chartUsedPayment['labels'][] = 'Desconocido'; 
                }
            }
        }


        $packageDataChart = LocalTransaction::whereBetween('TransationDate', [$startDate, $endDate])
                            ->whereNotNull('Package') 
                            ->where('Package', '!=', '') 
                            ->groupBy('Package')
                            ->selectRaw('Package, sum(Total) as total_sales, count(*) as total_purchases')
                            ->orderByDesc('total_sales')
                            ->get();


        $packageChart = [
            'series' => [],
            'labels' => []
        ];

        if ($packageDataChart->isEmpty()) {
            $packageChart['series'][] = 0;
            $packageChart['labels'][] = 'Sin datos'; 
        } else {
          
            foreach ($packageDataChart as $package) {

                $packageChart['series'][] = $package->total_sales; 
                $packageChart['labels'][] = $catalogs->package_type[(string)$package->Package] ?? 'Sin información';
            }
        }

        $usedCodes = UsedBusinessCodes::whereBetween('created_at', [$startDate, $endDate])
            ->pluck('Code')
            ->toArray();

        /*####
            USO DE PROMOCIONES
        ####*/
        $groupedSummaries = Orders::whereBetween('created_at', [$startDate, $endDate])
                        //->whereIn('OrderType', [1, 2])
                        ->select('OrderType', DB::raw('count(*) as total'))
                        ->groupBy('OrderType')
                        ->get();
     
        /*####
            FIN USO DE PROMOCIONES
        ####*/

            
        $sub1 = DB::table('orders')
        ->select('OrderType as type', DB::raw('count(*) as qty'))
        ->whereBetween('created_at', [$startDate, $endDate])
        ->whereIn('OrderType', [1, 2])
        ->groupBy('OrderType');

        $sub2 = DB::table('local_transaction')
            ->select(DB::raw("NULL as type"), DB::raw('count(*) as qty'))
            ->whereBetween('TransationDate', [$startDate, $endDate]);

        $combined = DB::table(DB::raw("({$sub1->toSql()} UNION ALL {$sub2->toSql()}) as t"))
            ->mergeBindings($sub1)
            ->mergeBindings($sub2)
            ->select('type', DB::raw('SUM(qty) as total'))
            ->groupBy('type')
            ->get();

   

        /*####
              Número total de transacciones
        ####*/
            $totalLocal_Transactions = LocalTransaction::whereBetween('TransationDate', [$startDate, $endDate])->count();
            $totalOrdersType1 = Orders::whereBetween('created_at', [$startDate, $endDate])
                                    ->where('OrderType', 1)
                                    ->count(); 
            $totalOrdersType2 = Orders::whereBetween('created_at', [$startDate, $endDate])
                                    ->where('OrderType', 2)
                                    ->count(); 

            $results = LocalTransaction::query()
                        ->whereBetween('TransationDate', [$startDate, $endDate])
                        ->where('Total', '>', 0)
                        ->count();

            $totalOrders12 = Orders::whereBetween('created_at', [$startDate, $endDate])
            //->whereIn('OrderType', [1, 2])
            ->count();
            
            $totalTransactions=$totalLocal_Transactions+$totalOrdersType1;
        
        /*####
            / Número total de transacciones
        ####*/

        //weather log
        // Obtener datos del clima para la fecha seleccionada (usando whereBetween)
        $weatherData = WeatherLog::whereBetween('created_at', [$startDate, $endDate])->orderBy('created_at', 'DESC')->get();


        return view('cajero.cajero', compact('activePage', 'groupedSummary', 'totalSales',  'averageSale', 'mostUsedPaymentType', 'totalMembershipUses', 'packageData', 'packageType', 'package1timeData', 'totalTransactions',  'totalInvoices', 'startDate', 'endDate', 'catalogs',  'chartData', 'chartUsedPayment', 'packageChart', 'ordersByPeriod', 'totalTransactionsList', 'chartDataPie', 'DateVisual', 'timePeriodsData', 'combined',  'groupedSummaries', 'weatherData', 'totalOrdersType2', 'combinedData', 'totals', 'realTotalOrders', 'carreraTotalSales', 'interlogicTotalSales'));

    }

    public function membershipPackages(Request $request)
    {
        $catalogs = new GeneralCatalogs();
        $activePage = 'cajero';

        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        // Obtener transacciones con información del cliente y paquete
        $transactions = LocalTransaction::whereNotNull('Membership')
            ->whereBetween('TransationDate', [$startDate, $endDate])
            ->orderBy('TransationDate', 'desc')
            ->get()
            ->map(function ($transaction) {
                $clientMembership = ClientMembership::where('prosepago_id', $transaction->PaymentFolio)->first();
                $package = null;
                $client = null;

                if ($clientMembership) {
                    $order = Orders::where('MembershipId', $clientMembership->membership_id)
                        ->where('UserId', $clientMembership->client_id)
                        ->first();

                    if ($order) {
                        $package = $order->package_id;
                        $client = Client::find($clientMembership->client_id);
                    }
                }

                $transaction->package   = $package;
                $transaction->client    = $client;
                $transaction->order     = $order;
                $transaction->client_membership = $clientMembership;
                return $transaction;
            });

        // Agrupar las transacciones por package_id
        $packageData = $transactions->groupBy('package')->map(function ($transactions, $packageId) {
            return [
                'package_id'      => $packageId,
                'total_sales'     => $transactions->sum('Total'),
                'total_purchases' => $transactions->count(),
            ];
        })->values();

        return view('cajero.membership_packages', [
            'activePage'   => $activePage,
            'packageData'  => $packageData,
            'startDate'    => $startDate,
            'endDate'      => $endDate,
            'catalogs'     => $catalogs,
            'transactions' => $transactions, // Pasar $transactions a la vista
        ]);
    }


    private function getStartDate($start_date){
       if($start_date){
        return $start_date;
       }else{
        return Carbon::now()->subDays(7)->format('Y-m-d\TH:i');
       }
        
    }

    private function getEndDate($end_date){
    if($end_date){
        return $end_date;
       }else{
        return Carbon::now()->format('Y-m-d\TH:i');
       }

        //return $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
    }

}
