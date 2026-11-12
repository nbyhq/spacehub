@extends('layouts.app')

@section('title', 'Masuk - SpaceHub')

@section('content')
    <div class="card mx-auto grid max-w-5xl overflow-hidden lg:min-h-[34rem] lg:grid-cols-[1.05fr_1fr]">
        @include('partials.auth-board', [
            'title' => 'Cek jadwal, pesan ruang, pantau persetujuan.',
            'text' => 'Masuk untuk melihat ruang yang masih kosong dan status booking Anda.',
        ])

        <div class="flex flex-col justify-center p-6 sm:p-10">
            <div class="mx-auto w-full max-w-sm">
                <h1 class="text-headline-lg font-bold tracking-tight">Masuk</h1>
                <p class="mb-space-lg mt-1 text-body-lg text-on-surface-variant">Lanjutkan pemesanan ruang Anda.</p>

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="label" for="email">Email</label>
                        <div class="relative">
                            <span class="material-symbols-outlined pointer-events-none absolute left-3 top-3 text-[20px] text-on-surface-variant">mail</span>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                                   class="input h-11 pl-10 text-base sm:text-body-md">
                        </div>
                        @error('email')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                    @include('partials.password-field', [
                        'id' => 'password', 'name' => 'password', 'label' => 'Password', 'autocomplete' => 'current-password',
                    ])

                    <label class="flex cursor-pointer items-center gap-2 text-body-md">
                        <input type="checkbox" name="remember" class="size-4 rounded border-outline-variant accent-primary-container">
                        Ingat saya
                    </label>

                    <button class="btn-primary h-11 w-full">Masuk</button>
                </form>

                <p class="mt-space-lg border-t border-outline-variant/40 pt-space-lg text-center text-body-md text-on-surface-variant">
                    Belum punya akun?
                    <a href="{{ route('register') }}" class="font-medium text-primary hover:underline">Daftar gratis</a>
                </p>
            </div>
        </div>
    </div>
@endsection
