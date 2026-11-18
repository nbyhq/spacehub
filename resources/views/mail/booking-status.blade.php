<x-mail::message>
# Halo, {{ $booking->user->name }}

Booking Anda **{{ $outcome }}**.

<x-mail::table>
| Detail | |
| :-- | :-- |
| Kode | {{ $booking->code }} |
| Ruang | {{ $space->name }} |
| Lokasi | {{ $space->location }} |
| Tanggal | {{ $booking->day_label }} |
| Jam | {{ $booking->time_range }} ({{ $booking->duration_label }}) |
| Estimasi biaya | Rp {{ number_format($booking->total_price, 0, ',', '.') }} |
</x-mail::table>

@if ($booking->status === 'approved')
Sampai jumpa di lokasi. Jika rencana berubah, Anda bisa membatalkan booking dari halaman Booking Saya.
@else
Slot waktu tersebut sudah dibuka kembali. Anda bisa memesan ruang atau jam lain kapan saja.
@endif

<x-mail::button :url="route('bookings.index')">
Lihat Booking Saya
</x-mail::button>

Terima kasih,<br>
{{ config('app.name') }}
</x-mail::message>
