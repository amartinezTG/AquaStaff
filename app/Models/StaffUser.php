<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class StaffUser extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'staff_users';

    protected $fillable = [
        'name',
        'username',
        'password',
        'user_role',
    ];

    protected $hidden = [
        'password'
    ];
}