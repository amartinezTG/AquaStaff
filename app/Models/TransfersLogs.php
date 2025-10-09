<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransfersLogs extends Model
{
    use HasFactory;
    protected $table = 'transfer_logs';
    protected $primaryKey = 'transfer_log_id';

}
