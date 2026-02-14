@props(['variant' => 'primary', 'size' => 'md'])

@php
    $variantClasses = match ($variant) {
        'ghost' => 'chatv2-btn chatv2-btn-ghost',
        'danger' => 'chatv2-btn chatv2-btn-danger',
        default => 'chatv2-btn chatv2-btn-primary',
    };

    $sizeClasses = match ($size) {
        'sm' => 'chatv2-btn-sm',
        'lg' => 'chatv2-btn-lg',
        default => 'chatv2-btn-md',
    };
@endphp

<button {{ $attributes->merge(['class' => $variantClasses.' '.$sizeClasses]) }}>
    {{ $slot }}
</button>

