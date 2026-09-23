<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Database\Seeder;

class TicketTypeSeeder extends Seeder
{
    public function run(): void
    {
        $event = Event::where('title', "Univers 2 M'boulè")->firstOrFail();

        $categories = [
            ['name' => 'Standard', 'price' => 5000, 'quantity' => 200],
            ['name' => 'VIP', 'price' => 10000, 'quantity' => 80],
            ['name' => 'Premium', 'price' => 15000, 'quantity' => 30],
        ];

        foreach ($categories as $category) {
            TicketType::updateOrCreate(
                ['event_id' => $event->id, 'name' => $category['name']],
                [
                    'description' => "Catégorie {$category['name']}",
                    'price' => $category['price'],
                    'quantity' => $category['quantity'],
                    'available_quantity' => $category['quantity'],
                    'status' => 'active',
                ]
            );
        }
    }
}
