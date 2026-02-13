<div class="rounded-3xl border border-white/70 bg-white/85 p-6 shadow-sm backdrop-blur">
    <h2 class="text-xl font-semibold text-gray-900">Your Wishlist Preferences</h2>
    <p class="mt-1 text-sm text-gray-600">Only your own wishlist can be edited here.</p>

    @if (session()->has('message'))
        <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="mt-6 grid gap-4 md:grid-cols-3">
        <div>
            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">Budget Min</label>
            <input type="number" wire:model="budgetMin" min="0"
                class="w-full rounded-xl border-gray-200 bg-white px-3 py-2 text-sm focus:border-orange-300 focus:ring-orange-100">
            @error('budgetMin') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">Budget Max</label>
            <input type="number" wire:model="budgetMax" min="0"
                class="w-full rounded-xl border-gray-200 bg-white px-3 py-2 text-sm focus:border-orange-300 focus:ring-orange-100">
            @error('budgetMax') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">Currency</label>
            <input type="text" wire:model="currency" placeholder="USD"
                class="w-full rounded-xl border-gray-200 bg-white px-3 py-2 text-sm focus:border-orange-300 focus:ring-orange-100">
            @error('currency') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="mt-4 grid gap-4">
        <div>
            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">Love Languages (comma-separated)</label>
            <input type="text" wire:model="loveLanguages" placeholder="words of affirmation, quality time"
                class="w-full rounded-xl border-gray-200 bg-white px-3 py-2 text-sm focus:border-orange-300 focus:ring-orange-100">
            @error('loveLanguages') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">Likes (comma-separated)</label>
            <textarea wire:model="likes" rows="3" placeholder="hiking, handmade notes, jazz"
                class="w-full rounded-xl border-gray-200 bg-white px-3 py-2 text-sm focus:border-orange-300 focus:ring-orange-100"></textarea>
            @error('likes') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">Dislikes (comma-separated)</label>
            <textarea wire:model="dislikes" rows="3" placeholder="seafood, crowded events"
                class="w-full rounded-xl border-gray-200 bg-white px-3 py-2 text-sm focus:border-orange-300 focus:ring-orange-100"></textarea>
            @error('dislikes') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <label class="mt-4 flex items-center gap-2 text-sm text-gray-700">
        <input type="checkbox" wire:model="shareWithPartner" class="rounded border-gray-300 text-orange-500 focus:ring-orange-200">
        Share this wishlist with partner
    </label>

    <div class="mt-6">
        <button wire:click="save"
            class="rounded-xl bg-orange-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-orange-600">
            Save Wishlist
        </button>
    </div>
</div>
