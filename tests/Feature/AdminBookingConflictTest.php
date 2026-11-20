<?php

namespace Tests\Feature;

use App\Mail\BookingStatusChanged;
use App\Models\Booking;
use App\Models\Space;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminBookingConflictTest extends TestCase
{
    use RefreshDatabase;

    private function space(string $name = 'Ruang Konflik'): Space
    {
        return Space::create([
            'name' => $name, 'type' => 'meeting_room', 'capacity' => 8,
            'location' => 'Lantai 2', 'price_per_hour' => 100000, 'is_active' => true,
        ]);
    }

    private function book(Space $space, string $status, string $start = '10:00', string $end = '12:00', ?string $date = null): Booking
    {
        return Booking::create([
            'user_id' => User::factory()->create()->id,
            'space_id' => $space->id,
            'booking_date' => $date ?? now()->addDays(2)->toDateString(),
            'start_time' => $start,
            'end_time' => $end,
            'status' => $status,
        ]);
    }

    private function setStatus(Booking $booking, string $status)
    {
        return $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->patch(route('admin.bookings.update', $booking), ['status' => $status]);
    }

    public function test_cancelled_booking_cannot_be_reactivated_when_slot_was_taken(): void
    {
        Mail::fake();
        $space = $this->space();
        $old = $this->book($space, 'cancelled');
        $taker = $this->book($space, 'approved');

        $this->setStatus($old, 'approved')->assertSessionHas('error');

        $this->assertSame('cancelled', $old->fresh()->status);
        Mail::assertNothingQueued();
    }

    public function test_error_message_names_the_conflicting_booking(): void
    {
        $space = $this->space();
        $old = $this->book($space, 'cancelled');
        $taker = $this->book($space, 'pending');

        $response = $this->setStatus($old, 'pending');

        $this->assertStringContainsString($taker->code, session('error'));
        $this->assertStringContainsString($taker->user->name, session('error'));
    }

    public function test_partial_overlap_also_blocks_reactivation(): void
    {
        $space = $this->space();
        $old = $this->book($space, 'cancelled', '10:00', '12:00');
        $this->book($space, 'approved', '11:00', '13:00');

        $this->setStatus($old, 'approved')->assertSessionHas('error');
        $this->assertSame('cancelled', $old->fresh()->status);
    }

    public function test_cancelled_booking_can_be_reactivated_when_slot_is_free(): void
    {
        Mail::fake();
        $space = $this->space();
        $old = $this->book($space, 'cancelled');

        $this->setStatus($old, 'approved')->assertSessionHas('success');

        $this->assertSame('approved', $old->fresh()->status);
        Mail::assertQueued(BookingStatusChanged::class);
    }

    public function test_back_to_back_booking_does_not_block_reactivation(): void
    {
        $space = $this->space();
        $old = $this->book($space, 'cancelled', '10:00', '12:00');
        $this->book($space, 'approved', '12:00', '14:00');
        $this->book($space, 'approved', '08:00', '10:00');

        $this->setStatus($old, 'approved')->assertSessionHas('success');
        $this->assertSame('approved', $old->fresh()->status);
    }

    public function test_other_cancelled_booking_does_not_block_reactivation(): void
    {
        $space = $this->space();
        $old = $this->book($space, 'cancelled');
        $this->book($space, 'cancelled');

        $this->setStatus($old, 'approved')->assertSessionHas('success');
    }

    public function test_other_space_or_other_day_does_not_block_reactivation(): void
    {
        $space = $this->space();
        $old = $this->book($space, 'cancelled');
        $this->book($this->space('Ruang Lain'), 'approved');
        $this->book($space, 'approved', '10:00', '12:00', now()->addDays(5)->toDateString());

        $this->setStatus($old, 'approved')->assertSessionHas('success');
    }

    public function test_approving_a_pending_booking_still_works(): void
    {
        $space = $this->space();
        $pending = $this->book($space, 'pending');

        $this->setStatus($pending, 'approved')->assertSessionHas('success');
        $this->assertSame('approved', $pending->fresh()->status);
    }

    public function test_cancelling_never_needs_a_conflict_check(): void
    {
        $space = $this->space();
        $approved = $this->book($space, 'approved');

        $this->setStatus($approved, 'cancelled')->assertSessionHas('success');
        $this->assertSame('cancelled', $approved->fresh()->status);
    }

    public function test_reactivation_works_for_a_soft_deleted_space_without_conflicts(): void
    {
        $space = $this->space();
        $old = $this->book($space, 'cancelled');
        $space->delete();

        $this->setStatus($old, 'approved')->assertSessionHas('success');
        $this->assertSame('approved', $old->fresh()->status);
    }
}
