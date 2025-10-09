<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Factura extends Model
{
    protected $table = 'local_transaction';

    // ... otros atributos y relaciones
    public function fiscalAccount(){
        return $this->belongsTo(FiscalAccounts::class, 'account_id');
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}



