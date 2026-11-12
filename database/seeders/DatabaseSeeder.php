<?php

namespace Database\Seeders;

use App\Models\Facility;
use App\Models\Space;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = new User([
            'name' => 'Admin SpaceHub',
            'email' => 'admin@spacehub.test',
            'password' => 'password',
        ]);
        $admin->forceFill(['role' => 'admin'])->save();

        User::create([
            'name' => 'User Contoh',
            'email' => 'user@spacehub.test',
            'password' => 'password',
        ]);

        $facilities = collect(['WiFi', 'AC', 'Proyektor', 'Whiteboard', 'Kopi Gratis', 'Parkir'])
            ->map(fn ($name) => Facility::create(['name' => $name]));

        $spaces = [
            ['Open Space Lantai 1', 'coworking', 30, 'Lantai 1', 25000, 'Area kerja bersama yang nyaman.'],
            ['Meeting Room Orion', 'meeting_room', 8, 'Lantai 2', 75000, 'Cocok untuk rapat tim kecil.'],
            ['Private Office Nova', 'private_office', 4, 'Lantai 3', 120000, 'Kantor privat untuk tim kecil.'],
            ['Aula Galaxy', 'event_hall', 100, 'Lantai 1', 350000, 'Untuk seminar dan acara besar.'],
        ];

        foreach ($spaces as [$name, $type, $capacity, $location, $price, $desc]) {
            $space = Space::create([
                'name' => $name,
                'type' => $type,
                'capacity' => $capacity,
                'location' => $location,
                'price_per_hour' => $price,
                'description' => $desc,
            ]);
            $space->facilities()->attach($facilities->random(3)->pluck('id'));
        }
    }
}
