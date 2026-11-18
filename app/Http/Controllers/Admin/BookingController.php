<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\BookingStatusChanged;
use App\Models\Booking;
use App\Models\Space;
use Illuminate\Http\Request;
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

        return view('admin.bookings.index', compact('bookings', 'filterSpace'));
    }

    public function update(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(Booking::STATUSES)],
        ]);

        $previous = $booking->status;
        $booking->update($data);

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
