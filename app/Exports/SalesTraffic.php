<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use App\Models\GeneralCatalogs;
use App\Models\LocalTransaction;
use App\Models\Orders;
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
use Carbon\Carbon;


class SalesTraffic implements FromCollection, WithHeadings, WithStyles, WithDrawings{

    public function __construct($startDate, $endDate){
        $this->catalogs = new GeneralCatalogs();
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        //
        // 1) Sub-consulta que agrupa por transaction _id y calcula totales
        //
        $sub = DB::table('local_transaction')
            ->select([
                '_id',
                DB::raw("CASE WHEN LENGTH(_id) = 36 THEN 'CARRERA' ELSE 'INTERLOGIC' END as proveedor"),
                DB::raw('SUM(Total) as tot'),
            ])
            ->whereBetween('TransationDate', [$this->startDate, $this->endDate])
            ->whereIn('PaymentType', [0,1,2,3])
            ->whereIn('TransactionType', [0,1,2])
            ->groupBy('_id');

        //
        // 2) A partir de ahí, filtro SOLO INTERLOGIC y vuelvo a agrupar por hora y paquete
        //
        $packageData = DB::table(DB::raw("({$sub->toSql()}) as uniques"))
            ->mergeBindings($sub)                    // importa los where/bindings del sub
            ->where('uniques.proveedor', 'INTERLOGIC')  // <— solo INTERLOGIC
            ->join('local_transaction as lt', 'lt._id', '=', 'uniques._id')
            ->selectRaw("
                DATE_FORMAT(lt.TransationDate, '%H') as hour,
                lt.Package              as Package,
                SUM(uniques.tot)        as total_sales
            ")
            ->whereBetween('lt.TransationDate', [$this->startDate, $this->endDate])
            ->groupBy('hour','Package')
            ->get();



        $orderData = Orders::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->whereIn('OrderType', [1, 2])
            ->selectRaw('DATE_FORMAT(created_at, "%H") as hour, package_id, COUNT(*) as total_orders')
            ->groupBy('hour', 'package_id')
            ->get();

        $data = [];
        $totals = [
            'Paquete Express' => 0,
            'Venta total paquete express' => 0,
            'Paquete Básico' => 0,
            'Venta total paquete Básico' => 0,
            'Paquete Ultra' => 0,
            'Venta total paquete Ultra' => 0,
            'Paquete Deluxe' => 0,
            'Venta total paquete Deluxe' => 0,
            'Compras por Membresía' => 0,
            'Venta total Compras por Membresía' => 0,
            'Total paquetes del día' => 0,
            'Total Venta' => 0,
        ];

        foreach ($this->catalogs->business_hours as $hourKey => $label) {
            $data[$hourKey] = [
                'Hora' => $label,
                'Paquete Express' => 0,
                'Venta total paquete express' => 0,
                'Paquete Básico' => 0,
                'Venta total paquete Básico' => 0,
                'Paquete Ultra' => 0,
                'Venta total paquete Ultra' => 0,
                'Paquete Deluxe' => 0,
                'Venta total paquete Deluxe' => 0,
                'Compras por Membresía' => 0,
                'Venta total Compras por Membresía' => 0,
                'Total paquetes del día' => 0,
                'Total Venta' => 0,
            ];
        }

        foreach ($packageData as $item) {
            $hour = $item->hour;
            $sales = $item->total_sales;
            $package = $item->Package;

            if ($package === null) {
                $data[$hour]['Venta total Compras por Membresía'] += $sales;
                $totals['Venta total Compras por Membresía'] += $sales;
            } else {
                switch ($package) {
                    case '612f057787e473107fda56aa':
                        $data[$hour]['Venta total paquete express'] += $sales;
                        $totals['Venta total paquete express'] += $sales;
                        break;
                    case '612f067387e473107fda56b0':
                        $data[$hour]['Venta total paquete Básico'] += $sales;
                        $totals['Venta total paquete Básico'] += $sales;
                        break;
                    case '612f1c4f30b90803837e7969':
                        $data[$hour]['Venta total paquete Ultra'] += $sales;
                        $totals['Venta total paquete Ultra'] += $sales;
                        break;
                    case '612abcd1c4ce4c141237a356':
                        $data[$hour]['Venta total paquete Deluxe'] += $sales;
                        $totals['Venta total paquete Deluxe'] += $sales;
                        break;
                }
            }

            $data[$hour]['Total Venta'] += $sales;
            $totals['Total Venta'] += $sales;
        }

        foreach ($orderData as $item) {
            $hour = $item->hour;
            $orders = $item->total_orders;
            $package = $item->package_id;

            if ($package === null) {
                $data[$hour]['Compras por Membresía'] += $orders;
                $totals['Compras por Membresía'] += $orders;
            } else {
                switch ($package) {
                    case '612f057787e473107fda56aa':
                        $data[$hour]['Paquete Express'] += $orders;
                        $totals['Paquete Express'] += $orders;
                        break;
                    case '612f067387e473107fda56b0':
                        $data[$hour]['Paquete Básico'] += $orders;
                        $totals['Paquete Básico'] += $orders;
                        break;
                    case '612f1c4f30b90803837e7969':
                        $data[$hour]['Paquete Ultra'] += $orders;
                        $totals['Paquete Ultra'] += $orders;
                        break;
                    case '612abcd1c4ce4c141237a356':
                        $data[$hour]['Paquete Deluxe'] += $orders;
                        $totals['Paquete Deluxe'] += $orders;
                        break;
                }
            }

            $data[$hour]['Total paquetes del día'] += $orders;
            $totals['Total paquetes del día'] += $orders;
        }

        $data['Total día'] = array_merge(['Hora' => 'Total día'], $totals);
        return collect(array_values($data));
    }

    public function headings(): array
    {
        return [
            ['*****', 'Tráfico de ventas (promedio por hora)'   . $this->startDate.' - '. $this->endDate ], // Fila con imagen y título
            [ // Encabezados de columnas
                'Hora',
                'Paquete Express',
                'Venta total paquete express',
                'Paquete Básico',
                'Venta total paquete Básico',
                'Paquete Ultra',
                'Venta total paquete Ultra',
                'Paquete Deluxe',
                'Venta total paquete Deluxe',
                'Compras por Membresía', // Agregar encabezado
                'Venta total Compras por Membresía', // Agregar encabezado
                'Total paquetes del día',
                'Total Venta',
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

        // Aplicar estilo base a todas las celdas
        $sheet->getStyle('A1:' . $highestColumn . $highestRow)->applyFromArray($baseStyle);

        // Estilos para la primera fila (título)
        $sheet->getStyle('A1:' . $highestColumn . '1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '000000'],
                'name' => 'Arial',
            ],
        ]);

        // Estilos para la última fila (totales)
        $sheet->getStyle('A' . $highestRow . ':' . $highestColumn . $highestRow)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '000000'],
                'name' => 'Arial',
            ],
        ]);

        // Estilos para la primera columna (horas)
        $sheet->getStyle('A2:A' . ($highestRow - 1))->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => 'fff2c2'],
            ],
        ]);

        // Estilos para el resto de las columnas (colores alternados)
        $colors = ['dbdcdf', 'ffffff'];
        $colorIndex = 0;

        for ($col = 'B'; $col <= $highestColumn; $col++) {
            $sheet->getStyle($col . '2:' . $col . ($highestRow - 1))->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['rgb' => $colors[$colorIndex]],
                ],
            ]);

            $colorIndex = 1 - $colorIndex;
        }

        // Estilos para los encabezados de columna
        $sheet->getStyle('A2:' . $highestColumn . '2')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'name' => 'Arial',
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => 'afb1b3'],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        // Formato de moneda para las columnas de ventas
        $currencyColumns = ['C', 'E', 'G', 'I', 'K']; // Columnas con valores de dinero
        foreach ($currencyColumns as $col) {
            $sheet->getStyle($col . '3:' . $col . $highestRow)->getNumberFormat()->setFormatCode('$#,##0.00');
        }

        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getRowDimension(2)->setRowHeight(30);

        $sheet->getStyle('A3:' . $highestColumn . ($highestRow - 1))->applyFromArray([
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

