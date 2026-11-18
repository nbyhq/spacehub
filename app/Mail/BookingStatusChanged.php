<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Email ke pemesan saat admin menyetujui atau menolak/membatalkan booking. Dikirim lewat antrean. */
class BookingStatusChanged extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking, public string $previousStatus)
    {
    }

    /** Booking yang tadinya menunggu lalu dibatalkan admin disebut "ditolak". */
    public function outcome(): string
    {
        return match (true) {
            $this->booking->status === 'approved' => 'disetujui',
            $this->previousStatus === 'pending' => 'ditolak',
            default => 'dibatalkan oleh admin',
        };
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Booking {$this->booking->code} {$this->outcome()}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.booking-status',
            with: ['booking' => $this->booking, 'space' => $this->booking->space, 'outcome' => $this->outcome()],
        );
    }
}
