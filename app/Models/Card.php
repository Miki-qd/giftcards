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

    protected function casts(): array
    {
        return [
            'activation_date' => 'datetime',
            'expiration_date' => 'date',
            'balance' => 'decimal:2',
        ];
    }

    protected static function booted()
    {
        static::creating(function ($card) {
            if ($card->pin) {
                $card->pin = Hash::make($card->pin);
            }
        });
    }
}
