@props([
    'variant' => 'primary',
    'size' => null,
    'type' => 'button',
])

@php
    $classes = trim(
        'btn btn-' . $variant .
        ($size ? ' btn-' . $size : '')
    );
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</button>
