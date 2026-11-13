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

                {{-- Lebar tiap zona dikunci supaya semua kartu sejajar: foto | info (fleksibel) | harga & aksi --}}
                <div class="grid min-w-0 flex-1 gap-space-md p-space-md lg:grid-cols-[10rem_minmax(0,1fr)_15rem] lg:items-center lg:gap-space-lg">
                    <div class="h-40 w-full overflow-hidden rounded-lg lg:h-28 lg:w-40">
                        @include('partials.space-photo', ['space' => $space, 'class' => 'h-full w-full'])
                    </div>

                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-mono text-label-sm font-semibold text-primary">{{ $booking->code }}</span>
                            @include('partials.status', ['status' => $booking->status])
                        </div>
                        <h2 class="mt-1 truncate text-title-card font-semibold {{ $booking->status === 'cancelled' ? 'text-on-surface-variant line-through' : '' }}">{{ $space->name }}</h2>

                        <dl class="mt-space-sm grid gap-x-space-md gap-y-space-sm sm:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)_minmax(0,1fr)]">
                            <div>
                                <dt class="text-body-sm text-on-surface-variant">Tanggal &amp; Jam</dt>
                                <dd class="mt-0.5 flex items-start gap-1.5 text-body-md font-medium">
                                    <span class="material-symbols-outlined mt-px text-[18px] text-primary">schedule</span>
                                    <span>{{ $booking->booking_date->format('d M Y') }}, {{ $booking->time_range }} <span class="font-normal text-on-surface-variant">({{ $booking->duration_label }})</span></span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-body-sm text-on-surface-variant">Lokasi</dt>
                                <dd class="mt-0.5 flex items-start gap-1.5 text-body-md font-medium">
                                    <span class="material-symbols-outlined mt-px text-[18px] text-primary">location_on</span>
                                    <span class="min-w-0 break-words">{{ $space->location }}</span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-body-sm text-on-surface-variant">Kapasitas</dt>
                                <dd class="mt-0.5 flex items-start gap-1.5 text-body-md font-medium">
                                    <span class="material-symbols-outlined mt-px text-[18px] text-primary">groups</span>
                                    <span>{{ $space->capacity }} orang</span>
                                </dd>
                            </div>
                        </dl>

                        @if ($booking->notes)
                            <p class="mt-space-sm flex items-start gap-1.5 rounded-lg bg-surface-container-low px-3 py-1.5 text-body-sm text-on-surface-variant">
                                <span class="material-symbols-outlined text-[16px]">sticky_note_2</span>
                                <span class="min-w-0 break-words">{{ \Illuminate\Support\Str::limit($booking->notes, 80) }}</span>
                            </p>
                        @endif
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-x-space-md gap-y-space-sm border-t border-outline-variant/30 pt-space-md lg:flex-col lg:items-end lg:justify-center lg:border-0 lg:pt-0">
                        <div class="lg:text-right">
                            <div class="text-body-sm text-on-surface-variant">Estimasi Biaya</div>
                            <div class="text-headline-md font-bold {{ $booking->status === 'cancelled' ? 'text-on-surface-variant' : '' }}">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</div>
                        </div>
                        <div class="flex gap-2">
                            @if (! $space->trashed() && $space->is_active)
                                <a href="{{ route('spaces.show', $space) }}" class="btn-outline h-9 w-28 px-3">Lihat Ruang</a>
                            @endif
                            @if (in_array($booking->status, ['pending', 'approved']) && $upcoming)
                                <form method="POST" action="{{ route('bookings.cancel', $booking) }}" onsubmit="return confirm('Batalkan booking ini?')">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn-danger h-9 w-28 px-3">Batalkan</button>
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
