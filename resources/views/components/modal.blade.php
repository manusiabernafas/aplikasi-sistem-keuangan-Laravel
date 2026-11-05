@props([
    'name',
    'maxWidth' => 'lg',
])

@php
$sizeClass = [
    'sm' => 'modal-sm',
    'md' => '',
    'lg' => 'modal-lg',
    'xl' => 'modal-xl',
][$maxWidth] ?? '';
@endphp

<div class="modal fade" id="{{ $name }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered {{ $sizeClass }}">
        <div class="modal-content border-0 shadow">
            {{ $slot }}
        </div>
    </div>
</div>
