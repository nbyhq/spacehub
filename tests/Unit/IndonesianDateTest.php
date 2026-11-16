<?php

namespace Tests\Unit;

use App\Support\IndonesianDate;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class IndonesianDateTest extends TestCase
{
    public function test_day_names_are_in_indonesian(): void
    {
        $this->assertSame('Jumat', IndonesianDate::dayName(new DateTimeImmutable('2026-11-20')));
        $this->assertSame('Sabtu', IndonesianDate::dayName(new DateTimeImmutable('2026-11-21')));
        $this->assertSame('Minggu', IndonesianDate::dayName(new DateTimeImmutable('2026-11-22')));
        $this->assertSame('Senin', IndonesianDate::dayName(new DateTimeImmutable('2026-11-23')));
        $this->assertSame('Selasa', IndonesianDate::dayName(new DateTimeImmutable('2026-11-24')));
        $this->assertSame('Rabu', IndonesianDate::dayName(new DateTimeImmutable('2026-11-25')));
        $this->assertSame('Kamis', IndonesianDate::dayName(new DateTimeImmutable('2026-11-26')));
    }

    public function test_short_format_uses_indonesian_month_abbreviations(): void
    {
        $this->assertSame('05 Jan 2026', IndonesianDate::short(new DateTimeImmutable('2026-01-05')));
        $this->assertSame('17 Mei 2026', IndonesianDate::short(new DateTimeImmutable('2026-05-17')));
        $this->assertSame('17 Agu 2026', IndonesianDate::short(new DateTimeImmutable('2026-08-17')));
        $this->assertSame('03 Des 2026', IndonesianDate::short(new DateTimeImmutable('2026-12-03')));
    }

    public function test_full_format_combines_day_name_and_short_date(): void
    {
        $this->assertSame('Sabtu, 21 Nov 2026', IndonesianDate::full(new DateTimeImmutable('2026-11-21')));
        $this->assertSame('Kamis, 31 Des 2026', IndonesianDate::full(new DateTimeImmutable('2026-12-31')));
    }

    public function test_time_of_day_does_not_change_the_result(): void
    {
        $this->assertSame('Sabtu, 21 Nov 2026', IndonesianDate::full(new DateTimeImmutable('2026-11-21 23:59:59')));
    }
}
