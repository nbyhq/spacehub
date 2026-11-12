@extends('layouts.app')

@section('title', $space->name.' - SpaceHub')

@section('content')
    <nav class="mb-space-lg flex flex-wrap items-center gap-1 text-body-sm text-on-surface-variant">
        <a href="{{ route('spaces.index') }}" class="hover:text-primary">Ruang</a>
        <span class="material-symbols-outlined text-[16px]">chevron_right</span>
        <a href="{{ route('spaces.index', ['type' => $space->type]) }}" class="hover:text-primary">{{ \App\Models\Space::TYPES[$space->type] ?? $space->type }}</a>
        <span class="material-symbols-outlined text-[16px]">chevron_right</span>
        <span class="font-medium text-on-surface">{{ $space->name }}</span>
    </nav>

    <div class="grid gap-space-lg lg:grid-cols-12">
        <div class="space-y-space-lg lg:col-span-8">
            <div class="card p-space-lg">
                @include('partials.type-chip', ['type' => $space->type])
                <h1 class="mt-space-sm text-headline-lg font-bold tracking-tight md:text-headline-xl">{{ $space->name }}</h1>
                <p class="mt-1 flex items-center gap-1 text-body-md text-on-surface-variant">
                    <span class="material-symbols-outlined text-[18px] text-primary">location_on</span>
                    {{ $space->location }}
                </p>
            </div>

            <a href="#booking-form" class="btn-primary h-12 w-full lg:hidden">
                <span class="material-symbols-outlined text-[18px]">event_available</span> Pesan Ruang Ini
            </a>

            <div class="card overflow-hidden">
                @include('partials.space-photo', ['space' => $space, 'class' => 'aspect-video w-full'])
            </div>

            <div class="card p-space-lg">
                <h2 class="text-headline-md font-semibold">Tentang Ruangan Ini</h2>
                <p class="mt-space-sm whitespace-pre-line text-body-lg text-on-surface-variant">{{ $space->description ?: 'Belum ada deskripsi untuk ruang ini.' }}</p>

                <div class="mt-space-lg grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div class="flex items-center gap-3 rounded-lg bg-primary-fixed/50 p-3">
                        <span class="material-symbols-outlined text-primary">groups</span>
                        <div>
                            <div class="text-body-sm text-on-surface-variant">Kapasitas</div>
                            <div class="text-label-md font-semibold">Maks. {{ $space->capacity }} Orang</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 rounded-lg bg-primary-fixed/50 p-3">
                        <span class="material-symbols-outlined text-primary">category</span>
                        <div>
                            <div class="text-body-sm text-on-surface-variant">Tipe</div>
                            <div class="text-label-md font-semibold">{{ \App\Models\Space::TYPES[$space->type] ?? $space->type }}</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 rounded-lg bg-primary-fixed/50 p-3">
                        <span class="material-symbols-outlined text-primary">payments</span>
                        <div>
                            <div class="text-body-sm text-on-surface-variant">Tarif</div>
                            <div class="text-label-md font-semibold">Rp {{ number_format($space->price_per_hour, 0, ',', '.') }} / jam</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card p-space-lg">
                <div class="flex items-center justify-between">
                    <h2 class="text-headline-md font-semibold">Fasilitas Termasuk</h2>
                    <span class="text-body-sm text-on-surface-variant">{{ $space->facilities->count() }} fasilitas</span>
                </div>
                <div class="mt-space-md grid gap-3 sm:grid-cols-2">
                    @forelse ($space->facilities as $facility)
                        <div class="flex items-center gap-3">
                            <span class="flex size-10 items-center justify-center rounded-lg bg-primary-fixed/60 text-primary">
                                <span class="material-symbols-outlined">{{ $facility->icon }}</span>
                            </span>
                            <span class="text-label-md font-medium">{{ $facility->name }}</span>
                        </div>
                    @empty
                        <p class="text-body-md text-on-surface-variant">Belum ada data fasilitas.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <aside class="space-y-space-lg lg:col-span-4">
            <div id="booking-form" class="card scroll-mt-20 p-space-lg lg:sticky lg:top-24">
                <div class="flex items-baseline justify-between">
                    <div class="text-headline-md font-bold">
                        Rp {{ number_format($space->price_per_hour, 0, ',', '.') }}
                        <span class="text-body-sm font-normal text-on-surface-variant">/ jam</span>
                    </div>
                </div>
                <p class="mt-1 flex items-center gap-1 text-body-sm text-on-surface-variant">
                    <span class="material-symbols-outlined text-[16px]">pending_actions</span>
                    Booking ditinjau admin sebelum disetujui
                </p>

                @auth
                    <form method="POST" action="{{ route('bookings.store', $space) }}" class="mt-space-lg space-y-4">
                        @csrf
                        <div>
                            <label class="label" for="booking_date">Tanggal Pemesanan</label>
                            <input id="booking_date" type="date" name="booking_date" min="{{ today()->toDateString() }}" value="{{ old('booking_date') }}" required class="input">
                            @error('booking_date')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="label" for="start_time">Jam Mulai</label>
                                <input id="start_time" type="time" name="start_time" value="{{ old('start_time') }}" required class="input">
                            </div>
                            <div>
                                <label class="label" for="end_time">Jam Selesai</label>
                                <input id="end_time" type="time" name="end_time" value="{{ old('end_time') }}" required class="input">
                            </div>
                        </div>
                        @error('start_time')
                            <p class="flex items-start gap-1 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-body-sm text-red-700">
                                <span class="material-symbols-outlined text-[16px]">error</span>{{ $message }}
                            </p>
                        @enderror
                        @error('end_time')<p class="field-error">{{ $message }}</p>@enderror
                        <div>
                            <label class="label" for="notes">Catatan Tambahan (Opsional)</label>
                            <textarea id="notes" name="notes" rows="2" class="input" placeholder="Contoh: butuh kabel HDMI tambahan">{{ old('notes') }}</textarea>
                            @error('notes')<p class="field-error">{{ $message }}</p>@enderror
                        </div>

                        <div id="estimate-box" class="hidden rounded-lg bg-primary-fixed/50 p-3">
                            <div class="flex justify-between text-body-md text-on-surface-variant">
                                <span>Durasi</span><span id="estimate-duration" class="font-medium text-on-surface"></span>
                            </div>
                            <div class="mt-1 flex items-center justify-between">
                                <span class="text-label-md font-semibold">Estimasi Biaya</span>
                                <span id="estimate-total" class="text-title-card font-bold text-primary"></span>
                            </div>
                        </div>

                        <button class="btn-primary h-12 w-full">
                            Kirim Booking <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                        </button>
                    </form>
                @else
                    <div class="mt-space-lg rounded-lg bg-surface-container-low p-4 text-body-md text-on-surface-variant">
                        Silakan <a href="{{ route('login') }}" class="font-medium text-primary hover:underline">masuk</a>
                        atau <a href="{{ route('register') }}" class="font-medium text-primary hover:underline">daftar</a> untuk melakukan booking.
                    </div>
                @endauth
            </div>

            <div class="card p-space-lg">
                <h2 class="text-title-card font-semibold">Jadwal Terisi</h2>
                <ul class="mt-space-md space-y-3 text-body-md">
                    @forelse ($booked as $b)
                        <li class="flex items-center justify-between gap-2">
                            <span>
                                <span class="font-medium">{{ $b->booking_date->format('d M Y') }}</span>
                                <span class="text-on-surface-variant">, {{ $b->time_range }}</span>
                            </span>
                            @include('partials.status', ['status' => $b->status])
                        </li>
                    @empty
                        <li class="text-on-surface-variant">Belum ada jadwal terisi.</li>
                    @endforelse
                </ul>
            </div>
        </aside>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            var price = {{ (int) $space->price_per_hour }};
            var start = document.getElementById('start_time');
            var end = document.getElementById('end_time');
            var box = document.getElementById('estimate-box');
            if (!start || !end || !box) return;

            function minutes(value) {
                var p = value.split(':');
                return parseInt(p[0], 10) * 60 + parseInt(p[1], 10);
            }

            function update() {
                if (!start.value || !end.value) { box.classList.add('hidden'); return; }
                var diff = minutes(end.value) - minutes(start.value);
                if (diff <= 0) { box.classList.add('hidden'); return; }
                var hours = diff / 60;
                document.getElementById('estimate-duration').textContent =
                    (Number.isInteger(hours) ? hours : hours.toFixed(1)) + ' jam';
                document.getElementById('estimate-total').textContent =
                    'Rp ' + Math.round(hours * price).toLocaleString('id-ID');
                box.classList.remove('hidden');
            }

            start.addEventListener('input', update);
            end.addEventListener('input', update);
            update();
        })();
    </script>
@endpush
