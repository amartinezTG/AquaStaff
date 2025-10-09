<?php
namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TransactionsList implements FromCollection, WithHeadings
{
    protected $rows, $catalogs;

    public function __construct($rows, $catalogs, $startDate, $endDate)
    {
        $this->rows     = $rows;
        $this->catalogs = $catalogs;
    }

    public function collection()
    {
        $data = [];

        foreach ($this->rows as $r) {
            $data[] = [
                'Tipo de uso'   => $this->catalogs->transactiontype_type[$r->TransactionType] ?? '',
                'Fecha'         => $r->TransationDate,
                'Total'         => $r->Total,
                'Membresía'     => $r->Membership
                                     ? ($this->catalogs->package_type[$r->Membership] ?? '')
                                     : '',
                'Paquete'       => !$r->Membership
                                     ? ($this->catalogs->package_type[$r->Package] ?? '')
                                     : '',
                'Cajero'        => $r->Atm,
                'Folio'         => $r->Folio,
                'Método pago'   => $this->catalogs->folio_payment_type[$r->PaymentType] ?? '',
            ];
        }

        return new Collection($data);
    }

    public function headings(): array
    {
        return [
            'Tipo de uso',
            'Fecha',
            'Total',
            'Membresía',
            'Paquete',
            'Cajero',
            'Folio',
            'Método pago',
        ];
    }
}
