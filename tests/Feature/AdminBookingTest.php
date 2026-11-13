<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Space;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_create_booking(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $space = Space::create([
            'name' => 'Ruang Tes',
            'type' => 'meeting_room',
            'capacity' => 8,
            'location' => 'Lantai 2',
            'price_per_hour' => 75000,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('bookings.store', $space), [
                'booking_date' => now()->addDay()->toDateString(),
                'start_time' => '09:00',
                'end_time' => '10:00',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_newest_booking_is_listed_first_for_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $space = Space::create([
            'name' => 'Ruang Urutan',
            'type' => 'meeting_room',
            'capacity' => 8,
            'location' => 'Lantai 2',
            'price_per_hour' => 75000,
            'is_active' => true,
        ]);

        // Booking lama, tetapi tanggal pemakaiannya paling jauh ke depan
        $older = Booking::create([
            'user_id' => $user->id,
            'space_id' => $space->id,
            'booking_date' => now()->addDays(10)->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => 'approved',
        ]);
        $older->forceFill(['created_at' => now()->subHour()])->save();

        // Booking yang baru dibuat, dengan tanggal pemakaian lebih awal
        $newer = Booking::create([
            'user_id' => $user->id,
            'space_id' => $space->id,
            'booking_date' => now()->addDays(2)->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.bookings.index'))
            ->assertOk()
            ->assertSeeInOrder([$newer->code, $older->code]);
    }
}
