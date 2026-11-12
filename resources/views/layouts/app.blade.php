<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SpaceHub')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-surface font-sans text-body-md text-on-surface antialiased">
@php
    $navClass = fn (bool $active) => 'rounded-lg px-4 py-1.5 text-label-md font-medium whitespace-nowrap transition-colors '
        .($active ? 'bg-primary-container text-white' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface');
    $isAdminPage = request()->routeIs('admin.*');
@endphp

<header class="sticky top-0 z-40 border-b border-outline-variant/30 bg-surface-container-lowest shadow-[0_1px_8px_rgba(0,0,0,0.04)]">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-6">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-title-card font-bold text-primary">
                <span class="flex size-8 items-center justify-center rounded-lg bg-primary text-white">
                    <span class="material-symbols-outlined text-[20px]">domain</span>
                </span>
                SpaceHub
            </a>
            <nav class="hidden items-center gap-1 md:flex">
                <a href="{{ route('spaces.index') }}" class="{{ $navClass(request()->routeIs('spaces.*')) }}">Ruang</a>
                @auth
                    <a href="{{ route('bookings.index') }}" class="{{ $navClass(request()->routeIs('bookings.*')) }}">Booking Saya</a>
                @endauth
            </nav>
        </div>

        <div class="flex items-center gap-3 text-label-md">
            @auth
                <div class="flex items-center gap-2">
                    <span class="flex size-8 items-center justify-center rounded-full bg-primary-fixed text-label-sm font-semibold text-on-primary-fixed">
                        {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                    </span>
                    <span class="hidden sm:inline">{{ auth()->user()->name }}</span>
                    @can('admin')
                        <span class="rounded-full bg-primary-fixed px-2 py-0.5 text-label-sm font-semibold text-on-primary-fixed">Admin</span>
                    @endcan
                </div>
                <span class="hidden h-5 w-px bg-outline-variant sm:block"></span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="flex cursor-pointer items-center gap-1 text-on-surface-variant transition-colors hover:text-error">
                        <span class="material-symbols-outlined text-[20px]">logout</span>
                        <span class="hidden sm:inline">Keluar</span>
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="text-on-surface-variant hover:text-on-surface">Masuk</a>
                <a href="{{ route('register') }}" class="btn-primary">Daftar</a>
            @endauth
        </div>
    </div>

    <nav class="flex gap-1 overflow-x-auto border-t border-outline-variant/30 px-4 py-2 md:hidden">
        <a href="{{ route('spaces.index') }}" class="{{ $navClass(request()->routeIs('spaces.*')) }}">Ruang</a>
        @auth
            <a href="{{ route('bookings.index') }}" class="{{ $navClass(request()->routeIs('bookings.*')) }}">Booking Saya</a>
        @endauth
    </nav>
</header>

<main class="mx-auto w-full max-w-7xl flex-1 px-4 py-space-xl sm:px-6 lg:px-8">
    @if ($isAdminPage)
        @php
            $pendingNav = \App\Models\Booking::where('status', 'pending')->count();
            $adminTabs = [
                ['admin.dashboard', 'dashboard', 'Dashboard', request()->routeIs('admin.dashboard')],
                ['admin.bookings.index', 'calendar_month', 'Booking', request()->routeIs('admin.bookings.*')],
                ['admin.spaces.index', 'meeting_room', 'Kelola Ruang', request()->routeIs('admin.spaces.*')],
                ['admin.facilities.index', 'tune', 'Fasilitas', request()->routeIs('admin.facilities.*')],
            ];
        @endphp
        <div class="card mb-space-lg flex flex-wrap items-center justify-between gap-3 p-2">
            <div class="flex flex-wrap gap-1">
                @foreach ($adminTabs as [$routeName, $icon, $label, $active])
                    <a href="{{ route($routeName) }}" class="{{ $navClass($active) }} flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[18px]">{{ $icon }}</span>
                        {{ $label }}
                        @if ($routeName === 'admin.bookings.index' && $pendingNav > 0)
                            <span class="rounded-full bg-amber-100 px-1.5 text-label-sm font-semibold text-amber-700">{{ $pendingNav }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
            <span class="hidden items-center gap-1 rounded-full bg-surface-container-low px-3 py-1 text-label-sm font-semibold text-primary sm:inline-flex">
                <span class="material-symbols-outlined text-[16px]">shield_person</span>
                Mode Admin
            </span>
        </div>
    @endif

    @if (session('success'))
        <div class="mb-space-lg flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">
            <span class="material-symbols-outlined text-[22px]">check_circle</span>
            <span class="text-label-md font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @yield('content')
</main>

<footer class="border-t border-outline-variant/30 bg-surface-container-lowest">
    <div class="mx-auto flex max-w-7xl flex-col items-start justify-between gap-2 px-4 py-space-lg text-body-sm text-on-surface-variant sm:flex-row sm:items-center sm:px-6 lg:px-8">
        <div>
            <span class="font-bold text-primary">SpaceHub</span>
            <span class="ml-1">Platform pemesanan ruang kerja, ruang rapat, dan aula fleksibel.</span>
        </div>
        <span>&copy; {{ date('Y') }} SpaceHub. Hak cipta dilindungi.</span>
    </div>
</footer>

@stack('scripts')
</body>
</html>
