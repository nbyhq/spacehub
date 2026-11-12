@extends('layouts.app')

@section('title', 'Profil Saya - SpaceHub')

@section('content')
    <div class="mb-space-lg">
        <span class="flex items-center gap-1 text-label-sm font-semibold text-primary">
            <span class="material-symbols-outlined text-[16px]">account_circle</span> Akun
        </span>
        <h1 class="text-headline-lg font-bold tracking-tight md:text-headline-xl">Profil Saya</h1>
        <p class="mt-1 text-body-lg text-on-surface-variant">
            Bergabung sejak {{ $user->created_at->translatedFormat('F Y') }}
            @can('admin')
                <span class="ml-1 rounded-full bg-primary-fixed px-2 py-0.5 text-label-sm font-semibold text-on-primary-fixed">Admin</span>
            @endcan
        </p>
    </div>

    <div class="grid items-start gap-space-lg lg:grid-cols-2">
        <form method="POST" action="{{ route('profile.update') }}" class="card space-y-4 p-space-lg">
            @csrf
            @method('PATCH')
            <div>
                <h2 class="text-title-card font-semibold">Data akun</h2>
                <p class="mt-1 text-body-md text-on-surface-variant">Nama dan email yang dipakai untuk login dan booking.</p>
            </div>

            <div>
                <label class="label" for="name">Nama lengkap</label>
                <div class="relative">
                    <span class="material-symbols-outlined pointer-events-none absolute left-3 top-3 text-[20px] text-on-surface-variant">person</span>
                    <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name"
                           class="input h-11 pl-10 text-base sm:text-body-md">
                </div>
                @error('name')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="label" for="email">Email</label>
                <div class="relative">
                    <span class="material-symbols-outlined pointer-events-none absolute left-3 top-3 text-[20px] text-on-surface-variant">mail</span>
                    <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="email"
                           class="input h-11 pl-10 text-base sm:text-body-md">
                </div>
                @error('email')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <button class="btn-primary h-11 w-full sm:w-auto">Simpan perubahan</button>
        </form>

        <form method="POST" action="{{ route('profile.password.update') }}" class="card space-y-4 p-space-lg">
            @csrf
            @method('PUT')
            <div>
                <h2 class="text-title-card font-semibold">Ganti password</h2>
                <p class="mt-1 text-body-md text-on-surface-variant">Gunakan minimal 8 karakter.</p>
            </div>

            @foreach ([
                ['current_password', 'Password saat ini', 'current-password'],
                ['password', 'Password baru', 'new-password'],
                ['password_confirmation', 'Ulangi password baru', 'new-password'],
            ] as [$field, $label, $autocomplete])
                <div>
                    <label class="label" for="{{ $field }}">{{ $label }}</label>
                    <div class="relative">
                        <span class="material-symbols-outlined pointer-events-none absolute left-3 top-3 text-[20px] text-on-surface-variant">lock</span>
                        <input id="{{ $field }}" type="password" name="{{ $field }}" required autocomplete="{{ $autocomplete }}"
                               class="input h-11 pl-10 text-base sm:text-body-md">
                    </div>
                    @error($field)<p class="field-error">{{ $message }}</p>@enderror
                </div>
            @endforeach

            <button class="btn-primary h-11 w-full sm:w-auto">Ganti password</button>
        </form>
    </div>
@endsection
