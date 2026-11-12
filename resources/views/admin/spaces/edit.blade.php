@extends('layouts.app')

@section('title', 'Edit Ruang - SpaceHub')

@section('content')
    <div class="mb-space-lg">
        <a href="{{ route('admin.spaces.index') }}" class="flex items-center gap-1 text-body-md text-on-surface-variant hover:text-primary">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali ke daftar ruang
        </a>
        <h1 class="mt-2 text-headline-lg font-bold tracking-tight">Edit Ruang</h1>
        <p class="mt-1 text-body-lg text-on-surface-variant">{{ $space->name }}</p>
    </div>

    <form method="POST" action="{{ route('admin.spaces.update', $space) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.spaces._form', ['submitLabel' => 'Simpan perubahan'])
    </form>
@endsection
