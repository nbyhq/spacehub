@extends('layouts.app')

@section('title', 'Daftar - SpaceHub')

@section('content')
    <div class="card mx-auto grid max-w-5xl overflow-hidden rounded-3xl shadow-xl shadow-primary/10 lg:min-h-[36rem] lg:grid-cols-[1.05fr_1fr]">
        @include('partials.auth-board', [
            'title' => 'Satu akun untuk semua booking ruang Anda.',
            'text' => 'Daftar gratis. Booking ditinjau admin, dan statusnya bisa Anda pantau kapan saja.',
            'points' => [
                'Daftar gratis, cukup beberapa detik',
                'Jadwal bentrok otomatis ditolak',
                'Estimasi biaya terlihat sebelum memesan',
            ],
        ])

        <div class="flex flex-col justify-center p-6 sm:p-10">
            <div class="mx-auto w-full max-w-md">
                <a href="{{ route('home') }}" class="mb-space-lg flex w-fit items-center gap-2 text-title-card font-bold text-primary lg:hidden">
                    <img src="{{ asset('images/logo.png') }}" alt="" class="size-9 shrink-0">
                    SpaceHub
                </a>

                <span class="flex size-12 items-center justify-center rounded-2xl bg-primary-fixed text-primary">
                    <span class="material-symbols-outlined text-[26px]">person_add</span>
                </span>
                <h1 class="mt-space-md text-headline-lg font-bold tracking-tight">Buat akun baru</h1>
                <p class="mb-space-lg mt-1 text-body-lg text-on-surface-variant">Cukup beberapa detik, lalu Anda bisa langsung memesan.</p>

                <form method="POST" action="{{ route('register') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="label" for="name">Nama lengkap</label>
                        <div class="relative">
                            <span class="material-symbols-outlined pointer-events-none absolute left-3 top-3 text-[20px] text-on-surface-variant">person</span>
                            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                                   class="input h-11 pl-10 text-base sm:text-body-md">
                        </div>
                        @error('name')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="label" for="email">Email</label>
                        <div class="relative">
                            <span class="material-symbols-outlined pointer-events-none absolute left-3 top-3 text-[20px] text-on-surface-variant">mail</span>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                                   placeholder="nama@email.com" class="input h-11 pl-10 text-base sm:text-body-md">
                        </div>
                        @error('email')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        @include('partials.password-field', [
                            'id' => 'password', 'name' => 'password', 'label' => 'Password',
                            'autocomplete' => 'new-password', 'hint' => 'Minimal 8 karakter',
                        ])
                        @include('partials.password-field', [
                            'id' => 'password_confirmation', 'name' => 'password_confirmation', 'label' => 'Ulangi password',
                            'autocomplete' => 'new-password',
                        ])
                    </div>

                    <button class="btn-primary h-12 w-full text-body-lg shadow-lg shadow-primary/25">
                        Buat akun <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                    </button>
                </form>

                <p class="mt-space-lg border-t border-outline-variant/40 pt-space-lg text-center text-body-md text-on-surface-variant">
                    Sudah punya akun?
                    <a href="{{ route('login') }}" class="font-semibold text-primary hover:underline">Masuk</a>
                </p>
            </div>
        </div>
    </div>
@endsection
