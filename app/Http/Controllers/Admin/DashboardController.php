<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Space;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $approvedThisMonth = Booking::with('space')
            ->where('status', 'approved')
            ->whereYear('booking_date', now()->year)
            ->whereMonth('booking_date', now()->month)
            ->get();

        return view('admin.dashboard', [
            'spaceActive' => Space::where('is_active', true)->count(),
            'spaceCount' => Space::count(),
            'userCount' => User::where('role', 'user')->count(),
            'todayCount' => Booking::whereDate('booking_date', today())
                ->whereIn('status', ['pending', 'approved'])
                ->count(),
            'pendingCount' => Booking::where('status', 'pending')->count(),
            'approvedMonth' => $approvedThisMonth->count(),
            'revenue' => $approvedThisMonth->sum(fn ($b) => $b->total_price),
            'pending' => Booking::with(['user', 'space'])
                ->where('status', 'pending')
                ->orderBy('booking_date')
                ->orderBy('start_time')
                ->take(4)
                ->get(),
            'recent' => Booking::with(['user', 'space'])->latest()->take(5)->get(),
        ]);
    }
}
