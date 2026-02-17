@props(['label', 'id'])

<div class="flex items-center justify-between py-2">
    <label for="{{ $id }}" class="font-pixel text-xl text-cocoa uppercase">{{ $label }}</label>
    <div class="relative inline-block w-12 h-6 cursor-pointer">
        <input type="checkbox" id="{{ $id }}" class="sr-only peer">
        <div class="w-full h-full bg-toast border-2 border-cocoa peer-checked:bg-leaf transition-colors"></div>
        <div class="absolute top-1 left-1 w-4 h-4 bg-parchment border-2 border-cocoa transition-transform peer-checked:translate-x-6"></div>
    </div>
</div>