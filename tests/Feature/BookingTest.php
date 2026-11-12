<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Space;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    private function makeSpace(): Space
    {
        return Space::create([
            'name' => 'Meeting Room Test',
            'type' => 'meeting_room',
            'capacity' => 8,
            'location' => 'Lantai 2',
            'price_per_hour' => 75000,
        ]);
    }

    private function makeAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        return $admin;
    }

    private function payload(string $start, string $end): array
    {
        return [
            'booking_date' => now()->addDay()->toDateString(),
            'start_time' => $start,
            'end_time' => $end,
        ];
    }

    public function test_guest_cannot_book(): void
    {
        $space = $this->makeSpace();

        $this->post(route('bookings.store', $space), $this->payload('10:00', '11:00'))
            ->assertRedirect(route('login'));
    }

    public function test_user_can_book_a_free_slot(): void
    {
        $space = $this->makeSpace();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('bookings.store', $space), $this->payload('10:00', '11:00'))
            ->assertRedirect(route('bookings.index'));

        $this->assertDatabaseHas('bookings', [
            'user_id' => $user->id,
            'space_id' => $space->id,
            'status' => 'pending',
        ]);
    }

    public function test_overlapping_booking_is_rejected(): void
    {
        $space = $this->makeSpace();
        $first = User::factory()->create();
        $second = User::factory()->create();

        $this->actingAs($first)->post(route('bookings.store', $space), $this->payload('10:00', '12:00'));

        $this->actingAs($second)
            ->post(route('bookings.store', $space), $this->payload('11:00', '13:00'))
            ->assertSessionHasErrors('start_time');

        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_back_to_back_booking_is_allowed(): void
    {
        $space = $this->makeSpace();
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('bookings.store', $space), $this->payload('10:00', '11:00'));
        $this->actingAs($user)
            ->post(route('bookings.store', $space), $this->payload('11:00', '12:00'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('bookings', 2);
    }

    public function test_end_time_must_be_after_start_time(): void
    {
        $space = $this->makeSpace();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('bookings.store', $space), $this->payload('12:00', '11:00'))
            ->assertSessionHasErrors('end_time');
    }

    public function test_cancelled_booking_frees_the_slot(): void
    {
        $space = $this->makeSpace();
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('bookings.store', $space), $this->payload('10:00', '11:00'));
        Booking::first()->update(['status' => 'cancelled']);

        $this->actingAs($user)
            ->post(route('bookings.store', $space), $this->payload('10:00', '11:00'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('bookings', 2);
    }

    public function test_user_cannot_cancel_another_users_booking(): void
    {
        $space = $this->makeSpace();
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $booking = Booking::create([
            'user_id' => $owner->id,
            'space_id' => $space->id,
            'booking_date' => now()->addDay()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => 'pending',
        ]);

        $this->actingAs($other)
            ->patch(route('bookings.cancel', $booking))
            ->assertForbidden();
    }

    public function test_regular_user_cannot_open_admin_dashboard(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_can_open_dashboard_and_approve_booking(): void
    {
        $space = $this->makeSpace();
        $user = User::factory()->create();

        $booking = Booking::create([
            'user_id' => $user->id,
            'space_id' => $space->id,
            'booking_date' => now()->addDay()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => 'pending',
        ]);

        $admin = $this->makeAdmin();

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();

        $this->actingAs($admin)
            ->patch(route('admin.bookings.update', $booking), ['status' => 'approved'])
            ->assertRedirect();

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'approved']);
    }
}
