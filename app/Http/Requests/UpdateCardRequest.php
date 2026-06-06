<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $card = $this->route('card');
        $activationDate = $card ? $card->activation_date->toDateString() : 'now';

        return [
            'expiration_date' => ['required', 'date', 'after:'.$activationDate],
            'balance' => ['required', 'numeric', 'min:'.($card ? $card->balance : 0), 'max:500'],
        ];
    }
}
