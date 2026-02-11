<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Us - Couple App') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="font-sans antialiased text-gray-900">
    <div class="min-h-screen flex">
        <!-- Left Side: Image Carousel (Hidden on mobile) -->
        <div class="hidden lg:flex lg:w-1/2 relative bg-gray-900 overflow-hidden">
            <div class="absolute inset-0 opacity-60">
                <img src="https://images.unsplash.com/photo-1516589178581-6cd7833ae3b2?q=80&w=1974&auto=format&fit=crop"
                    alt="Couple having fun" class="w-full h-full object-cover">
            </div>
            <div class="absolute inset-0 bg-gradient-to-br from-purple-900/40 to-pink-900/40 mix-blend-multiply"></div>

            <div class="relative z-10 w-full flex flex-col justify-center px-12 text-white">
                <div class="mb-8">
                    <div class="text-6xl mb-4">💙</div>
                    <h1 class="text-5xl font-bold mb-6 leading-tight">Grow Closer,<br>Every Day.</h1>
                    <p class="text-xl text-gray-200 max-w-lg leading-relaxed">
                        The fun way to strengthen your relationship, resolve conflicts, and preserve your favorite
                        memories.
                    </p>
                </div>

                <!-- Feature Pills -->
                <div class="flex flex-wrap gap-3">
                    <span class="px-4 py-2 bg-white/20 backdrop-blur-md rounded-full text-sm font-semibold">✨ AI
                        Conflict Resolution</span>
                    <span class="px-4 py-2 bg-white/20 backdrop-blur-md rounded-full text-sm font-semibold">🎮 Gamified
                        Growth</span>
                    <span class="px-4 py-2 bg-white/20 backdrop-blur-md rounded-full text-sm font-semibold">📸 Shared
                        Vault</span>
                </div>
            </div>
        </div>

        <!-- Right Side: Form -->
        <div
            class="w-full lg:w-1/2 flex items-center justify-center bg-gradient-to-br from-purple-50 via-white to-pink-50 p-8">
            <div class="w-full max-w-md">
                <!-- Mobile Logo (Visible only on mobile) -->
                <div class="lg:hidden text-center mb-8">
                    <div class="text-5xl mb-2">💙</div>
                    <h2 class="text-2xl font-bold text-gray-800">Us</h2>
                </div>

                <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-xl p-8 border border-white/50">
                    {{ $slot }}
                </div>

                <div class="mt-8 text-center text-sm text-gray-500">
                    &copy; {{ date('Y') }} Us App. Built with love.
                </div>
            </div>
        </div>
    </div>

    @livewireScripts
</body>

</html>