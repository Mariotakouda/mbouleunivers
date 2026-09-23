<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();

        Event::updateOrCreate(
            ['title' => "Univers 2 M'boulè"],
            [
                'user_id' => $admin->id,
                'description' => "Spectacle de stand-up / humour \"Univers 2 M'boulè\".",
                'date' => now()->addMonth()->toDateString(),
                'start_time' => '19:00',
                'end_time' => '22:00',
                'venue' => 'Salle à définir',
                'address' => 'Lomé, Togo',
                'status' => 'published',
            ]
        );
    }
}
