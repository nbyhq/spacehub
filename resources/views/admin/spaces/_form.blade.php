@php
    $selected = old('facilities', isset($space) ? $space->facilities->pluck('id')->all() : []);
@endphp

<div class="grid gap-space-md md:grid-cols-2">
    <div>
        <label class="label" for="name">Nama Ruang</label>
        <input id="name" type="text" name="name" value="{{ old('name', $space->name ?? '') }}" required class="input">
        @error('name')<p class="field-error">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="label" for="type">Tipe</label>
        <select id="type" name="type" required class="input">
            @foreach (\App\Models\Space::TYPES as $key => $label)
                <option value="{{ $key }}" @selected(old('type', $space->type ?? '') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        @error('type')<p class="field-error">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="label" for="capacity">Kapasitas (orang)</label>
        <input id="capacity" type="number" name="capacity" min="1" value="{{ old('capacity', $space->capacity ?? '') }}" required class="input">
        @error('capacity')<p class="field-error">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="label" for="location">Lokasi</label>
        <input id="location" type="text" name="location" value="{{ old('location', $space->location ?? '') }}" required class="input">
        @error('location')<p class="field-error">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="label" for="price_per_hour">Harga per jam (Rp)</label>
        <input id="price_per_hour" type="number" name="price_per_hour" min="0" value="{{ old('price_per_hour', $space->price_per_hour ?? '') }}" required class="input">
        @error('price_per_hour')<p class="field-error">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="label" for="photo">Foto (maks. 2 MB)</label>
        <input id="photo" type="file" name="photo" accept="image/*" class="block w-full text-body-md file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-primary-fixed file:px-3 file:py-2 file:text-label-md file:font-medium file:text-on-primary-fixed">
        @error('photo')<p class="field-error">{{ $message }}</p>@enderror
        @if (! empty($space->photo))
            <img src="{{ asset('storage/'.$space->photo) }}" alt="" class="mt-2 h-20 rounded-lg object-cover">
        @endif
    </div>
    <div class="md:col-span-2">
        <label class="label" for="description">Deskripsi</label>
        <textarea id="description" name="description" rows="4" class="input">{{ old('description', $space->description ?? '') }}</textarea>
        @error('description')<p class="field-error">{{ $message }}</p>@enderror
    </div>
    <div class="md:col-span-2">
        <span class="label">Fasilitas</span>
        <div class="flex flex-wrap gap-2">
            @forelse ($facilities as $facility)
                <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-outline-variant bg-white px-3 py-1.5 text-label-md font-medium text-on-surface-variant transition-colors has-[:checked]:border-primary-container has-[:checked]:bg-primary-fixed has-[:checked]:text-primary">
                    <input type="checkbox" name="facilities[]" value="{{ $facility->id }}" @checked(in_array($facility->id, $selected)) class="size-4 accent-primary-container">
                    <span class="material-symbols-outlined text-[16px]">{{ $facility->icon }}</span>{{ $facility->name }}
                </label>
            @empty
                <p class="text-body-md text-on-surface-variant">
                    Belum ada fasilitas. <a href="{{ route('admin.facilities.index') }}" class="text-primary hover:underline">Tambah fasilitas</a>
                </p>
            @endforelse
        </div>
    </div>
    <div class="md:col-span-2">
        <label class="flex items-center gap-2 text-body-md">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $space->is_active ?? true)) class="size-4 accent-primary-container">
            Ruang aktif (tampil di daftar publik)
        </label>
    </div>
</div>
