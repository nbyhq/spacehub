<?php

namespace App\Http\Controllers;

use App\Models\Space;
use Illuminate\Http\Request;

class SpaceController extends Controller
{
    public function index(Request $request)
    {
        $spaces = Space::with('facilities')
            ->where('is_active', true)
            ->when($request->filled('q'), function ($q) use ($request) {
                $keyword = '%'.$request->q.'%';
                $q->where(fn ($w) => $w->where('name', 'like', $keyword)->orWhere('location', 'like', $keyword));
            })
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->when($request->filled('capacity'), function ($q) use ($request) {
                [$min, $max] = match ($request->capacity) {
                    '1-4' => [1, 4],
                    '5-10' => [5, 10],
                    '11-30' => [11, 30],
                    '31+' => [31, null],
                    default => [null, null],
                };
                if ($min) {
                    $q->where('capacity', '>=', $min);
                }
                if ($max) {
                    $q->where('capacity', '<=', $max);
                }
            })
            ->latest()
            ->paginate(6)
            ->withQueryString();

        $typeCounts = Space::where('is_active', true)
            ->selectRaw('type, count(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        $totalActive = $typeCounts->sum();

        return view('spaces.index', compact('spaces', 'typeCounts', 'totalActive'));
    }

    public function show(Space $space)
    {
        abort_unless($space->is_active, 404);
        $space->load('facilities');

        $booked = $space->bookings()
            ->whereIn('status', ['pending', 'approved'])
            ->whereDate('booking_date', '>=', today())
            ->orderBy('booking_date')
            ->orderBy('start_time')
            ->get();

        return view('spaces.show', compact('space', 'booked'));
    }
}
