<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use App\Models\GeneralCatalogs;
use App\Models\LocalTransaction;
use Carbon\Carbon;



class UsersExport implements FromCollection, WithHeadings, WithStyles, WithDrawings{
    
    public function __construct($startDate, $endDate){
        $catalogs        = new GeneralCatalogs();
        $this->startDate = $startDate;
        $this->endDate   = $endDate;
        //$this->package_type = $package_type;
    }

    public function collection(){
$packageData = LocalTransaction::whereBetween('TransationDate', [$this->startDate, $this->endDate])
    ->whereNotNull('Package')
    ->where('Package', '!=', '')
    ->selectRaw('TransationDate, Package, sum(CASE WHEN Total > 0 THEN Total ELSE 0 END) as total_sales, sum(CASE WHEN Total = 0 THEN 1 ELSE 0 END) as total_courtesy_count, count(*) as total_purchases') // Conditional sums
    ->groupBy('TransationDate', 'Package')
    ->get();

$data = [];
foreach ($packageData as $item) {
    $transationDate = Carbon::parse($item->TransationDate);
    $weekNumber = $transationDate->weekOfYear;
    $key = 'Semana ' . $weekNumber;

    if (!isset($data[$key])) {
        $data[$key] = [
            'Semana' => $key,
            'Vehiculos Ingresados' => 0,
            'Paquete Express' => 0,
            'Paquete Express Cortesias' => 0,
            'Paquete Básico' => 0,
            'Paquete Básico Cortesias' => 0,
            'Paquete Ultra' => 0,
            'Paquete Ultra Cortesias' => 0,
            'Paquete Deluxe' => 0,
            'Paquete Deluxe Cortesias' => 0,
            'Total Venta' => 0,
            'Total Cortesias' => 0,
        ];
    }

    $data[$key]['Vehiculos Ingresados'] += $item->total_purchases;
    $data[$key]['Total Venta']          += $item->total_sales;
    $data[$key]['Total Cortesias']      += $item->total_courtesy_count; // Use the count of courtesy transactions

    switch ($item->Package) {
        case '612f057787e473107fda56aa': // express
            $data[$key]['Paquete Express'] += $item->total_purchases;
            $data[$key]['Paquete Express Cortesias'] += $item->total_courtesy_count;
            break;
        case '612f067387e473107fda56b0': //Básico
            $data[$key]['Paquete Básico'] += $item->total_purchases;
            $data[$key]['Paquete Básico Cortesias'] += $item->total_courtesy_count;
            break;
        case '612f1c4f30b90803837e7969': // Ultra
            $data[$key]['Paquete Ultra'] += $item->total_purchases;
            $data[$key]['Paquete Ultra Cortesias'] += $item->total_courtesy_count;
            break;
        case '612abcd1c4ce4c141237a356': // Deluxe
            $data[$key]['Paquete Deluxe'] += $item->total_purchases;
            $data[$key]['Paquete Deluxe Cortesias'] += $item->total_courtesy_count;
            break;
    }
}

$exportData = array_values($data);

return collect($exportData);

    }

    public function headings(): array{

        return [
            
            ['*****','Indicadores Operativos'], // Fila con imagen y título
            [ // Encabezados de columnas
                'FECHA',
                'Vehiculos Ingresados',
                'Paquete Express',
                'Paquete Express Cortesias',
                'Paquete Básico',
                'Paquete Básico Cortesias',
                'Paquete Ultra',
                'Paquete Ultra Cortesias',
                'Paquete Deluxe',
                'Paquete Deluxe Cortesias',
                'Total Venta',
                'Total Cortesias',
            ],
        ];
    }

    public function styles(Worksheet $sheet){
        // Estilos para la fila del título (combinada)
        $sheet->getStyle('A1')->applyFromArray([ // Estilos para el título
            
            'alignment'      => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'fill'         => [ // Color de fondo para el título
                'fillType' => Fill::FILL_SOLID,
                'color'    => ['rgb' => '3FAAA8'], // Tu color hexadecimal
            ],
        ]);

        $sheet->mergeCells('B1:L1'); // Combina las celdas para el título (ajusta 'L' si tienes más columnas)
        $sheet->getStyle('B1')->applyFromArray([ // Estilos para el título
            'font'     => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => 'FFFFFF'],
                'name' => 'Arial', // Aquí se agrega la tipografía Arial
            ],
            'alignment'      => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'fill'         => [ // Color de fondo para el título
                'fillType' => Fill::FILL_SOLID,
                'color'    => ['rgb' => '3FAAA8'], // Tu color hexadecimal
            ],
        ]);

        // Estilos para los encabezados de columnas (sin cambios importantes)
        $sheet->getStyle('A2:L2')->applyFromArray([ // Ajusta el rango
            'font'      => [
                'bold'  => true,
                'color' => ['rgb' => 'FFFFFF'],
                'name' => 'Arial', // Aquí se agrega la tipografía Arial
            ],
            'fill'         => [
                'fillType' => Fill::FILL_SOLID,
                'color'    => ['rgb' => 'afb1b3'],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true, // Ajuste de texto automático
            ],
        ]);

        // Estilos para el resto de las celdas (opcional)
        $sheet->getStyle('A3:L' . $sheet->getHighestRow())->applyFromArray([ // Ajusta el rango
            'font'     => [
                'size' => 12,
                'name' => 'Arial', // Aquí se agrega la tipografía Arial
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getColumnDimension('A')->setWidth(20); // Ajusta el valor (20) según el ancho deseado
        $sheet->getRowDimension(2)->setRowHeight(30); // Ajusta el valor (30) según la altura deseada

        // Estilos para el resto de las celdas (opcional)
        $sheet->getStyle('A4:L' . $sheet->getHighestRow())->applyFromArray([
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

    }

    public function drawings(){

        $drawing = new Drawing();
        $drawing->setPath(public_path('/assets/img/AQUA-CAR-CLUB-1.png'));
        $drawing->setHeight(250); // Ajusta la altura de la imagen
        $drawing->setWidth(150); // Ajusta el ancho de la imagen
        $drawing->setCoordinates('A1'); // La imagen va en la celda A1

        return $drawing;

    }
}

