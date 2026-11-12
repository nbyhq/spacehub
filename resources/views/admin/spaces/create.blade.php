@extends('layouts.admin')

@section('title', 'Tambah Ruang - SpaceHub')

@section('content')
    <div class="mb-space-lg">
        <a href="{{ route('admin.spaces.index') }}" class="flex items-center gap-1 text-body-md text-on-surface-variant hover:text-primary">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali ke daftar ruang
        </a>
        <h1 class="mt-2 text-headline-lg font-bold tracking-tight">Tambah Ruang Baru</h1>
    </div>

    <form method="POST" action="{{ route('admin.spaces.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.spaces._form', ['submitLabel' => 'Simpan ruang'])
    </form>
@endsection
