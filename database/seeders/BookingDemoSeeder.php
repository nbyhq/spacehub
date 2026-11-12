<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Space;
use App\Models\User;
use Illuminate\Database\Seeder;

class BookingDemoSeeder extends Seeder
{
    public function run(): void
    {
        $spaces = Space::where('is_active', true)->get();

        if ($spaces->isEmpty()) {
            $this->command?->warn('Belum ada ruang aktif. Jalankan SpaceSeeder dulu.');

            return;
        }

        $people = [
            ['Budi Santoso', 'budi.santoso@spacehub.test'],
            ['Siti Rahmawati', 'siti.rahmawati@spacehub.test'],
            ['Andi Pratama', 'andi.pratama@spacehub.test'],
            ['Dewi Lestari', 'dewi.lestari@spacehub.test'],
            ['Rizky Maulana', 'rizky.maulana@spacehub.test'],
            ['Nadia Putri', 'nadia.putri@spacehub.test'],
            ['Fajar Nugroho', 'fajar.nugroho@spacehub.test'],
            ['Maya Anggraini', 'maya.anggraini@spacehub.test'],
        ];

        $notes = [
            null, null, null,
            'Butuh kabel HDMI tambahan',
            'Mohon disiapkan 10 kursi',
            'Untuk rapat mingguan tim',
            'Sesi workshop internal',
            'Presentasi ke klien',
        ];

        // Seed tetap supaya hasilnya sama setiap kali dijalankan
        mt_srand(2026);
        $created = 0;

        foreach ($people as [$name, $email]) {
            $user = User::firstOrNew(['email' => $email]);

            if (! $user->exists) {
                $user->fill(['name' => $name, 'password' => 'password']);
                $user->save();
            }

            $attempts = mt_rand(5, 8);

            for ($i = 0; $i < $attempts; $i++) {
                $space = $spaces[mt_rand(0, $spaces->count() - 1)];
                $offset = mt_rand(-21, 21);
                $date = today()->addDays($offset)->toDateString();
                $startHour = mt_rand(8, 17);
                $start = sprintf('%02d:00:00', $startHour);
                $end = sprintf('%02d:00:00', $startHour + mt_rand(1, 4));
                $note = $notes[mt_rand(0, count($notes) - 1)];
                $roll = mt_rand(1, 100);

                if ($offset < 0) {
                    $status = $roll <= 80 ? 'approved' : 'cancelled';
                } elseif ($offset === 0) {
                    $status = 'approved';
                } else {
                    $status = $roll <= 45 ? 'pending' : ($roll <= 85 ? 'approved' : 'cancelled');
                }

                $exists = Booking::where('user_id', $user->id)
                    ->where('space_id', $space->id)
                    ->whereDate('booking_date', $date)
                    ->where('start_time', $start)
                    ->exists();

                if ($exists) {
                    continue;
                }

                if ($status !== 'cancelled') {
                    $conflict = Booking::where('space_id', $space->id)
                        ->whereDate('booking_date', $date)
                        ->whereIn('status', ['pending', 'approved'])
                        ->where('start_time', '<', $end)
                        ->where('end_time', '>', $start)
                        ->exists();

                    if ($conflict) {
                        continue;
                    }
                }

                Booking::create([
                    'user_id' => $user->id,
                    'space_id' => $space->id,
                    'booking_date' => $date,
                    'start_time' => $start,
                    'end_time' => $end,
                    'status' => $status,
                    'notes' => $note,
                ]);
                $created++;
            }
        }

        $this->command?->info('BookingDemoSeeder: '.count($people).' user, '.$created.' booking baru.');
    }
}
