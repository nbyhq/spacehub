@extends('layouts.print')

@section('title', 'Laporan Booking - SpaceHub')

@section('content')
    @php
        $statusLabels = ['pending' => 'Menunggu', 'approved' => 'Disetujui', 'cancelled' => 'Dibatalkan'];
        $statusStyle = ['pending' => 'text-amber-700', 'approved' => 'text-emerald-700', 'cancelled' => 'text-slate-500'];
        $rp = fn ($n) => 'Rp '.number_format($n, 0, ',', '.');
        $keep = array_filter(['status' => $status, 'space' => $space?->id]);
    @endphp

    {{-- Toolbar: hanya tampil di layar, tidak ikut tercetak --}}
    <div class="mx-auto mb-4 max-w-[210mm] space-y-3 print:hidden">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <a href="{{ route('admin.bookings.index') }}" class="inline-flex items-center gap-1 text-body-md text-on-surface-variant hover:text-primary">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali ke Kelola Booking
            </a>
            <button type="button" onclick="window.print()" class="btn-primary">
                <span class="material-symbols-outlined text-[18px]">print</span> Cetak
            </button>
        </div>

        @unless ($period['valid'])
            <div class="flex items-start gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-body-md text-amber-800">
                <span class="material-symbols-outlined text-[20px]">warning</span>
                <span>Rentang tanggal tidak valid, jadi laporan menampilkan bulan ini.</span>
            </div>
        @endunless

        <section class="card overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-outline-variant/40 bg-surface-container-low/60 px-4 py-3">
                <div class="flex items-center gap-2.5">
                    <span class="flex size-9 items-center justify-center rounded-lg bg-primary-fixed text-primary">
                        <span class="material-symbols-outlined text-[20px]">tune</span>
                    </span>
                    <div>
                        <h2 class="text-label-md font-semibold leading-tight">Filter Laporan</h2>
                        <p class="text-body-sm text-on-surface-variant">Pilih periode, ruang, dan status</p>
                    </div>
                </div>

                <nav class="flex max-w-full gap-1 overflow-x-auto rounded-lg bg-surface-container p-1" aria-label="Periode cepat">
                    @foreach (\App\Support\ReportPeriod::PRESETS as $key => $name)
                        <a href="{{ route('admin.reports.bookings', $keep + ['preset' => $key]) }}"
                           class="whitespace-nowrap rounded-md px-3 py-1.5 text-label-md font-medium transition-colors {{ $period['preset'] === $key ? 'bg-white text-primary shadow-sm' : 'text-on-surface-variant hover:text-on-surface' }}">{{ $name }}</a>
                    @endforeach
                </nav>
            </div>

            <form method="GET" action="{{ route('admin.reports.bookings') }}" class="grid gap-4 p-4 sm:grid-cols-2 lg:grid-cols-6">
                <div class="lg:col-span-2">
                    <label class="label" for="from">Dari tanggal</label>
                    <div class="relative">
                        <span class="material-symbols-outlined pointer-events-none absolute left-3 top-2.5 text-[20px] text-on-surface-variant">calendar_month</span>
                        <input id="from" type="date" name="from" value="{{ $period['from'] }}" placeholder="Pilih tanggal" class="input pl-10">
                    </div>
                </div>
                <div class="lg:col-span-2">
                    <label class="label" for="to">Sampai tanggal</label>
                    <div class="relative">
                        <span class="material-symbols-outlined pointer-events-none absolute left-3 top-2.5 text-[20px] text-on-surface-variant">calendar_month</span>
                        <input id="to" type="date" name="to" value="{{ $period['to'] }}" placeholder="Pilih tanggal" class="input pl-10">
                    </div>
                </div>
                <div class="sm:col-span-2 lg:col-span-2">
                    <label class="label" for="status">Status</label>
                    <div class="relative">
                        <span class="material-symbols-outlined pointer-events-none absolute left-3 top-2.5 text-[20px] text-on-surface-variant">flag</span>
                        <select id="status" name="status" class="input cursor-pointer appearance-none pl-10 pr-9">
                            <option value="">Semua status</option>
                            @foreach ($statusLabels as $value => $label)
                                <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <span class="material-symbols-outlined pointer-events-none absolute right-2.5 top-2.5 text-[20px] text-on-surface-variant">expand_more</span>
                    </div>
                </div>
                <div class="sm:col-span-2 lg:col-span-4">
                    <label class="label" for="space">Ruang</label>
                    <div class="relative">
                        <span class="material-symbols-outlined pointer-events-none absolute left-3 top-2.5 text-[20px] text-on-surface-variant">meeting_room</span>
                        <select id="space" name="space" class="input cursor-pointer appearance-none pl-10 pr-9">
                            <option value="">Semua ruang</option>
                            @foreach ($spaces as $s)
                                <option value="{{ $s->id }}" @selected($space && $space->id == $s->id)>{{ $s->name }}</option>
                            @endforeach
                        </select>
                        <span class="material-symbols-outlined pointer-events-none absolute right-2.5 top-2.5 text-[20px] text-on-surface-variant">expand_more</span>
                    </div>
                </div>
                <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-2">
                    <button class="btn-primary flex-1">
                        <span class="material-symbols-outlined text-[18px]">filter_alt</span> Terapkan
                    </button>
                    <a href="{{ route('admin.reports.bookings') }}" class="btn-outline" title="Reset filter" aria-label="Reset filter">
                        <span class="material-symbols-outlined text-[18px]">restart_alt</span>
                    </a>
                </div>
            </form>
        </section>
    </div>

    {{-- Lembar laporan --}}
    <article class="mx-auto max-w-[210mm] bg-white p-8 shadow-sm print:max-w-none print:p-0 print:shadow-none">
        <header class="flex items-start justify-between gap-4 border-b-2 border-primary pb-4">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo.png') }}" alt="" class="size-12">
                <div>
                    <div class="text-title-card font-bold text-primary">SpaceHub</div>
                    <div class="text-body-sm text-on-surface-variant">Platform pemesanan ruang kerja</div>
                </div>
            </div>
            <div class="text-right">
                <h1 class="text-headline-md font-bold">Laporan Booking</h1>
                <div class="text-body-sm text-on-surface-variant">Dicetak {{ $printedAt }}</div>
                <div class="text-body-sm text-on-surface-variant">oleh {{ auth()->user()->name }}</div>
            </div>
        </header>

        <dl class="mt-4 grid grid-cols-[auto_1fr] gap-x-4 gap-y-1 text-body-md">
            <dt class="text-on-surface-variant">Periode</dt>
            <dd class="font-medium">{{ $period['label'] }}</dd>
            <dt class="text-on-surface-variant">Ruang</dt>
            <dd class="font-medium">{{ $space ? $space->name.($space->trashed() ? ' (dihapus)' : '') : 'Semua ruang' }}</dd>
            <dt class="text-on-surface-variant">Status</dt>
            <dd class="font-medium">{{ $status ? $statusLabels[$status] : 'Semua status' }}</dd>
        </dl>

        <section class="mt-5 grid grid-cols-2 gap-2 text-center sm:grid-cols-5 print:grid-cols-5">
            @foreach ([['total', 'Total booking'], ['approved', 'Disetujui'], ['pending', 'Menunggu'], ['cancelled', 'Dibatalkan']] as [$key, $label])
                <div class="rounded-lg border border-outline-variant p-2">
                    <div class="text-headline-md font-bold leading-none" data-summary="{{ $key }}">{{ $summary[$key] }}</div>
                    <div class="mt-1 text-body-sm text-on-surface-variant">{{ $label }}</div>
                </div>
            @endforeach
            <div class="col-span-2 rounded-lg border border-primary bg-primary-fixed/40 p-2 sm:col-span-1 print:col-span-1">
                <div class="text-title-card font-bold leading-none text-primary" data-summary="revenue">{{ $rp($summary['revenue']) }}</div>
                <div class="mt-1 text-body-sm text-on-surface-variant">Estimasi Pendapatan</div>
            </div>
        </section>

        @if ($bySpace->count() > 1)
            <section class="mt-5">
                <h2 class="mb-2 text-label-md font-semibold uppercase tracking-wide text-on-surface-variant">Rekap per Ruang</h2>
                <table class="w-full border-collapse text-body-sm">
                    <thead class="table-header-group">
                        <tr class="border-y border-outline-variant bg-surface-container-low text-left">
                            <th class="px-2 py-1.5">Ruang</th>
                            <th class="px-2 py-1.5 text-right">Booking</th>
                            <th class="px-2 py-1.5 text-right">Disetujui</th>
                            <th class="px-2 py-1.5 text-right">Estimasi Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bySpace as $row)
                            <tr class="break-inside-avoid border-b border-outline-variant/50">
                                <td class="px-2 py-1.5 font-medium">{{ $row['name'] }}</td>
                                <td class="px-2 py-1.5 text-right">{{ $row['total'] }}</td>
                                <td class="px-2 py-1.5 text-right">{{ $row['approved'] }}</td>
                                <td class="px-2 py-1.5 text-right">{{ $rp($row['revenue']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        @endif

        <section class="mt-5">
            <h2 class="mb-2 text-label-md font-semibold uppercase tracking-wide text-on-surface-variant">Daftar Booking</h2>
            <div class="overflow-x-auto print:overflow-visible">
                <table class="w-full min-w-[640px] border-collapse text-body-sm print:min-w-0">
                    <thead class="table-header-group">
                        <tr class="border-y border-outline-variant bg-surface-container-low text-left">
                            <th class="px-2 py-1.5">No</th>
                            <th class="px-2 py-1.5">Kode</th>
                            <th class="px-2 py-1.5">Tanggal</th>
                            <th class="px-2 py-1.5">Jam</th>
                            <th class="px-2 py-1.5">Ruang</th>
                            <th class="px-2 py-1.5">Pemesan</th>
                            <th class="px-2 py-1.5">Status</th>
                            <th class="px-2 py-1.5 text-right">Biaya</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bookings as $b)
                            <tr class="break-inside-avoid border-b border-outline-variant/50 align-top">
                                <td class="px-2 py-1.5">{{ $loop->iteration }}</td>
                                <td class="px-2 py-1.5 font-mono font-semibold">{{ $b->code }}</td>
                                <td class="px-2 py-1.5">{{ $b->day_label }}</td>
                                <td class="whitespace-nowrap px-2 py-1.5">
                                    {{ $b->time_range }}
                                    <div class="text-on-surface-variant">{{ $b->duration_label }}</div>
                                </td>
                                <td class="px-2 py-1.5">{{ $b->space?->name ?? '—' }}</td>
                                <td class="px-2 py-1.5">
                                    {{ $b->user?->name ?? '—' }}
                                    <div class="text-on-surface-variant">{{ $b->user?->email }}</div>
                                </td>
                                <td class="px-2 py-1.5 font-semibold {{ $statusStyle[$b->status] ?? '' }}">{{ $statusLabels[$b->status] ?? $b->status }}</td>
                                <td class="whitespace-nowrap px-2 py-1.5 text-right {{ $b->status === 'approved' ? '' : 'text-on-surface-variant' }}">{{ $rp($b->total_price) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-2 py-8 text-center text-on-surface-variant">Tidak ada booking pada filter ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($bookings->isNotEmpty())
                        <tfoot class="table-row-group">
                            <tr class="border-t-2 border-on-surface font-semibold">
                                <td colspan="7" class="px-2 py-2 text-right">Estimasi pendapatan (hanya booking disetujui)</td>
                                <td class="whitespace-nowrap px-2 py-2 text-right">{{ $rp($summary['revenue']) }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </section>

        <footer class="mt-6 border-t border-outline-variant pt-3 text-body-sm text-on-surface-variant">
            <p>Estimasi pendapatan dihitung dari durasi dikali tarif per jam ruang saat ini, dan hanya mencakup booking berstatus Disetujui.</p>
            @if ($truncated)
                <p class="mt-1 font-semibold text-amber-700">Hanya {{ number_format($limit, 0, ',', '.') }} booking pertama yang ditampilkan. Persempit filter untuk melihat sisanya.</p>
            @endif
        </footer>
    </article>
@endsection

@push('scripts')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/id.js"></script>
    <script>
        (function () {
            var from = document.getElementById('from');
            var to = document.getElementById('to');
            if (!window.flatpickr || !from || !to) return;

            if (flatpickr.l10ns && flatpickr.l10ns.id) flatpickr.localize(flatpickr.l10ns.id);

            var fromPicker, toPicker;
            var base = {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'j M Y',
                altInputClass: 'input cursor-pointer pl-10',
                disableMobile: true
            };

            // Tanggal mulai tidak boleh melewati tanggal akhir, dan sebaliknya
            fromPicker = flatpickr(from, Object.assign({}, base, {
                onChange: function (dates) { if (toPicker) toPicker.set('minDate', dates[0] || null); }
            }));
            toPicker = flatpickr(to, Object.assign({}, base, {
                onChange: function (dates) { fromPicker.set('maxDate', dates[0] || null); }
            }));

            if (from.value) toPicker.set('minDate', from.value);
            if (to.value) fromPicker.set('maxDate', to.value);
        })();
    </script>
@endpush
