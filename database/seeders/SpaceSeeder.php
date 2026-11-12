<?php

namespace Database\Seeders;

use App\Models\Facility;
use App\Models\Space;
use Illuminate\Database\Seeder;

class SpaceSeeder extends Seeder
{
    /** Tarif rata per jam untuk setiap tipe ruang. */
    private const PRICES = [
        'meeting_room' => 700000,
        'event_hall' => 1000000,
        'private_office' => 650000,
        'coworking' => 500000,
    ];

    public function run(): void
    {
        $facilities = collect(['WiFi', 'AC', 'Proyektor', 'Whiteboard', 'Kopi Gratis', 'Parkir'])
            ->mapWithKeys(fn ($name) => [$name => Facility::firstOrCreate(['name' => $name])]);

        $byType = [
            'coworking' => ['WiFi', 'AC', 'Kopi Gratis', 'Parkir'],
            'meeting_room' => ['WiFi', 'AC', 'Proyektor', 'Whiteboard'],
            'private_office' => ['WiFi', 'AC', 'Kopi Gratis', 'Parkir'],
            'event_hall' => ['WiFi', 'AC', 'Proyektor', 'Parkir'],
        ];

        // [nama, tipe, kapasitas, lokasi, tarif per jam, deskripsi, aktif]
        $spaces = [
            ['Creative Hub Lantai 2', 'coworking', 24, 'Lantai 2, Gedung A', 28000, 'Area kerja terbuka dengan meja panjang dan pencahayaan alami.', true],
            ['Quiet Zone Coworking', 'coworking', 16, 'Lantai 3, Gedung A', 32000, 'Zona tenang untuk fokus bekerja, tanpa panggilan telepon.', true],
            ['Rooftop Coworking Deck', 'coworking', 20, 'Rooftop, Gedung B', 35000, 'Kerja santai di udara terbuka dengan pemandangan kota.', true],
            ['Studio Kolaborasi Barat', 'coworking', 18, 'Lantai 1, Gedung C', 22000, 'Studio kolaborasi dengan meja modular. Sedang direnovasi.', false],

            ['Meeting Room Andromeda', 'meeting_room', 8, 'Lantai 2, Gedung A', 75000, 'Ruang rapat kecil dengan layar presentasi dan papan tulis.', true],
            ['Meeting Room Pegasus', 'meeting_room', 10, 'Lantai 2, Gedung A', 90000, 'Ruang rapat tim dengan meja oval dan proyektor.', true],
            ['Meeting Room Lyra', 'meeting_room', 6, 'Lantai 3, Gedung A', 65000, 'Ruang diskusi ringkas untuk rapat singkat.', true],
            ['Boardroom Eksekutif', 'meeting_room', 14, 'Lantai 4, Gedung B', 150000, 'Ruang rapat eksekutif dengan kursi kulit dan sistem video konferensi.', true],
            ['Meeting Room Cassiopeia', 'meeting_room', 12, 'Lantai 3, Gedung B', 110000, 'Ruang rapat menengah dengan jendela besar.', true],

            ['Private Office Aurora', 'private_office', 4, 'Lantai 3, Gedung B', 125000, 'Kantor privat untuk tim kecil dengan akses 24 jam.', true],
            ['Private Office Zenith', 'private_office', 6, 'Lantai 4, Gedung B', 165000, 'Kantor privat lega dengan ruang penyimpanan.', true],
            ['Private Office Horizon', 'private_office', 8, 'Lantai 5, Gedung B', 210000, 'Kantor privat besar untuk tim menengah. Sedang tidak tersedia.', false],

            ['Aula Nebula', 'event_hall', 80, 'Lantai 1, Gedung C', 320000, 'Aula untuk seminar dan pelatihan dengan panggung kecil.', true],
            ['Ballroom Cosmos', 'event_hall', 150, 'Lantai 1, Gedung D', 550000, 'Ballroom luas untuk resepsi, konferensi, dan pameran.', true],
            ['Workshop Hall Meteor', 'event_hall', 40, 'Lantai 2, Gedung C', 280000, 'Ruang workshop fleksibel dengan susunan kursi yang bisa diubah.', true],
        ];

        foreach ($spaces as [$name, $type, $capacity, $location, $price, $description, $active]) {
            $space = Space::updateOrCreate(
                ['name' => $name],
                [
                    'type' => $type,
                    'capacity' => $capacity,
                    'location' => $location,
                    'price_per_hour' => $price,
                    'description' => $description,
                    'is_active' => $active,
                ]
            );

            $space->facilities()->syncWithoutDetaching(
                $facilities->only($byType[$type])->pluck('id')->values()->all()
            );
        }

        foreach (self::PRICES as $type => $price) {
            Space::withTrashed()->where('type', $type)->update(['price_per_hour' => $price]);
        }

        $this->command?->info('SpaceSeeder: '.count($spaces).' ruang siap.');
    }
}
