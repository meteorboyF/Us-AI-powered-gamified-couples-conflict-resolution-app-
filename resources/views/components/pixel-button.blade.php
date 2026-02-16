@props(['variant' => 'primary'])

@php
    $baseClasses = "font-pixel text-2xl uppercase tracking-wider px-6 py-2 border-b-4 active:border-b-0 active:translate-y-1 transition-all duration-75 inline-block text-center cursor-pointer select-none";
    
    $variants = [
        'primary' => 'bg-rose text-white border-berry hover:bg-[#ff6ab0]',
        'secondary' => 'bg-sand text-cocoa border-toast hover:bg-[#f5e3be]',
        'ghost' => 'bg-transparent text-rose border-transparent hover:bg-white/10',
    ];
    
    $classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['primary']);
@endphp

<button {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</button>