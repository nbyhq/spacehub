@extends('layouts.admin')

@section('title', 'Kelola Booking - SpaceHub')

@section('content')
    @php
        $statusNow = request('status');
        $ring = 'ring-2 ring-primary-container';
        $keep = array_filter(['q' => request('q'), 'space' => request('space')]);
        $cards = [
            ['', 'Semua', $stats['all'], 'calendar_month', 'bg-primary-fixed text-primary'],
            ['pending', 'Menunggu', $stats['pending'], 'hourglass_top', 'bg-amber-100 text-amber-700'],
            ['approved', 'Disetujui', $stats['approved'], 'check_circle', 'bg-emerald-100 text-emerald-700'],
            ['cancelled', 'Dibatalkan', $stats['cancelled'], 'cancel', 'bg-slate-100 text-slate-600'],
        ];
    @endphp

    <div class="mb-space-lg">
        <span class="text-label-sm font-semibold uppercase tracking-wide text-primary">Workspace Management</span>
        <h1 class="text-headline-lg font-bold tracking-tight md:text-headline-xl">Kelola Booking</h1>
        <p class="mt-1 max-w-2xl text-body-lg text-on-surface-variant">Tinjau, setujui, atau tolak permohonan reservasi.</p>
        @if ($filterSpace)
            <p class="mt-2 inline-flex items-center gap-2 rounded-full bg-primary-fixed px-3 py-1 text-label-md font-medium text-on-primary-fixed">
                Ruang: {{ $filterSpace->name }}
                <a href="{{ route('admin.bookings.index', array_filter(['status' => $statusNow, 'q' => request('q')])) }}" title="Hapus filter ruang" aria-label="Hapus filter ruang">
                    <span class="material-symbols-outlined text-[16px]">close</span>
                </a>
            </p>
        @endif
    </div>

    <div class="mb-space-lg grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach ($cards as [$value, $label, $count, $icon, $tone])
            <a href="{{ route('admin.bookings.index', $keep + ($value ? ['status' => $value] : [])) }}"
               class="card flex items-center gap-3 p-3 transition-shadow hover:shadow-md sm:p-space-md {{ ($statusNow ?: '') === $value ? $ring : '' }}">
                <span class="hidden size-10 shrink-0 items-center justify-center rounded-lg sm:flex {{ $tone }}">
                    <span class="material-symbols-outlined">{{ $icon }}</span>
                </span>
                <span>
                    <span class="block text-headline-md font-semibold leading-none">{{ $count }}</span>
                    <span class="text-body-sm text-on-surface-variant">{{ $label }}</span>
                </span>
            </a>
        @endforeach
    </div>

    <div class="card overflow-hidden">
        <form method="GET" action="{{ route('admin.bookings.index') }}" class="flex flex-col gap-2 border-b border-outline-variant/40 p-space-md md:flex-row">
            @if ($filterSpace)
                <input type="hidden" name="space" value="{{ $filterSpace->id }}">
            @endif
            <div class="relative flex-1">
                <span class="material-symbols-outlined pointer-events-none absolute left-3 top-2.5 text-[20px] text-on-surface-variant">search</span>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari pemesan, email, atau ruang..." class="input pl-10">
            </div>
            <select name="status" class="input md:w-44">
                <option value="">Semua Status</option>
                @foreach (['pending' => 'Menunggu', 'approved' => 'Disetujui', 'cancelled' => 'Dibatalkan'] as $value => $label)
                    <option value="{{ $value }}" @selected($statusNow === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <button class="btn-primary flex-1 md:flex-none">Filter</button>
                <a href="{{ route('admin.bookings.index', array_filter(['space' => request('space')])) }}" class="btn-outline" title="Reset filter" aria-label="Reset filter">
                    <span class="material-symbols-outlined text-[18px]">refresh</span>
                </a>
                <a href="{{ route('admin.reports.bookings', array_filter(['status' => $statusNow, 'space' => request('space')])) }}" target="_blank" rel="noopener" class="btn-outline" title="Cetak laporan" aria-label="Cetak laporan">
                    <span class="material-symbols-outlined text-[18px]">print</span>
                    <span class="hidden lg:inline">Cetak</span>
                </a>
            </div>
        </form>

        {{-- HP & tablet: kartu --}}
        <div class="divide-y divide-outline-variant/30 xl:hidden">
            @forelse ($bookings as $booking)
                <div class="space-y-3 p-space-md {{ $booking->status === 'pending' ? 'bg-amber-50/50' : '' }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="font-mono text-label-sm font-semibold text-primary">{{ $booking->code }}</div>
                            <div class="truncate font-semibold">{{ $booking->space->name }}</div>
                        </div>
                        @include('partials.status', ['status' => $booking->status])
                    </div>

                    <div class="flex items-center gap-3">
                        @include('partials.avatar', ['id' => $booking->user->id, 'name' => $booking->user->name, 'size' => 'size-9'])
                        <div class="min-w-0">
                            <div class="truncate font-medium">{{ $booking->user->name }}</div>
                            <div class="truncate text-body-sm text-on-surface-variant">{{ $booking->user->email }}</div>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-body-md">
                        <span class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[18px] text-primary">event</span>{{ \App\Support\IndonesianDate::short($booking->booking_date) }}
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[18px] text-primary">schedule</span>{{ $booking->time_range }}
                            <span class="text-body-sm text-on-surface-variant">({{ $booking->duration_label }})</span>
                        </span>
                        <span class="font-medium">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex items-center gap-2">
                        @if ($booking->status === 'pending')
                            <form method="POST" action="{{ route('admin.bookings.update', $booking) }}" class="flex-1 sm:flex-none">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="approved">
                                <button class="btn-success h-9 w-full px-4 sm:w-auto">Setujui</button>
                            </form>
                            <form method="POST" action="{{ route('admin.bookings.update', $booking) }}" class="flex-1 sm:flex-none"
                                  data-confirm-title="Tolak booking?" data-confirm="Booking {{ $booking->code }} akan dibatalkan dan slot waktunya dibuka kembali." data-confirm-ok="Ya, tolak" data-confirm-cancel="Kembali">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="cancelled">
                                <button class="btn-danger h-9 w-full px-4 sm:w-auto">Tolak</button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('admin.bookings.destroy', $booking) }}" class="ml-auto"
                              data-confirm-title="Hapus booking?" data-confirm="Booking ini akan dihapus dari daftar." data-confirm-ok="Hapus">
                            @csrf @method('DELETE')
                            <button class="inline-flex size-9 cursor-pointer items-center justify-center rounded-lg text-red-700 transition-colors hover:bg-red-50" title="Hapus" aria-label="Hapus {{ $booking->code }}">
                                <span class="material-symbols-outlined text-[20px]">delete</span>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center gap-2 px-space-md py-12 text-center text-on-surface-variant">
                    <span class="material-symbols-outlined text-[40px]">event_busy</span>
                    <p>Belum ada booking yang cocok.</p>
                </div>
            @endforelse
        </div>

        {{-- Desktop lebar: tabel --}}
        <div class="hidden overflow-x-auto xl:block">
            <table class="w-full table-fixed text-body-md">
                <thead class="bg-surface-container-low">
                    <tr>
                        <th class="th w-[10%]">Kode</th>
                        <th class="th w-[27%]">Pemesan</th>
                        <th class="th w-[22%]">Ruang</th>
                        <th class="th w-[18%]">Tanggal &amp; Jam</th>
                        <th class="th w-[13%]">Status</th>
                        <th class="th w-36 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30">
                    @forelse ($bookings as $booking)
                        <tr class="transition-colors hover:bg-surface-container-low/60 {{ $booking->status === 'pending' ? 'bg-amber-50/50' : '' }}">
                            <td class="td whitespace-nowrap font-mono text-label-sm font-semibold text-primary {{ $booking->status === 'pending' ? 'shadow-[inset_3px_0_0_0_#f59e0b]' : '' }}">{{ $booking->code }}</td>
                            <td class="td">
                                <div class="flex items-center gap-3">
                                    @include('partials.avatar', ['id' => $booking->user->id, 'name' => $booking->user->name, 'size' => 'size-9'])
                                    <div class="min-w-0">
                                        <div class="truncate font-medium" title="{{ $booking->user->name }}">{{ $booking->user->name }}</div>
                                        <div class="truncate text-body-sm text-on-surface-variant" title="{{ $booking->user->email }}">{{ $booking->user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="td"><div class="truncate font-medium" title="{{ $booking->space->name }}">{{ $booking->space->name }}</div></td>
                            <td class="td">
                                <div class="whitespace-nowrap font-medium">{{ \App\Support\IndonesianDate::short($booking->booking_date) }}</div>
                                <div class="whitespace-nowrap text-body-sm text-on-surface-variant">{{ $booking->time_range }} &middot; {{ $booking->duration_label }}</div>
                                <div class="whitespace-nowrap text-body-sm text-on-surface-variant">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</div>
                            </td>
                            <td class="td">@include('partials.status', ['status' => $booking->status])</td>
                            <td class="td">
                                <div class="ml-auto grid w-[7.25rem] grid-cols-3 justify-items-center gap-1">
                                    @if ($booking->status === 'pending')
                                        <form method="POST" action="{{ route('admin.bookings.update', $booking) }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="approved">
                                            <button class="inline-flex size-9 cursor-pointer items-center justify-center rounded-lg text-emerald-700 transition-colors hover:bg-emerald-50" title="Setujui" aria-label="Setujui {{ $booking->code }}"><span class="material-symbols-outlined text-[22px]">check_circle</span></button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.bookings.update', $booking) }}"
                                              data-confirm-title="Tolak booking?" data-confirm="Booking {{ $booking->code }} akan dibatalkan dan slot waktunya dibuka kembali." data-confirm-ok="Ya, tolak" data-confirm-cancel="Kembali">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="cancelled">
                                            <button class="inline-flex size-9 cursor-pointer items-center justify-center rounded-lg text-amber-700 transition-colors hover:bg-amber-50" title="Tolak" aria-label="Tolak {{ $booking->code }}"><span class="material-symbols-outlined text-[22px]">cancel</span></button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('admin.bookings.destroy', $booking) }}" class="col-start-3"
                                          data-confirm-title="Hapus booking?" data-confirm="Booking ini akan dihapus dari daftar." data-confirm-ok="Hapus">
                                        @csrf @method('DELETE')
                                        <button class="inline-flex size-9 cursor-pointer items-center justify-center rounded-lg text-red-700 transition-colors hover:bg-red-50" title="Hapus" aria-label="Hapus {{ $booking->code }}">
                                            <span class="material-symbols-outlined text-[20px]">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="td py-14 text-center text-on-surface-variant">
                                <span class="material-symbols-outlined text-[40px]">event_busy</span>
                                <p class="mt-1">Belum ada booking yang cocok.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($bookings->total() > 0)
            <div class="flex flex-col items-center justify-between gap-3 border-t border-outline-variant/40 px-space-md py-space-md sm:flex-row">
                <p class="text-body-md text-on-surface-variant">
                    Menampilkan <strong>{{ $bookings->firstItem() }} - {{ $bookings->lastItem() }}</strong> dari <strong>{{ $bookings->total() }}</strong> booking
                </p>
                <div>{{ $bookings->links() }}</div>
            </div>
        @endif
    </div>
@endsection
