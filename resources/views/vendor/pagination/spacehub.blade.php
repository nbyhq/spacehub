@if ($paginator->hasPages())
    @php
        $btn = 'inline-flex h-9 min-w-9 items-center justify-center rounded-lg border px-2 text-label-md font-medium transition-colors';
    @endphp

    <nav role="navigation" aria-label="Navigasi halaman" class="flex items-center gap-1">
        @if ($paginator->onFirstPage())
            <span class="{{ $btn }} cursor-not-allowed border-outline-variant/50 bg-surface-container-low text-outline" aria-disabled="true" aria-label="Sebelumnya">
                <span class="material-symbols-outlined text-[20px]">chevron_left</span>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $btn }} border-outline-variant bg-white text-on-surface hover:bg-surface-container-low" aria-label="Sebelumnya">
                <span class="material-symbols-outlined text-[20px]">chevron_left</span>
            </a>
        @endif

        <span class="px-2 text-body-md text-on-surface-variant sm:hidden">
            Hal. {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
        </span>

        <div class="hidden items-center gap-1 sm:flex">
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="{{ $btn }} border-transparent text-on-surface-variant">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="{{ $btn }} border-primary bg-primary text-white shadow-sm" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="{{ $btn }} border-outline-variant bg-white text-on-surface hover:bg-surface-container-low" aria-label="Halaman {{ $page }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </div>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $btn }} border-outline-variant bg-white text-on-surface hover:bg-surface-container-low" aria-label="Berikutnya">
                <span class="material-symbols-outlined text-[20px]">chevron_right</span>
            </a>
        @else
            <span class="{{ $btn }} cursor-not-allowed border-outline-variant/50 bg-surface-container-low text-outline" aria-disabled="true" aria-label="Berikutnya">
                <span class="material-symbols-outlined text-[20px]">chevron_right</span>
            </span>
        @endif
    </nav>
@endif
