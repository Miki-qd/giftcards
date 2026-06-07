<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
            'registration_token' => [
                'required', 
                'string', 
                \Illuminate\Validation\Rule::exists('registration_tokens', 'token')
                    ->where('is_used', 0)
                    ->where(fn ($query) => $query->where('expires_at', '>', now()))
            ],
        ])->validate();

        return \Illuminate\Support\Facades\DB::transaction(function () use ($input) {
            $updated = \App\Models\RegistrationToken::where('token', $input['registration_token'])
                ->where('is_used', false)
                ->where('expires_at', '>', now())
                ->update(['is_used' => true]);

            if (! $updated) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'registration_token' => ['The registration token has already been used or expired.'],
                ]);
            }

            return User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
            ]);
        });
    }
}
