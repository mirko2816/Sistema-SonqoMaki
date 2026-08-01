<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Modules\Authentication\Actions\CreateSpecialist;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreateSpecialistCommand extends Command
{
    protected $signature = 'specialist:create {email? : Correo electrónico del especialista}';

    protected $description = 'Crea de forma segura la cuenta inicial del especialista';

    public function handle(CreateSpecialist $createSpecialist): int
    {
        $email = (string) ($this->argument('email') ?: $this->ask('Correo electrónico'));

        if ($this->accountAlreadyExists($email)) {
            $this->error('Ya existe una cuenta con ese correo electrónico. No se realizaron cambios.');

            return self::FAILURE;
        }

        $password = (string) $this->secret('Contraseña (mínimo 12 caracteres)');
        $confirmation = (string) $this->secret('Confirma la contraseña');

        $validator = Validator::make(
            [
                'email' => mb_strtolower(trim($email)),
                'password' => $password,
                'password_confirmation' => $confirmation,
            ],
            [
                'email' => ['required', 'string', 'email:rfc', 'max:320'],
                'password' => ['required', 'string', 'min:12', 'confirmed'],
            ],
            [
                'email.required' => 'El correo electrónico es obligatorio.',
                'email.email' => 'El correo electrónico no es válido.',
                'email.max' => 'El correo electrónico no puede superar los 320 caracteres.',
                'password.required' => 'La contraseña es obligatoria.',
                'password.min' => 'La contraseña debe tener al menos 12 caracteres.',
                'password.confirmed' => 'La confirmación de la contraseña no coincide.',
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        try {
            $createSpecialist->handle($email, $password);
        } catch (ValidationException $exception) {
            $this->error($exception->validator->errors()->first());

            return self::FAILURE;
        }

        $this->info('Cuenta del especialista creada correctamente.');

        return self::SUCCESS;
    }

    private function accountAlreadyExists(string $email): bool
    {
        return User::withTrashed()
            ->whereRaw('lower(email) = ?', [mb_strtolower(trim($email))])
            ->exists();
    }
}
