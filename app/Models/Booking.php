<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    public const STATUSES = ['pending', 'approved', 'cancelled'];

    protected $fillable = [
        'user_id', 'space_id', 'booking_date',
        'start_time', 'end_time', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return ['booking_date' => 'date'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function space()
    {
        return $this->belongsTo(Space::class)->withTrashed();
    }

    /** Kode booking untuk ditampilkan, misalnya #SPH-00012. */
    public function getCodeAttribute(): string
    {
        return '#SPH-'.str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }

    public function getDurationHoursAttribute(): float
    {
        return round((strtotime($this->end_time) - strtotime($this->start_time)) / 3600, 2);
    }

    public function getDurationLabelAttribute(): string
    {
        $hours = $this->duration_hours;

        return (floor($hours) == $hours ? (int) $hours : number_format($hours, 1, ',', '.')).' Jam';
    }

    /** Estimasi biaya = durasi x tarif per jam. */
    public function getTotalPriceAttribute(): int
    {
        return (int) round($this->duration_hours * ($this->space->price_per_hour ?? 0));
    }

    public function getTimeRangeAttribute(): string
    {
        return substr($this->start_time, 0, 5).'–'.substr($this->end_time, 0, 5);
    }
}
