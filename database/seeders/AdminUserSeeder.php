<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@universmboule.tg'],
            [
                'name' => 'Administrateur',
                'password' => Hash::make('password'), // à changer avant prod
                'role' => 'admin',
                'phone' => '+22890000000',
                'status' => 'active',
            ]
        );

        User::updateOrCreate(
            ['email' => 'agent@universmboule.tg'],
            [
                'name' => 'Agent Contrôle',
                'password' => Hash::make('password'),
                'role' => 'agent',
                'phone' => '+22891111111',
                'status' => 'active',
            ]
        );
    }
}
