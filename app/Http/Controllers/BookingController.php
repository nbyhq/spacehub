<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Space;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $tab = in_array($request->tab, ['aktif', 'selesai', 'dibatalkan']) ? $request->tab : 'semua';

        $counts = [
            'semua' => Booking::where('user_id', $userId)->count(),
            'aktif' => Booking::where('user_id', $userId)
                ->whereIn('status', ['pending', 'approved'])
                ->whereDate('booking_date', '>=', today())->count(),
            'selesai' => Booking::where('user_id', $userId)
                ->where('status', 'approved')
                ->whereDate('booking_date', '<', today())->count(),
            'dibatalkan' => Booking::where('user_id', $userId)->where('status', 'cancelled')->count(),
        ];
        $pendingCount = Booking::where('user_id', $userId)->where('status', 'pending')->count();

        $bookings = Booking::with('space')
            ->where('user_id', $userId)
            ->when($tab === 'aktif', fn ($q) => $q->whereIn('status', ['pending', 'approved'])->whereDate('booking_date', '>=', today()))
            ->when($tab === 'selesai', fn ($q) => $q->where('status', 'approved')->whereDate('booking_date', '<', today()))
            ->when($tab === 'dibatalkan', fn ($q) => $q->where('status', 'cancelled'))
            ->when($request->filled('q'), fn ($q) => $q->whereHas('space', fn ($s) => $s->where('name', 'like', '%'.$request->q.'%')))
            ->latest('booking_date')
            ->latest('start_time')
            ->paginate(8)
            ->withQueryString();

        return view('bookings.index', compact('bookings', 'tab', 'counts', 'pendingCount'));
    }

    public function store(Request $request, Space $space)
    {
        abort_if(auth()->user()?->role === 'admin', 403, 'Admin tidak dapat membuat booking.');

        abort_unless($space->is_active, 404);

        $data = $request->validate([
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        if ($data['booking_date'] === today()->toDateString() && $data['start_time'] <= now()->format('H:i')) {
            return back()->withErrors(['start_time' => 'Jam mulai sudah lewat.'])->withInput();
        }

        // Normalisasi ke H:i:s supaya perbandingan jam konsisten di semua database
        $data['start_time'] .= ':00';
        $data['end_time'] .= ':00';

        $conflict = $space->bookings()
            ->whereDate('booking_date', $data['booking_date'])
            ->whereIn('status', ['pending', 'approved'])
            ->where('start_time', '<', $data['end_time'])
            ->where('end_time', '>', $data['start_time'])
            ->exists();

        if ($conflict) {
            return back()
                ->withErrors(['start_time' => 'Jadwal bentrok dengan booking lain di ruang ini.'])
                ->withInput();
        }

        Booking::create($data + [
            'user_id' => $request->user()->id,
            'space_id' => $space->id,
            'status' => 'pending',
        ]);

        return redirect()->route('bookings.index')
            ->with('success', 'Booking dikirim dan menunggu persetujuan admin.');
    }

    public function cancel(Request $request, Booking $booking)
    {
        abort_unless($booking->user_id === $request->user()->id, 403);
        abort_unless(in_array($booking->status, ['pending', 'approved']), 422);

        $booking->update(['status' => 'cancelled']);

        return back()->with('success', 'Booking dibatalkan.');
    }
}
