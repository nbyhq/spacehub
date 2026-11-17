<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Space;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AdminUserTest extends TestCase
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

    private function book(User $user, string $date, string $status = 'approved'): Booking
    {
        $space = Space::firstOrCreate(['name' => 'Ruang Uji'], [
            'type' => 'meeting_room', 'capacity' => 6, 'location' => 'Lt 1',
            'price_per_hour' => 50000, 'is_active' => true,
        ]);

        return Booking::create([
            'user_id' => $user->id, 'space_id' => $space->id, 'booking_date' => $date,
            'start_time' => '10:00', 'end_time' => '12:00', 'status' => $status,
        ]);
    }

    public function test_admin_can_list_users_with_booking_counts(): void
    {
        $member = User::factory()->create(['name' => 'Budi Santoso', 'role' => 'user']);
        $this->book($member, '2026-11-25');
        $this->book($member, '2026-11-01');

        $this->actingAs($this->admin())
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Budi Santoso')
            ->assertSee('(1 aktif)');
    }

    public function test_search_and_role_filter(): void
    {
        User::factory()->create(['name' => 'Citra Lestari', 'role' => 'user']);
        User::factory()->create(['name' => 'Dewi Anggraini', 'role' => 'user']);

        $this->actingAs($this->admin())
            ->get(route('admin.users.index', ['q' => 'Citra']))
            ->assertSee('Citra Lestari')
            ->assertDontSee('Dewi Anggraini');

        $this->actingAs($this->admin())
            ->get(route('admin.users.index', ['role' => 'admin']))
            ->assertDontSee('Citra Lestari');
    }

    public function test_detail_shows_booking_history(): void
    {
        $member = User::factory()->create(['name' => 'Eka Putra', 'role' => 'user']);
        $booking = $this->book($member, '2026-11-25');

        $this->actingAs($this->admin())
            ->get(route('admin.users.show', $member))
            ->assertOk()
            ->assertSee('Eka Putra')
            ->assertSee($booking->code)
            ->assertSee('Ruang Uji');
    }

    public function test_detail_for_user_without_bookings(): void
    {
        $member = User::factory()->create(['role' => 'user']);

        $this->actingAs($this->admin())
            ->get(route('admin.users.show', $member))
            ->assertOk()
            ->assertSee('belum pernah booking');
    }

    public function test_admin_can_promote_and_demote_another_user(): void
    {
        $admin = $this->admin();
        $member = User::factory()->create(['role' => 'user']);

        $this->actingAs($admin)
            ->patch(route('admin.users.role', $member), ['role' => 'admin'])
            ->assertSessionHas('success');
        $this->assertSame('admin', $member->fresh()->role);

        $this->actingAs($admin)
            ->patch(route('admin.users.role', $member), ['role' => 'user'])
            ->assertSessionHas('success');
        $this->assertSame('user', $member->fresh()->role);
    }

    public function test_admin_cannot_change_own_role(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->patch(route('admin.users.role', $admin), ['role' => 'user'])
            ->assertSessionHas('error');

        $this->assertSame('admin', $admin->fresh()->role);
    }

    public function test_invalid_role_is_rejected(): void
    {
        $member = User::factory()->create(['role' => 'user']);

        $this->actingAs($this->admin())
            ->patch(route('admin.users.role', $member), ['role' => 'superadmin'])
            ->assertSessionHasErrors('role');

        $this->assertSame('user', $member->fresh()->role);
    }

    public function test_regular_user_cannot_access_user_pages(): void
    {
        $member = User::factory()->create(['role' => 'user']);
        $other = User::factory()->create(['role' => 'user']);

        $this->actingAs($member)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($member)->get(route('admin.users.show', $other))->assertForbidden();
        $this->actingAs($member)->patch(route('admin.users.role', $other), ['role' => 'admin'])->assertForbidden();

        $this->assertSame('user', $other->fresh()->role);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.users.index'))->assertRedirect(route('login'));
    }
}
