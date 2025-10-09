<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlobalInvoice extends Model
{
    use HasFactory;
    protected $table = 'global_invoice';
    // ...
    protected $fillable = ['name','total','start_date_group','end_date_group','paymentType'];
   
}
