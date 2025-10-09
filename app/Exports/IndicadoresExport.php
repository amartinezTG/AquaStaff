<?php

namespace App\Exports;

use App\Models\Orders;
use App\Models\LocalTransaction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class IndicadoresExport implements FromCollection, WithHeadings, WithMapping
{
    protected $startDate;
    protected $endDate;
    protected $datos;
    protected $usoMembresias;
    protected $totTrans;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = Carbon::parse($startDate)->startOfDay();
        $this->endDate   = Carbon::parse($endDate)->endOfDay();

        // Consulta principal
        $this->datos = DB::table('local_transaction as lt')
            ->join(DB::raw("(
                SELECT
                    _id,
                    CASE WHEN LENGTH(_id) = 36 THEN 'CARRERA' ELSE 'INTERLOGIC' END AS proveedor
                FROM local_transaction
                WHERE TransationDate BETWEEN '{$this->startDate}' AND '{$this->endDate}'
                    AND PaymentType IN (0,1,2,3)
                    AND TransactionType IN (0,1,2)
                GROUP BY _id
            ) as ag"), 'lt._id', '=', 'ag._id')
            ->selectRaw("
                DATE(lt.TransationDate) as fecha,
                COUNT(DISTINCT lt._id) as ids_unicos,
                SUM(CASE WHEN lt.TransactionType = 2 AND lt.Package = '612f057787e473107fda56aa' THEN 1 ELSE 0 END) as Paquete_Express,
                SUM(CASE WHEN lt.TransactionType = 2 AND lt.Package = '612f067387e473107fda56b0' THEN 1 ELSE 0 END) as Paquete_Basico,
                SUM(CASE WHEN lt.TransactionType = 2 AND lt.Package = '612f1c4f30b90803837e7969' THEN 1 ELSE 0 END) as Paquete_Ultra,
                SUM(CASE WHEN lt.TransactionType = 2 AND lt.Package = '61344bab37a5f00383106c88' THEN 1 ELSE 0 END) as Paquete_Deluxe,
                SUM(CASE WHEN lt.TransactionType = 2 AND lt.Total = 50 THEN 1 ELSE 0 END) as Promo_50,
                SUM(CASE WHEN lt.TransactionType = 2 AND lt.Total = 150 THEN 1 ELSE 0 END) as Promo_150,
                SUM(CASE WHEN lt.TransactionType = 2 AND lt.Total = 200 THEN 1 ELSE 0 END) as Promo_200,
                SUM(CASE WHEN lt.TransactionType = 0 AND lt.Package = '61344ae637a5f00383106c7a' THEN 1 ELSE 0 END) as Membresia_Express,
                SUM(CASE WHEN lt.TransactionType = 0 AND lt.Package = '61344b5937a5f00383106c80' THEN 1 ELSE 0 END) as Membresia_Basico,
                SUM(CASE WHEN lt.TransactionType = 0 AND lt.Package = '61344b9137a5f00383106c84' THEN 1 ELSE 0 END) as Membresia_Ultra,
                SUM(CASE WHEN lt.TransactionType = 0 AND lt.Package = '61344bab37a5f00383106c88' THEN 1 ELSE 0 END) as Membresia_Deluxe,
                SUM(CASE WHEN lt.TransactionType IN (0,1,2) THEN lt.Total ELSE 0 END) as Ingresos_Totales,
                SUM(CASE WHEN lt.TransactionType IN (0,1,2) THEN lt.Total / 1.08 ELSE 0 END) as Ingresos_Sin_IVA,
                AVG(CASE WHEN lt.TransactionType IN (0,1,2) THEN lt.Total ELSE NULL END) as Ticket_Promedio
            ")
            ->where('ag.proveedor', 'INTERLOGIC')
            ->whereBetween('lt.TransationDate', [$this->startDate, $this->endDate])
            ->groupBy(DB::raw('DATE(lt.TransationDate)'))
            ->get()
            ->keyBy('fecha');

        // Lavados por membresía (orders)
        $this->usoMembresias = DB::table('orders')
            ->selectRaw("
                DATE(created_at) as fecha,
                SUM(CASE WHEN MembershipId = '61344ae637a5f00383106c7a' THEN 1 ELSE 0 END) AS Uso_Express,
                SUM(CASE WHEN MembershipId = '61344b5937a5f00383106c80' THEN 1 ELSE 0 END) AS Uso_Basico,
                SUM(CASE WHEN MembershipId = '61344b9137a5f00383106c84' THEN 1 ELSE 0 END) AS Uso_Ultra,
                SUM(CASE WHEN MembershipId = '61344bab37a5f00383106c88' THEN 1 ELSE 0 END) AS Uso_Deluxe
            ")
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->where('OrderType', 1)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->get()
            ->keyBy('fecha');
    }

    public function collection(): Collection
    {
        $fechas = collect();
        $start = Carbon::parse($this->startDate);
        $end = Carbon::parse($this->endDate);

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $fechaStr = $date->toDateString();

            $d = $this->datos[$fechaStr] ?? null;
            $m = $this->usoMembresias[$fechaStr] ?? null;

            $fechas->push([
            $fechaStr,
            $d->ids_unicos ?? 0,
            $d->Paquete_Express ?? 0,
            $d->Paquete_Basico ?? 0,
            $d->Paquete_Ultra ?? 0,
            $d->Paquete_Deluxe ?? 0,
            $d->Promo_50 ?? 0,
            $d->Promo_150 ?? 0,
            $d->Promo_200 ?? 0,
            $totalPaquete = ($d->Paquete_Express ?? 0) + ($d->Paquete_Basico ?? 0) + ($d->Paquete_Ultra ?? 0) + ($d->Paquete_Deluxe ?? 0)
                + ($d->Promo_50 ?? 0) + ($d->Promo_150 ?? 0) + ($d->Promo_200 ?? 0),
            $m->Uso_Express ?? 0,
            $m->Uso_Basico ?? 0,
            $m->Uso_Ultra ?? 0,
            $m->Uso_Deluxe ?? 0,
            $totalMembresia = ($m->Uso_Express ?? 0) + ($m->Uso_Basico ?? 0) + ($m->Uso_Ultra ?? 0) + ($m->Uso_Deluxe ?? 0),
            $lavadosNoContados = max(0, ($d->ids_unicos ?? 0) - $totalPaquete - $totalMembresia),
            round(($d->Ingresos_Totales ?? 0), 2),
            round(($d->Ingresos_Sin_IVA ?? 0), 2),
            round(($d->Ticket_Promedio ?? 0), 2),
        ]);
        }

        return $fechas;
    }

    public function headings(): array
        {
            return [
                [
                    'Fecha',
                    'Vehículos',
                    'Express',
                    'Básico',
                    'Ultra',
                    'Deluxe',
                    'Promo $50',
                    'Promo $150',
                    'Promo $200',
                    'Total x Paquete',
                    'Membresía Express',
                    'Membresía Básico',
                    'Membresía Ultra',
                    'Membresía Deluxe',
                    'Total Membresía',
                    'Lavados No Contabilizados',
                    'Ingresos Totales',
                    'Ingresos sin IVA',
                    'Ticket Promedio',
                ],
            ];
        }

    public function map($row): array
    {
        return $row;
    }
}
