<?php

namespace App\Http\Controllers;

use App\Models\Space;
use App\Support\AvailabilityGrid;
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

    public function show(Request $request, Space $space)
    {
        abort_unless($space->is_active, 404);
        $space->load('facilities');

        // Kalender menampilkan 14 hari ke depan. Tanggal di luar rentang atau tidak valid jatuh ke hari ini.
        $days = collect(range(0, 13))->map(fn ($i) => today()->addDays($i));
        $requested = (string) ($request->query('date') ?: $request->old('booking_date', ''));
        $date = $days->first(fn ($day) => $day->toDateString() === $requested) ?? $days->first();

        $activeStatuses = ['pending', 'approved'];

        $dayBookings = $space->bookings()
            ->whereDate('booking_date', $date->toDateString())
            ->whereIn('status', $activeStatuses)
            ->orderBy('start_time')
            ->get();

        $slots = AvailabilityGrid::build(
            AvailabilityGrid::toMinutes($space->opensAtLabel()),
            AvailabilityGrid::toMinutes($space->closesAtLabel()),
            $dayBookings->map(fn ($b) => [
                AvailabilityGrid::toMinutes($b->start_time),
                AvailabilityGrid::toMinutes($b->end_time),
                $b->status,
            ])->all(),
            $date->isToday() ? now()->hour * 60 + now()->minute : null,
        );

        // Jumlah booking per tanggal, untuk penanda titik di pemilih tanggal
        $dayCounts = $space->bookings()
            ->whereDate('booking_date', '>=', $days->first()->toDateString())
            ->whereDate('booking_date', '<=', $days->last()->toDateString())
            ->whereIn('status', $activeStatuses)
            ->get(['booking_date'])
            ->groupBy(fn ($b) => $b->booking_date->toDateString())
            ->map->count();

        return view('spaces.show', compact('space', 'days', 'date', 'slots', 'dayCounts'));
    }
}
