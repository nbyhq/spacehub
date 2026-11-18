@extends('layouts.app')

@section('title', 'Atur Ulang Password - SpaceHub')

@section('content')
    <div class="card mx-auto grid max-w-5xl overflow-hidden rounded-3xl shadow-xl shadow-primary/10 lg:min-h-[32rem] lg:grid-cols-[1.05fr_1fr]">
        @include('partials.auth-board', [
            'title' => 'Buat password baru yang kuat.',
            'text' => 'Setelah disimpan, Anda bisa langsung masuk dengan password baru.',
            'points' => [
                'Minimal 8 karakter',
                'Jangan pakai ulang password lama',
                'Akun dan booking Anda tidak berubah',
            ],
        ])

        <div class="flex flex-col justify-center p-6 sm:p-10">
            <div class="mx-auto w-full max-w-sm">
                <a href="{{ route('home') }}" class="mb-space-lg flex w-fit items-center gap-2 text-title-card font-bold text-primary lg:hidden">
                    <img src="{{ asset('images/logo.png') }}" alt="" class="size-9 shrink-0">
                    SpaceHub
                </a>

                <span class="flex size-12 items-center justify-center rounded-2xl bg-primary-fixed text-primary">
                    <span class="material-symbols-outlined text-[26px]">key</span>
                </span>
                <h1 class="mt-space-md text-headline-lg font-bold tracking-tight">Atur ulang password</h1>
                <p class="mb-space-lg mt-1 text-body-lg text-on-surface-variant">Masukkan password baru untuk akun Anda.</p>

                <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div>
                        <label class="label" for="email">Email</label>
                        <div class="relative">
                            <span class="material-symbols-outlined pointer-events-none absolute left-3 top-3 text-[20px] text-on-surface-variant">mail</span>
                            <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required autocomplete="email"
                                   class="input h-11 pl-10 text-base sm:text-body-md">
                        </div>
                        @error('email')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                    @include('partials.password-field', [
                        'id' => 'password', 'name' => 'password', 'label' => 'Password baru', 'autocomplete' => 'new-password',
                        'hint' => 'Minimal 8 karakter.',
                    ])
                    @include('partials.password-field', [
                        'id' => 'password_confirmation', 'name' => 'password_confirmation', 'label' => 'Ulangi password baru', 'autocomplete' => 'new-password',
                    ])

                    <button class="btn-primary h-12 w-full text-body-lg shadow-lg shadow-primary/25">
                        Simpan password baru <span class="material-symbols-outlined text-[20px]">check</span>
                    </button>
                </form>

                <p class="mt-space-lg border-t border-outline-variant/40 pt-space-lg text-center text-body-md text-on-surface-variant">
                    Tautan sudah kedaluwarsa?
                    <a href="{{ route('password.request') }}" class="font-semibold text-primary hover:underline">Minta tautan baru</a>
                </p>
            </div>
        </div>
    </div>
@endsection
