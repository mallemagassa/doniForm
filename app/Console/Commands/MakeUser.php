<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class MakeUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'doucsoft:user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crée un nouvel utilisateur avec un rôle';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->ask('Quel est le nom de l\'utilisateur ?');
        $email = $this->ask('Quel est l\'email de l\'utilisateur ?');
        $password = $this->secret('Quel est le mot de passe ?');
        $roleName = $this->ask('Quel est le rôle de l\'utilisateur ?');

        // Validation des entrées
        if (empty($name) || empty($email) || empty($password) || empty($roleName)) {
            $this->error('Tous les champs sont obligatoires !');
            return;
        }

        // Vérification de l'email unique
        if (User::where('email', $email)->exists()) {
            $this->error('Un utilisateur avec cet email existe déjà !');
            return;
        }

        // Création ou récupération du rôle
        $role = Role::firstOrCreate(['name' => $roleName], [
            'guard_name' => 'web'
        ]);

        // Création de l'utilisateur
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        // Attribution du rôle
        $user->assignRole($role);

        $this->info("Utilisateur créé avec succès !");
        $this->line("Nom: {$name}");
        $this->line("Email: {$email}");
        $this->line("Rôle: {$roleName}");
    }
}