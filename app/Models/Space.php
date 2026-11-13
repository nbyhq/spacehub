<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Space extends Model
{
    use SoftDeletes;

    public const TYPES = [
        'coworking' => 'Coworking',
        'meeting_room' => 'Ruang Meeting',
        'private_office' => 'Private Office',
        'event_hall' => 'Event Hall',
    ];

    protected $fillable = [
        'name', 'type', 'capacity', 'location',
        'price_per_hour', 'description', 'photo', 'is_active',
        'opens_at', 'closes_at', 'min_duration_minutes', 'max_duration_minutes',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function facilities()
    {
        return $this->belongsToMany(Facility::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /** Jam buka, format H:i. Nilai bawaan dipakai kalau kolom belum terisi. */
    public function opensAtLabel(): string
    {
        return substr($this->attributes['opens_at'] ?? '08:00:00', 0, 5);
    }

    /** Jam tutup, format H:i. */
    public function closesAtLabel(): string
    {
        return substr($this->attributes['closes_at'] ?? '22:00:00', 0, 5);
    }

    public function minDurationMinutes(): int
    {
        return (int) ($this->attributes['min_duration_minutes'] ?? 60);
    }

    public function maxDurationMinutes(): int
    {
        return (int) ($this->attributes['max_duration_minutes'] ?? 480);
    }

    /** 60 menjadi "1 jam", 90 menjadi "1,5 jam". */
    public static function durationLabel(int $minutes): string
    {
        $hours = $minutes / 60;

        return (floor($hours) == $hours ? (int) $hours : number_format($hours, 1, ',', '.')).' jam';
    }
}
