@php
    $span = [1 => 'col-span-1', 2 => 'col-span-2', 3 => 'col-span-3'];
    $kind = [
        'free' => ['', 'border border-dashed border-outline-variant'],
        'taken' => ['Terisi', 'bg-surface-container-high text-on-surface-variant'],
        'mine' => ['Booking Anda', 'bg-primary text-white'],
    ];
    $rows = [
        ['Ruang Meeting', [[2, 'taken'], [1, 'free'], [2, 'mine'], [1, 'free']]],
        ['Coworking', [[1, 'free'], [3, 'taken'], [2, 'free']]],
        ['Aula', [[3, 'free'], [3, 'taken']]],
    ];
    $points = $points ?? [];
@endphp

<div class="relative isolate hidden flex-col overflow-hidden bg-gradient-to-br from-primary via-primary-container to-violet-600 p-10 text-white lg:flex">
    <div aria-hidden="true" class="pointer-events-none absolute -right-20 -top-20 -z-10 size-72 rounded-full bg-white/10 blur-3xl"></div>
    <div aria-hidden="true" class="pointer-events-none absolute -left-24 top-1/2 -z-10 size-64 rounded-full bg-secondary-fixed-dim/20 blur-3xl"></div>

    <a href="{{ route('home') }}" class="flex w-fit items-center gap-2 text-title-card font-bold">
        <img src="{{ asset('images/logo-white.png') }}" alt="" class="size-10 shrink-0">
        SpaceHub
    </a>

    <h2 class="mt-space-xl max-w-sm text-headline-lg font-bold leading-tight tracking-tight text-balance">{{ $title }}</h2>
    <p class="mt-space-sm max-w-sm text-body-lg text-on-primary-container">{{ $text }}</p>

    @if (count($points))
        <ul class="mt-space-lg space-y-2.5">
            @foreach ($points as $point)
                <li class="flex items-center gap-2.5 text-body-md">
                    <span class="flex size-5 shrink-0 items-center justify-center rounded-full bg-white/20">
                        <span class="material-symbols-outlined text-[14px]">check</span>
                    </span>
                    {{ $point }}
                </li>
            @endforeach
        </ul>
    @endif

    <div aria-hidden="true" class="-mb-10 -mr-10 mt-auto pt-space-xl">
        <div class="rounded-tl-2xl bg-surface-container-lowest p-5 pb-16 pr-14 text-on-surface shadow-2xl">
            <div class="mb-space-md flex items-baseline justify-between gap-3">
                <span class="text-label-md font-semibold">Jadwal ruang hari ini</span>
                <span class="text-body-sm text-on-surface-variant">{{ now()->translatedFormat('l, j M') }}</span>
            </div>

            <div class="grid grid-cols-[6.25rem_repeat(6,minmax(0,1fr))] items-center gap-x-1.5 gap-y-2">
                <span></span>
                @foreach (['09', '10', '11', '12', '13', '14'] as $hour)
                    <span class="text-body-sm tabular-nums text-on-surface-variant">{{ $hour }}</span>
                @endforeach

                @foreach ($rows as [$room, $slots])
                    <span class="truncate text-body-sm font-medium">{{ $room }}</span>
                    @foreach ($slots as [$size, $type])
                        <span class="{{ $span[$size] }} {{ $kind[$type][1] }} flex h-9 items-center truncate rounded-md px-2 text-body-sm font-medium">{{ $kind[$type][0] }}</span>
                    @endforeach
                @endforeach
            </div>

            <div class="mt-space-md flex flex-wrap gap-x-4 gap-y-1 text-body-sm text-on-surface-variant">
                <span class="flex items-center gap-1.5"><span class="size-3 rounded-sm border border-dashed border-outline-variant"></span>Kosong</span>
                <span class="flex items-center gap-1.5"><span class="size-3 rounded-sm bg-surface-container-high"></span>Terisi</span>
                <span class="flex items-center gap-1.5"><span class="size-3 rounded-sm bg-primary"></span>Booking Anda</span>
            </div>
        </div>
    </div>
</div>
