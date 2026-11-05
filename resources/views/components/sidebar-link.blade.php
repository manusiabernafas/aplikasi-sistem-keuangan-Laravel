@props(['href', 'icon' => 'bi-dot', 'active' => false])

<a href="{{ $href }}" class="nav-link {{ $active ? 'active' : '' }}">
    <i class="bi {{ $icon }}"></i>
    <span>{{ $slot }}</span>
</a>
