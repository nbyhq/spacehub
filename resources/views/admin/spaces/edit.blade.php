@extends('layouts.app')

@section('title', 'Edit Ruang - SpaceHub')

@section('content')
    <div class="mb-space-lg">
        <a href="{{ route('admin.spaces.index') }}" class="flex items-center gap-1 text-body-md text-on-surface-variant hover:text-primary">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali ke daftar ruang
        </a>
        <h1 class="mt-2 text-headline-lg font-bold tracking-tight">Edit Ruang</h1>
    </div>

    <form method="POST" action="{{ route('admin.spaces.update', $space) }}" enctype="multipart/form-data" class="card p-space-lg">
        @csrf
        @method('PUT')
        @include('admin.spaces._form')
        <div class="mt-space-lg flex gap-3">
            <button class="btn-primary">Perbarui</button>
            <a href="{{ route('admin.spaces.index') }}" class="btn-outline">Batal</a>
        </div>
    </form>
@endsection
