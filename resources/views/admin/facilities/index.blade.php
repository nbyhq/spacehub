@extends('layouts.admin')

@section('title', 'Fasilitas - SpaceHub')

@section('content')
    <div class="mb-space-lg">
        <span class="text-label-sm font-semibold uppercase tracking-wide text-primary">Workspace Management</span>
        <h1 class="text-headline-lg font-bold tracking-tight md:text-headline-xl">Kelola Fasilitas</h1>
        <p class="mt-1 text-body-lg text-on-surface-variant">Daftar fasilitas yang bisa dipilih untuk setiap ruang.</p>
    </div>

    <form method="POST" action="{{ route('admin.facilities.store') }}" class="card mb-space-lg flex flex-wrap items-start gap-3 p-space-md">
        @csrf
        <div class="min-w-64 flex-1">
            <input type="text" name="name" value="{{ old('name') }}" placeholder="Nama fasilitas baru" required class="input">
            @error('name')<p class="field-error">{{ $message }}</p>@enderror
        </div>
        <button class="btn-primary">
            <span class="material-symbols-outlined text-[18px]">add</span> Tambah
        </button>
    </form>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-body-md">
                <thead class="bg-surface-container-low">
                    <tr>
                        <th class="th">Nama</th>
                        <th class="th">Dipakai di</th>
                        <th class="th text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30">
                    @forelse ($facilities as $facility)
                        <tr>
                            <td class="td">
                                <span class="flex items-center gap-2 font-medium">
                                    <span class="flex size-8 items-center justify-center rounded-lg bg-primary-fixed/60 text-primary">
                                        <span class="material-symbols-outlined text-[18px]">{{ $facility->icon }}</span>
                                    </span>
                                    {{ $facility->name }}
                                </span>
                            </td>
                            <td class="td text-on-surface-variant">{{ $facility->spaces_count }} ruang</td>
                            <td class="td">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.facilities.edit', $facility) }}" class="rounded-lg bg-surface-container px-3 py-1.5 text-label-md font-medium hover:bg-surface-container-high">Edit</a>
                                    <form method="POST" action="{{ route('admin.facilities.destroy', $facility) }}" onsubmit="return confirm('Hapus fasilitas ini?')">
                                        @csrf @method('DELETE')
                                        <button class="rounded-lg px-2 py-1.5 text-red-700 hover:bg-red-50" title="Hapus">
                                            <span class="material-symbols-outlined text-[20px]">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="td py-10 text-center text-on-surface-variant">Belum ada fasilitas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
