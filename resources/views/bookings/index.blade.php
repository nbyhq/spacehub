@extends('layouts.app')

@section('title', 'Booking Saya - SpaceHub')

@section('content')
    @php
        $tabs = ['semua' => 'Semua Booking', 'aktif' => 'Mendatang / Aktif', 'selesai' => 'Selesai', 'dibatalkan' => 'Dibatalkan'];
        $strip = ['pending' => 'bg-amber-400', 'approved' => 'bg-emerald-500', 'cancelled' => 'bg-slate-300'];
    @endphp

    <div class="mb-space-lg flex flex-col justify-between gap-space-md md:flex-row md:items-end">
        <div>
            <span class="flex items-center gap-1 text-label-sm font-semibold text-primary">
                <span class="material-symbols-outlined text-[16px]">calendar_month</span> Riwayat Reservasi
            </span>
            <h1 class="text-headline-lg font-bold tracking-tight md:text-headline-xl">Booking Saya</h1>
            <p class="mt-1 text-body-lg text-on-surface-variant">Kelola jadwal pemesanan ruang kerja Anda.</p>
        </div>
        <div class="card flex items-center gap-2 self-start p-2 text-label-md md:self-auto">
            <span class="flex items-center gap-1.5 rounded-lg bg-surface-container-low px-3 py-1.5">
                <span class="size-2 rounded-full bg-emerald-600"></span> {{ $counts['aktif'] }} Aktif
            </span>
            <span class="flex items-center gap-1.5 rounded-lg bg-surface-container-low px-3 py-1.5">
                <span class="size-2 rounded-full bg-amber-500"></span> {{ $pendingCount }} Menunggu
            </span>
        </div>
    </div>

    <div class="mb-space-md flex items-center gap-space-sm overflow-x-auto pb-1">
        @foreach ($tabs as $key => $label)
            <a href="{{ route('bookings.index', array_filter(['tab' => $key === 'semua' ? null : $key, 'q' => request('q')])) }}"
               class="flex shrink-0 items-center gap-2 rounded-lg px-4 py-2 text-label-md font-medium transition-colors {{ $tab === $key ? 'bg-primary-container text-white shadow-sm' : 'bg-surface-container-lowest text-on-surface-variant hover:bg-surface-container-low' }}">
                {{ $label }}
                <span class="rounded-full px-2 text-label-sm {{ $tab === $key ? 'bg-white/20' : 'bg-primary-fixed text-on-primary-fixed' }}">{{ $counts[$key] }}</span>
            </a>
        @endforeach
    </div>

    <form method="GET" action="{{ route('bookings.index') }}" class="card mb-space-lg flex gap-2 p-3">
        @if ($tab !== 'semua')
            <input type="hidden" name="tab" value="{{ $tab }}">
        @endif
        <div class="relative flex-1">
            <span class="material-symbols-outlined pointer-events-none absolute left-3 top-2.5 text-[20px] text-on-surface-variant">search</span>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama ruang..." class="input pl-10">
        </div>
        <button class="btn-primary">Cari</button>
    </form>

    <div class="space-y-space-md">
        @forelse ($bookings as $booking)
            @php
                $space = $booking->space;
                $upcoming = $booking->booking_date->gte(today());
            @endphp
            <article class="card flex overflow-hidden">
                <div class="w-1.5 shrink-0 {{ $strip[$booking->status] ?? 'bg-slate-300' }}"></div>
                <div class="flex flex-1 flex-col gap-space-md p-space-md md:flex-row md:items-center">
                    <div class="h-28 w-full shrink-0 overflow-hidden rounded-lg md:w-40">
                        @include('partials.space-photo', ['space' => $space, 'class' => 'h-full w-full'])
                    </div>

                    <div class="flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-mono text-label-sm font-semibold text-primary">{{ $booking->code }}</span>
                            @include('partials.status', ['status' => $booking->status])
                        </div>
                        <h2 class="mt-1 text-title-card font-semibold {{ $booking->status === 'cancelled' ? 'text-on-surface-variant line-through' : '' }}">{{ $space->name }}</h2>
                        <div class="mt-space-sm grid gap-1 text-body-md text-on-surface-variant sm:grid-cols-2">
                            <span class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[18px]">schedule</span>
                                <span class="font-medium text-on-surface">{{ $booking->booking_date->format('d M Y') }}, {{ $booking->time_range }}</span>
                                ({{ $booking->duration_label }})
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[18px]">location_on</span>{{ $space->location }}
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[18px]">groups</span>Kapasitas {{ $space->capacity }} orang
                            </span>
                            @if ($booking->notes)
                                <span class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[18px]">sticky_note_2</span>{{ \Illuminate\Support\Str::limit($booking->notes, 40) }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-col items-start gap-2 md:items-end">
                        <div class="text-body-sm text-on-surface-variant">Estimasi Biaya</div>
                        <div class="text-headline-md font-bold">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</div>
                        <div class="flex flex-wrap gap-2">
                            @if (! $space->trashed() && $space->is_active)
                                <a href="{{ route('spaces.show', $space) }}" class="btn-outline">Lihat Ruang</a>
                            @endif
                            @if (in_array($booking->status, ['pending', 'approved']) && $upcoming)
                                <form method="POST" action="{{ route('bookings.cancel', $booking) }}" onsubmit="return confirm('Batalkan booking ini?')">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn-danger">Batalkan</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <div class="card flex flex-col items-center gap-2 py-16 text-center text-on-surface-variant">
                <span class="material-symbols-outlined text-[40px]">event_busy</span>
                <p>Belum ada booking di kategori ini.</p>
                <a href="{{ route('spaces.index') }}" class="text-label-md font-medium text-primary hover:underline">Cari ruang</a>
            </div>
        @endforelse
    </div>

    @if ($bookings->total() > 0)
        <div class="card mt-space-lg flex flex-col items-center justify-between gap-3 px-space-lg py-space-md sm:flex-row">
            <p class="text-body-md text-on-surface-variant">
                Menampilkan <strong>{{ $bookings->firstItem() }} - {{ $bookings->lastItem() }}</strong> dari <strong>{{ $bookings->total() }}</strong> booking
            </p>
            <div>{{ $bookings->links() }}</div>
        </div>
    @endif
@endsection
