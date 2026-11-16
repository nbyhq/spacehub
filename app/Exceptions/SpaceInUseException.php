<?php

namespace App\Exceptions;

use App\Models\Space;
use RuntimeException;

/**
 * Dilempar saat ruang akan dihapus padahal masih punya booking aktif
 * (menunggu atau disetujui, dan belum selesai).
 */
class SpaceInUseException extends RuntimeException
{
    public static function forSpace(Space $space, int $activeBookings): self
    {
        return new self(sprintf(
            'Ruang "%s" tidak dapat dihapus karena masih memiliki %d booking aktif (menunggu atau disetujui). '
            .'Batalkan booking tersebut atau tunggu sampai selesai. Kalau hanya ingin menyembunyikan ruang, nonaktifkan saja.',
            $space->name,
            $activeBookings
        ));
    }
}
