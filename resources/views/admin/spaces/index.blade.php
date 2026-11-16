@extends('layouts.admin')

@section('title', 'Kelola Ruang - SpaceHub')

@section('content')
    @php
        $statusNow = request('status');
        $ring = 'ring-2 ring-primary-container';
        $inactive = $stats['total'] - $stats['active'];
    @endphp

    <div class="mb-space-lg flex flex-col justify-between gap-space-md md:flex-row md:items-end">
        <div>
            <span class="text-label-sm font-semibold uppercase tracking-wide text-primary">Workspace Management</span>
            <h1 class="text-headline-lg font-bold tracking-tight md:text-headline-xl">Kelola Ruang Kerja</h1>
            <p class="mt-1 max-w-2xl text-body-lg text-on-surface-variant">Atur data ruang, tarif, fasilitas, dan status ketersediaan.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.facilities.index') }}" class="btn-outline">Kelola Fasilitas</a>
            <a href="{{ route('admin.spaces.create') }}" class="btn-primary">
                <span class="material-symbols-outlined text-[18px]">add</span> Tambah Ruang Baru
            </a>
        </div>
    </div>

    <div class="mb-space-lg grid grid-cols-3 gap-3">
        <a href="{{ route('admin.spaces.index') }}" class="card flex items-center gap-3 p-3 transition-shadow hover:shadow-md sm:p-space-md {{ $statusNow ? '' : $ring }}">
            <span class="hidden size-10 shrink-0 items-center justify-center rounded-lg bg-primary-fixed text-primary sm:flex">
                <span class="material-symbols-outlined">apartment</span>
            </span>
            <span>
                <span class="block text-headline-md font-semibold leading-none">{{ $stats['total'] }}</span>
                <span class="text-body-sm text-on-surface-variant">Total ruang</span>
            </span>
        </a>
        <a href="{{ route('admin.spaces.index', ['status' => 'aktif']) }}" class="card flex items-center gap-3 p-3 transition-shadow hover:shadow-md sm:p-space-md {{ $statusNow === 'aktif' ? $ring : '' }}">
            <span class="hidden size-10 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700 sm:flex">
                <span class="material-symbols-outlined">check_circle</span>
            </span>
            <span>
                <span class="block text-headline-md font-semibold leading-none">{{ $stats['active'] }}</span>
                <span class="text-body-sm text-on-surface-variant">Aktif</span>
            </span>
        </a>
        <a href="{{ route('admin.spaces.index', ['status' => 'nonaktif']) }}" class="card flex items-center gap-3 p-3 transition-shadow hover:shadow-md sm:p-space-md {{ $statusNow === 'nonaktif' ? $ring : '' }}">
            <span class="hidden size-10 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600 sm:flex">
                <span class="material-symbols-outlined">visibility_off</span>
            </span>
            <span>
                <span class="block text-headline-md font-semibold leading-none">{{ $inactive }}</span>
                <span class="text-body-sm text-on-surface-variant">Nonaktif</span>
            </span>
        </a>
    </div>

    <div class="card overflow-hidden">
        <form method="GET" action="{{ route('admin.spaces.index') }}" class="flex flex-col gap-2 border-b border-outline-variant/40 p-space-md md:flex-row">
            <div class="relative flex-1">
                <span class="material-symbols-outlined pointer-events-none absolute left-3 top-2.5 text-[20px] text-on-surface-variant">search</span>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama atau lokasi ruang..." class="input pl-10">
            </div>
            <select name="type" class="input md:w-48">
                <option value="">Semua Tipe Ruang</option>
                @foreach (\App\Models\Space::TYPES as $key => $label)
                    <option value="{{ $key }}" @selected(request('type') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="status" class="input md:w-40">
                <option value="">Semua Status</option>
                <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
                <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option>
            </select>
            <div class="flex gap-2">
                <button class="btn-primary flex-1 md:flex-none">Filter</button>
                <a href="{{ route('admin.spaces.index') }}" class="btn-outline" title="Reset filter" aria-label="Reset filter">
                    <span class="material-symbols-outlined text-[18px]">refresh</span>
                </a>
            </div>
        </form>

        {{-- Tablet & HP: daftar kartu --}}
        <div class="divide-y divide-outline-variant/30 lg:hidden">
            @forelse ($spaces as $space)
                <div class="space-y-3 p-space-md {{ $space->is_active ? '' : 'bg-surface-container-low/40' }}">
                    <div class="flex items-start gap-3">
                        <div class="size-16 shrink-0 overflow-hidden rounded-lg">
                            @include('partials.space-photo', ['space' => $space, 'class' => 'size-16'])
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="font-semibold">{{ $space->name }}</div>
                            <div class="mt-0.5 flex items-center gap-1 text-body-sm text-on-surface-variant">
                                <span class="material-symbols-outlined text-[14px]">location_on</span>{{ $space->location }}
                            </div>
                            <div class="mt-1.5">@include('partials.type-chip', ['type' => $space->type])</div>
                        </div>
                        @include('partials.active', ['active' => $space->is_active])
                    </div>

                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-body-md">
                        <span class="flex items-center gap-1 text-on-surface-variant">
                            <span class="material-symbols-outlined text-[18px]">groups</span>{{ $space->capacity }} orang
                        </span>
                        <span class="font-medium">Rp {{ number_format($space->price_per_hour, 0, ',', '.') }}
                            <span class="text-body-sm font-normal text-on-surface-variant">/ jam</span>
                        </span>
                    </div>

                    @if ($space->facilities->isNotEmpty())
                        <div class="flex flex-wrap gap-1">
                            @foreach ($space->facilities->take(3) as $facility)
                                <span class="chip-sm">{{ $facility->name }}</span>
                            @endforeach
                            @if ($space->facilities->count() > 3)
                                <span class="chip-sm">+{{ $space->facilities->count() - 3 }}</span>
                            @endif
                        </div>
                    @endif

                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.spaces.edit', $space) }}" class="btn-outline h-9 flex-1 px-3 sm:flex-none">
                            <span class="material-symbols-outlined text-[18px]">edit</span> Edit
                        </a>
                        <button type="button" onclick="document.getElementById('space-bookings-{{ $space->id }}').showModal()" class="btn-outline h-9 flex-1 px-3 sm:flex-none">
                            <span class="material-symbols-outlined text-[18px]">calendar_month</span> Jadwal
                            @if ($space->bookings->isNotEmpty())
                                <span class="rounded-full bg-primary-fixed px-1.5 text-label-sm font-semibold text-on-primary-fixed">{{ $space->bookings->count() }}</span>
                            @endif
                        </button>
                        @if (($space->active_bookings_count ?? 0) > 0)
                            <span class="ml-auto inline-flex size-9 cursor-not-allowed items-center justify-center rounded-lg text-slate-400" title="Tidak bisa dihapus: {{ $space->active_bookings_count }} booking aktif" aria-label="Ruang tidak bisa dihapus karena masih ada booking aktif">
                                <span class="material-symbols-outlined text-[20px]">lock</span>
                            </span>
                        @else
                            <form method="POST" action="{{ route('admin.spaces.destroy', $space) }}" onsubmit="return confirm('Hapus ruang ini?')" class="ml-auto">
                                @csrf @method('DELETE')
                                <button class="inline-flex size-9 cursor-pointer items-center justify-center rounded-lg text-red-700 transition-colors hover:bg-red-50" title="Hapus" aria-label="Hapus ruang">
                                    <span class="material-symbols-outlined text-[20px]">delete</span>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center gap-2 px-space-md py-12 text-center text-on-surface-variant">
                    <span class="material-symbols-outlined text-[40px]">meeting_room</span>
                    <p>Belum ada ruang yang cocok.</p>
                    <a href="{{ route('admin.spaces.create') }}" class="text-label-md font-medium text-primary hover:underline">Tambah ruang baru</a>
                </div>
            @endforelse
        </div>

        {{-- Desktop: tabel --}}
        <div class="hidden overflow-x-auto lg:block">
            <table class="w-full text-body-md">
                <thead class="bg-surface-container-low">
                    <tr>
                        <th class="th">Ruang</th>
                        <th class="th">Tipe</th>
                        <th class="th">Kapasitas</th>
                        <th class="th">Tarif Sewa</th>
                        <th class="th">Fasilitas</th>
                        <th class="th">Status</th>
                        <th class="th text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30">
                    @forelse ($spaces as $space)
                        <tr class="transition-colors hover:bg-surface-container-low/60 {{ $space->is_active ? '' : 'bg-surface-container-low/30' }}">
                            <td class="td">
                                <div class="flex items-center gap-3">
                                    <div class="size-14 shrink-0 overflow-hidden rounded-lg">
                                        @include('partials.space-photo', ['space' => $space, 'class' => 'size-14'])
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-semibold">{{ $space->name }}</div>
                                        <div class="mt-0.5 flex items-center gap-1 text-body-sm text-on-surface-variant">
                                            <span class="material-symbols-outlined text-[14px]">location_on</span>{{ $space->location }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="td">@include('partials.type-chip', ['type' => $space->type])</td>
                            <td class="td whitespace-nowrap">
                                <span class="flex items-center gap-1 text-on-surface-variant">
                                    <span class="material-symbols-outlined text-[18px]">groups</span>{{ $space->capacity }} orang
                                </span>
                            </td>
                            <td class="td whitespace-nowrap font-medium">Rp {{ number_format($space->price_per_hour, 0, ',', '.') }}
                                <span class="text-body-sm font-normal text-on-surface-variant">/ jam</span>
                            </td>
                            <td class="td">
                                <div class="flex max-w-56 flex-wrap gap-1">
                                    @foreach ($space->facilities->take(3) as $facility)
                                        <span class="chip-sm">{{ $facility->name }}</span>
                                    @endforeach
                                    @if ($space->facilities->count() > 3)
                                        <span class="chip-sm">+{{ $space->facilities->count() - 3 }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="td">@include('partials.active', ['active' => $space->is_active])</td>
                            <td class="td">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.spaces.edit', $space) }}" class="btn-outline h-9 px-3">
                                        <span class="material-symbols-outlined text-[18px]">edit</span> Edit
                                    </a>
                                    <button type="button" onclick="document.getElementById('space-bookings-{{ $space->id }}').showModal()" class="btn-outline h-9 px-3">
                                        <span class="material-symbols-outlined text-[18px]">calendar_month</span> Jadwal
                                        @if ($space->bookings->isNotEmpty())
                                            <span class="rounded-full bg-primary-fixed px-1.5 text-label-sm font-semibold text-on-primary-fixed">{{ $space->bookings->count() }}</span>
                                        @endif
                                    </button>
                                    @if (($space->active_bookings_count ?? 0) > 0)
                                        <span class="inline-flex size-9 cursor-not-allowed items-center justify-center rounded-lg text-slate-400" title="Tidak bisa dihapus: {{ $space->active_bookings_count }} booking aktif" aria-label="Ruang tidak bisa dihapus karena masih ada booking aktif">
                                            <span class="material-symbols-outlined text-[20px]">lock</span>
                                        </span>
                                    @else
                                        <form method="POST" action="{{ route('admin.spaces.destroy', $space) }}" onsubmit="return confirm('Hapus ruang ini?')">
                                            @csrf @method('DELETE')
                                            <button class="inline-flex size-9 cursor-pointer items-center justify-center rounded-lg text-red-700 transition-colors hover:bg-red-50" title="Hapus" aria-label="Hapus ruang">
                                                <span class="material-symbols-outlined text-[20px]">delete</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="td py-14 text-center text-on-surface-variant">
                                <span class="material-symbols-outlined text-[40px]">meeting_room</span>
                                <p class="mt-1">Belum ada ruang yang cocok.</p>
                                <a href="{{ route('admin.spaces.create') }}" class="mt-1 inline-block text-label-md font-medium text-primary hover:underline">Tambah ruang baru</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($spaces->total() > 0)
            <div class="flex flex-col items-center justify-between gap-3 border-t border-outline-variant/40 px-space-md py-space-md sm:flex-row">
                <p class="text-body-md text-on-surface-variant">
                    Menampilkan <strong>{{ $spaces->firstItem() }} - {{ $spaces->lastItem() }}</strong> dari <strong>{{ $spaces->total() }}</strong> ruang
                </p>
                <div>{{ $spaces->links() }}</div>
            </div>
        @endif
    </div>
    {{-- Modal jadwal booking per ruang: siapa saja yang sudah booking dan di hari apa --}}
    @foreach ($spaces as $space)
        @php
            $visible = $space->bookings->take(40);
            $hidden = $space->bookings->count() - $visible->count();
            $byDay = $visible->groupBy(fn ($b) => $b->booking_date->toDateString());
        @endphp
        <dialog id="space-bookings-{{ $space->id }}" aria-labelledby="space-bookings-title-{{ $space->id }}"
                class="m-auto max-h-[85vh] w-[calc(100%-2rem)] max-w-2xl overflow-hidden rounded-xl bg-surface-container-lowest p-0 text-on-surface shadow-xl backdrop:bg-slate-900/50"
                onclick="if (event.target === this) this.close()">
            <div class="flex max-h-[85vh] flex-col">
                <div class="flex items-start justify-between gap-3 border-b border-outline-variant/40 px-space-lg py-space-md">
                    <div class="min-w-0">
                        <h2 id="space-bookings-title-{{ $space->id }}" class="truncate text-title-card font-semibold">Jadwal Booking &middot; {{ $space->name }}</h2>
                        <p class="mt-0.5 text-body-sm text-on-surface-variant">
                            {{ $space->bookings->count() }} booking aktif &middot; buka {{ $space->opensAtLabel() }}&ndash;{{ $space->closesAtLabel() }}
                        </p>
                    </div>
                    <form method="dialog">
                        <button class="inline-flex size-9 cursor-pointer items-center justify-center rounded-lg text-on-surface-variant hover:bg-surface-container-low" aria-label="Tutup">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </form>
                </div>

                <div class="overflow-y-auto px-space-lg py-space-md">
                    @forelse ($byDay as $items)
                        @php $day = $items->first()->booking_date; @endphp
                        <div class="{{ $loop->first ? '' : 'mt-space-md' }}">
                            <div class="flex items-center gap-2">
                                <h3 class="text-label-md font-semibold">{{ $items->first()->day_label }}</h3>
                                @if ($day->isToday())
                                    <span class="rounded-full bg-primary-fixed px-2 py-0.5 text-label-sm font-semibold text-on-primary-fixed">Hari ini</span>
                                @elseif ($day->isTomorrow())
                                    <span class="rounded-full bg-primary-fixed px-2 py-0.5 text-label-sm font-semibold text-on-primary-fixed">Besok</span>
                                @endif
                            </div>
                            <ul class="mt-space-sm divide-y divide-outline-variant/30 rounded-lg border border-outline-variant/40">
                                @foreach ($items as $b)
                                    <li class="flex flex-wrap items-center justify-between gap-x-space-md gap-y-1 px-3 py-2.5">
                                        <div class="flex min-w-0 items-center gap-3">
                                            <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-primary-fixed text-label-sm font-semibold text-on-primary-fixed">
                                                {{ mb_strtoupper(mb_substr($b->user?->name ?? '?', 0, 1)) }}
                                            </span>
                                            <div class="min-w-0">
                                                <div class="truncate font-medium">{{ $b->user?->name ?? 'Pengguna dihapus' }}</div>
                                                <div class="truncate text-body-sm text-on-surface-variant">{{ $b->user?->email }}</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <span class="text-body-md font-medium">{{ $b->time_range }}</span>
                                            @include('partials.status', ['status' => $b->status])
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @empty
                        <div class="flex flex-col items-center gap-1 py-10 text-center text-on-surface-variant">
                            <span class="material-symbols-outlined text-[36px]">event_available</span>
                            <p>Belum ada booking mendatang untuk ruang ini.</p>
                        </div>
                    @endforelse

                    @if ($hidden > 0)
                        <p class="mt-space-md text-center text-body-sm text-on-surface-variant">dan {{ $hidden }} booking lainnya. Lihat selengkapnya di Kelola Booking.</p>
                    @endif
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-outline-variant/40 px-space-lg py-space-md">
                    <span class="text-body-sm text-on-surface-variant">Hanya booking menunggu atau disetujui yang belum selesai.</span>
                    <a href="{{ route('admin.bookings.index', ['space' => $space->id]) }}" class="btn-outline h-9 px-3">Lihat semua di Kelola Booking</a>
                </div>
            </div>
        </dialog>
    @endforeach
@endsection
