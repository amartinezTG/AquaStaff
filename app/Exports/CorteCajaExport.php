<?php

namespace App\Exports;

use App\Models\CorteCaja;
use App\Models\DetalleArqueo;
use App\Models\CorteCajaComentarios;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CorteCajaExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize
{
    protected $corteId;

    public function __construct($corteId)
    {
        $this->corteId = $corteId;
    }

    public function array(): array
    {
        $corte       = CorteCaja::findOrFail($this->corteId);
        $detalles    = DetalleArqueo::where('id_corte', $this->corteId)->get();
        $comentarios = CorteCajaComentarios::where('id_corte', $this->corteId)->pluck('comentarios')->implode('; ');

        $data   = [];

        $data[] = ['Fecha',           $corte->fecha_corte];
        $data[] = ['Sucursal',        $corte->sucursal];
        $data[] = ['Total Ventas',    $corte->total_ventas];
        $data[] = ['Total Tickets',   $corte->total_tickets];
        $data[] = ['Dinero Efectivo', $corte->dinero_acumulado_efectivo];
        $data[] = ['Dinero Tarjeta',  $corte->dinero_acumulado_tarjeta];
        $data[] = ['Dinero Recibido', $corte->dinero_recibido];
        $data[] = ['Comentarios',     $comentarios];

        $data[] = [];
        $data[] = ['Denominación', 'Cantidad'];

        foreach ($detalles as $detalle) {
            $data[] = [$detalle->denominacion, $detalle->cantidad];
        }

        return $data;
    }

    public function headings(): array
    {
        return ['Corte de Caja Exportado'];
    }

    public function title(): string
    {
        return 'Corte_' . $this->corteId;
    }

    public function view(): View
    {
        $corteCaja = CorteCaja::with('detalleArqueo', 'comentarios')
            ->findOrFail($this->corteId);

        return view('exports.corte_caja_excel', [
            'corte' => $corteCaja
        ]);
    }
}
