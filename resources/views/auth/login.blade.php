@extends('layouts.app')

@section('title', 'Masuk - SpaceHub')

@section('content')
    <div class="card mx-auto grid max-w-4xl overflow-hidden md:grid-cols-2">
        <div class="hidden flex-col justify-between bg-primary p-10 text-white md:flex">
            <div class="flex items-center gap-2 text-title-card font-bold">
                <span class="material-symbols-outlined">domain</span> SpaceHub
            </div>
            <div>
                <h2 class="text-headline-md font-semibold">Booking ruang jadi lebih mudah</h2>
                <p class="mt-2 text-body-md text-on-primary-container">Masuk untuk memesan ruang dan memantau status booking Anda.</p>
            </div>
        </div>

        <div class="p-8">
            <h1 class="text-headline-md font-semibold">Masuk</h1>
            <p class="mb-space-lg mt-1 text-body-md text-on-surface-variant">Selamat datang kembali.</p>

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="label" for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required class="input">
                    @error('email')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="password">Password</label>
                    <input id="password" type="password" name="password" required class="input">
                </div>
                <label class="flex items-center gap-2 text-body-md">
                    <input type="checkbox" name="remember" class="size-4 rounded border-outline-variant accent-primary-container"> Ingat saya
                </label>
                <button class="btn-primary h-11 w-full">Masuk</button>
            </form>

            <p class="mt-space-lg text-center text-body-md text-on-surface-variant">
                Belum punya akun? <a href="{{ route('register') }}" class="font-medium text-primary hover:underline">Daftar</a>
            </p>
        </div>
    </div>
@endsection
