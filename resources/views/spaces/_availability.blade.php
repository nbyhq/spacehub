@php
    $canBook = auth()->check() && auth()->user()->role !== 'admin';
    $freeCount = collect($slots)->where('state', 'free')->count();
    $cell = [
        'free' => 'border border-dashed border-outline-variant bg-surface-container-lowest text-on-surface',
        'partial' => 'border border-outline-variant bg-surface-container-low text-on-surface-variant',
        'taken' => 'bg-surface-container-high text-on-surface-variant',
        'past' => 'bg-surface-container-low/60 text-outline opacity-60',
    ];
    $label = ['free' => 'Kosong', 'partial' => 'Sebagian', 'taken' => 'Terisi', 'past' => 'Lewat'];
    $closeMin = \App\Support\AvailabilityGrid::toMinutes($space->closesAtLabel());
@endphp

<div id="ketersediaan" class="card scroll-mt-24 p-space-lg">
    <div class="flex flex-wrap items-end justify-between gap-2">
        <div>
            <h2 class="text-headline-md font-semibold">Ketersediaan</h2>
            <p class="mt-1 text-body-md text-on-surface-variant">
                Buka {{ $space->opensAtLabel() }}–{{ $space->closesAtLabel() }}, durasi
                {{ \App\Models\Space::durationLabel($space->minDurationMinutes()) }} sampai {{ \App\Models\Space::durationLabel($space->maxDurationMinutes()) }}.
            </p>
        </div>
        <p class="text-label-md font-semibold">{{ $date->copy()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</p>
    </div>

    <nav aria-label="Pilih tanggal" class="-mx-1 mt-space-md flex gap-2 overflow-x-auto px-1 pb-2">
        @foreach ($days as $day)
            @php $selected = $day->isSameDay($date); @endphp
            <a href="{{ route('spaces.show', [$space, 'date' => $day->toDateString()]) }}#ketersediaan"
               @if ($selected) aria-current="date" @endif
               class="flex w-14 shrink-0 flex-col items-center rounded-lg border px-2 py-2 transition-colors {{ $selected ? 'border-primary bg-primary text-white' : 'border-outline-variant bg-surface-container-lowest hover:bg-surface-container-low' }}">
                <span class="text-body-sm {{ $selected ? 'text-on-primary-container' : 'text-on-surface-variant' }}">{{ $day->copy()->locale('id')->isoFormat('ddd') }}</span>
                <span class="text-title-card font-semibold leading-tight">{{ $day->day }}</span>
                <span class="mt-0.5 size-1.5 rounded-full {{ ($dayCounts[$day->toDateString()] ?? 0) > 0 ? ($selected ? 'bg-white' : 'bg-primary') : 'bg-transparent' }}"
                      title="{{ ($dayCounts[$day->toDateString()] ?? 0) > 0 ? $dayCounts[$day->toDateString()].' booking' : '' }}"></span>
            </a>
        @endforeach
    </nav>

    <div class="mt-space-md grid grid-cols-3 gap-2 sm:grid-cols-4 md:grid-cols-6">
        @foreach ($slots as $slot)
            @php
                $suggestEnd = \App\Support\AvailabilityGrid::format(min($slot['startMin'] + $space->minDurationMinutes(), $closeMin));
                $state = $slot['state'];
                $classes = $cell[$state];
                $text = $label[$state];
                if ($state === 'taken' && $slot['pending']) {
                    $classes = 'bg-amber-100 text-amber-700';
                    $text = 'Menunggu';
                }
                $title = $slot['ranges'] !== '' ? 'Booking: '.$slot['ranges'] : null;
            @endphp

            @if ($state === 'free' && $canBook)
                <button type="button" data-slot data-date="{{ $date->toDateString() }}" data-start="{{ $slot['start'] }}" data-end="{{ $suggestEnd }}"
                        class="flex cursor-pointer flex-col items-start rounded-lg px-3 py-2 text-left transition-colors hover:border-primary hover:bg-primary-fixed/50 focus-visible:outline-2 focus-visible:outline-primary {{ $classes }}">
                    <span class="text-label-md font-semibold tabular-nums">{{ $slot['start'] }}</span>
                    <span class="text-body-sm">{{ $text }}</span>
                </button>
            @else
                <div @if ($title) title="{{ $title }}" @endif class="flex flex-col items-start rounded-lg px-3 py-2 {{ $classes }}">
                    <span class="text-label-md font-semibold tabular-nums">{{ $slot['start'] }}</span>
                    <span class="text-body-sm">{{ $text }}</span>
                </div>
            @endif
        @endforeach
    </div>

    @if ($freeCount === 0)
        <p class="mt-space-md rounded-lg bg-surface-container-low px-3 py-2 text-body-md text-on-surface-variant">
            Tidak ada jam kosong pada tanggal ini. Pilih tanggal lain di atas.
        </p>
    @elseif ($canBook)
        <p class="mt-space-md text-body-sm text-on-surface-variant">Pilih jam yang kosong untuk mengisi formulir booking.</p>
    @else
        <p class="mt-space-md text-body-sm text-on-surface-variant">Masuk dengan akun pengguna untuk memesan jam yang kosong.</p>
    @endif
</div>

@push('scripts')
    <script>
        document.querySelectorAll('[data-slot]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var values = { booking_date: btn.dataset.date, start_time: btn.dataset.start, end_time: btn.dataset.end };

                Object.keys(values).forEach(function (id) {
                    var el = document.getElementById(id);
                    if (!el) return;
                    if (el._flatpickr) {
                        el._flatpickr.setDate(values[id], true);
                    } else {
                        el.value = values[id];
                        el.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                });

                var form = document.getElementById('booking-form');
                if (form) form.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });
    </script>
@endpush
