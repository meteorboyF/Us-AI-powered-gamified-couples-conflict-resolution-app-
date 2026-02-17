<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Us — My Profile</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-navy min-h-screen p-4 pb-24">

    <header class="max-w-xl mx-auto mb-8 flex items-center justify-between">
        <a href="/dashboard" class="text-sand font-pixel text-xl">← WORLD</a>
        <h1 class="text-3xl text-white font-pixel uppercase">Settings</h1>
        <div class="text-2xl">⚙️</div>
    </header>

    <main class="max-w-xl mx-auto space-y-6">
        
        <!-- COUPLE LINKING (PDF Page 3) -->
        <section>
            <h2 class="font-pixel text-2xl text-moonlight mb-3 uppercase">Partner Linking</h2>
            <x-rpg-panel>
                <p class="text-xs uppercase font-pixel text-toast mb-2">Your Invite Code:</p>
                <div class="flex gap-2 mb-4">
                    <div class="flex-1 bg-white border-4 border-dashed border-toast p-3 text-center font-pixel text-3xl tracking-widest text-rose">
                        LOVE-9921
                    </div>
                    <button class="bg-sand border-b-4 border-toast px-4 active:translate-y-1 active:border-b-0">📋</button>
                </div>
                <p class="text-xs italic text-cocoa">Share this code with your partner to link your worlds.</p>
            </x-rpg-panel>
        </section>

        <!-- PREFERENCES (PDF Page 3: Love Language & Boundaries) -->
        <section>
            <h2 class="font-pixel text-2xl text-moonlight mb-3 uppercase">Preferences</h2>
            <x-rpg-panel>
                <div class="space-y-4">
                    <div>
                        <label class="font-pixel text-rose uppercase text-sm block mb-1">Primary Love Language</label>
                        <select class="w-full bg-white border-4 border-toast p-2 font-pixel text-lg">
                            <option>Words of Affirmation</option>
                            <option>Quality Time</option>
                            <option>Acts of Service</option>
                            <option>Physical Touch</option>
                            <option>Receiving Gifts</option>
                        </select>
                    </div>

                    <div class="border-t-2 border-sand pt-4">
                        <x-pixel-toggle id="notifications" label="Push Notifications" />
                        <x-pixel-toggle id="comfort" label="Comfort Mode AI" />
                        <x-pixel-toggle id="visibility" label="Show Presence" />
                    </div>
                </div>
            </x-rpg-panel>
        </section>

        <!-- ACCOUNT -->
        <section>
            <x-pixel-button variant="secondary" class="w-full">Logout</x-pixel-button>
            <button class="w-full mt-4 font-pixel text-danger uppercase hover:underline">Delete Data & Account</button>
        </section>

    </main>

</body>
</html>