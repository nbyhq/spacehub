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
}
