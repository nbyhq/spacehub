{{-- Saklar aktif/nonaktif ruang. Menonaktifkan ruang yang masih punya booking aktif meminta konfirmasi dulu. --}}
@php
    $count = (int) ($space->active_bookings_count ?? 0);
    $confirm = $space->is_active && $count > 0
        ? "Ruang ini masih punya {$count} booking aktif. Booking tersebut tetap berjalan, tetapi ruang tidak bisa dipesan lagi. Nonaktifkan?"
        : null;
@endphp
<form method="POST" action="{{ route('admin.spaces.toggle', $space) }}"
      @if ($confirm) onsubmit="return confirm(@js($confirm))" @endif
      class="inline-flex">
    @csrf @method('PATCH')
    <button type="submit" role="switch" aria-checked="{{ $space->is_active ? 'true' : 'false' }}"
            aria-label="{{ $space->is_active ? 'Nonaktifkan' : 'Aktifkan' }} {{ $space->name }}"
            title="{{ $space->is_active ? 'Klik untuk menonaktifkan' : 'Klik untuk mengaktifkan' }}"
            class="group inline-flex cursor-pointer items-center gap-2 rounded-full py-1 pr-1 text-label-sm font-semibold focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
        <span class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors {{ $space->is_active ? 'bg-emerald-600' : 'bg-slate-300' }}">
            <span class="absolute left-0.5 size-5 rounded-full bg-white shadow transition-transform {{ $space->is_active ? 'translate-x-5' : '' }}"></span>
        </span>
        <span class="w-16 text-left {{ $space->is_active ? 'text-emerald-700' : 'text-slate-500' }}">{{ $space->is_active ? 'Aktif' : 'Nonaktif' }}</span>
    </button>
</form>
