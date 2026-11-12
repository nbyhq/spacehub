@extends('layouts.app')

@section('title', 'Dashboard Admin - SpaceHub')

@section('content')
    <div class="mb-space-lg flex flex-col justify-between gap-space-md md:flex-row md:items-end">
        <div>
            <span class="text-label-sm font-semibold uppercase tracking-wide text-primary">Workspace Management</span>
            <h1 class="text-headline-lg font-bold tracking-tight md:text-headline-xl">Dashboard Admin</h1>
            <p class="mt-1 max-w-2xl text-body-lg text-on-surface-variant">
                Pantau ketersediaan ruang dan permohonan reservasi yang masuk. {{ $userCount }} pengguna terdaftar.
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.spaces.index') }}" class="btn-outline">Kelola Ruang</a>
            <a href="{{ route('admin.spaces.create') }}" class="btn-primary">
                <span class="material-symbols-outlined text-[18px]">add</span> Tambah Ruang Baru
            </a>
        </div>
    </div>

    <div class="grid gap-space-md sm:grid-cols-2 lg:grid-cols-4">
        <div class="card p-space-lg">
            <div class="flex items-center justify-between text-body-md text-on-surface-variant">
                Total Ruang Aktif
                <span class="flex size-9 items-center justify-center rounded-lg bg-primary-fixed text-primary">
                    <span class="material-symbols-outlined text-[20px]">apartment</span>
                </span>
            </div>
            <div class="mt-space-md text-headline-lg font-bold">{{ $spaceActive }} Unit</div>
            <div class="mt-1 text-body-sm text-on-surface-variant">dari {{ $spaceCount }} ruang terdaftar</div>
        </div>

        <a href="{{ route('admin.bookings.index', ['status' => 'pending']) }}" class="card p-space-lg transition-shadow hover:shadow-md">
            <div class="flex items-center justify-between text-body-md text-on-surface-variant">
                Permohonan Menunggu
                <span class="flex size-9 items-center justify-center rounded-lg bg-amber-100 text-amber-700">
                    <span class="material-symbols-outlined text-[20px]">pending_actions</span>
                </span>
            </div>
            <div class="mt-space-md text-headline-lg font-bold {{ $pendingCount > 0 ? 'text-amber-700' : '' }}">{{ $pendingCount }} Reservasi</div>
            <div class="mt-1 text-body-sm text-on-surface-variant">{{ $pendingCount > 0 ? 'Perlu tindakan' : 'Semua sudah ditangani' }}</div>
        </a>

        <div class="card p-space-lg">
            <div class="flex items-center justify-between text-body-md text-on-surface-variant">
                Booking Disetujui (Bulan Ini)
                <span class="flex size-9 items-center justify-center rounded-lg bg-primary-fixed text-primary">
                    <span class="material-symbols-outlined text-[20px]">event_available</span>
                </span>
            </div>
            <div class="mt-space-md text-headline-lg font-bold text-primary">{{ $approvedMonth }} Booking</div>
            <div class="mt-1 text-body-sm text-on-surface-variant">{{ $todayCount }} aktif hari ini</div>
        </div>

        <div class="card p-space-lg">
            <div class="flex items-center justify-between text-body-md text-on-surface-variant">
                Estimasi Pendapatan
                <span class="flex size-9 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700">
                    <span class="material-symbols-outlined text-[20px]">account_balance_wallet</span>
                </span>
            </div>
            <div class="mt-space-md text-headline-md font-bold">Rp {{ number_format($revenue, 0, ',', '.') }}</div>
            <div class="mt-1 text-body-sm text-on-surface-variant">Dari booking disetujui, {{ now()->translatedFormat('F Y') }}</div>
        </div>
    </div>

    <section class="card mt-space-lg p-space-lg">
        <div class="mb-space-md flex items-center justify-between border-b border-outline-variant/40 pb-space-md">
            <h2 class="flex items-center gap-2 text-title-card font-semibold">
                <span class="size-2.5 rounded-full bg-amber-600"></span>
                Permohonan Reservasi Terbaru
                @if ($pendingCount > 0)
                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-label-sm font-semibold text-amber-700">{{ $pendingCount }} Menunggu</span>
                @endif
            </h2>
            <a href="{{ route('admin.bookings.index', ['status' => 'pending']) }}" class="text-label-md font-medium text-primary hover:underline">Lihat semua</a>
        </div>

        <div class="grid gap-space-md lg:grid-cols-2">
            @forelse ($pending as $booking)
                <div class="rounded-xl border border-outline-variant/40 bg-surface-container-low/50 p-space-md">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="flex size-11 items-center justify-center rounded-full bg-primary-fixed text-label-md font-semibold text-on-primary-fixed">
                                {{ mb_strtoupper(mb_substr($booking->user->name, 0, 1)) }}
                            </span>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold">{{ $booking->user->name }}</span>
                                    @include('partials.status', ['status' => $booking->status])
                                </div>
                                <div class="text-body-sm text-on-surface-variant">{{ $booking->code }} • {{ $booking->user->email }}</div>
                            </div>
                        </div>
                        <div class="text-title-card font-bold text-primary">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</div>
                    </div>

                    <div class="mt-space-md flex flex-wrap items-center justify-between gap-2 rounded-lg bg-white px-3 py-2 text-body-md">
                        <span class="flex items-center gap-1.5 font-medium">
                            <span class="material-symbols-outlined text-[18px] text-primary">meeting_room</span>{{ $booking->space->name }}
                        </span>
                        <span class="flex items-center gap-1.5 text-on-surface-variant">
                            <span class="material-symbols-outlined text-[18px]">schedule</span>
                            {{ $booking->booking_date->format('d M Y') }}, {{ $booking->time_range }} ({{ $booking->duration_label }})
                        </span>
                    </div>

                    <div class="mt-space-md flex justify-end gap-2">
                        <form method="POST" action="{{ route('admin.bookings.update', $booking) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="cancelled">
                            <button class="btn-danger">
                                <span class="material-symbols-outlined text-[18px]">close</span> Tolak
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.bookings.update', $booking) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="approved">
                            <button class="btn-success">
                                <span class="material-symbols-outlined text-[18px]">check</span> Setujui Reservasi
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-full flex flex-col items-center gap-2 py-10 text-on-surface-variant">
                    <span class="material-symbols-outlined text-[36px]">task_alt</span>
                    Tidak ada permohonan yang menunggu.
                </div>
            @endforelse
        </div>
    </section>

    <section class="card mt-space-lg overflow-hidden">
        <div class="border-b border-outline-variant/40 px-space-lg py-space-md">
            <h2 class="text-title-card font-semibold">Aktivitas Booking Terbaru</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-body-md">
                <thead class="bg-surface-container-low">
                    <tr>
                        <th class="th">Pemesan</th>
                        <th class="th">Ruang</th>
                        <th class="th">Tanggal &amp; Jam</th>
                        <th class="th">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30">
                    @forelse ($recent as $booking)
                        <tr>
                            <td class="td">{{ $booking->user->name }}</td>
                            <td class="td font-medium">{{ $booking->space->name }}</td>
                            <td class="td">{{ $booking->booking_date->format('d M Y') }}, {{ $booking->time_range }}</td>
                            <td class="td">@include('partials.status', ['status' => $booking->status])</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="td py-8 text-center text-on-surface-variant">Belum ada booking.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
