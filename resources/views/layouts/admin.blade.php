<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin - SpaceHub')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-surface font-sans text-body-md text-on-surface antialiased">
@php
    $pendingNav = \App\Models\Booking::where('status', 'pending')->count();
    $menu = [
        ['admin.dashboard', 'dashboard', 'Dashboard', request()->routeIs('admin.dashboard')],
        ['admin.bookings.index', 'calendar_month', 'Booking', request()->routeIs('admin.bookings.*')],
        ['admin.spaces.index', 'meeting_room', 'Kelola Ruang', request()->routeIs('admin.spaces.*')],
        ['admin.facilities.index', 'tune', 'Fasilitas', request()->routeIs('admin.facilities.*')],
    ];
    $initial = mb_strtoupper(mb_substr(auth()->user()->name, 0, 1));
@endphp

<div class="lg:flex">
    <div id="admin-overlay" class="fixed inset-0 z-40 hidden bg-slate-900/50 lg:hidden"></div>

    <aside id="admin-sidebar" class="fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col bg-slate-900 text-slate-300 transition-transform duration-200 lg:sticky lg:top-0 lg:h-screen lg:translate-x-0 lg:self-start">
        <div class="flex h-16 shrink-0 items-center justify-between border-b border-white/10 px-5">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 text-title-card font-bold text-white">
                <span class="flex size-8 items-center justify-center rounded-lg bg-primary-container">
                    <span class="material-symbols-outlined text-[20px]">domain</span>
                </span>
                SpaceHub
            </a>
            <span class="rounded bg-white/10 px-2 py-0.5 text-label-sm font-semibold uppercase tracking-wide text-indigo-200">Admin</span>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-space-lg">
            <p class="px-3 pb-2 text-label-sm font-semibold uppercase tracking-wider text-slate-500">Menu</p>
            @foreach ($menu as [$routeName, $icon, $label, $active])
                <a href="{{ route($routeName) }}"
                   class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-label-md transition-colors {{ $active ? 'bg-white/10 text-white shadow-[inset_3px_0_0_0_#818cf8]' : 'hover:bg-white/5 hover:text-white' }}">
                    <span class="material-symbols-outlined text-[20px]">{{ $icon }}</span>
                    <span class="flex-1">{{ $label }}</span>
                    @if ($routeName === 'admin.bookings.index' && $pendingNav > 0)
                        <span class="rounded-full bg-amber-400 px-2 py-0.5 text-label-sm font-bold text-slate-900">{{ $pendingNav }}</span>
                    @endif
                </a>
            @endforeach

            <p class="px-3 pb-2 pt-space-lg text-label-sm font-semibold uppercase tracking-wider text-slate-500">Akun</p>
            @php
                $site = [
                    ['profile.edit', 'manage_accounts', 'Profil saya', request()->routeIs('profile.*')],
                ];
            @endphp
            @foreach ($site as [$routeName, $icon, $label, $active])
                <a href="{{ route($routeName) }}"
                   class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-label-md transition-colors {{ $active ? 'bg-white/10 text-white shadow-[inset_3px_0_0_0_#818cf8]' : 'hover:bg-white/5 hover:text-white' }}">
                    <span class="material-symbols-outlined text-[20px]">{{ $icon }}</span>
                    <span class="flex-1">{{ $label }}</span>
                </a>
            @endforeach
        </nav>

        <div class="shrink-0 border-t border-white/10 p-3">
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-lg p-2 transition-colors hover:bg-white/5">
                <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-indigo-400/20 text-label-md font-semibold text-indigo-200">{{ $initial }}</span>
                <span class="min-w-0">
                    <span class="block truncate text-label-md font-medium text-white">{{ auth()->user()->name }}</span>
                    <span class="block truncate text-body-sm text-slate-400">{{ auth()->user()->email }}</span>
                </span>
            </a>
            <form method="POST" action="{{ route('logout') }}" class="mt-1">
                @csrf
                <button class="flex w-full cursor-pointer items-center gap-3 rounded-lg px-3 py-2.5 text-label-md text-slate-300 transition-colors hover:bg-red-500/10 hover:text-red-300">
                    <span class="material-symbols-outlined text-[20px]">logout</span> Keluar
                </button>
            </form>
        </div>
    </aside>

    <div class="flex min-h-screen min-w-0 flex-1 flex-col">
        <header class="sticky top-0 z-30 flex h-14 items-center justify-between bg-slate-900 px-4 text-white lg:hidden">
            <button id="admin-menu-btn" type="button" class="flex size-10 cursor-pointer items-center justify-center rounded-lg hover:bg-white/10" aria-label="Buka menu">
                <span class="material-symbols-outlined">menu</span>
            </button>
            <span class="text-title-card font-bold">SpaceHub <span class="text-label-sm font-semibold text-indigo-200">Admin</span></span>
            <span class="flex size-8 items-center justify-center rounded-full bg-indigo-400/20 text-label-md font-semibold text-indigo-200">{{ $initial }}</span>
        </header>

        <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-space-lg sm:px-6 lg:px-10 lg:py-space-xl">
            @if (session('success'))
                <div class="mb-space-lg flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">
                    <span class="material-symbols-outlined text-[22px]">check_circle</span>
                    <span class="text-label-md font-medium">{{ session('success') }}</span>
                </div>
            @endif
            @if (session('error'))
                <div class="mb-space-lg flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                    <span class="material-symbols-outlined text-[22px]">error</span>
                    <span class="text-label-md font-medium">{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<script>
    (function () {
        var sidebar = document.getElementById('admin-sidebar');
        var overlay = document.getElementById('admin-overlay');
        var btn = document.getElementById('admin-menu-btn');
        function toggle(open) {
            sidebar.classList.toggle('-translate-x-full', !open);
            overlay.classList.toggle('hidden', !open);
        }
        btn.addEventListener('click', function () { toggle(true); });
        overlay.addEventListener('click', function () { toggle(false); });
    })();
</script>

@stack('scripts')
</body>
</html>
