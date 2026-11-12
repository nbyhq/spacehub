<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    protected $fillable = ['name'];

    public function spaces()
    {
        return $this->belongsToMany(Space::class);
    }

    /** Nama ikon Material Symbols berdasarkan nama fasilitas. */
    public function getIconAttribute(): string
    {
        $name = mb_strtolower(trim($this->name));

        return match (true) {
            str_contains($name, 'wifi') => 'wifi',
            $name === 'ac' => 'ac_unit',
            str_contains($name, 'proyektor') => 'videocam',
            str_contains($name, 'whiteboard') => 'draw',
            str_contains($name, 'kopi') => 'coffee',
            str_contains($name, 'parkir') => 'local_parking',
            default => 'check_circle',
        };
    }
}
