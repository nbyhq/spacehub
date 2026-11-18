<?php

namespace Tests\Feature;

use App\Mail\BookingStatusChanged;
use App\Models\Booking;
use App\Models\Space;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BookingMailTest extends TestCase
{
    use RefreshDatabase;

    private function booking(string $status = 'pending'): Booking
    {
        $space = Space::create([
            'name' => 'Ruang Surat', 'type' => 'meeting_room', 'capacity' => 6,
            'location' => 'Lantai 3', 'price_per_hour' => 100000, 'is_active' => true,
        ]);

        return Booking::create([
            'user_id' => User::factory()->create(['name' => 'Rani Wulandari'])->id,
            'space_id' => $space->id,
            'booking_date' => now()->addDays(3)->toDateString(),
            'start_time' => '10:00', 'end_time' => '12:00', 'status' => $status,
        ]);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_approving_queues_an_email_to_the_booker(): void
    {
        Mail::fake();
        $booking = $this->booking('pending');

        $this->actingAs($this->admin())
            ->patch(route('admin.bookings.update', $booking), ['status' => 'approved'])
            ->assertSessionHas('success');

        Mail::assertQueued(BookingStatusChanged::class, fn ($mail) => $mail->hasTo($booking->user->email)
            && $mail->outcome() === 'disetujui');
    }

    public function test_rejecting_a_pending_booking_is_worded_as_rejected(): void
    {
        Mail::fake();
        $booking = $this->booking('pending');

        $this->actingAs($this->admin())
            ->patch(route('admin.bookings.update', $booking), ['status' => 'cancelled']);

        Mail::assertQueued(BookingStatusChanged::class, fn ($mail) => $mail->outcome() === 'ditolak');
    }

    public function test_cancelling_an_approved_booking_is_worded_as_cancelled_by_admin(): void
    {
        Mail::fake();
        $booking = $this->booking('approved');

        $this->actingAs($this->admin())
            ->patch(route('admin.bookings.update', $booking), ['status' => 'cancelled']);

        Mail::assertQueued(BookingStatusChanged::class, fn ($mail) => $mail->outcome() === 'dibatalkan oleh admin');
    }

    public function test_no_email_when_status_does_not_change(): void
    {
        Mail::fake();
        $booking = $this->booking('approved');

        $this->actingAs($this->admin())
            ->patch(route('admin.bookings.update', $booking), ['status' => 'approved']);

        Mail::assertNothingQueued();
    }

    public function test_no_email_when_set_back_to_pending(): void
    {
        Mail::fake();
        $booking = $this->booking('approved');

        $this->actingAs($this->admin())
            ->patch(route('admin.bookings.update', $booking), ['status' => 'pending']);

        Mail::assertNothingQueued();
    }

    public function test_user_cancelling_own_booking_sends_no_email(): void
    {
        Mail::fake();
        $booking = $this->booking('approved');

        $this->actingAs($booking->user)
            ->patch(route('bookings.cancel', $booking));

        Mail::assertNothingQueued();
    }

    public function test_email_content_has_booking_details(): void
    {
        $booking = $this->booking('approved');

        $html = (new BookingStatusChanged($booking, 'pending'))->render();

        $this->assertStringContainsString('Rani Wulandari', $html);
        $this->assertStringContainsString($booking->code, $html);
        $this->assertStringContainsString('Ruang Surat', $html);
        $this->assertStringContainsString('10:00', $html);
        $this->assertStringContainsString('disetujui', $html);
        $this->assertStringContainsString('Rp 200.000', $html);
    }

    public function test_subject_mentions_code_and_outcome(): void
    {
        $booking = $this->booking('cancelled');

        $mail = new BookingStatusChanged($booking, 'pending');

        $mail->assertHasSubject("Booking {$booking->code} ditolak");
    }
}
