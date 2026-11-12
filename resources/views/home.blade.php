@extends('layouts.app')

@section('title', 'SpaceHub - Booking Ruang Kerja')

@section('content')
    @php
        $typeIcons = [
            'coworking' => 'desk',
            'meeting_room' => 'meeting_room',
            'private_office' => 'business_center',
            'event_hall' => 'celebration',
        ];
    @endphp

    {{-- Hero --}}
    <section class="grid items-center gap-space-xl py-space-lg lg:grid-cols-[1.1fr_1fr] lg:py-space-xl">
        <div>
            <h1 class="text-4xl font-bold leading-tight tracking-tight text-balance md:text-5xl md:leading-[1.1]">
                Atur reservasi ruang kerja tanpa ribet.
            </h1>
            <p class="mt-space-md max-w-xl text-body-lg text-on-surface-variant">
                Pilih meeting room, coworking desk, private office, hingga event hall sesuai kebutuhan. Cek jadwal tersedia secara real-time, ajukan booking, dan pantau persetujuannya di satu tempat.
            </p>

            <form action="{{ route('spaces.index') }}" method="GET" class="card mt-space-lg flex flex-col gap-2 p-2 sm:flex-row sm:items-center">
                <div class="relative flex-1">
                    <span class="material-symbols-outlined pointer-events-none absolute left-3 top-3 text-[20px] text-on-surface-variant">search</span>
                    <label for="home-q" class="sr-only">Nama atau lokasi ruang</label>
                    <input id="home-q" type="text" name="q" placeholder="Cari nama atau lokasi ruang" class="input h-11 pl-10 text-base sm:text-body-md">
                </div>
                <div>
                    <label for="home-type" class="sr-only">Tipe ruang</label>
                    <select id="home-type" name="type" class="input h-11 text-base sm:w-44 sm:text-body-md">
                        <option value="">Semua tipe</option>
                        @foreach (\App\Models\Space::TYPES as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="btn-primary h-11 px-6">Cari ruang</button>
            </form>

            @guest
                <p class="mt-space-md text-body-md text-on-surface-variant">
                    Belum punya akun?
                    <a href="{{ route('register') }}" class="font-medium text-primary hover:underline">Daftar gratis</a>
                </p>
            @endguest
        </div>

        @include('partials.schedule-board')
    </section>

    {{-- Tipe ruang --}}
    <section class="mt-space-xl">
        <div class="card grid grid-cols-2 gap-px overflow-hidden bg-outline-variant/40 lg:grid-cols-4">
            @foreach (\App\Models\Space::TYPES as $key => $label)
                <a href="{{ route('spaces.index', ['type' => $key]) }}"
                   class="flex items-center gap-3 bg-surface-container-lowest p-space-md transition-colors hover:bg-surface-container-low">
                    <span class="flex size-11 shrink-0 items-center justify-center rounded-lg bg-primary-fixed text-primary">
                        <span class="material-symbols-outlined">{{ $typeIcons[$key] ?? 'domain' }}</span>
                    </span>
                    <span class="min-w-0">
                        <span class="block truncate text-label-md font-semibold">{{ $label }}</span>
                        <span class="block text-body-sm text-on-surface-variant">{{ $typeCounts[$key] ?? 0 }} ruang</span>
                    </span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Ruang pilihan --}}
    @if ($featured->isNotEmpty())
        <section class="mt-14 sm:mt-20">
            <div class="mb-space-lg flex items-end justify-between gap-4">
                <div>
                    <h2 class="text-headline-lg font-bold tracking-tight">Ruang terbaru</h2>
                    <p class="mt-1 text-body-md text-on-surface-variant">Ruang yang baru ditambahkan dan siap dipesan.</p>
                </div>
                <a href="{{ route('spaces.index') }}" class="shrink-0 text-label-md font-medium text-primary hover:underline">Lihat semua ruang</a>
            </div>

            <div class="grid gap-space-lg sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($featured as $space)
                    <a href="{{ route('spaces.show', $space) }}" class="card group flex flex-col overflow-hidden transition-shadow hover:shadow-md">
                        <div class="relative aspect-[16/10] overflow-hidden bg-surface-container">
                            @include('partials.space-photo', ['space' => $space, 'class' => 'h-full w-full transition-transform duration-500 group-hover:scale-105'])
                            <span class="absolute left-3 top-3">@include('partials.type-chip', ['type' => $space->type])</span>
                        </div>
                        <div class="flex flex-1 flex-col justify-between gap-space-md p-space-lg">
                            <div>
                                <h3 class="text-title-card font-semibold group-hover:text-primary">{{ $space->name }}</h3>
                                <div class="mt-1 flex items-center gap-1 text-body-sm text-on-surface-variant">
                                    <span class="material-symbols-outlined text-[16px] text-primary">location_on</span>{{ $space->location }}
                                </div>
                            </div>
                            <div class="flex items-end justify-between gap-3">
                                <span class="flex items-center gap-1 text-body-md text-on-surface-variant">
                                    <span class="material-symbols-outlined text-[18px]">groups</span>{{ $space->capacity }} orang
                                </span>
                                <span class="text-title-card font-bold text-primary">
                                    Rp {{ number_format($space->price_per_hour, 0, ',', '.') }}
                                    <span class="text-body-sm font-normal text-on-surface-variant">/ jam</span>
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Cara kerja --}}
    <section class="mt-14 sm:mt-20">
        <h2 class="mb-space-lg text-headline-lg font-bold tracking-tight">Cara memesan</h2>
        <ol class="grid gap-space-lg md:grid-cols-3">
            @foreach ([
                ['Pilih ruang', 'Telusuri ruang berdasarkan tipe, kapasitas, dan fasilitas.'],
                ['Tentukan jadwal', 'Pilih tanggal dan jam. Jadwal yang bentrok otomatis ditolak.'],
                ['Tunggu persetujuan', 'Admin meninjau booking Anda. Pantau statusnya di Booking Saya.'],
            ] as $i => [$title, $text])
                <li class="border-t-2 border-primary pt-space-md">
                    <span class="text-headline-lg font-bold text-primary">{{ $i + 1 }}</span>
                    <h3 class="mt-1 text-title-card font-semibold">{{ $title }}</h3>
                    <p class="mt-1 text-body-md text-on-surface-variant">{{ $text }}</p>
                </li>
            @endforeach
        </ol>
    </section>

    {{-- Ajakan --}}
    <section class="mt-14 flex flex-col gap-space-lg rounded-2xl bg-primary p-space-lg text-white sm:mt-20 sm:p-10 md:flex-row md:items-center md:justify-between">
        @guest
            <div class="max-w-xl">
                <h2 class="text-headline-lg font-bold tracking-tight">Siap memesan ruang pertama Anda?</h2>
                <p class="mt-1 text-body-lg text-on-primary-container">Buat akun gratis, lalu pilih ruang dan jadwalnya.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('register') }}" class="inline-flex h-11 items-center justify-center rounded-lg bg-white px-5 text-label-md font-medium text-primary transition-colors hover:bg-primary-fixed">Buat akun</a>
                <a href="{{ route('login') }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-white/40 px-5 text-label-md font-medium text-white transition-colors hover:bg-white/10">Masuk</a>
            </div>
        @else
            <div class="max-w-xl">
                <h2 class="text-headline-lg font-bold tracking-tight">Butuh ruang untuk kegiatan berikutnya?</h2>
                <p class="mt-1 text-body-lg text-on-primary-container">Cari ruang yang kosong, atau cek status booking Anda.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('spaces.index') }}" class="inline-flex h-11 items-center justify-center rounded-lg bg-white px-5 text-label-md font-medium text-primary transition-colors hover:bg-primary-fixed">Cari ruang</a>
                <a href="{{ route('bookings.index') }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-white/40 px-5 text-label-md font-medium text-white transition-colors hover:bg-white/10">Booking saya</a>
            </div>
        @endguest
    </section>
@endsection
