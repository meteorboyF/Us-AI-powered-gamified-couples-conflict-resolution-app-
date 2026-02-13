<div class="min-h-screen bg-gradient-to-br from-rose-50 via-orange-50 to-amber-100 py-8">
    <div class="mx-auto max-w-5xl px-4">
        <div class="mb-6 rounded-3xl border border-white/60 bg-white/80 p-5 shadow-sm backdrop-blur">
            <h1 class="text-3xl font-bold text-gray-900">Gifts & Date Ideas</h1>
            <p class="mt-1 text-sm text-gray-600">Save your preferences, then generate ideas powered by AI with safe fallback mode.</p>
        </div>

        <div class="mb-6 inline-flex rounded-2xl bg-white p-1 shadow-sm">
            <button wire:click="setTab('wishlist')"
                class="rounded-xl px-4 py-2 text-sm font-semibold {{ $tab === 'wishlist' ? 'bg-orange-500 text-white' : 'text-gray-600 hover:text-gray-900' }}">
                Wishlist
            </button>
            <button wire:click="setTab('suggestions')"
                class="rounded-xl px-4 py-2 text-sm font-semibold {{ $tab === 'suggestions' ? 'bg-orange-500 text-white' : 'text-gray-600 hover:text-gray-900' }}">
                Suggestions
            </button>
        </div>

        @if ($tab === 'wishlist')
            @livewire('gifts.wishlist-form')
        @else
            @livewire('gifts.suggestions')
        @endif
    </div>
</div>
