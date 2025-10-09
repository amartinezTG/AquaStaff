<?php
namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MembresiasExport implements FromCollection, WithHeadings
{
    protected $resultados;

    public function __construct($startDate, $endDate)
    {
        $paquetes = [
            '61344ae637a5f00383106c7a' => 'Express',
            '61344b5937a5f00383106c80' => 'Basico',
            '61344b9137a5f00383106c84' => 'Ultra',
            '61344bab37a5f00383106c88' => 'Delux',
        ];

        // Subconsulta para obtener el primer uso real de la membresía
        $sub = DB::table('orders')
            ->select('UserId', DB::raw('MIN(created_at) as FechaPrimerUso'))
            ->where('OrderType', 1)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('UserId');

        // Join con tabla original para traer el resto de los datos
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

        // Enriquecer resultados
        $this->resultados = $primerosUsos->map(function ($item) use ($paquetes) {
            $userId = $item->Referencia;

            $item->Nombre_Paquete = $paquetes[$item->Paquete] ?? 'Otro';

            $item->usos = DB::table('orders')
                ->where('UserId', $userId)
                ->where('OrderType', 1)
                ->count();

            $primerCobro = DB::table('client_membership as cm')
                ->join('local_transaction as lt', 'lt.PaymentFolio', '=', 'cm.prosepago_id')
                ->where('cm.client_id', $userId)
                ->where('lt.TransactionType', 0)
                ->orderBy('lt.TransationDate')
                ->select('lt.Total', 'lt.created_at')
                ->first();

            $item->Precio = $primerCobro->Total ?? null;
            $item->Fecha_PrimerCobro = $primerCobro->created_at ?? null;

            $item->ticket_promedio = ($item->usos > 0 && $item->Precio)
                ? round($item->Precio / $item->usos, 2)
                : null;

            return $item;
        });
    }

    public function collection(): Collection
    {
        return $this->resultados->map(function ($item) {
            return [
                $item->Referencia,
                $item->Nombre . ' ' . $item->Apellido,
                $item->usos,
                $item->Nombre_Paquete,
                optional($item->Fecha_PrimerCobro)->format('Y-m-d'),
                number_format($item->Precio, 2),
                number_format($item->ticket_promedio, 2),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Referencia',
            'Nombre',
            'Usos',
            'Paquete',
            'Fecha Primer Cobro',
            'Precio',
            'Ticket Promedio',
        ];
    }
}