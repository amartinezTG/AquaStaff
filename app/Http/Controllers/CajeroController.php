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
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

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








        /*######
        7. Total lavados por tipo de paquete (compra + renovación)
        #######*/
        $packageTotals = Orders::query()
            ->whereIn('OrderType', [1, 2])
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->leftJoin('packages', 'orders.package_id', '=', 'packages._id')
            ->groupBy('packages._id', 'packages.name')
            ->select([
                DB::raw("COALESCE(packages.name, 'Sin paquete') AS name"),
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

        return view('cajero.cajero', compact('activePage', 'groupedSummary', 'totalSales',  'averageSale', 'mostUsedPaymentType', 'totalMembershipUses', 'packageData', 'packageType', 'package1timeData', 'packageTotals', 'totalTransactions',  'totalInvoices', 'startDate', 'endDate', 'catalogs',  'chartData', 'chartUsedPayment', 'packageChart', 'ordersByPeriod', 'totalTransactionsList', 'chartDataPie', 'DateVisual', 'timePeriodsData', 'combined',  'groupedSummaries', 'weatherData', 'totalOrdersType2', 'combinedData', 'totals', 'realTotalOrders', 'carreraTotalSales', 'interlogicTotalSales'));

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
    function cajero_transacciones(){
        $activePage = 'cajero_transacciones';

        return view('cajero.cajero_transacciones', compact('activePage'));
    }

    // ─── IMPORTACIÓN PROCEPAGO ────────────────────────────────────────

    public function importacionView()
    {
        $activePage = 'importacion';

        // Resumen de importaciones anteriores
        $importaciones = DB::table('procepago_pagos')
            ->select(
                'archivo_origen',
                'importado_en',
                DB::raw('MIN(fecha) as fecha_desde'),
                DB::raw('MAX(fecha) as fecha_hasta'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(monto_total) as monto_total')
            )
            ->groupBy('archivo_origen', 'importado_en')
            ->orderByDesc('importado_en')
            ->limit(10)
            ->get();

        return view('cajero.importacion', compact('activePage', 'importaciones'));
    }

    public function importarProcepago(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls|max:20480',
        ]);

        try {
            $archivo   = $request->file('archivo');
            $nombre    = $archivo->getClientOriginalName();
            $usuarioId = Auth::id();

            $spreadsheet = IOFactory::load($archivo->getRealPath());
            $ws          = $spreadsheet->getActiveSheet();

            $headerFound  = false;
            $colMap       = [];   // nombre_columna => índice 0-based
            $insertados   = 0;
            $omitidos     = 0;
            $errores      = [];
            $lote         = [];
            $loteSize     = 200;
            $filaActual   = 0;

            foreach ($ws->getRowIterator() as $row) {
                $filaActual++;
                $cells = [];
                foreach ($row->getCellIterator() as $cell) {
                    // Leer valor calculado; si es INF/NAN lo convertimos a null
                    try {
                        $v = $cell->getCalculatedValue();
                    } catch (\Throwable $e) {
                        $v = $cell->getValue();
                    }
                    if (is_float($v) && (is_infinite($v) || is_nan($v))) {
                        $v = null;
                    }
                    $cells[] = $v;
                }

                // Detectar fila de encabezado
                if (!$headerFound) {
                    if (isset($cells[0]) && strtoupper(trim((string)$cells[0])) === 'ID') {
                        $headerFound = true;
                        foreach ($cells as $idx => $colName) {
                            $colMap[strtolower(trim((string)$colName))] = $idx;
                        }
                    }
                    continue;
                }

                // Saltar filas vacías o de totales
                $idVal = $cells[0] ?? null;
                if ($idVal === null || $idVal === '' || !is_numeric($idVal)) {
                    continue;
                }

                $pagaloId     = (int) $idVal;
                $fecha        = trim((string)($cells[1] ?? ''));
                $hora         = trim((string)($cells[2] ?? ''));
                $sucursal     = trim((string)($cells[3] ?? ''));
                $claveCajero  = trim((string)($cells[4] ?? ''));
                $cajero       = trim((string)($cells[5] ?? ''));
                $numOperacion = is_numeric($cells[6] ?? null) ? (int)$cells[6] : 0;
                $referencia   = trim((string)($cells[7] ?? ''));
                $recibo       = trim((string)($cells[8] ?? ''));
                $servicio     = trim((string)($cells[9] ?? ''));
                $formaPago    = strtoupper(trim((string)($cells[10] ?? '')));
                $montoEfectivo = is_numeric($cells[11] ?? null) ? (float)$cells[11] : 0;
                $montoTarjeta  = is_numeric($cells[12] ?? null) ? (float)$cells[12] : 0;
                $ultimos4     = trim((string)($cells[13] ?? ''));
                $autorizacion = trim((string)($cells[14] ?? ''));

                // Normalizar N/A y ceros
                $referencia   = in_array($referencia,   ['N/A','0','']) ? null : $referencia;
                $recibo       = in_array($recibo,       ['N/A',''])     ? null : $recibo;
                $ultimos4     = in_array($ultimos4,     ['N/A',''])     ? null : $ultimos4;
                $autorizacion = in_array($autorizacion, ['N/A',''])     ? null : $autorizacion;

                if (!$fecha || !$hora || !$claveCajero || !$servicio) {
                    continue;
                }

                $lote[] = [
                    'pagalo_id'      => $pagaloId,
                    'num_operacion'  => $numOperacion,
                    'referencia'     => $referencia,
                    'recibo'         => $recibo,
                    'autorizacion'   => $autorizacion,
                    'ultimos_4'      => $ultimos4,
                    'fecha'          => $fecha,
                    'hora'           => $hora,
                    'sucursal'       => $sucursal ?: null,
                    'clave_cajero'   => $claveCajero,
                    'cajero'         => $cajero ?: null,
                    'servicio'       => $servicio,
                    'forma_pago'     => in_array($formaPago, ['EFECTIVO','TARJETA']) ? $formaPago : 'EFECTIVO',
                    'monto_efectivo' => $montoEfectivo,
                    'monto_tarjeta'  => $montoTarjeta,
                    'archivo_origen' => $nombre,
                    'importado_en'   => now(),
                    'importado_por'  => $usuarioId,
                ];

                // Insertar en lotes de 200
                if (count($lote) >= $loteSize) {
                    try {
                        $antes = DB::table('procepago_pagos')->count();
                        DB::table('procepago_pagos')->insertOrIgnore($lote);
                        $despues   = DB::table('procepago_pagos')->count();
                        $insertados += ($despues - $antes);
                        $omitidos   += (count($lote) - ($despues - $antes));
                    } catch (\Exception $e) {
                        $errores[] = "Lote fila ~{$filaActual}: " . $e->getMessage();
                    }
                    $lote = [];
                }
            }

            // Insertar lote restante
            if (!empty($lote)) {
                try {
                    $antes = DB::table('procepago_pagos')->count();
                    DB::table('procepago_pagos')->insertOrIgnore($lote);
                    $despues    = DB::table('procepago_pagos')->count();
                    $insertados += ($despues - $antes);
                    $omitidos   += (count($lote) - ($despues - $antes));
                } catch (\Exception $e) {
                    $errores[] = "Lote final: " . $e->getMessage();
                }
            }

            return response()->json([
                'success'    => true,
                'insertados' => $insertados,
                'omitidos'   => $omitidos,
                'errores'    => array_slice($errores, 0, 20),
                'archivo'    => $nombre,
            ]);
  
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al procesar el archivo: ' . $e->getMessage()], 500);
        }
    }
  
    public function analisisView()
    {
        $activePage = 'analisis_procepago';
        return view('cajero.analisis_procepago', compact('activePage'));
    }

    public function analisisData(Request $request)
    {
        $desde  = $request->input('fecha_inicio', now()->startOfMonth()->toDateString());
        $hasta  = $request->input('fecha_final',  now()->toDateString());
        $cajero = $request->input('cajero', '');

        // AquaAdmin: agrupar por _id
        // - whereNull('deleted_at'): excluye soft deletes
        // - MAX(Total): precio del servicio registrado en AquaAdmin — se compara vs Procepago
        //   para detectar cobros distintos al precio (ej: registrado $180, cobrado $800)
        // - MAX(TotalPayed - `Change`): lo que el cliente pagó neto (referencia informativa)
        // - MAX en vez de SUM: hay filas duplicadas exactas en algunos _id
        $ltSub = DB::table('local_transaction')
            ->select(
                '_id',
                DB::raw('DATE(MIN(TransationDate)) as fecha'),
                DB::raw('TIME(MIN(TransationDate)) as hora'),
                DB::raw('MAX(Atm) as cajero'),
                DB::raw('MAX(Total) as precio_aqua'),
                DB::raw('MAX(TotalPayed - `Change`) as cobrado_aqua')
            )
            ->whereBetween('TransationDate', [$desde.' 00:00:00', $hasta.' 23:59:59'])
            ->whereIn('Atm', ['AQUA01','AQUA02'])
            ->whereNull('deleted_at')
            ->when($cajero, fn($q) => $q->where('Atm', $cajero))
            ->groupBy('_id');

        // Procepago
        $ppBase = DB::table('procepago_pagos')
            ->whereBetween('fecha', [$desde, $hasta])
            ->when($cajero, fn($q) => $q->where('clave_cajero', $cajero));

        $lt = DB::query()->fromSub($ltSub, 'lt')->get()->keyBy('_id');
        $pp = (clone $ppBase)->get()->keyBy('num_operacion');

        $ltIds = $lt->keys();
        $ppIds = $pp->keys();

        $soloEnAqua      = [];
        $soloEnProcepago = [];
        $diferenciaMonto = [];

        // En AquaAdmin con cobro pero sin registro en Procepago
        foreach ($ltIds->diff($ppIds) as $id) {
            $row = $lt[$id];
            if ($row->precio_aqua > 0) {
                $soloEnAqua[] = [
                    'id'           => $id,
                    'fecha'        => $row->fecha,
                    'hora'         => $row->hora,
                    'cajero'       => $row->cajero,
                    'precio_aqua'  => $row->precio_aqua,
                    'cobrado_aqua' => $row->cobrado_aqua,
                    'total_pp'     => '-',
                    'diferencia'   => -$row->precio_aqua,
                ];
            }
        }

        // En Procepago con cobro pero sin registro en AquaAdmin
        foreach ($ppIds->diff($ltIds) as $id) {
            $row = $pp[$id];
            if ($row->monto_total > 0) {
                $soloEnProcepago[] = [
                    'id'           => $id,
                    'fecha'        => $row->fecha,
                    'hora'         => $row->hora,
                    'cajero'       => $row->clave_cajero,
                    'servicio'     => $row->servicio,
                    'forma_pago'   => $row->forma_pago,
                    'precio_aqua'  => '-',
                    'cobrado_aqua' => '-',
                    'total_pp'     => $row->monto_total,
                    'diferencia'   => $row->monto_total,
                ];
            }
        }

        // Existen en ambos — comparar precio registrado en Aqua vs cobrado en Procepago
        foreach ($ltIds->intersect($ppIds) as $id) {
            $rowLt = $lt[$id];
            $rowPp = $pp[$id];
            // Diferencia entre precio del servicio en Aqua y lo cobrado por Procepago
            $difPrecio   = round($rowPp->monto_total - $rowLt->precio_aqua, 2);
            // Diferencia entre lo cobrado neto en Aqua y lo cobrado en Procepago
            $difCobrado  = round($rowPp->monto_total - $rowLt->cobrado_aqua, 2);

            if (abs($difPrecio) > 0.01) {
                $diferenciaMonto[] = [
                    'id'           => $id,
                    'fecha'        => $rowLt->fecha,
                    'hora'         => $rowLt->hora,
                    'cajero'       => $rowLt->cajero,
                    'servicio'     => $rowPp->servicio,
                    'forma_pago'   => $rowPp->forma_pago,
                    'precio_aqua'  => $rowLt->precio_aqua,   // precio del servicio en AquaAdmin
                    'cobrado_aqua' => $rowLt->cobrado_aqua,  // lo que pagó el cliente en Aqua
                    'total_pp'     => $rowPp->monto_total,   // lo que cobró el TPV
                    'dif_precio'   => $difPrecio,            // diferencia precio vs TPV
                    'dif_cobrado'  => $difCobrado,           // diferencia cobrado vs TPV
                ];
            }
        }

        return response()->json([
            'resumen' => [
                'total_pp'           => round($pp->sum('monto_total'), 2),
                'total_aqua'         => round($lt->sum('precio_aqua'), 2),
                'solo_en_aqua_n'     => count($soloEnAqua),
                'solo_en_aqua_monto' => round(collect($soloEnAqua)->sum('precio_aqua'), 2),
                'solo_en_pp_n'       => count($soloEnProcepago),
                'solo_en_pp_monto'   => round(collect($soloEnProcepago)->sum('total_pp'), 2),
                'dif_monto_n'        => count($diferenciaMonto),
                'dif_monto_total'    => round(collect($diferenciaMonto)->sum('dif_precio'), 2),
            ],
            'solo_en_aqua'      => collect($soloEnAqua)->sortBy('fecha')->values(),
            'solo_en_procepago' => collect($soloEnProcepago)->sortBy('fecha')->values(),
            'diferencia_monto'  => collect($diferenciaMonto)->sortBy('fecha')->values(),
        ]);
    }


    public function importacionTable(Request $request)
    {
        $desde  = $request->input('fecha_inicio');
        $hasta  = $request->input('fecha_final');
        $cajero = $request->input('cajero');

        $q = DB::table('procepago_pagos')
            ->when($desde,  fn($q) => $q->where('fecha', '>=', $desde))
            ->when($hasta,  fn($q) => $q->where('fecha', '<=', $hasta))
            ->when($cajero, fn($q) => $q->where('clave_cajero', $cajero))
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->get();

        $data = $q->map(fn($r) => [
            'pagalo_id'     => $r->pagalo_id,
            'num_operacion' => $r->num_operacion,
            'fecha'         => $r->fecha,
            'hora'          => $r->hora,
            'clave_cajero'  => $r->clave_cajero,
            'servicio'      => $r->servicio,
            'forma_pago'    => $r->forma_pago,
            'monto_efectivo'=> $r->monto_efectivo,
            'monto_tarjeta' => $r->monto_tarjeta,
            'monto_total'   => $r->monto_total,
            'autorizacion'  => $r->autorizacion,
            'ultimos_4'     => $r->ultimos_4,
            'archivo_origen'=> $r->archivo_origen,
        ]);

        return response()->json(['data' => $data]);
    }

    // ─────────────────────────────────────────────────────────────────

    public function CajerosTable(Request $request){
        $from  = $request->input('fecha_inicio'); // opcisonal
        $until = $request->input('fecha_final');  // opcional

       $rows = LocalTransaction::pagosCajero($from, $until);
        $data  = [];
        foreach ($rows as $row) {
            $data[] = [
                '_id' => $row->_id,
                'local_transaction_id' => $row->local_transaction_id,
                'fecha' => $row->fecha,
                'hora' => $row->hora,
                'cliente' => $row->cliente,
                'package_name' => $row->package_name,
                'Atm' => $row->Atm,
                'method' => $row->method,
                'tipo_transaccion' => $row->tipo_transaccion,
                'Total' => $row->Total,
                'CadenaFacturacion' => $row->CadenaFacturacion,
                'fiscal_invoice' => $row->fiscal_invoice,
                'rfc' => $row->rfc,
                'company_name' => $row->company_name,
            ];
        }

       echo json_encode(array("data" => $data));
    }


}
