@php
    $selected = old('facilities', isset($space) ? $space->facilities->pluck('id')->all() : []);
    $submitLabel = $submitLabel ?? 'Simpan';
@endphp

<div class="grid items-start gap-space-lg lg:grid-cols-3">
    <div class="space-y-space-lg lg:col-span-2">
        <section class="card space-y-4 p-space-lg">
            <h2 class="text-title-card font-semibold">Informasi ruang</h2>

            <div>
                <label class="label" for="name">Nama ruang</label>
                <input id="name" type="text" name="name" value="{{ old('name', $space->name ?? '') }}" required class="input">
                @error('name')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
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
                    <label class="label" for="capacity">Kapasitas</label>
                    <div class="relative">
                        <input id="capacity" type="number" name="capacity" min="1" value="{{ old('capacity', $space->capacity ?? '') }}" required class="input pr-16">
                        <span class="pointer-events-none absolute right-3 top-2.5 text-body-sm text-on-surface-variant">orang</span>
                    </div>
                    @error('capacity')<p class="field-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="label" for="location">Lokasi</label>
                <div class="relative">
                    <span class="material-symbols-outlined pointer-events-none absolute left-3 top-2.5 text-[20px] text-on-surface-variant">location_on</span>
                    <input id="location" type="text" name="location" value="{{ old('location', $space->location ?? '') }}" required placeholder="Contoh: Lantai 2, Gedung A" class="input pl-10">
                </div>
                @error('location')<p class="field-error">{{ $message }}</p>@enderror
            </div>
        </section>

        <section class="card space-y-4 p-space-lg">
            <h2 class="text-title-card font-semibold">Tarif dan deskripsi</h2>

            <div class="sm:max-w-xs">
                <label class="label" for="price_per_hour">Harga sewa</label>
                <div class="relative">
                    <span class="pointer-events-none absolute left-3 top-2.5 text-body-md text-on-surface-variant">Rp</span>
                    <input id="price_per_hour" type="number" name="price_per_hour" min="0" value="{{ old('price_per_hour', $space->price_per_hour ?? '') }}" required class="input pl-10 pr-14">
                    <span class="pointer-events-none absolute right-3 top-2.5 text-body-sm text-on-surface-variant">/ jam</span>
                </div>
                @error('price_per_hour')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="label" for="description">Deskripsi</label>
                <textarea id="description" name="description" rows="5" class="input" placeholder="Ceritakan suasana, fasilitas utama, dan cocok untuk kegiatan apa.">{{ old('description', $space->description ?? '') }}</textarea>
                @error('description')<p class="field-error">{{ $message }}</p>@enderror
            </div>
        </section>

        <section class="card space-y-4 p-space-lg">
            <div>
                <h2 class="text-title-card font-semibold">Jam operasional</h2>
                <p class="mt-1 text-body-md text-on-surface-variant">Booking hanya diterima di dalam jam buka dan sesuai batas durasi.</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label" for="opens_at">Jam buka</label>
                    <input id="opens_at" type="time" name="opens_at" value="{{ old('opens_at', isset($space) ? $space->opensAtLabel() : '08:00') }}" required class="input">
                    @error('opens_at')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="closes_at">Jam tutup</label>
                    <input id="closes_at" type="time" name="closes_at" value="{{ old('closes_at', isset($space) ? $space->closesAtLabel() : '22:00') }}" required class="input">
                    @error('closes_at')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="min_hours">Durasi minimal</label>
                    <div class="relative">
                        <input id="min_hours" type="number" name="min_hours" min="0.5" max="24" step="0.5" value="{{ old('min_hours', isset($space) ? $space->minDurationMinutes() / 60 : 1) }}" required class="input pr-14">
                        <span class="pointer-events-none absolute right-3 top-2.5 text-body-sm text-on-surface-variant">jam</span>
                    </div>
                    @error('min_hours')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="max_hours">Durasi maksimal</label>
                    <div class="relative">
                        <input id="max_hours" type="number" name="max_hours" min="0.5" max="24" step="0.5" value="{{ old('max_hours', isset($space) ? $space->maxDurationMinutes() / 60 : 8) }}" required class="input pr-14">
                        <span class="pointer-events-none absolute right-3 top-2.5 text-body-sm text-on-surface-variant">jam</span>
                    </div>
                    @error('max_hours')<p class="field-error">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <section class="card p-space-lg">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-title-card font-semibold">Fasilitas</h2>
                <a href="{{ route('admin.facilities.index') }}" class="text-label-md font-medium text-primary hover:underline">Kelola fasilitas</a>
            </div>
            <div class="mt-space-md flex flex-wrap gap-2">
                @forelse ($facilities as $facility)
                    <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-outline-variant bg-white px-3 py-2 text-label-md font-medium text-on-surface-variant transition-colors hover:bg-surface-container-low has-[:checked]:border-primary-container has-[:checked]:bg-primary-fixed has-[:checked]:text-primary">
                        <input type="checkbox" name="facilities[]" value="{{ $facility->id }}" @checked(in_array($facility->id, $selected)) class="size-4 accent-primary-container">
                        <span class="material-symbols-outlined text-[18px]">{{ $facility->icon }}</span>{{ $facility->name }}
                    </label>
                @empty
                    <p class="text-body-md text-on-surface-variant">
                        Belum ada fasilitas. <a href="{{ route('admin.facilities.index') }}" class="text-primary hover:underline">Tambah fasilitas</a>
                    </p>
                @endforelse
            </div>
        </section>
    </div>

    <aside class="space-y-space-lg lg:sticky lg:top-24">
        <section class="card p-space-lg">
            <h2 class="text-title-card font-semibold">Foto ruang</h2>
            <div id="photo-preview" class="mt-space-md flex aspect-[16/10] items-center justify-center overflow-hidden rounded-lg bg-surface-container text-outline">
                @if (! empty($space->photo))
                    <img src="{{ asset('storage/'.$space->photo) }}" alt="{{ $space->name }}" class="h-full w-full object-cover">
                @else
                    <div class="flex flex-col items-center gap-1 text-body-sm">
                        <span class="material-symbols-outlined text-[36px]">add_photo_alternate</span>
                        Belum ada foto
                    </div>
                @endif
            </div>
            <input id="photo" type="file" name="photo" accept="image/*"
                   class="mt-space-md block w-full text-body-sm file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-primary-fixed file:px-3 file:py-2 file:text-label-md file:font-medium file:text-on-primary-fixed">
            <p class="mt-1 text-body-sm text-on-surface-variant">Format gambar, maksimal 2 MB.</p>
            @error('photo')<p class="field-error">{{ $message }}</p>@enderror
        </section>

        <section class="card p-space-lg">
            <label class="flex cursor-pointer items-start justify-between gap-3">
                <span>
                    <span class="block text-label-md font-medium">Ruang aktif</span>
                    <span class="block text-body-sm text-on-surface-variant">Tampil di daftar publik dan bisa dipesan.</span>
                </span>
                <input type="hidden" name="is_active" value="0">
                <span class="relative mt-0.5 inline-flex shrink-0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $space->is_active ?? true)) class="peer sr-only">
                    <span class="h-6 w-11 rounded-full bg-outline-variant transition-colors peer-checked:bg-primary-container peer-focus-visible:ring-2 peer-focus-visible:ring-primary-container/40"></span>
                    <span class="absolute left-0.5 top-0.5 size-5 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></span>
                </span>
            </label>
        </section>

        <div class="flex flex-col gap-2">
            <button class="btn-primary h-11 w-full">{{ $submitLabel }}</button>
            <a href="{{ route('admin.spaces.index') }}" class="btn-outline h-11 w-full">Batal</a>
        </div>
    </aside>
</div>

@push('scripts')
    <script>
        (function () {
            var input = document.getElementById('photo');
            var box = document.getElementById('photo-preview');
            if (!input || !box) return;
            input.addEventListener('change', function () {
                var file = input.files[0];
                if (!file) return;
                var img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                img.alt = '';
                img.className = 'h-full w-full object-cover';
                box.innerHTML = '';
                box.appendChild(img);
            });
        })();
    </script>
@endpush
