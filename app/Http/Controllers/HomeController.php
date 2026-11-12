<?php

namespace App\Http\Controllers;

use App\Models\Space;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $featured = Space::with('facilities')
            ->where('is_active', true)
            ->latest()
            ->take(3)
            ->get();

        $typeCounts = Space::where('is_active', true)
            ->selectRaw('type, count(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        return view('home', compact('featured', 'typeCounts'));
    }
}
