@props(['title', 'description' => null])

<div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
    <div>
        <h1 class="h4 fw-semibold mb-1">{{ $title }}</h1>
        @if ($description)
            <p class="text-secondary small mb-0">{{ $description }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="d-flex align-items-center gap-2">
            {{ $actions }}
        </div>
    @endisset
</div>
