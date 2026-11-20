<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\BookingStatusChanged;
use App\Models\Booking;
use App\Models\Space;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $base = Booking::query()
            ->when($request->filled('space'), fn ($q) => $q->where('space_id', $request->space));

        $counts = (clone $base)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $stats = [
            'all' => (int) $counts->sum(),
            'pending' => (int) ($counts['pending'] ?? 0),
            'approved' => (int) ($counts['approved'] ?? 0),
            'cancelled' => (int) ($counts['cancelled'] ?? 0),
        ];

        $bookings = (clone $base)
            ->with(['user', 'space'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('q'), function ($q) use ($request) {
                $keyword = '%'.$request->q.'%';
                $q->where(function ($w) use ($keyword) {
                    $w->whereHas('user', fn ($u) => $u->where('name', 'like', $keyword)->orWhere('email', 'like', $keyword))
                        ->orWhereHas('space', fn ($s) => $s->withTrashed()->where('name', 'like', $keyword));
                });
            })
            ->latest()
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $filterSpace = $request->filled('space') ? Space::withTrashed()->find($request->space) : null;

        return view('admin.bookings.index', compact('bookings', 'filterSpace', 'stats'));
    }

    public function update(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(Booking::STATUSES)],
        ]);

        $previous = $booking->status;

        // Menghidupkan kembali booking yang dibatalkan = menempati slot lagi. Slot itu mungkin sudah diambil orang lain,
        // jadi cek bentrok dan ubah status dalam satu transaksi dengan baris ruang dikunci (sama seperti saat user memesan).
        $activating = $previous === 'cancelled' && in_array($data['status'], ['pending', 'approved'], true);

        if ($activating) {
            $clash = DB::transaction(function () use ($booking, $data) {
                Space::withTrashed()->whereKey($booking->space_id)->lockForUpdate()->firstOrFail();

                $clash = Booking::where('space_id', $booking->space_id)
                    ->whereKeyNot($booking->id)
                    ->whereDate('booking_date', $booking->booking_date)
                    ->whereIn('status', ['pending', 'approved'])
                    ->where('start_time', '<', $booking->end_time)
                    ->where('end_time', '>', $booking->start_time)
                    ->with('user')
                    ->first();

                if (! $clash) {
                    $booking->update($data);
                }

                return $clash;
            }, 3);

            if ($clash) {
                return back()->with('error', "Tidak bisa mengaktifkan kembali: jadwalnya bentrok dengan booking {$clash->code} ({$clash->user?->name}). Batalkan booking itu dulu bila perlu.");
            }
        } else {
            $booking->update($data);
        }

        // Beri tahu pemesan hanya kalau statusnya benar-benar berubah menjadi disetujui atau dibatalkan.
        if ($previous !== $booking->status && in_array($booking->status, ['approved', 'cancelled'], true)) {
            Mail::to($booking->user)->queue(new BookingStatusChanged($booking, $previous));
        }

        return back()->with('success', 'Status booking diperbarui.');
    }

    public function destroy(Booking $booking)
    {
        $booking->delete();

        return back()->with('success', 'Booking dihapus.');
    }
}
