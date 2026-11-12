<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Space;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $bookings = Booking::with(['user', 'space'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('space'), fn ($q) => $q->where('space_id', $request->space))
            ->latest('booking_date')
            ->latest('start_time')
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

        $booking->update($data);

        return back()->with('success', 'Status booking diperbarui.');
    }

    public function destroy(Booking $booking)
    {
        $booking->delete();

        return back()->with('success', 'Booking dihapus.');
    }
}
