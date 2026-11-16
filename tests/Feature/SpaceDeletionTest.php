<?php

namespace Tests\Feature;

use App\Exceptions\SpaceInUseException;
use App\Models\Booking;
use App\Models\Space;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SpaceDeletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Bekukan waktu: Jumat 20 Nov 2026, pukul 10:30, supaya kasus "sedang berlangsung" stabil.
        $this->travelTo(Carbon::parse('2026-11-20 10:30:00'));
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function space(): Space
    {
        return Space::create([
            'name' => 'Ruang Hapus',
            'type' => 'meeting_room',
            'capacity' => 8,
            'location' => 'Lantai 2',
            'price_per_hour' => 100000,
            'is_active' => true,
        ]);
    }

    private function book(Space $space, string $date, string $start, string $end, string $status = 'approved'): Booking
    {
        return Booking::create([
            'user_id' => User::factory()->create()->id,
            'space_id' => $space->id,
            'booking_date' => $date,
            'start_time' => $start,
            'end_time' => $end,
            'status' => $status,
        ]);
    }

    public function test_space_without_bookings_can_be_deleted(): void
    {
        $space = $this->space();

        $this->actingAs($this->admin())
            ->delete(route('admin.spaces.destroy', $space))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSoftDeleted($space);
    }

    public function test_space_with_upcoming_approved_booking_cannot_be_deleted(): void
    {
        $space = $this->space();
        $this->book($space, '2026-11-21', '10:00:00', '11:00:00', 'approved');

        $this->actingAs($this->admin())
            ->delete(route('admin.spaces.destroy', $space))
            ->assertSessionHas('error');

        $this->assertNotSoftDeleted($space);
    }

    public function test_space_with_upcoming_pending_booking_cannot_be_deleted(): void
    {
        $space = $this->space();
        $this->book($space, '2026-11-25', '09:00:00', '10:00:00', 'pending');

        $this->actingAs($this->admin())
            ->delete(route('admin.spaces.destroy', $space))
            ->assertSessionHas('error');

        $this->assertNotSoftDeleted($space);
    }

    public function test_space_in_use_right_now_cannot_be_deleted(): void
    {
        $space = $this->space();
        // Sekarang pukul 10:30, booking berlangsung 10:00 sampai 11:00
        $this->book($space, '2026-11-20', '10:00:00', '11:00:00', 'approved');

        $this->actingAs($this->admin())
            ->delete(route('admin.spaces.destroy', $space))
            ->assertSessionHas('error');

        $this->assertNotSoftDeleted($space);
    }

    public function test_space_with_booking_later_today_cannot_be_deleted(): void
    {
        $space = $this->space();
        $this->book($space, '2026-11-20', '15:00:00', '16:00:00', 'approved');

        $this->actingAs($this->admin())
            ->delete(route('admin.spaces.destroy', $space))
            ->assertSessionHas('error');

        $this->assertNotSoftDeleted($space);
    }

    public function test_finished_bookings_do_not_block_deletion(): void
    {
        $space = $this->space();
        $this->book($space, '2026-11-19', '10:00:00', '11:00:00', 'approved'); // kemarin
        $this->book($space, '2026-11-20', '08:00:00', '09:00:00', 'approved'); // hari ini, sudah selesai

        $this->actingAs($this->admin())
            ->delete(route('admin.spaces.destroy', $space))
            ->assertSessionHas('success');

        $this->assertSoftDeleted($space);
    }

    public function test_cancelled_bookings_do_not_block_deletion(): void
    {
        $space = $this->space();
        $this->book($space, '2026-11-25', '10:00:00', '11:00:00', 'cancelled');

        $this->actingAs($this->admin())
            ->delete(route('admin.spaces.destroy', $space))
            ->assertSessionHas('success');

        $this->assertSoftDeleted($space);
    }

    public function test_error_message_mentions_number_of_active_bookings(): void
    {
        $space = $this->space();
        $this->book($space, '2026-11-21', '10:00:00', '11:00:00', 'approved');
        $this->book($space, '2026-11-22', '10:00:00', '11:00:00', 'pending');

        $this->actingAs($this->admin())
            ->delete(route('admin.spaces.destroy', $space))
            ->assertSessionHas('error', fn ($message) => str_contains($message, '2 booking aktif'));
    }

    public function test_deleting_through_the_model_throws_an_exception(): void
    {
        $space = $this->space();
        $this->book($space, '2026-11-21', '10:00:00', '11:00:00', 'approved');

        $this->expectException(SpaceInUseException::class);

        $space->delete();
    }

    public function test_force_deleting_a_space_in_use_also_throws_an_exception(): void
    {
        $space = $this->space();
        $this->book($space, '2026-11-21', '10:00:00', '11:00:00', 'approved');

        $this->expectException(SpaceInUseException::class);

        $space->forceDelete();
    }

    public function test_admin_list_shows_lock_for_space_in_use(): void
    {
        $space = $this->space();
        $this->book($space, '2026-11-21', '10:00:00', '11:00:00', 'approved');

        $this->actingAs($this->admin())
            ->get(route('admin.spaces.index'))
            ->assertOk()
            ->assertSee('Tidak bisa dihapus: 1 booking aktif');
    }

    public function test_regular_user_cannot_delete_a_space(): void
    {
        $space = $this->space();

        $this->actingAs(User::factory()->create())
            ->delete(route('admin.spaces.destroy', $space))
            ->assertForbidden();

        $this->assertNotSoftDeleted($space);
    }
}
