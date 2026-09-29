<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * Crée le premier administrateur à partir des variables ADMIN_EMAIL / ADMIN_PASSWORD (utilisé au démarrage
 * sur Render). Ne fait RIEN si un administrateur existe déjà : le mot de passe n'est jamais écrasé.
 */
class CreateAdmin extends Command
{
    protected $signature = 'admin:create {--email=} {--password=} {--name=Administrateur}';
    protected $description = "Crée le premier administrateur s'il n'en existe aucun";

    public function handle(): int
    {
        if (User::where('role', 'admin')->exists()) {
            $this->info('Un administrateur existe déjà : rien à faire.');

            return self::SUCCESS;
        }

        $email = $this->option('email') ?: env('ADMIN_EMAIL');
        $password = $this->option('password') ?: env('ADMIN_PASSWORD');

        if (! $email || ! $password) {
            $this->warn('ADMIN_EMAIL et ADMIN_PASSWORD ne sont pas définis : aucun administrateur créé.');

            return self::SUCCESS;
        }

        if (strlen($password) < 10) {
            $this->error('ADMIN_PASSWORD doit faire au moins 10 caractères.');

            return self::FAILURE;
        }

        User::create([
            'name' => $this->option('name'),
            'email' => $email,
            'password' => $password, // haché automatiquement par le modèle
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->info("Administrateur {$email} créé.");

        return self::SUCCESS;
    }
}
