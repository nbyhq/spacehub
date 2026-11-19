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

        <p class="flex items-start gap-1.5 text-body-sm text-on-surface-variant">
            <span class="material-symbols-outlined text-[16px]">info</span>
            <span>Saat mencetak: pilih kertas A4, margin Default, dan matikan opsi &quot;Header dan footer&quot; (Headers and footers) agar hasilnya bersih.</span>
        </p>
    </div>

    {{-- Lembar laporan (laporan-corporate) --}}
    @php
        $docNo = 'LAP-BKG/'.now()->format('Y/m/d-His');
        $rate = $summary['total'] > 0 ? round($summary['approved'] / $summary['total'] * 100) : 0;
        $no = 0;
    @endphp
    <article data-laporan-responsif class="mx-auto max-w-[210mm] bg-white p-4 text-slate-900 sm:p-8 shadow-sm print:max-w-none print:p-0 print:shadow-none">
        <header class="print-head flex items-center justify-between gap-4 border-b-2 border-slate-900 pb-3 max-sm:flex-col max-sm:items-start">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo.png') }}" alt="" class="size-11">
                <div>
                    <div class="text-title-card font-bold leading-tight tracking-tight">SpaceHub</div>
                    <div class="text-body-sm text-slate-600">Platform Pemesanan Ruang Kerja</div>
                </div>
            </div>
            <div class="text-right text-body-sm leading-snug text-slate-600 max-sm:text-left">
                <div class="text-label-md font-semibold text-slate-900">Laporan Booking</div>
                <div class="font-mono">{{ $docNo }}</div>
            </div>
        </header>

        <table class="print-frame w-full table-fixed">
            <thead><tr><td></td></tr></thead>
            <tbody><tr><td class="align-top">

        <h1 class="mt-1 text-center text-headline-md font-bold tracking-tight">Laporan Booking Ruang Kerja</h1>

        <table class="mt-4 w-full border-collapse border border-slate-300 text-body-sm max-sm:block">
            <tbody class="max-sm:block">
                <tr class="border-b border-slate-300 max-sm:grid max-sm:grid-cols-[7.5rem_1fr]">
                    <th class="w-28 bg-slate-100 px-2.5 py-1.5 text-left font-semibold text-slate-700">Nomor Dokumen</th>
                    <td class="px-2.5 py-1.5 font-mono">{{ $docNo }}</td>
                    <th class="w-24 border-l border-slate-300 bg-slate-100 max-sm:border-l-0 max-sm:border-t px-2.5 py-1.5 text-left font-semibold text-slate-700">Ruang</th>
                    <td class="px-2.5 py-1.5">{{ $space ? $space->name.($space->trashed() ? ' (dihapus)' : '') : 'Semua ruang' }}</td>
                </tr>
                <tr class="border-b border-slate-300 max-sm:grid max-sm:grid-cols-[7.5rem_1fr]">
                    <th class="bg-slate-100 px-2.5 py-1.5 text-left font-semibold text-slate-700">Periode</th>
                    <td class="px-2.5 py-1.5">{{ $period['label'] }}</td>
                    <th class="border-l border-slate-300 bg-slate-100 max-sm:border-l-0 max-sm:border-t px-2.5 py-1.5 text-left font-semibold text-slate-700">Status</th>
                    <td class="px-2.5 py-1.5">{{ $status ? $statusLabels[$status] : 'Semua status' }}</td>
                </tr>
                <tr class="max-sm:grid max-sm:grid-cols-[7.5rem_1fr]">
                    <th class="bg-slate-100 px-2.5 py-1.5 text-left font-semibold text-slate-700">Tanggal Cetak</th>
                    <td class="px-2.5 py-1.5">{{ $printedAt }}</td>
                    <th class="border-l border-slate-300 bg-slate-100 max-sm:border-l-0 max-sm:border-t px-2.5 py-1.5 text-left font-semibold text-slate-700">Dicetak oleh</th>
                    <td class="px-2.5 py-1.5">{{ auth()->user()->name }}</td>
                </tr>
            </tbody>
        </table>

        <section class="mt-5 break-inside-avoid">
            <h2 class="mb-1.5 text-label-md font-bold">{{ ++$no }}. Ringkasan</h2>
            <p class="mb-2 text-body-sm leading-relaxed text-slate-700">
                Pada periode {{ $period['label'] }} tercatat <strong>{{ $summary['total'] }}</strong> booking{{ $space ? ' untuk ruang '.$space->name : '' }},
                dengan {{ $summary['approved'] }} disetujui ({{ $rate }}%), {{ $summary['pending'] }} menunggu persetujuan, dan {{ $summary['cancelled'] }} dibatalkan.
                Estimasi pendapatan dari booking disetujui sebesar <strong>{{ $rp($summary['revenue']) }}</strong>.
            </p>
            <div class="overflow-x-auto print:overflow-visible">
            <table class="w-full min-w-[34rem] border-collapse border border-slate-300 text-center text-body-sm print:min-w-0">
                <thead>
                    <tr class="bg-slate-800 text-white">
                        <th class="border border-slate-700 px-2 py-1.5 font-semibold">Total Booking</th>
                        <th class="border border-slate-700 px-2 py-1.5 font-semibold">Disetujui</th>
                        <th class="border border-slate-700 px-2 py-1.5 font-semibold">Menunggu</th>
                        <th class="border border-slate-700 px-2 py-1.5 font-semibold">Dibatalkan</th>
                        <th class="border border-slate-700 px-2 py-1.5 font-semibold">Tingkat Persetujuan</th>
                        <th class="border border-slate-700 px-2 py-1.5 font-semibold">Estimasi Pendapatan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="tabular-nums">
                        @foreach (['total', 'approved', 'pending', 'cancelled'] as $key)
                            <td class="border border-slate-300 px-2 py-2.5 text-label-md font-semibold"><div data-summary="{{ $key }}">{{ $summary[$key] }}</div></td>
                        @endforeach
                        <td class="border border-slate-300 px-2 py-2.5 text-label-md font-semibold">{{ $rate }}%</td>
                        <td class="border border-slate-300 px-2 py-2.5 text-label-md font-semibold"><div data-summary="revenue">{{ $rp($summary['revenue']) }}</div></td>
                    </tr>
                </tbody>
            </table>
            </div>
        </section>

        @if ($bySpace->count() > 1)
            <section class="mt-5">
                <h2 class="mb-1.5 text-label-md font-bold">{{ ++$no }}. Rekap per Ruang</h2>
                <div class="overflow-x-auto print:overflow-visible">
                <table class="w-full min-w-[28rem] border-collapse border border-slate-300 text-body-sm tabular-nums print:min-w-0">
                    <thead class="table-header-group">
                        <tr class="bg-slate-800 text-left text-white">
                            <th class="px-2 py-1.5 font-semibold">Ruang</th>
                            <th class="px-2 py-1.5 font-semibold text-right">Booking</th>
                            <th class="px-2 py-1.5 font-semibold text-right">Disetujui</th>
                            <th class="px-2 py-1.5 font-semibold text-right">Estimasi Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bySpace as $row)
                            <tr class="break-inside-avoid border-b border-slate-300 even:bg-slate-50">
                                <td class="px-2 py-1.5 font-medium">{{ $row['name'] }}</td>
                                <td class="px-2 py-1.5 text-right">{{ $row['total'] }}</td>
                                <td class="px-2 py-1.5 text-right">{{ $row['approved'] }}</td>
                                <td class="px-2 py-1.5 text-right">{{ $rp($row['revenue']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            </section>
        @endif

        <section class="mt-5">
            <h2 class="mb-1.5 text-label-md font-bold">{{ ++$no }}. Rincian Booking</h2>
            <div class="overflow-x-auto print:overflow-visible">
                <table class="w-full min-w-[640px] border-collapse border border-slate-300 text-body-sm tabular-nums print:min-w-0">
                    <thead class="table-header-group">
                        <tr class="bg-slate-800 text-left text-white">
                            <th class="px-2 py-1.5 font-semibold">No</th>
                            <th class="px-2 py-1.5 font-semibold">Kode</th>
                            <th class="px-2 py-1.5 font-semibold">Tanggal</th>
                            <th class="px-2 py-1.5 font-semibold">Jam</th>
                            <th class="px-2 py-1.5 font-semibold">Ruang</th>
                            <th class="px-2 py-1.5 font-semibold">Pemesan</th>
                            <th class="px-2 py-1.5 font-semibold">Status</th>
                            <th class="px-2 py-1.5 font-semibold text-right">Biaya</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bookings as $b)
                            <tr class="break-inside-avoid border-b border-slate-300 align-top even:bg-slate-50">
                                <td class="px-2 py-1.5">{{ $loop->iteration }}</td>
                                <td class="whitespace-nowrap px-2 py-1.5 font-mono font-semibold">{{ $b->code }}</td>
                                <td class="px-2 py-1.5">{{ $b->day_label }}</td>
                                <td class="whitespace-nowrap px-2 py-1.5">
                                    {{ $b->time_range }}
                                    <div class="text-slate-500">{{ $b->duration_label }}</div>
                                </td>
                                <td class="px-2 py-1.5">{{ $b->space?->name ?? '—' }}</td>
                                <td class="px-2 py-1.5">
                                    {{ $b->user?->name ?? '—' }}
                                    <div class="text-slate-500">{{ $b->user?->email }}</div>
                                </td>
                                <td class="px-2 py-1.5 font-medium {{ $statusStyle[$b->status] ?? '' }}">{{ $statusLabels[$b->status] ?? $b->status }}</td>
                                <td class="whitespace-nowrap px-2 py-1.5 text-right {{ $b->status === 'approved' ? '' : 'text-slate-500' }}">{{ $rp($b->total_price) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-2 py-8 text-center text-slate-500">Tidak ada booking pada filter ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($bookings->isNotEmpty())
                        <tfoot class="table-row-group">
                            <tr class="break-inside-avoid border-t-2 border-slate-800 bg-slate-100 font-semibold">
                                <td colspan="7" class="px-2 py-2 text-right">Estimasi pendapatan (hanya booking disetujui)</td>
                                <td class="whitespace-nowrap px-2 py-2 text-right">{{ $rp($summary['revenue']) }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </section>

        <div class="mt-4 space-y-1 text-body-sm text-slate-600">
            <p>Catatan: estimasi pendapatan dihitung dari durasi dikali tarif per jam ruang saat ini, dan hanya mencakup booking berstatus Disetujui.</p>
            @if ($truncated)
                <p class="font-semibold text-amber-700">Hanya {{ number_format($limit, 0, ',', '.') }} booking pertama yang ditampilkan. Persempit filter untuk melihat sisanya.</p>
            @endif
        </div>

        <section class="mt-8 grid grid-cols-1 gap-6 break-inside-avoid sm:grid-cols-2 sm:gap-10 text-center text-body-sm">
            <div>
                <div class="text-slate-600">Dibuat oleh,</div>
                <div class="h-16"></div>
                <div class="border-t border-slate-800 pt-1 font-semibold">{{ auth()->user()->name }}</div>
                <div class="text-slate-600">Administrator SpaceHub</div>
            </div>
            <div>
                <div class="text-slate-600">Disetujui oleh,</div>
                <div class="h-16"></div>
                <div class="border-t border-slate-800 pt-1 font-semibold">&nbsp;</div>
                <div class="text-slate-600">Nama, jabatan, dan tanggal</div>
            </div>
        </section>

            </td></tr></tbody>
            <tfoot><tr><td></td></tr></tfoot>
        </table>

        <footer class="print-foot mt-6 flex items-center justify-between gap-4 max-sm:flex-col max-sm:items-start border-t border-slate-400 pt-2 text-body-sm text-slate-500">
            <span>Dokumen internal SpaceHub &middot; <span class="font-mono">{{ $docNo }}</span></span>
            <span>{{ $period['label'] }}</span>
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
