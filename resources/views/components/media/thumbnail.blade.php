@props([
    'memory',
    'locked' => false,
])

@php
    $mediaUrl = $memory->getFileUrl();
    $thumbnailUrl = $memory->getThumbnailUrl();
    $renderUrl = $thumbnailUrl ?? $mediaUrl;
@endphp

@if($locked)
    <div class="w-full h-full flex items-center justify-center text-lg font-semibold text-yellow-700">Dual consent required</div>
@elseif($renderUrl)
    <img
        src="{{ $renderUrl }}"
        alt="{{ $memory->title ?: 'Memory thumbnail' }}"
        class="w-full h-full object-cover"
        loading="lazy"
    >
@else
    <div class="w-full h-full flex items-center justify-center p-4 text-sm text-gray-700 text-center">
        Memory unavailable
    </div>
@endif
