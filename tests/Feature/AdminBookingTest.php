<?php

namespace Tests\Feature;

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
}
