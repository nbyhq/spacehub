@if ($active)
    <span class="inline-flex h-6 items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-100 px-2.5 text-label-sm font-semibold text-emerald-700">
        <span class="size-1.5 rounded-full bg-emerald-600"></span>Aktif
    </span>
@else
    <span class="inline-flex h-6 items-center gap-1.5 rounded-full border border-slate-200 bg-slate-100 px-2.5 text-label-sm font-semibold text-slate-600">
        <span class="size-1.5 rounded-full bg-slate-400"></span>Nonaktif
    </span>
@endif
