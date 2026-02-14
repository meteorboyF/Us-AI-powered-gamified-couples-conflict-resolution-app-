@props(['name' => 'dot'])

@php
    $icons = config('chat_v2_ui.icons', []);
    $assetPath = $icons[$name] ?? null;
@endphp

@if ($assetPath)
    <img {{ $attributes->merge(['class' => 'chatv2-icon', 'src' => $assetPath, 'alt' => $name]) }}>
@else
    <span {{ $attributes->merge(['class' => 'chatv2-icon-fallback']) }} aria-hidden="true">
        @switch($name)
            @case('send')
                ↑
                @break
            @case('attach')
                ⎘
                @break
            @case('mic')
                ●
                @break
            @case('tick_sent')
                ✓
                @break
            @case('tick_delivered')
                ✓✓
                @break
            @case('tick_read')
                ✓✓
                @break
            @default
                ●
        @endswitch
    </span>
@endif

