@if ($space->photo)
    <img src="{{ asset('storage/'.$space->photo) }}" alt="{{ $space->name }}" class="{{ $class ?? '' }} object-cover">
@else
    <div class="{{ $class ?? '' }} flex items-center justify-center bg-surface-container text-outline">
        <span class="material-symbols-outlined text-[40px]">image</span>
    </div>
@endif
