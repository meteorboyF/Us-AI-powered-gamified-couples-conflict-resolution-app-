<x-action-section>
    <x-slot name="title">
        {{ __('Reminders') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Choose which daily reminders you want to receive in-app.') }}
    </x-slot>

    <x-slot name="content">
        <div class="space-y-4">
            <label class="flex items-center gap-3">
                <input type="checkbox" wire:model="dailyCheckin" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                <span class="text-sm text-gray-700">Daily check-in reminders</span>
            </label>

            <label class="flex items-center gap-3">
                <input type="checkbox" wire:model="mission" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                <span class="text-sm text-gray-700">Mission reminders</span>
            </label>

            <label class="flex items-center gap-3">
                <input type="checkbox" wire:model="anniversary" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                <span class="text-sm text-gray-700">Anniversary reminders</span>
            </label>
        </div>

        <div class="mt-6 flex items-center gap-3">
            <x-button wire:click="save">
                {{ __('Save') }}
            </x-button>
        </div>

        @if (session()->has('message'))
            <p class="mt-3 text-sm text-green-600">{{ session('message') }}</p>
        @endif
    </x-slot>
</x-action-section>
