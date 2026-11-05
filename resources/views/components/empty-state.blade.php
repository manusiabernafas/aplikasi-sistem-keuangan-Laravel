@props(['icon' => 'bi-inbox', 'title', 'description' => null])

<div class="empty-state">
    <i class="bi {{ $icon }} empty-state-icon d-block mb-3"></i>
    <h2 class="h6 fw-semibold text-dark mb-1">{{ $title }}</h2>
    @if ($description)
        <p class="small mb-3 mx-auto" style="max-width: 26rem;">{{ $description }}</p>
    @endif

    @isset($action)
        <div class="mt-2">{{ $action }}</div>
    @endisset
</div>
