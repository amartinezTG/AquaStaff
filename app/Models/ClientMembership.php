<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ClientMembership extends Model
{
    use HasFactory;
    protected $table = 'client_membership';
    protected $primaryKey = 'id';

    
    /*public function memberships()
    {
        return $this->hasMany(ClientMembership::class);
    }*/

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', '_id');
    }

    public function nextMembership()
    {
        if (!is_null($this->client_id)) { 
            return $this->hasOne(ClientMembership::class, 'client_id', 'client_id')
                ->where('start_date', '>', $this->end_date)
                ->where('client_id', '=', $this->client_id); 
        }
            // Manejar el caso en el que $this->end_date no esté definida
            return null; 
        
    }

    public function previousMembership()
    {
            $this->start_date = Carbon::parse($this->start_date); 
        return $this->hasOne(ClientMembership::class, 'client_id', 'client_id')
            ->where('end_date', '<', $this->start_date)
            ->orderBy('end_date', 'desc'); // Obtener la membresía anterior más reciente
    }
}

