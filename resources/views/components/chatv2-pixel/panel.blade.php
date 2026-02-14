@props(['tone' => 'base'])

@php
    $toneClasses = match ($tone) {
        'header' => 'chatv2-panel chatv2-panel-header',
        'composer' => 'chatv2-panel chatv2-panel-composer',
        default => 'chatv2-panel',
    };
@endphp

<section {{ $attributes->merge(['class' => $toneClasses]) }}>
    {{ $slot }}
</section>

