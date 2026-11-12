@php
    $styles = [
        'coworking' => 'bg-secondary-fixed text-on-secondary-fixed',
        'meeting_room' => 'bg-primary-fixed text-on-primary-fixed',
        'private_office' => 'bg-surface-container-high text-on-surface',
        'event_hall' => 'bg-tertiary-fixed text-on-tertiary-fixed',
    ];
@endphp
<span class="inline-flex items-center rounded-full px-2.5 py-1 text-label-sm font-semibold shadow-sm {{ $styles[$type] ?? 'bg-surface-container text-on-surface' }}">
    {{ \App\Models\Space::TYPES[$type] ?? $type }}
</span>
