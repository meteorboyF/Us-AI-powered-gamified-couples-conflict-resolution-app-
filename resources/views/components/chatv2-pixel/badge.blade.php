@props(['state' => 'neutral'])

@php
    $classes = match ($state) {
        'online' => 'chatv2-badge chatv2-badge-online',
        'away' => 'chatv2-badge chatv2-badge-away',
        'offline' => 'chatv2-badge chatv2-badge-offline',
        default => 'chatv2-badge',
    };
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>

