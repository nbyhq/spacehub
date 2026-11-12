@php
    $map = [
        'pending' => ['Menunggu', 'border-amber-200 bg-amber-100 text-amber-700', 'bg-amber-600'],
        'approved' => ['Disetujui', 'border-emerald-200 bg-emerald-100 text-emerald-700', 'bg-emerald-600'],
        'cancelled' => ['Dibatalkan', 'border-slate-200 bg-slate-100 text-slate-600', 'bg-slate-400'],
    ];
    [$label, $class, $dot] = $map[$status] ?? [$status, 'border-slate-200 bg-slate-100 text-slate-600', 'bg-slate-400'];
@endphp
<span class="inline-flex h-6 items-center gap-1.5 rounded-full border px-2.5 text-label-sm font-semibold {{ $class }}">
    <span class="size-1.5 rounded-full {{ $dot }}"></span>{{ $label }}
</span>
