@props(['paginator'])

@if ($paginator->hasPages())
    <nav class="mt-10 flex items-center justify-center gap-3" aria-label="Pagination">
        @if ($paginator->onFirstPage())
            <span class="btn-secondary pointer-events-none opacity-40"><x-icon name="left" class="size-4" /> Prev</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="btn-secondary"><x-icon name="left" class="size-4" /> Prev</a>
        @endif
        <span class="type-label text-text-dim">{{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="btn-secondary">Next <x-icon name="right" class="size-4" /></a>
        @else
            <span class="btn-secondary pointer-events-none opacity-40">Next <x-icon name="right" class="size-4" /></span>
        @endif
    </nav>
@endif
