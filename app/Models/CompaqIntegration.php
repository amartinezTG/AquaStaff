<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompaqIntegration extends Model
{
    use HasFactory;
    protected $table = 'compaq_integration';
    // ...
   // protected $primaryKey = 'id';
    protected $fillable = ['name'];

   public function localTransactions()
{
    return $this->hasMany(LocalTransaction::class, 'integrate_cp', 'id');
}
}
