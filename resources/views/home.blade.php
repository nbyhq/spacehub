@extends('layouts.app')

@section('title', 'SpaceHub - Booking Ruang Kerja')

@section('content')
    @php
        $totalSpaces = $typeCounts->sum();
        $typeMeta = [
            'coworking' => ['desk', 'bg-indigo-100 text-indigo-700', 'Kerja fleksibel harian'],
            'meeting_room' => ['meeting_room', 'bg-emerald-100 text-emerald-700', 'Rapat dan presentasi'],
            'private_office' => ['business_center', 'bg-amber-100 text-amber-700', 'Ruang privat untuk tim'],
            'event_hall' => ['celebration', 'bg-rose-100 text-rose-700', 'Acara dan workshop'],
        ];
        $benefits = [
            ['event_busy', 'Anti bentrok', 'Jadwal yang sudah terisi otomatis ditolak, jadi tidak ada pesanan ganda.', 'bg-indigo-100 text-indigo-700'],
            ['payments', 'Harga transparan', 'Tarif per jam tampil jelas dan estimasi biaya dihitung sebelum kamu memesan.', 'bg-emerald-100 text-emerald-700'],
            ['verified', 'Ditinjau admin', 'Setiap booking dicek admin sehingga ruang yang kamu pesan benar-benar siap.', 'bg-amber-100 text-amber-700'],
            ['chair', 'Fasilitas lengkap', 'Lihat Wi-Fi, proyektor, dan fasilitas lain di tiap ruang sebelum memutuskan.', 'bg-rose-100 text-rose-700'],
        ];
    @endphp

    {{-- Hero --}}
    <section class="relative isolate overflow-hidden rounded-3xl border border-outline-variant/30 bg-gradient-to-br from-primary-fixed/80 via-surface to-secondary-container/30 px-6 py-10 sm:px-10 sm:py-14 lg:px-14 lg:py-16">
        <div aria-hidden="true" class="pointer-events-none absolute -left-24 -top-24 -z-10 size-72 rounded-full bg-primary-container/20 blur-3xl"></div>
        <div aria-hidden="true" class="pointer-events-none absolute -bottom-28 right-0 -z-10 size-80 rounded-full bg-secondary-fixed-dim/30 blur-3xl"></div>

        <div class="grid items-center gap-space-xl lg:grid-cols-[1.1fr_1fr]">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full border border-primary/15 bg-white/70 px-3 py-1 text-label-sm font-semibold text-primary backdrop-blur">
                    <span class="relative flex size-2">
                        <span class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-500 opacity-60"></span>
                        <span class="relative inline-flex size-2 rounded-full bg-emerald-500"></span>
                    </span>
                    {{ $totalSpaces }} ruang siap dipesan
                </span>

                <h1 class="mt-space-md text-4xl font-bold leading-tight tracking-tight text-balance md:text-5xl md:leading-[1.1]">
                    Ruang kerja yang tepat,
                    <span class="bg-gradient-to-r from-primary to-violet-500 bg-clip-text text-transparent">dipesan dalam beberapa menit.</span>
                </h1>
                <p class="mt-space-md max-w-xl text-body-lg text-on-surface-variant">
                    Pilih meeting room, coworking desk, private office, atau event hall. Cek jadwal yang masih kosong, kirim booking, lalu pantau persetujuannya di satu tempat.
                </p>

                <form action="{{ route('spaces.index') }}" method="GET" class="mt-space-lg flex flex-col gap-2 rounded-2xl border border-outline-variant/40 bg-white p-2 shadow-lg shadow-primary/10 sm:flex-row sm:items-center">
                    <div class="relative flex-1">
                        <span class="material-symbols-outlined pointer-events-none absolute left-3 top-3 text-[20px] text-on-surface-variant">search</span>
                        <label for="home-q" class="sr-only">Nama atau lokasi ruang</label>
                        <input id="home-q" type="text" name="q" placeholder="Cari nama atau lokasi ruang" class="input h-11 border-transparent pl-10 text-base sm:text-body-md">
                    </div>
                    <div>
                        <label for="home-type" class="sr-only">Tipe ruang</label>
                        <select id="home-type" name="type" class="input h-11 border-transparent bg-surface-container-low text-base sm:w-44 sm:text-body-md">
                            <option value="">Semua tipe</option>
                            @foreach (\App\Models\Space::TYPES as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="btn-primary h-11 px-6">
                        <span class="material-symbols-outlined text-[18px]">search</span> Cari ruang
                    </button>
                </form>

                <div class="mt-space-md flex flex-wrap items-center gap-2 text-body-sm">
                    <span class="text-on-surface-variant">Cari cepat:</span>
                    @foreach (\App\Models\Space::TYPES as $key => $label)
                        <a href="{{ route('spaces.index', ['type' => $key]) }}" class="rounded-full border border-outline-variant/60 bg-white/70 px-3 py-1 font-medium text-on-surface-variant transition-colors hover:border-primary hover:text-primary">{{ $label }}</a>
                    @endforeach
                </div>

                <dl class="mt-space-lg flex flex-wrap gap-x-8 gap-y-3 border-t border-outline-variant/40 pt-space-md">
                    <div>
                        <dt class="text-body-sm text-on-surface-variant">Ruang aktif</dt>
                        <dd class="text-headline-md font-bold text-primary">{{ $totalSpaces }}</dd>
                    </div>
                    <div>
                        <dt class="text-body-sm text-on-surface-variant">Tipe ruang</dt>
                        <dd class="text-headline-md font-bold text-primary">{{ count(\App\Models\Space::TYPES) }}</dd>
                    </div>
                    <div>
                        <dt class="text-body-sm text-on-surface-variant">Proses booking</dt>
                        <dd class="text-headline-md font-bold text-primary">Online</dd>
                    </div>
                </dl>
            </div>

            <div class="relative">
                @include('partials.schedule-board')

                <div aria-hidden="true" class="absolute -right-2 -top-4 hidden items-center gap-2 rounded-xl border border-outline-variant/40 bg-white px-3 py-2 shadow-lg sm:flex">
                    <span class="flex size-8 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                        <span class="material-symbols-outlined text-[18px]">check_circle</span>
                    </span>
                    <span class="text-body-sm"><strong class="block text-label-md">Booking disetujui</strong>Ruang Meeting, 10:00</span>
                </div>
                <div aria-hidden="true" class="absolute -bottom-4 -left-2 hidden items-center gap-2 rounded-xl border border-outline-variant/40 bg-white px-3 py-2 shadow-lg sm:flex">
                    <span class="flex size-8 items-center justify-center rounded-full bg-primary-fixed text-primary">
                        <span class="material-symbols-outlined text-[18px]">bolt</span>
                    </span>
                    <span class="text-body-sm"><strong class="block text-label-md">Cek jadwal instan</strong>Tanpa telepon, tanpa chat</span>
                </div>
            </div>
        </div>
    </section>

    {{-- Tipe ruang --}}
    <section class="mt-14 sm:mt-20">
        <div class="mb-space-lg">
            <span class="text-label-sm font-semibold uppercase tracking-wide text-primary">Jelajahi</span>
            <h2 class="text-headline-lg font-bold tracking-tight">Pilih tipe ruang sesuai kebutuhan</h2>
        </div>
        <div class="grid grid-cols-2 gap-space-md lg:grid-cols-4">
            @foreach (\App\Models\Space::TYPES as $key => $label)
                @php [$icon, $tone, $tagline] = $typeMeta[$key] ?? ['domain', 'bg-primary-fixed text-primary', '']; @endphp
                <a href="{{ route('spaces.index', ['type' => $key]) }}"
                   class="card group flex flex-col gap-space-md p-space-lg transition-all duration-200 hover:-translate-y-1 hover:border-primary/40 hover:shadow-lg">
                    <span class="flex size-12 items-center justify-center rounded-xl {{ $tone }}">
                        <span class="material-symbols-outlined text-[26px]">{{ $icon }}</span>
                    </span>
                    <div>
                        <h3 class="text-title-card font-semibold">{{ $label }}</h3>
                        <p class="mt-0.5 text-body-sm text-on-surface-variant">{{ $tagline }}</p>
                    </div>
                    <div class="mt-auto flex items-center justify-between text-body-md">
                        <span class="font-medium text-on-surface-variant">{{ $typeCounts[$key] ?? 0 }} ruang</span>
                        <span class="material-symbols-outlined text-[20px] text-primary transition-transform group-hover:translate-x-1">arrow_forward</span>
                    </div>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Ruang terbaru --}}
    @if ($featured->isNotEmpty())
        <section class="mt-14 sm:mt-20">
            <div class="mb-space-lg flex items-end justify-between gap-4">
                <div>
                    <span class="text-label-sm font-semibold uppercase tracking-wide text-primary">Baru ditambahkan</span>
                    <h2 class="text-headline-lg font-bold tracking-tight">Ruang terbaru</h2>
                    <p class="mt-1 text-body-md text-on-surface-variant">Siap dipesan hari ini.</p>
                </div>
                <a href="{{ route('spaces.index') }}" class="flex shrink-0 items-center gap-1 text-label-md font-medium text-primary hover:underline">
                    Lihat semua <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                </a>
            </div>

            <div class="grid gap-space-lg sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($featured as $space)
                    <a href="{{ route('spaces.show', $space) }}" class="card group flex flex-col overflow-hidden transition-all duration-200 hover:-translate-y-1 hover:shadow-xl">
                        <div class="relative aspect-[16/10] overflow-hidden bg-surface-container">
                            @include('partials.space-photo', ['space' => $space, 'class' => 'h-full w-full transition-transform duration-500 group-hover:scale-105'])
                            <div aria-hidden="true" class="absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-black/50 to-transparent"></div>
                            <span class="absolute left-3 top-3">@include('partials.type-chip', ['type' => $space->type])</span>
                            <span class="absolute bottom-3 right-3 rounded-lg bg-white/95 px-3 py-1.5 text-label-md font-bold text-primary shadow">
                                Rp {{ number_format($space->price_per_hour, 0, ',', '.') }}<span class="text-body-sm font-normal text-on-surface-variant"> / jam</span>
                            </span>
                        </div>
                        <div class="flex flex-1 flex-col gap-space-sm p-space-lg">
                            <h3 class="text-title-card font-semibold transition-colors group-hover:text-primary">{{ $space->name }}</h3>
                            <div class="flex items-center gap-1 text-body-sm text-on-surface-variant">
                                <span class="material-symbols-outlined text-[16px] text-primary">location_on</span>{{ $space->location }}
                            </div>
                            <div class="mt-auto flex items-center justify-between border-t border-outline-variant/30 pt-space-sm text-body-md text-on-surface-variant">
                                <span class="flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[18px]">groups</span>{{ $space->capacity }} orang
                                </span>
                                <span class="flex items-center gap-1 font-medium text-primary">Lihat detail <span class="material-symbols-outlined text-[18px]">arrow_forward</span></span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Keunggulan --}}
    <section class="mt-14 sm:mt-20">
        <div class="mb-space-lg max-w-2xl">
            <span class="text-label-sm font-semibold uppercase tracking-wide text-primary">Kenapa SpaceHub</span>
            <h2 class="text-headline-lg font-bold tracking-tight">Pesan ruang tanpa drama</h2>
        </div>
        <div class="grid gap-space-md sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($benefits as [$icon, $title, $text, $tone])
                <div class="rounded-2xl border border-outline-variant/30 bg-surface-container-lowest p-space-lg">
                    <span class="flex size-11 items-center justify-center rounded-xl {{ $tone }}">
                        <span class="material-symbols-outlined">{{ $icon }}</span>
                    </span>
                    <h3 class="mt-space-md text-title-card font-semibold">{{ $title }}</h3>
                    <p class="mt-1 text-body-md text-on-surface-variant">{{ $text }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Cara memesan --}}
    <section class="mt-14 sm:mt-20">
        <div class="mb-space-lg">
            <span class="text-label-sm font-semibold uppercase tracking-wide text-primary">Mudah</span>
            <h2 class="text-headline-lg font-bold tracking-tight">Cara memesan</h2>
        </div>
        <ol class="relative grid gap-space-lg md:grid-cols-3">
            <div aria-hidden="true" class="absolute left-[16%] right-[16%] top-6 hidden border-t-2 border-dashed border-primary/30 md:block"></div>
            @foreach ([
                ['search', 'Pilih ruang', 'Telusuri ruang berdasarkan tipe, kapasitas, dan fasilitas.'],
                ['edit_calendar', 'Tentukan jadwal', 'Pilih tanggal dan jam. Jadwal yang bentrok otomatis ditolak.'],
                ['task_alt', 'Tunggu persetujuan', 'Admin meninjau booking kamu. Pantau statusnya di Booking Saya.'],
            ] as $i => [$icon, $title, $text])
                <li class="relative flex flex-col items-center text-center">
                    <span class="relative z-10 flex size-12 items-center justify-center rounded-full bg-primary text-white shadow-lg shadow-primary/30">
                        <span class="material-symbols-outlined">{{ $icon }}</span>
                    </span>
                    <span class="mt-space-sm text-label-sm font-bold uppercase tracking-wide text-primary">Langkah {{ $i + 1 }}</span>
                    <h3 class="mt-1 text-title-card font-semibold">{{ $title }}</h3>
                    <p class="mt-1 max-w-xs text-body-md text-on-surface-variant">{{ $text }}</p>
                </li>
            @endforeach
        </ol>
    </section>

    {{-- Ajakan --}}
    <section class="relative isolate mt-14 overflow-hidden rounded-3xl bg-gradient-to-br from-primary via-primary-container to-violet-600 p-space-lg text-white sm:mt-20 sm:p-12">
        <div aria-hidden="true" class="pointer-events-none absolute -right-16 -top-16 -z-10 size-64 rounded-full bg-white/10 blur-2xl"></div>
        <div aria-hidden="true" class="pointer-events-none absolute -bottom-20 left-1/3 -z-10 size-64 rounded-full bg-secondary-fixed-dim/20 blur-3xl"></div>

        <div class="flex flex-col gap-space-lg md:flex-row md:items-center md:justify-between">
            @guest
                <div class="max-w-xl">
                    <h2 class="text-headline-lg font-bold tracking-tight text-balance">Siap memesan ruang pertama kamu?</h2>
                    <p class="mt-1 text-body-lg text-on-primary-container">Buat akun gratis, lalu pilih ruang dan jadwalnya.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="inline-flex h-12 items-center justify-center rounded-xl bg-white px-6 text-label-md font-semibold text-primary shadow-lg transition-transform hover:-translate-y-0.5">Buat akun</a>
                    <a href="{{ route('login') }}" class="inline-flex h-12 items-center justify-center rounded-xl border border-white/40 px-6 text-label-md font-medium text-white transition-colors hover:bg-white/10">Masuk</a>
                </div>
            @else
                @can('admin')
                    <div class="max-w-xl">
                        <h2 class="text-headline-lg font-bold tracking-tight text-balance">Ada permohonan yang menunggu?</h2>
                        <p class="mt-1 text-body-lg text-on-primary-container">Tinjau booking dan kelola ruang dari panel admin.</p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('admin.dashboard') }}" class="inline-flex h-12 items-center justify-center rounded-xl bg-white px-6 text-label-md font-semibold text-primary shadow-lg transition-transform hover:-translate-y-0.5">Buka dashboard</a>
                        <a href="{{ route('admin.spaces.index') }}" class="inline-flex h-12 items-center justify-center rounded-xl border border-white/40 px-6 text-label-md font-medium text-white transition-colors hover:bg-white/10">Kelola ruang</a>
                    </div>
                @else
                    <div class="max-w-xl">
                        <h2 class="text-headline-lg font-bold tracking-tight text-balance">Butuh ruang untuk kegiatan berikutnya?</h2>
                        <p class="mt-1 text-body-lg text-on-primary-container">Cari ruang yang kosong, atau cek status booking kamu.</p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('spaces.index') }}" class="inline-flex h-12 items-center justify-center rounded-xl bg-white px-6 text-label-md font-semibold text-primary shadow-lg transition-transform hover:-translate-y-0.5">Cari ruang</a>
                        <a href="{{ route('bookings.index') }}" class="inline-flex h-12 items-center justify-center rounded-xl border border-white/40 px-6 text-label-md font-medium text-white transition-colors hover:bg-white/10">Booking saya</a>
                    </div>
                @endcan
            @endguest
        </div>
    </section>
@endsection
