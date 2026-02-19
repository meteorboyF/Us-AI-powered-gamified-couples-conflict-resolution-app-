@props(['variant' => 'primary'])

@php
    $baseClasses = "text-sm font-semibold uppercase tracking-wider px-4 py-2 rounded-md border transition-all duration-75 inline-block text-center cursor-pointer select-none";
    
    $variants = [
        'primary' => 'bg-indigo-600 text-white border-indigo-700 hover:bg-indigo-500',
        'secondary' => 'bg-gray-200 text-gray-900 border-gray-300 hover:bg-gray-100',
        'ghost' => 'bg-white text-gray-800 border-gray-300 hover:bg-gray-50',
    ];
    
    $classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['primary']);
@endphp

<button {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</button>
