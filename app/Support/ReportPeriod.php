<?php

namespace App\Support;

use DateTimeImmutable;

/**
 * Menentukan rentang tanggal laporan dari parameter URL (preset atau dari/sampai).
 * Murni PHP tanpa ketergantungan framework supaya mudah diuji.
 */
class ReportPeriod
{
    public const PRESETS = [
        'bulan-ini' => 'Bulan ini',
        'bulan-lalu' => 'Bulan lalu',
        '7-hari' => '7 hari terakhir',
        'semua' => 'Semua tanggal',
    ];

    /**
     * @return array{from: ?string, to: ?string, label: string, preset: ?string, valid: bool}
     */
    public static function resolve(?string $from, ?string $to, ?string $preset, DateTimeImmutable $today): array
    {
        $today = $today->setTime(0, 0);

        if ($preset !== null && isset(self::PRESETS[$preset])) {
            return self::preset($preset, $today, true);
        }

        $from = $from === '' ? null : $from;
        $to = $to === '' ? null : $to;

        // Tanpa filter apa pun: bulan ini
        if ($from === null && $to === null) {
            return self::preset('bulan-ini', $today, true);
        }

        $fromDate = $from === null ? null : self::parse($from);
        $toDate = $to === null ? null : self::parse($to);

        $invalid = ($from !== null && $fromDate === null)
            || ($to !== null && $toDate === null)
            || ($fromDate !== null && $toDate !== null && $fromDate > $toDate);

        if ($invalid) {
            return self::preset('bulan-ini', $today, false);
        }

        return [
            'from' => $fromDate?->format('Y-m-d'),
            'to' => $toDate?->format('Y-m-d'),
            'label' => self::label($fromDate, $toDate),
            'preset' => null,
            'valid' => true,
        ];
    }

    private static function preset(string $key, DateTimeImmutable $today, bool $valid): array
    {
        [$from, $to] = match ($key) {
            'bulan-lalu' => [$today->modify('first day of last month'), $today->modify('last day of last month')],
            '7-hari' => [$today->modify('-6 days'), $today],
            'semua' => [null, null],
            default => [$today->modify('first day of this month'), $today->modify('last day of this month')],
        };

        $name = self::PRESETS[$key];

        return [
            'from' => $from?->format('Y-m-d'),
            'to' => $to?->format('Y-m-d'),
            'label' => $key === 'semua' ? $name : $name.' ('.self::label($from, $to).')',
            'preset' => $key,
            'valid' => $valid,
        ];
    }

    private static function parse(string $value): ?DateTimeImmutable
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        // Tolak tanggal yang tidak nyata, misalnya 2026-02-30
        return ($date !== false && $date->format('Y-m-d') === $value) ? $date : null;
    }

    private static function label(?DateTimeImmutable $from, ?DateTimeImmutable $to): string
    {
        if ($from && $to) {
            return IndonesianDate::short($from).' – '.IndonesianDate::short($to);
        }

        return $from
            ? 'Sejak '.IndonesianDate::short($from)
            : 'Hingga '.IndonesianDate::short($to);
    }
}
