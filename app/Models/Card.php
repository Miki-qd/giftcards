<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Card extends Model
{
    protected $fillable = [
        'card_number',
        'pin',
        'activation_date',
        'expiration_date',
        'balance',
    ];

    protected $hidden = [
        'pin',
    ];

    protected function casts(): array
    {
        return [
            'pin' => 'hashed',
            'activation_date' => 'datetime',
            'expiration_date' => 'date',
            'balance' => 'decimal:2',
        ];
    }
}
