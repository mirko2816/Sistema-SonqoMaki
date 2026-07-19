<?php

namespace App\Modules\Authentication\Actions;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class CreateSpecialist
{
    public function handle(string $email, string $password): User
    {
        $normalizedEmail = mb_strtolower(trim($email));

        if (User::withTrashed()->whereRaw('lower(email) = ?', [$normalizedEmail])->exists()) {
            throw ValidationException::withMessages([
                'email' => 'Ya existe una cuenta con ese correo electrónico.',
            ]);
        }

        return User::create([
            'email' => $normalizedEmail,
            'password' => $password,
            'is_active' => true,
        ]);
    }
}
