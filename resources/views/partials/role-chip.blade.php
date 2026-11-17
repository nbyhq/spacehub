@if ($role === 'admin')
    <span class="inline-flex h-6 items-center whitespace-nowrap rounded-full border border-indigo-200 bg-indigo-100 px-2.5 text-label-sm font-semibold text-indigo-700">Admin</span>
@else
    <span class="inline-flex h-6 items-center whitespace-nowrap rounded-full border border-slate-200 bg-slate-100 px-2.5 text-label-sm font-semibold text-slate-600">Pengguna</span>
@endif
