<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCardRequest extends FormRequest
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
        return [
            'activation_date' => ['required', 'date'],
            'expiration_date' => ['required', 'date', 'after:activation_date'],
            'balance' => ['required', 'numeric', 'min:5', 'max:500'],
        ];
    }
}
