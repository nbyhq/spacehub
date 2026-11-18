@php
    $palette = [
        'bg-indigo-100 text-indigo-700',
        'bg-emerald-100 text-emerald-700',
        'bg-amber-100 text-amber-700',
        'bg-rose-100 text-rose-700',
        'bg-sky-100 text-sky-700',
        'bg-violet-100 text-violet-700',
    ];
    $tone = $palette[($id ?? 0) % count($palette)];
@endphp
<span class="flex {{ $size ?? 'size-10' }} shrink-0 items-center justify-center rounded-full text-label-md font-semibold {{ $tone }}">{{ mb_strtoupper(mb_substr($name ?? '?', 0, 1)) }}</span>
