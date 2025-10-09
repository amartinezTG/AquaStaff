<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use App\Models\GeneralCatalogs;
use App\Models\ClientMembership;
use Carbon\Carbon;

class SalesMembership implements FromCollection, WithHeadings, WithStyles, WithDrawings
{
    protected $startDate;
    protected $endDate;
    protected $catalogs;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->catalogs = new GeneralCatalogs();
    }

    public function collection()
    {
        return ClientMembership::whereBetween('start_date', [$this->startDate, $this->endDate])
            ->with('client') // Cargar la relación con el cliente
             ->orderBy('created_at', 'desc')
             ->get()
            ->map(function ($membership) {
                return [
                    'Fecha' => $membership->start_date ? Carbon::parse($membership->start_date)->format('Y-m-d H:i:s') : '',
                    'Cliente' => $membership->client ? $membership->client->first_name . ' ' . $membership->client->last_name : 'Cliente no encontrado',
                    'ID Cliente' => $membership->client ? $membership->client->id : '', // O la columna que uses como ID en clients
                    'Paquete' => $this->catalogs->memberships_type[$membership->membership_id] ?? 'Desconocido',
                    'Sucursal' => $membership->facility ?? '',
                ];
            });
    }

    public function headings(): array
    {
        return [
            ['*****', 'Reporte de Membresías', 'Desde: ' . $this->startDate, 'Hasta: ' . $this->endDate],
            [
                'Fecha',
                'Cliente',
                'ID Cliente',
                'Paquete',
                'Sucursal',
            ],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        // Estilo base para la tipografía Arial
        $baseStyle = [
            'font' => [
                'name' => 'Arial',
            ],
        ];
        $sheet->getStyle('A1:' . $highestColumn . $highestRow)->applyFromArray($baseStyle);

        // Estilos para la primera fila (título)
        $sheet->getStyle('A1:D1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Estilos para los encabezados de columna
        $sheet->getStyle('A2:' . $highestColumn . '2')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'name' => 'Arial',
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['rgb' => 'afb1b3'],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(25);
        $sheet->getColumnDimension('E')->setWidth(20);
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setPath(public_path('/assets/img/AQUA-CAR-CLUB-1.png'));
        $drawing->setHeight(250);
        $drawing->setWidth(150);
        $drawing->setCoordinates('A1');

        return $drawing;
    }
}