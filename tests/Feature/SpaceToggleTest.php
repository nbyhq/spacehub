<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Space;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SpaceToggleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::parse('2026-11-20 10:30:00'));
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function space(bool $active = true): Space
    {
        return Space::create([
            'name' => 'Ruang Saklar',
            'type' => 'meeting_room',
            'capacity' => 8,
            'location' => 'Lantai 2',
            'price_per_hour' => 100000,
            'is_active' => $active,
        ]);
    }

    public function test_admin_can_deactivate_a_space(): void
    {
        $space = $this->space(true);

        $this->actingAs($this->admin())
            ->from(route('admin.spaces.index'))
            ->patch(route('admin.spaces.toggle', $space))
            ->assertRedirect(route('admin.spaces.index'))
            ->assertSessionHas('success');

        $this->assertFalse($space->fresh()->is_active);
    }

    public function test_admin_can_activate_a_space(): void
    {
        $space = $this->space(false);

        $this->actingAs($this->admin())
            ->patch(route('admin.spaces.toggle', $space))
            ->assertSessionHas('success');

        $this->assertTrue($space->fresh()->is_active);
    }

    public function test_deactivating_keeps_existing_bookings(): void
    {
        $space = $this->space(true);
        $booking = Booking::create([
            'user_id' => User::factory()->create()->id,
            'space_id' => $space->id,
            'booking_date' => '2026-11-25',
            'start_time' => '10:00',
            'end_time' => '11:00',
            'status' => 'approved',
        ]);

        $this->actingAs($this->admin())->patch(route('admin.spaces.toggle', $space));

        $this->assertFalse($space->fresh()->is_active);
        $this->assertSame('approved', $booking->fresh()->status);
    }

    public function test_inactive_space_is_hidden_from_the_public_page(): void
    {
        $space = $this->space(true);

        $this->actingAs($this->admin())->patch(route('admin.spaces.toggle', $space));

        $this->get(route('spaces.show', $space))->assertNotFound();
    }

    public function test_list_shows_toggle_and_confirm_for_space_with_active_bookings(): void
    {
        $space = $this->space(true);
        Booking::create([
            'user_id' => User::factory()->create()->id,
            'space_id' => $space->id,
            'booking_date' => '2026-11-25',
            'start_time' => '10:00',
            'end_time' => '11:00',
            'status' => 'pending',
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.spaces.index'))
            ->assertOk()
            ->assertSee(route('admin.spaces.toggle', $space), false)
            ->assertSee('masih punya 1 booking aktif');
    }

    public function test_regular_user_cannot_toggle(): void
    {
        $space = $this->space(true);

        $this->actingAs(User::factory()->create())
            ->patch(route('admin.spaces.toggle', $space))
            ->assertForbidden();

        $this->assertTrue($space->fresh()->is_active);
    }

    public function test_guest_cannot_toggle(): void
    {
        $space = $this->space(true);

        $this->patch(route('admin.spaces.toggle', $space))->assertRedirect(route('login'));
    }
}
