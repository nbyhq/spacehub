@extends('layouts.admin')

@section('title', $user->name.' - SpaceHub')

@section('content')
    <a href="{{ route('admin.users.index') }}" class="mb-space-md inline-flex items-center gap-1 text-label-md font-medium text-primary hover:underline">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali ke daftar pengguna
    </a>

    <div class="card mb-space-lg flex flex-col gap-space-md p-space-md sm:p-space-lg md:flex-row md:items-center md:justify-between">
        <div class="flex min-w-0 items-center gap-4">
            <span class="flex size-16 shrink-0 items-center justify-center rounded-full bg-primary-fixed text-headline-md font-semibold text-on-primary-fixed">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="truncate text-headline-lg font-bold tracking-tight">{{ $user->name }}</h1>
                    @include('partials.role-chip', ['role' => $user->role])
                </div>
                <p class="truncate text-body-md text-on-surface-variant">{{ $user->email }}</p>
                <p class="text-body-sm text-on-surface-variant">Terdaftar {{ $user->created_at ? \App\Support\IndonesianDate::short($user->created_at) : '-' }}</p>
            </div>
        </div>
        <div>@include('partials.role-form', ['user' => $user])</div>
    </div>

    <div class="mb-space-lg grid grid-cols-3 gap-3">
        <div class="card p-3 sm:p-space-md">
            <span class="block text-headline-md font-semibold leading-none">{{ $summary['total'] }}</span>
            <span class="text-body-sm text-on-surface-variant">Total booking</span>
        </div>
        <div class="card p-3 sm:p-space-md">
            <span class="block text-headline-md font-semibold leading-none">{{ $summary['active'] }}</span>
            <span class="text-body-sm text-on-surface-variant">Aktif</span>
        </div>
        <div class="card p-3 sm:p-space-md">
            <span class="block text-headline-md font-semibold leading-none">{{ $summary['cancelled'] }}</span>
            <span class="text-body-sm text-on-surface-variant">Dibatalkan</span>
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="border-b border-outline-variant/40 px-space-md py-space-md">
            <h2 class="text-title-card font-semibold">Riwayat booking</h2>
        </div>

        <div class="divide-y divide-outline-variant/30 lg:hidden">
            @forelse ($bookings as $booking)
                <div class="space-y-1 p-space-md">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="font-mono text-label-sm font-semibold text-primary">{{ $booking->code }}</div>
                            <div class="truncate font-medium">{{ $booking->space->name }}</div>
                        </div>
                        @include('partials.status', ['status' => $booking->status])
                    </div>
                    <div class="text-body-md">{{ \App\Support\IndonesianDate::short($booking->booking_date) }}, {{ $booking->time_range }}</div>
                    <div class="text-body-sm text-on-surface-variant">{{ $booking->duration_label }} &middot; Rp {{ number_format($booking->total_price, 0, ',', '.') }}</div>
                </div>
            @empty
                <div class="px-space-md py-12 text-center text-on-surface-variant">Pengguna ini belum pernah booking.</div>
            @endforelse
        </div>

        <div class="hidden overflow-x-auto lg:block">
            <table class="w-full text-body-md">
                <thead class="bg-surface-container-low">
                    <tr>
                        <th class="th">Kode</th>
                        <th class="th">Ruang</th>
                        <th class="th">Tanggal &amp; Jam</th>
                        <th class="th">Biaya</th>
                        <th class="th">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30">
                    @forelse ($bookings as $booking)
                        <tr>
                            <td class="td whitespace-nowrap font-mono text-label-sm font-semibold text-primary">{{ $booking->code }}</td>
                            <td class="td font-medium">{{ $booking->space->name }}</td>
                            <td class="td whitespace-nowrap">{{ \App\Support\IndonesianDate::short($booking->booking_date) }}, {{ $booking->time_range }}
                                <span class="text-body-sm text-on-surface-variant">({{ $booking->duration_label }})</span></td>
                            <td class="td whitespace-nowrap">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</td>
                            <td class="td">@include('partials.status', ['status' => $booking->status])</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="td py-14 text-center text-on-surface-variant">Pengguna ini belum pernah booking.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($bookings->hasPages())
            <div class="border-t border-outline-variant/40 px-space-md py-space-md">{{ $bookings->links() }}</div>
        @endif
    </div>
@endsection
