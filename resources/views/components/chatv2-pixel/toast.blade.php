@props(['tone' => 'info'])

@php
    $classes = match ($tone) {
        'warn' => 'chatv2-toast chatv2-toast-warn',
        'success' => 'chatv2-toast chatv2-toast-success',
        default => 'chatv2-toast',
    };
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>

