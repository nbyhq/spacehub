<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Space;
use App\Support\IndonesianDate;
use App\Support\ReportPeriod;
use DateTimeImmutable;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /** Batas baris agar halaman cetak tidak terlalu berat. */
    private const LIMIT = 2000;

    public function bookings(Request $request)
    {
        $period = ReportPeriod::resolve(
            $this->text($request, 'from'),
            $this->text($request, 'to'),
            $this->text($request, 'preset'),
            new DateTimeImmutable(today()->toDateString())
        );

        $status = $this->text($request, 'status');
        $status = in_array($status, Booking::STATUSES, true) ? $status : null;

        $spaceId = $this->text($request, 'space');
        $space = ($spaceId !== null && ctype_digit($spaceId))
            ? Space::withTrashed()->find((int) $spaceId)
            : null;

        $rows = Booking::query()
            ->with(['user:id,name,email', 'space'])
            ->when($period['from'], fn ($q, $date) => $q->whereDate('booking_date', '>=', $date))
            ->when($period['to'], fn ($q, $date) => $q->whereDate('booking_date', '<=', $date))
            ->when($status, fn ($q, $value) => $q->where('status', $value))
            ->when($space, fn ($q, $value) => $q->where('space_id', $value->id))
            ->orderBy('booking_date')
            ->orderBy('start_time')
            ->orderBy('id')
            ->limit(self::LIMIT + 1)
            ->get();

        $truncated = $rows->count() > self::LIMIT;
        $bookings = $rows->take(self::LIMIT);

        $summary = [
            'total' => $bookings->count(),
            'pending' => $bookings->where('status', 'pending')->count(),
            'approved' => $bookings->where('status', 'approved')->count(),
            'cancelled' => $bookings->where('status', 'cancelled')->count(),
            'revenue' => (int) $bookings->where('status', 'approved')->sum(fn ($b) => $b->total_price),
        ];

        $bySpace = $bookings->groupBy('space_id')->map(function ($items) {
            $approved = $items->where('status', 'approved');

            return [
                'name' => $items->first()->space?->name ?? '—',
                'total' => $items->count(),
                'approved' => $approved->count(),
                'revenue' => (int) $approved->sum(fn ($b) => $b->total_price),
            ];
        })->sortByDesc('revenue')->values();

        return view('admin.reports.bookings', [
            'period' => $period,
            'status' => $status,
            'space' => $space,
            'spaces' => Space::withTrashed()->orderBy('name')->get(['id', 'name']),
            'bookings' => $bookings,
            'summary' => $summary,
            'bySpace' => $bySpace,
            'truncated' => $truncated,
            'limit' => self::LIMIT,
            'printedAt' => IndonesianDate::full(now()).', '.now()->format('H:i'),
        ]);
    }

    /** Ambil parameter query hanya jika berupa teks (abaikan bentuk array seperti ?from[]=x). */
    private function text(Request $request, string $key): ?string
    {
        $value = $request->query($key);

        return is_string($value) ? $value : null;
    }
}
