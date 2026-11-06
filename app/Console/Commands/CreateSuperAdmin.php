<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

/**
 * Seul moyen de créer un superadmin (pas d'inscription publique).
 *   php artisan bdw:create-superadmin
 */
class CreateSuperAdmin extends Command
{
    protected $signature = 'bdw:create-superadmin';

    protected $description = 'Crée un compte superadmin pour le dashboard BDW';

    public function handle()
    {
        $existing = User::where('role', User::ROLE_SUPERADMIN)->count();
        if ($existing > 0 && ! $this->confirm("Il existe déjà $existing superadmin(s). En créer un autre ?", false)) {
            return self::SUCCESS;
        }

        $username = $this->ask('Nom affiché');
        $email = $this->ask('Email');
        $password = $this->secret('Mot de passe (8 caractères min., lettres et chiffres)');
        $confirmation = $this->secret('Confirmer le mot de passe');

        $validator = Validator::make([
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $confirmation,
        ], [
            'username' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        User::create([
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'role' => User::ROLE_SUPERADMIN,
            'is_active' => true,
        ]);

        $this->info("Superadmin $email créé.");

        return self::SUCCESS;
    }
}
