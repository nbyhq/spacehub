@extends('layouts.admin')

@section('title', 'Pengguna - SpaceHub')

@section('content')
    <div class="mb-space-lg">
        <span class="text-label-sm font-semibold uppercase tracking-wide text-primary">User Management</span>
        <h1 class="text-headline-lg font-bold tracking-tight md:text-headline-xl">Pengguna</h1>
        <p class="mt-1 max-w-2xl text-body-lg text-on-surface-variant">Lihat siapa saja yang terdaftar, riwayat bookingnya, dan atur siapa yang menjadi admin.</p>
    </div>

    <div class="mb-space-lg grid grid-cols-3 gap-3">
        <a href="{{ route('admin.users.index') }}" class="card p-3 transition-shadow hover:shadow-md sm:p-space-md {{ request('role') ? '' : 'ring-2 ring-primary-container' }}">
            <span class="block text-headline-md font-semibold leading-none">{{ $stats['total'] }}</span>
            <span class="text-body-sm text-on-surface-variant">Total akun</span>
        </a>
        <a href="{{ route('admin.users.index', ['role' => 'user']) }}" class="card p-3 transition-shadow hover:shadow-md sm:p-space-md {{ request('role') === 'user' ? 'ring-2 ring-primary-container' : '' }}">
            <span class="block text-headline-md font-semibold leading-none">{{ $stats['total'] - $stats['admin'] }}</span>
            <span class="text-body-sm text-on-surface-variant">Pengguna</span>
        </a>
        <a href="{{ route('admin.users.index', ['role' => 'admin']) }}" class="card p-3 transition-shadow hover:shadow-md sm:p-space-md {{ request('role') === 'admin' ? 'ring-2 ring-primary-container' : '' }}">
            <span class="block text-headline-md font-semibold leading-none">{{ $stats['admin'] }}</span>
            <span class="text-body-sm text-on-surface-variant">Admin</span>
        </a>
    </div>

    <div class="card overflow-hidden">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col gap-2 border-b border-outline-variant/40 p-space-md md:flex-row">
            <div class="relative flex-1">
                <span class="material-symbols-outlined pointer-events-none absolute left-3 top-2.5 text-[20px] text-on-surface-variant">search</span>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama atau email..." class="input pl-10">
            </div>
            <select name="role" class="input md:w-44">
                <option value="">Semua Role</option>
                <option value="user" @selected(request('role') === 'user')>Pengguna</option>
                <option value="admin" @selected(request('role') === 'admin')>Admin</option>
            </select>
            <div class="flex gap-2">
                <button class="btn-primary flex-1 md:flex-none">Filter</button>
                <a href="{{ route('admin.users.index') }}" class="btn-outline" title="Reset filter" aria-label="Reset filter">
                    <span class="material-symbols-outlined text-[18px]">refresh</span>
                </a>
            </div>
        </form>

        {{-- HP & tablet: kartu --}}
        <div class="divide-y divide-outline-variant/30 lg:hidden">
            @forelse ($users as $user)
                <div class="space-y-3 p-space-md">
                    <div class="flex items-start gap-3">
                        <span class="flex size-11 shrink-0 items-center justify-center rounded-full bg-primary-fixed text-label-md font-semibold text-on-primary-fixed">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                        <div class="min-w-0 flex-1">
                            <div class="truncate font-semibold">{{ $user->name }}</div>
                            <div class="truncate text-body-sm text-on-surface-variant">{{ $user->email }}</div>
                        </div>
                        @include('partials.role-chip', ['role' => $user->role])
                    </div>
                    <div class="flex flex-wrap gap-x-4 gap-y-1 text-body-sm text-on-surface-variant">
                        <span>{{ $user->bookings_count }} booking ({{ $user->active_bookings_count }} aktif)</span>
                        <span>Daftar {{ $user->created_at ? \App\Support\IndonesianDate::short($user->created_at) : '-' }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.users.show', $user) }}" class="btn-outline h-9 flex-1 px-3 sm:flex-none">Lihat detail</a>
                        @include('partials.role-form', ['user' => $user])
                    </div>
                </div>
            @empty
                <div class="px-space-md py-12 text-center text-on-surface-variant">Belum ada pengguna yang cocok.</div>
            @endforelse
        </div>

        {{-- Desktop: tabel --}}
        <div class="hidden overflow-x-auto lg:block">
            <table class="w-full table-fixed text-body-md">
                <thead class="bg-surface-container-low">
                    <tr>
                        <th class="th w-[32%]">Pengguna</th>
                        <th class="th w-[12%]">Role</th>
                        <th class="th w-[16%]">Booking</th>
                        <th class="th w-[16%]">Terdaftar</th>
                        <th class="th w-56 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30">
                    @forelse ($users as $user)
                        <tr class="transition-colors hover:bg-surface-container-low/60">
                            <td class="td">
                                <div class="flex items-center gap-3">
                                    <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary-fixed text-label-md font-semibold text-on-primary-fixed">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                                    <div class="min-w-0">
                                        <div class="truncate font-semibold" title="{{ $user->name }}">{{ $user->name }}</div>
                                        <div class="truncate text-body-sm text-on-surface-variant" title="{{ $user->email }}">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="td">@include('partials.role-chip', ['role' => $user->role])</td>
                            <td class="td whitespace-nowrap">
                                <span class="font-medium">{{ $user->bookings_count }}</span>
                                <span class="text-body-sm text-on-surface-variant">({{ $user->active_bookings_count }} aktif)</span>
                            </td>
                            <td class="td whitespace-nowrap text-on-surface-variant">{{ $user->created_at ? \App\Support\IndonesianDate::short($user->created_at) : '-' }}</td>
                            <td class="td">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.users.show', $user) }}" class="btn-outline h-9 px-3">Detail</a>
                                    @include('partials.role-form', ['user' => $user])
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="td py-14 text-center text-on-surface-variant">Belum ada pengguna yang cocok.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->total() > 0)
            <div class="flex flex-col items-center justify-between gap-3 border-t border-outline-variant/40 px-space-md py-space-md sm:flex-row">
                <p class="text-body-md text-on-surface-variant">
                    Menampilkan <strong>{{ $users->firstItem() }} - {{ $users->lastItem() }}</strong> dari <strong>{{ $users->total() }}</strong> akun
                </p>
                <div>{{ $users->links() }}</div>
            </div>
        @endif
    </div>
@endsection
