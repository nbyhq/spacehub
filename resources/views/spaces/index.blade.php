@extends('layouts.app')

@section('title', 'Eksplorasi Ruang Kerja - SpaceHub')

@section('content')
    @php
        $keep = request()->except(['type', 'page']);
        $chipActive = 'bg-primary-fixed text-on-primary-fixed shadow-sm';
        $chipIdle = 'bg-surface-container-lowest text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface';
    @endphp

    <div class="mb-space-xl flex flex-col justify-between gap-space-lg md:flex-row md:items-end">
        <div class="max-w-2xl">
            <span class="mb-space-sm inline-flex items-center gap-1 rounded-full bg-surface-container-high px-space-sm py-1 text-label-sm font-semibold text-primary">
                <span class="material-symbols-outlined text-[16px]">domain_verification</span>
                Reservasi Ruang Real-Time
            </span>
            <h1 class="text-headline-lg font-bold tracking-tight md:text-headline-xl">Temukan Ruang Kerja Ideal untuk Tim Anda</h1>
            <p class="mt-space-xs text-body-lg text-on-surface-variant">
                Pilih dan pesan meeting room, coworking desk, private office, atau event hall dengan fasilitas lengkap.
            </p>
        </div>
        <div class="flex items-center gap-space-sm self-start rounded-xl bg-surface-container-low px-space-md py-space-sm md:self-auto">
            <span class="material-symbols-outlined text-[28px] text-primary">apartment</span>
            <div>
                <div class="text-headline-md font-semibold leading-none">{{ $totalActive }}</div>
                <div class="text-body-sm text-on-surface-variant">Ruang Aktif</div>
            </div>
        </div>
    </div>

    <form method="GET" action="{{ route('spaces.index') }}" class="card mb-space-lg grid grid-cols-1 items-end gap-space-md p-space-lg md:grid-cols-2 lg:grid-cols-12">
        @if (request('type'))
            <input type="hidden" name="type" value="{{ request('type') }}">
        @endif
        <div class="md:col-span-2 lg:col-span-5">
            <label class="label" for="q">Pencarian Nama &amp; Lokasi</label>
            <div class="relative">
                <span class="material-symbols-outlined pointer-events-none absolute left-3 top-2.5 text-[20px] text-on-surface-variant">search</span>
                <input id="q" type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama ruang atau lokasi..." class="input pl-10">
            </div>
        </div>
        <div class="lg:col-span-4">
            <label class="label" for="capacity">Kapasitas</label>
            <select id="capacity" name="capacity" class="input">
                <option value="">Semua kapasitas</option>
                @foreach (['1-4' => '1 - 4 orang', '5-10' => '5 - 10 orang', '11-30' => '11 - 30 orang', '31+' => '31+ orang'] as $value => $text)
                    <option value="{{ $value }}" @selected(request('capacity') === $value)>{{ $text }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2 lg:col-span-3">
            <button class="btn-primary flex-1">
                <span class="material-symbols-outlined text-[18px]">tune</span> Terapkan
            </button>
            <a href="{{ route('spaces.index') }}" class="btn-outline">Reset</a>
        </div>
    </form>

    <div class="mb-space-lg flex items-center gap-space-sm overflow-x-auto pb-space-sm">
        <a href="{{ route('spaces.index', $keep) }}" class="shrink-0 rounded-lg px-space-md py-2 text-label-md font-medium transition-colors {{ request('type') ? $chipIdle : $chipActive }}">
            Semua ({{ $totalActive }})
        </a>
        @foreach (\App\Models\Space::TYPES as $key => $label)
            <a href="{{ route('spaces.index', $keep + ['type' => $key]) }}" class="shrink-0 rounded-lg px-space-md py-2 text-label-md font-medium transition-colors {{ request('type') === $key ? $chipActive : $chipIdle }}">
                {{ $label }} ({{ $typeCounts[$key] ?? 0 }})
            </a>
        @endforeach
    </div>

    <div class="mb-space-xl grid grid-cols-1 gap-space-lg md:grid-cols-2 lg:grid-cols-3">
        @forelse ($spaces as $space)
            <article class="card group flex flex-col overflow-hidden transition-shadow hover:shadow-md">
                <a href="{{ route('spaces.show', $space) }}" class="relative block aspect-[16/10] overflow-hidden bg-surface-container">
                    @include('partials.space-photo', ['space' => $space, 'class' => 'h-full w-full transition-transform duration-500 group-hover:scale-105'])
                    <span class="absolute left-3 top-3">@include('partials.type-chip', ['type' => $space->type])</span>
                </a>
                <div class="flex flex-1 flex-col justify-between p-space-lg">
                    <div>
                        <div class="mb-1 flex items-center gap-1 text-body-sm text-on-surface-variant">
                            <span class="material-symbols-outlined text-[16px] text-primary">location_on</span>
                            {{ $space->location }}
                        </div>
                        <h2 class="text-title-card font-semibold">
                            <a href="{{ route('spaces.show', $space) }}" class="hover:text-primary">{{ $space->name }}</a>
                        </h2>
                        <div class="mt-space-sm inline-flex items-center gap-1 rounded-lg bg-surface-container-low px-2 py-1 text-body-sm font-medium text-on-surface-variant">
                            <span class="material-symbols-outlined text-[16px]">groups</span>
                            Kapasitas {{ $space->capacity }} Orang
                        </div>
                        <div class="mt-space-sm flex flex-wrap gap-1.5">
                            @foreach ($space->facilities->take(4) as $facility)
                                <span class="chip-sm">
                                    <span class="material-symbols-outlined text-[14px]">{{ $facility->icon }}</span>{{ $facility->name }}
                                </span>
                            @endforeach
                            @if ($space->facilities->count() > 4)
                                <span class="chip-sm">+{{ $space->facilities->count() - 4 }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="mt-space-lg flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <div class="text-body-sm text-on-surface-variant">Mulai dari</div>
                            <div class="text-headline-md font-bold text-primary">
                                Rp {{ number_format($space->price_per_hour, 0, ',', '.') }}
                                <span class="text-body-sm font-normal text-on-surface-variant">/ jam</span>
                            </div>
                        </div>
                        <a href="{{ route('spaces.show', $space) }}" class="btn-primary">Pesan Sekarang</a>
                    </div>
                </div>
            </article>
        @empty
            <div class="card col-span-full flex flex-col items-center gap-2 py-16 text-center text-on-surface-variant">
                <span class="material-symbols-outlined text-[40px]">search_off</span>
                <p>Belum ada ruang yang cocok dengan filter Anda.</p>
                <a href="{{ route('spaces.index') }}" class="text-label-md font-medium text-primary hover:underline">Reset filter</a>
            </div>
        @endforelse
    </div>

    @if ($spaces->total() > 0)
        <div class="card flex flex-col items-center justify-between gap-3 px-space-lg py-space-md sm:flex-row">
            <p class="text-body-md text-on-surface-variant">
                Menampilkan <strong>{{ $spaces->firstItem() }} - {{ $spaces->lastItem() }}</strong> dari <strong>{{ $spaces->total() }}</strong> ruang
            </p>
            <div>{{ $spaces->links() }}</div>
        </div>
    @endif
@endsection
