<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->withCount([
                'bookings',
                'bookings as active_bookings_count' => fn ($q) => $q->active(),
            ])
            ->when($request->filled('q'), function ($q) use ($request) {
                $keyword = '%'.$request->q.'%';
                $q->where(fn ($w) => $w->where('name', 'like', $keyword)->orWhere('email', 'like', $keyword));
            })
            ->when(in_array($request->role, ['admin', 'user'], true), fn ($q) => $q->where('role', $request->role))
            ->latest()
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => User::count(),
            'admin' => User::where('role', 'admin')->count(),
        ];

        return view('admin.users.index', compact('users', 'stats'));
    }

    public function show(User $user)
    {
        $bookings = $user->bookings()
            ->with('space')
            ->orderByDesc('booking_date')
            ->orderByDesc('start_time')
            ->paginate(10);

        $summary = [
            'total' => $user->bookings()->count(),
            'active' => $user->bookings()->active()->count(),
            'cancelled' => $user->bookings()->where('status', 'cancelled')->count(),
        ];

        return view('admin.users.show', compact('user', 'bookings', 'summary'));
    }

    public function updateRole(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(['user', 'admin'])],
        ]);

        // Mencegah admin menurunkan dirinya sendiri sehingga aplikasi tidak punya admin sama sekali.
        if ($user->is($request->user())) {
            return back()->with('error', 'Anda tidak bisa mengubah role akun sendiri.');
        }

        if ($user->role !== $data['role']) {
            $user->forceFill(['role' => $data['role']])->save();
        }

        return back()->with('success', $data['role'] === 'admin'
            ? "{$user->name} sekarang menjadi admin."
            : "{$user->name} sekarang menjadi pengguna biasa.");
    }
}
