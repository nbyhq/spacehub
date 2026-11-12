@extends('layouts.app')

@section('title', 'Daftar - SpaceHub')

@section('content')
    <div class="card mx-auto grid max-w-4xl overflow-hidden md:grid-cols-2">
        <div class="hidden flex-col justify-between bg-primary p-10 text-white md:flex">
            <div class="flex items-center gap-2 text-title-card font-bold">
                <span class="material-symbols-outlined">domain</span> SpaceHub
            </div>
            <div>
                <h2 class="text-headline-md font-semibold">Mulai pesan ruang kerja</h2>
                <p class="mt-2 text-body-md text-on-primary-container">Buat akun gratis dan booking ruang meeting, coworking, atau aula dalam hitungan menit.</p>
            </div>
        </div>

        <div class="p-8">
            <h1 class="text-headline-md font-semibold">Daftar</h1>
            <p class="mb-space-lg mt-1 text-body-md text-on-surface-variant">Buat akun baru Anda.</p>

            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="label" for="name">Nama</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required class="input">
                    @error('name')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required class="input">
                    @error('email')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="password">Password</label>
                    <input id="password" type="password" name="password" required class="input">
                    @error('password')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="password_confirmation">Konfirmasi Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required class="input">
                </div>
                <button class="btn-primary h-11 w-full">Daftar</button>
            </form>

            <p class="mt-space-lg text-center text-body-md text-on-surface-variant">
                Sudah punya akun? <a href="{{ route('login') }}" class="font-medium text-primary hover:underline">Masuk</a>
            </p>
        </div>
    </div>
@endsection
