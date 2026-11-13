<?php

namespace App\Support;

/**
 * Membangun slot per jam untuk satu hari dari jam operasional dan booking yang ada.
 * Semua waktu dihitung dalam menit sejak 00:00, tanpa ketergantungan ke framework.
 */
final class AvailabilityGrid
{
    /** "08:00" atau "08:00:00" menjadi menit sejak tengah malam. */
    public static function toMinutes(string $time): int
    {
        [$hour, $minute] = array_map('intval', explode(':', $time) + [0, 0]);

        return $hour * 60 + $minute;
    }

    /** Menit sejak tengah malam menjadi "H:i". */
    public static function format(int $minutes): string
    {
        return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
    }

    /**
     * @param  int  $openMin  jam buka (menit)
     * @param  int  $closeMin  jam tutup (menit)
     * @param  array<int, array{0: int, 1: int, 2: string}>  $bookings  [mulai, selesai, status]
     * @param  int|null  $nowMin  menit saat ini kalau tanggalnya hari ini, selain itu null
     * @return list<array{start: string, end: string, startMin: int, endMin: int, state: string, pending: bool, ranges: string}>
     *                                                                                                                              state: free | partial | taken | past
     */
    public static function build(int $openMin, int $closeMin, array $bookings, ?int $nowMin = null, int $step = 60): array
    {
        $slots = [];

        for ($start = $openMin; $start < $closeMin; $start += $step) {
            $end = min($start + $step, $closeMin);
            $length = $end - $start;
            $busy = 0;
            $overlapping = [];

            foreach ($bookings as [$bookedStart, $bookedEnd, $status]) {
                $overlap = min($end, $bookedEnd) - max($start, $bookedStart);

                if ($overlap > 0) {
                    $busy += $overlap;
                    $overlapping[] = [$bookedStart, $bookedEnd, $status];
                }
            }

            if ($nowMin !== null && $start <= $nowMin) {
                $state = 'past';
            } elseif ($busy >= $length) {
                $state = 'taken';
            } elseif ($busy > 0) {
                $state = 'partial';
            } else {
                $state = 'free';
            }

            $slots[] = [
                'start' => self::format($start),
                'end' => self::format($end),
                'startMin' => $start,
                'endMin' => $end,
                'state' => $state,
                // true kalau semua booking di slot ini masih menunggu persetujuan
                'pending' => $overlapping !== [] && count(array_filter($overlapping, fn ($b) => $b[2] === 'pending')) === count($overlapping),
                'ranges' => implode(', ', array_map(fn ($b) => self::format($b[0]).'–'.self::format($b[1]), $overlapping)),
            ];
        }

        return $slots;
    }
}
