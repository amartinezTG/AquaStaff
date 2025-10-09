<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Membership extends Model
{
    use HasFactory;
    protected $table = 'client_membership';
    protected $primaryKey = 'membership_id';

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
