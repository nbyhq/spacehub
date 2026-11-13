<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Space;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperatingHoursTest extends TestCase
{
    use RefreshDatabase;

    /** Buka 09:00-17:00, durasi 1 sampai 3 jam. */
    private function makeSpace(): Space
    {
        return Space::create([
            'name' => 'Ruang Jam Terbatas',
            'type' => 'meeting_room',
            'capacity' => 8,
            'location' => 'Lantai 2',
            'price_per_hour' => 75000,
            'opens_at' => '09:00',
            'closes_at' => '17:00',
            'min_duration_minutes' => 60,
            'max_duration_minutes' => 180,
        ]);
    }

    private function payload(string $start, string $end): array
    {
        return [
            'booking_date' => now()->addDay()->toDateString(),
            'start_time' => $start,
            'end_time' => $end,
        ];
    }

    private function makeAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        return $admin;
    }

    public function test_booking_inside_operating_hours_is_accepted(): void
    {
        $space = $this->makeSpace();

        $this->actingAs(User::factory()->create())
            ->post(route('bookings.store', $space), $this->payload('09:00', '11:00'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_booking_may_end_exactly_at_closing_time(): void
    {
        $space = $this->makeSpace();

        $this->actingAs(User::factory()->create())
            ->post(route('bookings.store', $space), $this->payload('15:00', '17:00'))
            ->assertSessionHasNoErrors();
    }

    public function test_booking_before_opening_is_rejected(): void
    {
        $space = $this->makeSpace();

        $this->actingAs(User::factory()->create())
            ->post(route('bookings.store', $space), $this->payload('08:00', '10:00'))
            ->assertSessionHasErrors('start_time');

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_booking_after_closing_is_rejected(): void
    {
        $space = $this->makeSpace();

        $this->actingAs(User::factory()->create())
            ->post(route('bookings.store', $space), $this->payload('16:00', '18:00'))
            ->assertSessionHasErrors('start_time');

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_booking_shorter_than_minimum_is_rejected(): void
    {
        $space = $this->makeSpace();

        $this->actingAs(User::factory()->create())
            ->post(route('bookings.store', $space), $this->payload('10:00', '10:30'))
            ->assertSessionHasErrors('end_time');
    }

    public function test_booking_longer_than_maximum_is_rejected(): void
    {
        $space = $this->makeSpace();

        $this->actingAs(User::factory()->create())
            ->post(route('bookings.store', $space), $this->payload('09:00', '13:00'))
            ->assertSessionHasErrors('end_time');
    }

    public function test_default_hours_apply_to_spaces_without_explicit_hours(): void
    {
        $space = Space::create([
            'name' => 'Ruang Bawaan',
            'type' => 'coworking',
            'capacity' => 4,
            'location' => 'Lantai 1',
            'price_per_hour' => 25000,
        ]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('bookings.store', $space), $this->payload('02:00', '04:00'))
            ->assertSessionHasErrors('start_time');

        $this->actingAs($user)
            ->post(route('bookings.store', $space), $this->payload('10:00', '12:00'))
            ->assertSessionHasNoErrors();
    }

    public function test_space_page_shows_operating_hours_and_booked_slot(): void
    {
        $space = $this->makeSpace();
        $tomorrow = now()->addDay()->toDateString();

        Booking::create([
            'user_id' => User::factory()->create()->id,
            'space_id' => $space->id,
            'booking_date' => $tomorrow,
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => 'approved',
        ]);

        $this->get(route('spaces.show', [$space, 'date' => $tomorrow]))
            ->assertOk()
            ->assertSee('Buka 09:00–17:00')
            ->assertSee('Terisi')
            ->assertSee('Kosong');
    }

    public function test_space_page_falls_back_to_today_for_invalid_date(): void
    {
        $space = $this->makeSpace();

        $this->get(route('spaces.show', [$space, 'date' => '2000-01-01']))->assertOk();
        $this->get(route('spaces.show', [$space, 'date' => 'bukan-tanggal']))->assertOk();
    }

    public function test_admin_can_update_operating_hours(): void
    {
        $space = $this->makeSpace();

        $this->actingAs($this->makeAdmin())
            ->put(route('admin.spaces.update', $space), [
                'name' => $space->name,
                'type' => $space->type,
                'capacity' => $space->capacity,
                'location' => $space->location,
                'price_per_hour' => $space->price_per_hour,
                'opens_at' => '07:30',
                'closes_at' => '20:00',
                'min_hours' => 1.5,
                'max_hours' => 6,
            ])
            ->assertRedirect(route('admin.spaces.index'));

        $this->assertDatabaseHas('spaces', [
            'id' => $space->id,
            'min_duration_minutes' => 90,
            'max_duration_minutes' => 360,
        ]);
        $this->assertSame('07:30', $space->fresh()->opensAtLabel());
        $this->assertSame('20:00', $space->fresh()->closesAtLabel());
    }

    public function test_admin_cannot_set_closing_time_before_opening_time(): void
    {
        $space = $this->makeSpace();

        $this->actingAs($this->makeAdmin())
            ->put(route('admin.spaces.update', $space), [
                'name' => $space->name,
                'type' => $space->type,
                'capacity' => $space->capacity,
                'location' => $space->location,
                'price_per_hour' => $space->price_per_hour,
                'opens_at' => '18:00',
                'closes_at' => '09:00',
                'min_hours' => 1,
                'max_hours' => 4,
            ])
            ->assertSessionHasErrors('closes_at');
    }
}
