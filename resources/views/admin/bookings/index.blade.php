@extends('layouts.admin')

@section('title', 'Kelola Booking - SpaceHub')

@section('content')
    <div class="mb-space-lg flex flex-col justify-between gap-space-md md:flex-row md:items-end">
        <div>
            <span class="text-label-sm font-semibold uppercase tracking-wide text-primary">Workspace Management</span>
            <h1 class="text-headline-lg font-bold tracking-tight md:text-headline-xl">Kelola Booking</h1>
            <p class="mt-1 text-body-lg text-on-surface-variant">Tinjau, setujui, atau tolak permohonan reservasi.</p>
            @if ($filterSpace)
                <p class="mt-2 inline-flex items-center gap-2 rounded-full bg-primary-fixed px-3 py-1 text-label-md font-medium text-on-primary-fixed">
                    Ruang: {{ $filterSpace->name }}
                    <a href="{{ route('admin.bookings.index', array_filter(['status' => request('status')])) }}" title="Hapus filter ruang">
                        <span class="material-symbols-outlined text-[16px]">close</span>
                    </a>
                </p>
            @endif
        </div>

        <form method="GET" action="{{ route('admin.bookings.index') }}" class="flex w-full gap-2 md:w-auto">
            @if ($filterSpace)
                <input type="hidden" name="space" value="{{ $filterSpace->id }}">
            @endif
            <select name="status" class="input min-w-0 flex-1 md:w-44 md:flex-none">
                <option value="">Semua Status</option>
                @foreach (['pending' => 'Menunggu', 'approved' => 'Disetujui', 'cancelled' => 'Dibatalkan'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="btn-primary">Filter</button>
        </form>
    </div>

    <div class="card overflow-hidden">
        <div class="divide-y divide-outline-variant/30 lg:hidden">
            @forelse ($bookings as $booking)
                <div class="space-y-3 p-space-md">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="font-mono text-label-sm font-semibold text-primary">{{ $booking->code }}</div>
                            <div class="font-medium">{{ $booking->space->name }}</div>
                        </div>
                        @include('partials.status', ['status' => $booking->status])
                    </div>
                    <div class="text-body-sm text-on-surface-variant">
                        <div class="font-medium text-on-surface">{{ $booking->user->name }}</div>
                        <div class="break-all">{{ $booking->user->email }}</div>
                    </div>
                    <div class="text-body-md">
                        {{ $booking->booking_date->translatedFormat('d M Y') }}, {{ $booking->time_range }}
                        <div class="text-body-sm text-on-surface-variant">{{ $booking->duration_label }} • Rp {{ number_format($booking->total_price, 0, ',', '.') }}</div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($booking->status === 'pending')
                            <form method="POST" action="{{ route('admin.bookings.update', $booking) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="approved">
                                <button class="btn-success h-9 px-4">Setujui</button>
                            </form>
                            <form method="POST" action="{{ route('admin.bookings.update', $booking) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="cancelled">
                                <button class="btn-danger h-9 px-4">Tolak</button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('admin.bookings.destroy', $booking) }}" onsubmit="return confirm('Hapus booking ini?')" class="ml-auto">
                            @csrf @method('DELETE')
                            <button class="rounded-lg px-2 py-1.5 text-red-700 hover:bg-red-50" title="Hapus">
                                <span class="material-symbols-outlined text-[20px]">delete</span>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="py-12 text-center text-on-surface-variant">Belum ada booking.</div>
            @endforelse
        </div>

        <div class="hidden overflow-x-auto lg:block">
            <table class="w-full min-w-[820px] text-body-md">
                <thead class="bg-surface-container-low">
                    <tr>
                        <th class="th">Kode</th>
                        <th class="th">Pemesan</th>
                        <th class="th">Ruang</th>
                        <th class="th">Tanggal &amp; Jam</th>
                        <th class="th">Status</th>
                        <th class="th text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30">
                    @forelse ($bookings as $booking)
                        <tr class="hover:bg-surface-container-low/40">
                            <td class="td font-mono text-label-sm font-semibold text-primary">{{ $booking->code }}</td>
                            <td class="td">
                                <div class="font-medium">{{ $booking->user->name }}</div>
                                <div class="text-body-sm text-on-surface-variant">{{ $booking->user->email }}</div>
                            </td>
                            <td class="td font-medium">{{ $booking->space->name }}</td>
                            <td class="td">
                                {{ $booking->booking_date->translatedFormat('d M Y') }}, {{ $booking->time_range }}
                                <div class="text-body-sm text-on-surface-variant">{{ $booking->duration_label }} • Rp {{ number_format($booking->total_price, 0, ',', '.') }}</div>
                            </td>
                            <td class="td">@include('partials.status', ['status' => $booking->status])</td>
                            <td class="td">
                                <div class="flex items-center justify-end gap-2">
                                    @if ($booking->status === 'pending')
                                        <form method="POST" action="{{ route('admin.bookings.update', $booking) }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="approved">
                                            <button class="btn-success h-8 px-3">Setujui</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.bookings.update', $booking) }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="cancelled">
                                            <button class="btn-danger h-8 px-3">Tolak</button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('admin.bookings.destroy', $booking) }}" onsubmit="return confirm('Hapus booking ini?')">
                                        @csrf @method('DELETE')
                                        <button class="rounded-lg px-2 py-1.5 text-red-700 hover:bg-red-50" title="Hapus">
                                            <span class="material-symbols-outlined text-[20px]">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="td py-12 text-center text-on-surface-variant">Belum ada booking.</td></tr>
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
