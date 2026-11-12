@extends('layouts.app')

@section('title', 'Kelola Ruang - SpaceHub')

@section('content')
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

    <div class="card overflow-hidden">
        <form method="GET" action="{{ route('admin.spaces.index') }}" class="flex flex-col gap-2 p-space-md md:flex-row">
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
            <button class="btn-primary">Filter</button>
            <a href="{{ route('admin.spaces.index') }}" class="btn-outline" title="Reset filter">
                <span class="material-symbols-outlined text-[18px]">refresh</span>
            </a>
        </form>

        <div class="divide-y divide-outline-variant/30 lg:hidden">
            @forelse ($spaces as $space)
                <div class="space-y-3 p-space-md">
                    <div class="flex items-start gap-3">
                        <div class="size-14 shrink-0 overflow-hidden rounded-lg">
                            @include('partials.space-photo', ['space' => $space, 'class' => 'size-14'])
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="font-semibold">{{ $space->name }}</div>
                            <div class="text-body-sm text-on-surface-variant">{{ $space->location }}</div>
                        </div>
                        @include('partials.active', ['active' => $space->is_active])
                    </div>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-body-md">
                        @include('partials.type-chip', ['type' => $space->type])
                        <span>{{ $space->capacity }} Orang</span>
                        <span class="font-medium">Rp {{ number_format($space->price_per_hour, 0, ',', '.') }} <span class="text-body-sm font-normal text-on-surface-variant">/ jam</span></span>
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
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('admin.spaces.edit', $space) }}" class="rounded-lg bg-surface-container px-3 py-1.5 text-label-md font-medium hover:bg-surface-container-high">Edit</a>
                        <a href="{{ route('admin.bookings.index', ['space' => $space->id]) }}" class="rounded-lg bg-primary px-3 py-1.5 text-label-md font-medium text-white hover:bg-primary-container">Kelola Jadwal</a>
                        <form method="POST" action="{{ route('admin.spaces.destroy', $space) }}" onsubmit="return confirm('Hapus ruang ini?')" class="ml-auto">
                            @csrf @method('DELETE')
                            <button class="rounded-lg px-2 py-1.5 text-red-700 hover:bg-red-50" title="Hapus">
                                <span class="material-symbols-outlined text-[20px]">delete</span>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="py-12 text-center text-on-surface-variant">Belum ada ruang yang cocok.</div>
            @endforelse
        </div>

        <div class="hidden overflow-x-auto lg:block">
            <table class="w-full min-w-[860px] text-body-md">
                <thead class="bg-surface-container-low">
                    <tr>
                        <th class="th">Nama &amp; Foto Ruang</th>
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
                        <tr class="hover:bg-surface-container-low/40">
                            <td class="td">
                                <div class="flex items-center gap-3">
                                    <div class="size-12 shrink-0 overflow-hidden rounded-lg">
                                        @include('partials.space-photo', ['space' => $space, 'class' => 'size-12'])
                                    </div>
                                    <div>
                                        <div class="font-semibold">{{ $space->name }}</div>
                                        <div class="text-body-sm text-on-surface-variant">{{ $space->location }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="td">@include('partials.type-chip', ['type' => $space->type])</td>
                            <td class="td">{{ $space->capacity }} Orang</td>
                            <td class="td font-medium">Rp {{ number_format($space->price_per_hour, 0, ',', '.') }} <span class="text-body-sm font-normal text-on-surface-variant">/ jam</span></td>
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
                            <td class="td">
                                @if ($space->is_active)
                                    <span class="inline-flex h-6 items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-100 px-2.5 text-label-sm font-semibold text-emerald-700">
                                        <span class="size-1.5 rounded-full bg-emerald-600"></span>Aktif
                                    </span>
                                @else
                                    <span class="inline-flex h-6 items-center gap-1.5 rounded-full border border-slate-200 bg-slate-100 px-2.5 text-label-sm font-semibold text-slate-600">
                                        <span class="size-1.5 rounded-full bg-slate-400"></span>Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="td">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.spaces.edit', $space) }}" class="rounded-lg bg-surface-container px-3 py-1.5 text-label-md font-medium hover:bg-surface-container-high">Edit</a>
                                    <a href="{{ route('admin.bookings.index', ['space' => $space->id]) }}" class="rounded-lg bg-primary px-3 py-1.5 text-label-md font-medium text-white hover:bg-primary-container">Kelola Jadwal</a>
                                    <form method="POST" action="{{ route('admin.spaces.destroy', $space) }}" onsubmit="return confirm('Hapus ruang ini?')">
                                        @csrf @method('DELETE')
                                        <button class="rounded-lg px-2 py-1.5 text-red-700 hover:bg-red-50" title="Hapus">
                                            <span class="material-symbols-outlined text-[20px]">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="td py-12 text-center text-on-surface-variant">Belum ada ruang yang cocok.</td></tr>
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
@endsection
