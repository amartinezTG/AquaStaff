<?php

namespace App\Http\Controllers;

use App\Models\Membership;
use App\Models\ClientMembership;
use App\Models\GeneralCatalogs;
use App\Models\LocalTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesMembership;

class MembershipController extends Controller{

    public function  index(Request $request){

        $catalogs         = new GeneralCatalogs();
        $activePage       = 'membresias';

        $startDate        = $this->getStartDate($request->start_date);
        $endDate          = $this->getEndDate($request->end_date);

        $startDateCarbon  = Carbon::parse($startDate);
        $endDateCarbon    = Carbon::parse($endDate);

        //$memberships      = ClientMembership::whereBetween('start_date', [$startDate, $endDate])->get();
        $memberships = ClientMembership::whereBetween('start_date', [$startDate, $endDate])
            ->with('client')
            ->orderBy('created_at', 'desc') // Asumiendo que tienes una columna 'created_at'
            ->get();

        //$memberships    = ClientMembership::whereBetween('start_date', ['2023-11-01', '2024-11-13'])->get();
        $totalMemberships = $memberships->count();
        $averageDuration  = null;//$memberships->avg('duration_in_days');

        $price            = array("111,200","255,000","350,000","125,251","250,000");
        $rand_keys        = array_rand($price, 2);

        $totalValue       = $this->calculateMembershipRevenue($startDate, $endDate);
        $renewalRate      = null;//$this->calculateRenewalRate($memberships, $startDate, $endDate);
        $popularPackages  = $this->getPopularPackages($memberships, $catalogs, $startDate, $endDate);

        $DateVisual       = $catalogs->day_of_week[$startDateCarbon->dayOfWeek].', '.$startDateCarbon->format('d') .' de '. $catalogs->month[$startDateCarbon->month].' del '.$startDateCarbon->format('Y') .' '. $startDateCarbon->format('H:i:s') .' al '. $catalogs->day_of_week[$endDateCarbon->dayOfWeek].', '.$endDateCarbon->format('d') .' de '. $catalogs->month[$endDateCarbon->month].' del '.$endDateCarbon->format('Y') .' '. $startDateCarbon->format('H:i:s');

        $totalRecurrent = ClientMembership::whereBetween('created_at', [$startDate, $endDate])
        ->whereHas('client', function($q){
            $q->where('is_recurrent', '1');
        })
        ->count();

 
        $newClientsCount = DB::table('client_membership')
            ->select('client_id')
            ->selectRaw('MIN(created_at) as first_date')
            ->groupBy('client_id')
            ->havingRaw('first_date BETWEEN ? AND ?', [$startDate, $endDate])
            ->get();  // te devuelve una colección con client_id y first_date

        // si sólo quieres el número:
        $totalNew = $newClientsCount->count();


        $membershipsChart = $this->calculateMembershipRevenueAndGrowth($startDate, $endDate);


        //List

        $list = DB::table('client_membership as cm')
    // 1) subconsulta: obtener el MIN(created_at) para cada client_id
    ->joinSub(
        DB::table('client_membership')
          ->select('client_id', DB::raw('MIN(created_at) as first_date'))
          ->groupBy('client_id'),
        'first',
        function($join) {
            $join->on('cm.client_id', '=', 'first.client_id')
                 ->on('cm.created_at', '=', 'first.first_date');
        }
    )
    // 2) aplicar el rango de fechas sobre esa primera fecha
    ->whereBetween('cm.created_at', [$startDate, $endDate])
    // 3) left join para traer el cliente (si existe)
    ->leftJoin('clients as c', function($j){
        // si tu client_id en cm es VARCHAR y en clients INT, haz el cast:
        $j->on('c.client_id', '=', DB::raw('CAST(cm.client_id AS UNSIGNED)'));
    })
    // 4) seleccionar exactamente las columnas que necesitas
    ->select([
        DB::raw("DATE_FORMAT(cm.created_at, '%Y-%m-%d %H:%i:%s') AS Fecha"),
        DB::raw("COALESCE(c.full_name, 'Cliente no encontrado') AS ClienteNombre"),
        'cm.client_id as ClienteID',
        DB::raw("cm.membership_id AS Paquete"),
        'cm.facility AS Sucursal',
    ])
    ->orderBy('cm.created_at', 'desc')
    ->get();


        //return view('membresias', compact('activePage','totalMemberships', 'averageDuration', 'totalValue', 'renewalRate', 'popularPackages', 'startDate', 'endDate', 'memberships', 'DateVisual', 'membershipsChart'));

        return view('membresias.membresias', [
            'catalogs'         => $catalogs, 
            'activePage'       => $activePage, 
            'totalMemberships' => $totalMemberships, 
            'averageDuration'  => $averageDuration, 
            'totalValue'       => number_format($totalValue,2), 
            'renewalRate'      => $renewalRate, 
            'popularPackages'  => $popularPackages, 
            'startDate'        => $startDate, 
            'endDate'          => $endDate, 
            'memberships'      => $memberships, 
            'DateVisual'       => $DateVisual, 
            'membershipsChart' => $membershipsChart['newMemberships'], 
            'renewals'         => $membershipsChart['renewals'], 
            'months'           => $membershipsChart['months'],
            'totalRecurrent'   => $totalRecurrent,
            'totalNew'         => $totalNew,
            'list'             => $list,
            'catalogs'         => $catalogs
        ]);

    }
    public function membresia_cajero()
    {
        $activePage = 'membresia_cajero';
         return view('membresias.cajero', compact('activePage'));
    }

    public function membresias_cajero_table(Request $request)
    {
        $from  = $request->input('fecha_inicio');
        $until = $request->input('fecha_final');

        $rows = LocalTransaction::membresiasCajero($from, $until);
        $data = [];

        if ($rows) {
            foreach ($rows as $row) {
                // Determinar qué paquete usar según el tipo de transacción
                $paquete = '';
                if ($row->tipo_transaccion === 'Renovacion') {
                    // Para renovación, usar Membership (package_name2)
                    $paquete = $this->getPackageName($row->Membership);
                } else {
                    // Para compra, usar Package (package_name)
                    $paquete = $this->getPackageName($row->Package);
                }

                $data[] = [
                    '_id'               => $row->_id,
                    'fecha'             => $row->fecha,
                    'hora'              => $row->hora,
                    'tipo_transaccion'  => $row->tipo_transaccion,
                    'tipo_pago'         => $row->tipo_pago,
                    'paquete'           => $paquete,
                    'total'             => $row->Total,
                    'atm'               => $row->Atm ?? 'N/A'
                ];
            }
            return response()->json(["data" => $data]);
        }

        return response()->json(["data" => []]);
    }

    /**
     * Mapea el ID del paquete a su nombre
     */
    private function getPackageName($packageId)
    {
        $packages = [
            '612f057787e473107fda56aa' => 'Express',
            '61344ae637a5f00383106c7a' => 'Express',
            '612f067387e473107fda56b0' => 'Básico',
            '61344b5937a5f00383106c80' => 'Básico',
            '612f1c4f30b90803837e7969' => 'Ultra',
            '61344b9137a5f00383106c84' => 'Ultra',
            '61344bab37a5f00383106c88' => 'Delux',
            '612abcd1c4ce4c141237a356' => 'Delux',
        ];

        return $packages[$packageId] ?? 'N/A';
    }
    public function exportMembershipTraffic(Request $request){
        $startDate = $this->getStartDate($request->startDate);
        $endDate   = $this->getEndDate($request->endDate);

       // die($request->startDate.' '.$request->endDate);

        return Excel::download(new SalesMembership($startDate, $endDate), 'membresias-' . Carbon::now()->format('Y-m-d H-i-s') . '.xlsx');
    }

    private function calculateRenewalRate($initialMemberships, $startDate, $endDate) {

         // Obtener membresías renovadas (considerando un margen de gracia)
       /* $gracePeriodDays = 7; // Período de gracia en días
        $renewedMemberships = $initialMemberships
        ->filter(function ($membership) use ($startDate, $endDate, $gracePeriodDays) {
            return $membership->has('nextMembership', function ($query) use ($startDate, $endDate, $gracePeriodDays) {
                $model = $query->getModel(); // Obtener el modelo relacionado
                if ($model) {
                    return $model->where('start_date', '>=', $startDate)
                                     ->where('start_date', '<=', $endDate->copy()->addDays($gracePeriodDays))
                                     ->where('client_id', '=', $model->client_id); 
                }
                return $query; 
            });
        })
        ->count(); 

        // Calcular la tasa de renovación
        $totalInitialMemberships = $initialMemberships->count();
        $totalRenewedMemberships = $renewedMemberships->count();

        if ($totalInitialMemberships == 0) {
            return 0; // Evitar división por cero
        }

        $renewalRate = ($totalRenewedMemberships / $totalInitialMemberships) * 100;*/

        return 0;//$renewalRate;
    }

    public function calculateMembershipRevenue($startDate, $endDate){

        $memberships = ClientMembership::whereBetween('start_date', [$startDate, $endDate])
            ->get();

        $totalRevenue = 0;

        foreach ($memberships as $membership) {
            if ($membership->prosepago_id) { // Verifica si existe un ID de Prosepago
                $transaction = LocalTransaction::where('PaymentFolio', $membership->prosepago_id)
                    ->first();
                if ($transaction) {
                    $totalRevenue += $transaction->Total; 
                }
            }
        }

        return $totalRevenue;
    }

    private function getPopularPackages($memberships, $catalogs, $startDate, $endDate) {
        $popularMembershipIds = DB::select('
            SELECT membership_id, COUNT(*) AS total_registros
            FROM client_membership
            WHERE start_date >= :start_date AND start_date <= :end_date
            GROUP BY membership_id
            ORDER BY total_registros DESC
            LIMIT 5
        ', ['start_date' => $startDate, 'end_date' => $endDate]);

        $popularPackages = [];

        foreach ($popularMembershipIds as $item) {
            $membershipId = $item->membership_id;
            $count = $item->total_registros;

            if (isset($catalogs->memberships_type[$membershipId])) {
                $popularPackages[] = [
                    'package' => $catalogs->memberships_type[$membershipId],
                    'count' => $count,
                ];
            }
        }

        return $popularPackages;
    }

    public function calculateMembershipRevenueAndGrowth($startDate, $endDate){
        // Obtener membresías agrupadas por mes
        $memberships = ClientMembership::whereBetween('start_date', [$startDate, $endDate])
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->groupBy('month')
            ->get();

        // Obtener los meses como etiquetas para el gráfico
        $months = $memberships->pluck('month')->toArray();

        // Obtener los totales de membresías nuevas por mes
        $newMemberships = $memberships->pluck('total')->toArray(); 

        // Obtener renovaciones (implementación simplificada)
        // Esta lógica necesita ser refinada según tu definición de "renovación"
        $renewals = []; 
        foreach ($months as $month) {
            $renewed = ClientMembership::whereMonth('start_date', $month)
                ->whereHas('previousMembership') // Suponiendo que tienes una relación 'previousMembership' 
                ->count();
            $renewals[] = $renewed;
        }

        return [
            'newMemberships' => $newMemberships,
            'renewals' => $renewals,
            'months' => $months, 
        ];
    }

    private function getStartDate($start_date){
        if($start_date){
            return $start_date;
        }else{
            return Carbon::now()->startOfMonth()->format('Y-m-d');
        }
        
    }

    private function getEndDate($end_date){
        if($end_date){
            return $end_date;
        }else{
            return Carbon::now()->endOfMonth()->format('Y-m-d');
        }
    }

}
