<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistrationToken extends Model
{
    protected $fillable = ['token', 'is_used', 'expires_at'];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
