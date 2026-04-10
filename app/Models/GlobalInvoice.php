<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlobalInvoice extends Model
{
    use HasFactory;
    protected $table = 'global_invoice';
    // ...
    protected $fillable = ['name','uuid','serie','folio','file_name','total','start_date_group','end_date_group','paymentType','periodicidad'];
     
}
