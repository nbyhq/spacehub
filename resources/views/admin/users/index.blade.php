@extends('layouts.admin')

@section('title', 'Pengguna - SpaceHub')

@section('content')
    @php
        $roleNow = request('role');
        $ring = 'ring-2 ring-primary-container';
        $keep = array_filter(['q' => request('q')]);
        $cards = [
            ['', 'Total akun', $stats['total'], 'group', 'bg-primary-fixed text-primary'],
            ['user', 'Pengguna', $stats['total'] - $stats['admin'], 'person', 'bg-slate-100 text-slate-600'],
            ['admin', 'Admin', $stats['admin'], 'admin_panel_settings', 'bg-indigo-100 text-indigo-700'],
        ];
    @endphp

    <div class="mb-space-lg">
        <span class="text-label-sm font-semibold uppercase tracking-wide text-primary">User Management</span>
        <h1 class="text-headline-lg font-bold tracking-tight md:text-headline-xl">Pengguna</h1>
        <p class="mt-1 max-w-2xl text-body-lg text-on-surface-variant">Lihat siapa saja yang terdaftar, riwayat bookingnya, dan atur siapa yang menjadi admin.</p>
    </div>

    <div class="mb-space-lg grid grid-cols-3 gap-3">
        @foreach ($cards as [$value, $label, $count, $icon, $tone])
            <a href="{{ route('admin.users.index', $keep + ($value ? ['role' => $value] : [])) }}"
               class="card flex items-center gap-3 p-3 transition-shadow hover:shadow-md sm:p-space-md {{ ($roleNow ?: '') === $value ? $ring : '' }}">
                <span class="hidden size-10 shrink-0 items-center justify-center rounded-lg sm:flex {{ $tone }}">
                    <span class="material-symbols-outlined">{{ $icon }}</span>
                </span>
                <span>
                    <span class="block text-headline-md font-semibold leading-none">{{ $count }}</span>
                    <span class="text-body-sm text-on-surface-variant">{{ $label }}</span>
                </span>
            </a>
        @endforeach
    </div>

    <div class="card overflow-hidden">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col gap-2 border-b border-outline-variant/40 p-space-md md:flex-row">
            <div class="relative flex-1">
                <span class="material-symbols-outlined pointer-events-none absolute left-3 top-2.5 text-[20px] text-on-surface-variant">search</span>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama atau email..." class="input pl-10">
            </div>
            <select name="role" class="input md:w-44">
                <option value="">Semua Role</option>
                <option value="user" @selected($roleNow === 'user')>Pengguna</option>
                <option value="admin" @selected($roleNow === 'admin')>Admin</option>
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
                        @include('partials.avatar', ['id' => $user->id, 'name' => $user->name, 'size' => 'size-11'])
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="truncate font-semibold">{{ $user->name }}</span>
                                @if ($user->is(auth()->user()))
                                    <span class="rounded bg-primary-fixed px-1.5 text-label-sm font-semibold text-on-primary-fixed">Anda</span>
                                @endif
                            </div>
                            <div class="truncate text-body-sm text-on-surface-variant">{{ $user->email }}</div>
                        </div>
                        @include('partials.role-chip', ['role' => $user->role])
                    </div>
                    <div class="flex flex-wrap gap-x-4 gap-y-1 text-body-sm text-on-surface-variant">
                        <span class="flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">calendar_month</span>{{ $user->bookings_count }} booking ({{ $user->active_bookings_count }} aktif)</span>
                        <span class="flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">event</span>Daftar {{ $user->created_at ? \App\Support\IndonesianDate::short($user->created_at) : '-' }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.users.show', $user) }}" class="btn-outline h-9 flex-1 px-3 sm:flex-none">Lihat detail</a>
                        @include('partials.role-form', ['user' => $user])
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center gap-2 px-space-md py-12 text-center text-on-surface-variant">
                    <span class="material-symbols-outlined text-[40px]">person_search</span>
                    <p>Belum ada pengguna yang cocok.</p>
                </div>
            @endforelse
        </div>

        {{-- Desktop: tabel --}}
        <div class="hidden overflow-x-auto lg:block">
            <table class="w-full table-fixed text-body-md">
                <thead class="bg-surface-container-low">
                    <tr>
                        <th class="th w-[34%]">Pengguna</th>
                        <th class="th w-[12%]">Role</th>
                        <th class="th w-[18%]">Booking</th>
                        <th class="th w-[16%]">Terdaftar</th>
                        <th class="th w-64 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30">
                    @forelse ($users as $user)
                        <tr class="transition-colors hover:bg-surface-container-low/60 {{ $user->is(auth()->user()) ? 'bg-primary-fixed/20' : '' }}">
                            <td class="td">
                                <div class="flex items-center gap-3">
                                    @include('partials.avatar', ['id' => $user->id, 'name' => $user->name, 'size' => 'size-10'])
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="truncate font-semibold" title="{{ $user->name }}">{{ $user->name }}</span>
                                            @if ($user->is(auth()->user()))
                                                <span class="shrink-0 rounded bg-primary-fixed px-1.5 text-label-sm font-semibold text-on-primary-fixed">Anda</span>
                                            @endif
                                        </div>
                                        <div class="truncate text-body-sm text-on-surface-variant" title="{{ $user->email }}">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="td">@include('partials.role-chip', ['role' => $user->role])</td>
                            <td class="td whitespace-nowrap">
                                <span class="font-medium">{{ $user->bookings_count }}</span>
                                @if ($user->active_bookings_count > 0)
                                    <span class="ml-1 rounded-full bg-emerald-100 px-2 py-0.5 text-label-sm font-semibold text-emerald-700">{{ $user->active_bookings_count }} aktif</span>
                                @endif
                            </td>
                            <td class="td whitespace-nowrap text-on-surface-variant">{{ $user->created_at ? \App\Support\IndonesianDate::short($user->created_at) : '-' }}</td>
                            <td class="td">
                                <div class="ml-auto flex w-[14rem] items-center justify-end gap-2">
                                    <a href="{{ route('admin.users.show', $user) }}" class="btn-outline h-9 px-3">Detail</a>
                                    @include('partials.role-form', ['user' => $user])
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="td py-14 text-center text-on-surface-variant">
                                <span class="material-symbols-outlined text-[40px]">person_search</span>
                                <p class="mt-1">Belum ada pengguna yang cocok.</p>
                            </td>
                        </tr>
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
