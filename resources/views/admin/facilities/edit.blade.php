@extends('layouts.app')

@section('title', 'Edit Fasilitas - SpaceHub')

@section('content')
    <div class="mb-space-lg">
        <a href="{{ route('admin.facilities.index') }}" class="flex items-center gap-1 text-body-md text-on-surface-variant hover:text-primary">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali ke daftar fasilitas
        </a>
        <h1 class="mt-2 text-headline-lg font-bold tracking-tight">Edit Fasilitas</h1>
    </div>

    <form method="POST" action="{{ route('admin.facilities.update', $facility) }}" class="card max-w-md p-space-lg">
        @csrf
        @method('PUT')
        <label class="label" for="name">Nama</label>
        <input id="name" type="text" name="name" value="{{ old('name', $facility->name) }}" required class="input">
        @error('name')<p class="field-error">{{ $message }}</p>@enderror
        <div class="mt-space-lg flex gap-3">
            <button class="btn-primary">Perbarui</button>
            <a href="{{ route('admin.facilities.index') }}" class="btn-outline">Batal</a>
        </div>
    </form>
@endsection
