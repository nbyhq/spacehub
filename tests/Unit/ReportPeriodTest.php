<?php

namespace Tests\Unit;

use App\Support\ReportPeriod;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class ReportPeriodTest extends TestCase
{
    private function today(string $date = '2026-11-18'): DateTimeImmutable
    {
        return new DateTimeImmutable($date);
    }

    public function test_default_is_the_current_month(): void
    {
        $period = ReportPeriod::resolve(null, null, null, $this->today());

        $this->assertSame('2026-11-01', $period['from']);
        $this->assertSame('2026-11-30', $period['to']);
        $this->assertSame('bulan-ini', $period['preset']);
        $this->assertTrue($period['valid']);
        $this->assertSame('Bulan ini (01 Nov 2026 – 30 Nov 2026)', $period['label']);
    }

    public function test_current_month_handles_leap_february(): void
    {
        $period = ReportPeriod::resolve(null, null, 'bulan-ini', $this->today('2028-02-10'));

        $this->assertSame('2028-02-01', $period['from']);
        $this->assertSame('2028-02-29', $period['to']);
    }

    public function test_last_month_crosses_the_year_boundary(): void
    {
        $period = ReportPeriod::resolve(null, null, 'bulan-lalu', $this->today('2026-01-15'));

        $this->assertSame('2025-12-01', $period['from']);
        $this->assertSame('2025-12-31', $period['to']);
        $this->assertSame('bulan-lalu', $period['preset']);
    }

    public function test_last_month_from_the_31st_does_not_overflow(): void
    {
        $period = ReportPeriod::resolve(null, null, 'bulan-lalu', $this->today('2026-03-31'));

        $this->assertSame('2026-02-01', $period['from']);
        $this->assertSame('2026-02-28', $period['to']);
    }

    public function test_last_seven_days_includes_today(): void
    {
        $period = ReportPeriod::resolve(null, null, '7-hari', $this->today());

        $this->assertSame('2026-11-12', $period['from']);
        $this->assertSame('2026-11-18', $period['to']);
    }

    public function test_all_dates_has_no_bounds(): void
    {
        $period = ReportPeriod::resolve(null, null, 'semua', $this->today());

        $this->assertNull($period['from']);
        $this->assertNull($period['to']);
        $this->assertSame('Semua tanggal', $period['label']);
        $this->assertTrue($period['valid']);
    }

    public function test_preset_wins_over_explicit_dates(): void
    {
        $period = ReportPeriod::resolve('2026-01-01', '2026-01-31', '7-hari', $this->today());

        $this->assertSame('7-hari', $period['preset']);
        $this->assertSame('2026-11-12', $period['from']);
    }

    public function test_custom_range_is_accepted(): void
    {
        $period = ReportPeriod::resolve('2026-11-05', '2026-11-20', null, $this->today());

        $this->assertSame('2026-11-05', $period['from']);
        $this->assertSame('2026-11-20', $period['to']);
        $this->assertNull($period['preset']);
        $this->assertTrue($period['valid']);
        $this->assertSame('05 Nov 2026 – 20 Nov 2026', $period['label']);
    }

    public function test_single_day_range_is_valid(): void
    {
        $period = ReportPeriod::resolve('2026-11-05', '2026-11-05', null, $this->today());

        $this->assertTrue($period['valid']);
        $this->assertSame('2026-11-05', $period['from']);
        $this->assertSame('2026-11-05', $period['to']);
    }

    public function test_open_ended_ranges_are_labelled(): void
    {
        $from = ReportPeriod::resolve('2026-11-05', null, null, $this->today());
        $to = ReportPeriod::resolve(null, '2026-11-20', null, $this->today());

        $this->assertNull($from['to']);
        $this->assertSame('Sejak 05 Nov 2026', $from['label']);
        $this->assertNull($to['from']);
        $this->assertSame('Hingga 20 Nov 2026', $to['label']);
        $this->assertTrue($from['valid']);
        $this->assertTrue($to['valid']);
    }

    public function test_reversed_range_falls_back_to_current_month_as_invalid(): void
    {
        $period = ReportPeriod::resolve('2026-11-30', '2026-11-01', null, $this->today());

        $this->assertFalse($period['valid']);
        $this->assertSame('bulan-ini', $period['preset']);
        $this->assertSame('2026-11-01', $period['from']);
    }

    public function test_garbage_and_impossible_dates_are_invalid(): void
    {
        $this->assertFalse(ReportPeriod::resolve('bukan-tanggal', null, null, $this->today())['valid']);
        $this->assertFalse(ReportPeriod::resolve(null, '2026-13-01', null, $this->today())['valid']);
        $this->assertFalse(ReportPeriod::resolve('2026-02-30', null, null, $this->today())['valid']);
        $this->assertFalse(ReportPeriod::resolve('18-11-2026', null, null, $this->today())['valid']);
    }

    public function test_empty_strings_and_unknown_presets_mean_default(): void
    {
        $empty = ReportPeriod::resolve('', '', null, $this->today());
        $unknown = ReportPeriod::resolve(null, null, 'tahun-depan', $this->today());

        $this->assertTrue($empty['valid']);
        $this->assertSame('bulan-ini', $empty['preset']);
        $this->assertTrue($unknown['valid']);
        $this->assertSame('bulan-ini', $unknown['preset']);
    }

    public function test_time_of_day_does_not_change_the_result(): void
    {
        $period = ReportPeriod::resolve(null, null, '7-hari', $this->today('2026-11-18 23:59:59'));

        $this->assertSame('2026-11-12', $period['from']);
        $this->assertSame('2026-11-18', $period['to']);
    }
}
