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

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
        ]);

        \App\Models\RegistrationToken::where('token', $input['registration_token'])->update(['is_used' => true]);

        return $user;
    }
}
