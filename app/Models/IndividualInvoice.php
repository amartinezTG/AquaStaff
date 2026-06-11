<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndividualInvoice extends Model
{
    protected $table = 'individual_invoices';

    protected $fillable = [
        'uuid', 'local_transaction_id', 'fiscal_account_id',
        'serie', 'folio', 'subtotal', 'iva', 'total',
        'file_name', 'fecha_emision', 'status',
        'cancelada_at', 'cancel_motivo', 'origen', 'created_by',
    ];

    protected $casts = [
        'fecha_emision' => 'datetime',
        'cancelada_at'  => 'datetime',
    ];
}
