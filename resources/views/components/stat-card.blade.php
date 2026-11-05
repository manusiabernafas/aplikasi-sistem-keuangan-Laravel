@props(['label', 'value', 'icon' => 'bi-wallet2', 'variant' => 'primary'])

<div class="card stat-card h-100">
    <div class="card-body d-flex align-items-start gap-3">
        <div class="stat-icon bg-{{ $variant }}-subtle text-{{ $variant }}">
            <i class="bi {{ $icon }}"></i>
        </div>
        <div class="flex-grow-1 min-w-0">
            <div class="text-secondary small mb-1">{{ $label }}</div>
            <div class="fs-5 fw-semibold text-dark text-truncate">{{ $value }}</div>
        </div>
    </div>
</div>
