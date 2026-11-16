<?php

namespace App\Support;

use DateTimeInterface;

/**
 * Nama hari dan bulan berbahasa Indonesia, tidak bergantung pada pengaturan locale aplikasi.
 */
class IndonesianDate
{
    private const DAYS = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    private const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    /** "Jumat" */
    public static function dayName(DateTimeInterface $date): string
    {
        return self::DAYS[(int) $date->format('w')];
    }

    /** "21 Nov 2026" */
    public static function short(DateTimeInterface $date): string
    {
        return $date->format('d').' '.self::MONTHS[(int) $date->format('n') - 1].' '.$date->format('Y');
    }

    /** "Sabtu, 21 Nov 2026" */
    public static function full(DateTimeInterface $date): string
    {
        return self::dayName($date).', '.self::short($date);
    }
}
