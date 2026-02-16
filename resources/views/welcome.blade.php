<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Us — Couples Platform</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-navy min-h-screen flex items-center justify-center p-6">

        <!-- Background Decor (PDF Page 13: "In-world" feeling) -->
        <div class="fixed inset-0 opacity-20 pointer-events-none overflow-hidden">
            <div class="absolute top-10 left-10 w-32 h-32 bg-moonlight rounded-full blur-3xl"></div>
            <div class="absolute bottom-20 right-10 w-64 h-64 bg-rose rounded-full blur-3xl"></div>
        </div>

        <div class="w-full max-w-md relative z-10">
            
            <!-- Logo Section -->
            <div class="text-center mb-10">
                <h1 class="text-7xl text-white drop-shadow-[4px_4px_0_#E85A9B] mb-2">Us</h1>
                <p class="text-moonlight font-pixel text-2xl">A gamified space for two ❤️</p>
            </div>

            <!-- Onboarding Panel (PDF Page 17) -->
            <x-rpg-panel>
                <div class="text-center space-y-6">
                    <h2 class="text-4xl text-berry uppercase tracking-tighter">Welcome Home</h2>
                    
                    <p class="text-xl font-medium leading-tight">
                        Transform your relationship effort into a beautiful shared world.
                    </p>

                    <div class="flex flex-col gap-4 py-2">
                        <x-pixel-button class="w-full">
                            Create New Couple
                        </x-pixel-button>
                        
                        <x-pixel-button variant="secondary" class="w-full">
                            Join with Code
                        </x-pixel-button>
                    </div>
                </div>
            </x-rpg-panel>

            <!-- Secondary Links -->
            <div class="mt-8 text-center">
                <a href="#" class="text-sand font-pixel text-2xl hover:text-white transition-colors">
                    Existing User? Log In
                </a>
            </div>

        </div>

    </body>
</html>