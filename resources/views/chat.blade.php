<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Us — Couple Chat</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-navy flex flex-col h-screen overflow-hidden">

    <!-- Top Bar (PDF Page 18) -->
    <header class="bg-cocoa border-b-4 border-toast p-4 flex items-center justify-between z-10">
        <a href="/dashboard" class="text-sand font-pixel text-xl">← BACK</a>
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-sand border-2 border-white rounded-sm flex items-center justify-center text-2xl">🐱</div>
            <div>
                <p class="text-white font-pixel leading-none">PARTNER</p>
                <p class="text-leaf font-pixel text-xs animate-pulse">● ONLINE</p>
            </div>
        </div>
        <div class="text-2xl">⚙️</div>
    </header>

    <!-- Chat Area -->
    <main class="flex-1 overflow-y-auto p-4 bg-[#1a2a44] space-y-2">
        <x-chat-bubble side="left" sender="Alex">
            Hey! Did you see the new gazebo I added to our garden?
        </x-chat-bubble>

        <x-chat-bubble side="right" sender="Me">
            It looks so cute! I'm going to earn some Love Seeds today to buy some flowers for it. 🌸
        </x-chat-bubble>

        <x-chat-bubble side="left" sender="Alex">
            Can't wait! Our world is looking amazing.
        </x-chat-bubble>
    </main>

    <!-- Composer (PDF Page 18) -->
    <footer class="p-4 bg-cocoa border-t-4 border-toast">
        <div class="max-w-4xl mx-auto flex gap-2">
            <!-- Attach button -->
            <button class="bg-sand border-b-4 border-toast p-2 text-xl active:translate-y-1 active:border-b-0">📎</button>
            
            <!-- Input Field -->
            <input type="text" 
                   class="flex-1 bg-parchment border-4 border-toast p-2 font-sans focus:outline-none focus:border-rose text-cocoa" 
                   placeholder="Type a message...">

            <!-- Send button -->
            <button class="bg-rose text-white border-b-4 border-berry px-4 font-pixel text-xl active:translate-y-1 active:border-b-0">
                SEND
            </button>
        </div>
    </footer>

</body>
</html>