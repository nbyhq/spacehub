@extends('layouts.app')

@section('title', 'SpaceHub - Booking Ruang Kerja')

@section('content')
    <section class="grid items-center gap-space-xl py-space-lg lg:grid-cols-2">
        <div>
            <span class="inline-flex items-center gap-1 rounded-full bg-surface-container-high px-3 py-1 text-label-sm font-semibold text-primary">
                <span class="material-symbols-outlined text-[16px]">domain_verification</span>
                Reservasi Ruang Real-Time
            </span>
            <h1 class="mt-space-md text-headline-lg font-bold tracking-tight md:text-5xl md:leading-tight">
                Temukan ruang kerja ideal untuk tim Anda
            </h1>
            <p class="mt-space-md max-w-xl text-body-lg text-on-surface-variant">
                Pilih dan pesan ruang meeting, coworking, private office, atau event hall lengkap dengan fasilitasnya.
            </p>
            <div class="mt-space-lg flex flex-wrap gap-3">
                <a href="{{ route('spaces.index') }}" class="btn-primary h-12 px-6">
                    Lihat Semua Ruang
                    <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                </a>
                @guest
                    <a href="{{ route('register') }}" class="btn-outline h-12 px-6">Buat Akun</a>
                @endguest
            </div>
        </div>

        <form action="{{ route('spaces.index') }}" method="GET" class="card space-y-4 p-space-lg">
            <h2 class="text-title-card font-semibold">Cari ruang sekarang</h2>
            <div>
                <label class="label" for="home-q">Nama atau lokasi</label>
                <input id="home-q" type="text" name="q" placeholder="Cari nama ruang atau lokasi..." class="input">
            </div>
            <div>
                <label class="label" for="home-type">Tipe ruang</label>
                <select id="home-type" name="type" class="input">
                    <option value="">Semua tipe</option>
                    @foreach (\App\Models\Space::TYPES as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn-primary w-full">
                <span class="material-symbols-outlined text-[18px]">search</span>
                Cari Ruang
            </button>
        </form>
    </section>

    <section class="mt-space-xl">
        <h2 class="mb-space-lg text-headline-md font-semibold">Cara kerja</h2>
        <div class="grid gap-space-lg md:grid-cols-3">
            @foreach ([
                ['search', 'Pilih ruang', 'Jelajahi ruang berdasarkan tipe, kapasitas, dan fasilitas.'],
                ['event_available', 'Tentukan jadwal', 'Pilih tanggal dan jam. Jadwal yang bentrok otomatis ditolak.'],
                ['task_alt', 'Tunggu persetujuan', 'Admin meninjau booking dan statusnya bisa dipantau di Booking Saya.'],
            ] as $i => [$icon, $title, $text])
                <div class="card p-space-lg">
                    <span class="flex size-11 items-center justify-center rounded-xl bg-primary-fixed text-primary">
                        <span class="material-symbols-outlined">{{ $icon }}</span>
                    </span>
                    <h3 class="mt-space-md text-title-card font-semibold">{{ $i + 1 }}. {{ $title }}</h3>
                    <p class="mt-1 text-body-md text-on-surface-variant">{{ $text }}</p>
                </div>
            @endforeach
        </div>
    </section>
@endsection
