<div class="min-h-screen bg-slate-100 py-10">
    <div class="mx-auto flex w-full max-w-3xl flex-col gap-4 rounded-xl bg-white p-6 shadow-sm">
        <header class="border-b border-slate-200 pb-4">
            <h1 class="text-xl font-semibold text-slate-900">Chat</h1>
            <p class="text-sm text-slate-500">Messages are shown oldest first.</p>
        </header>

        @if (!$chat)
            <div class="rounded-lg border border-dashed border-slate-300 bg-slate-50 p-8 text-center">
                <p class="text-slate-700">No chat exists for this couple yet.</p>
                <button
                    type="button"
                    wire:click="startChat"
                    class="mt-4 inline-flex items-center rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700"
                >
                    Start chat
                </button>
            </div>
        @else
            <div class="max-h-[28rem] space-y-3 overflow-y-auto rounded-lg border border-slate-200 bg-slate-50 p-4">
                @forelse ($messages as $message)
                    @php($isMine = $message->sender_id === $currentUserId)
                    <div class="flex {{ $isMine ? 'justify-end' : 'justify-start' }}">
                        <article class="max-w-[85%] rounded-xl px-4 py-3 text-sm {{ $isMine ? 'bg-slate-900 text-white' : 'bg-white text-slate-800 ring-1 ring-slate-200' }}">
                            <p class="whitespace-pre-wrap break-words">{{ $message->body }}</p>
                            <p class="mt-2 text-xs {{ $isMine ? 'text-slate-300' : 'text-slate-500' }}">
                                {{ $message->sender?->name ?? 'Unknown' }} • {{ $message->sent_at?->format('M j, Y g:i A') }}
                            </p>
                        </article>
                    </div>
                @empty
                    <p class="text-center text-sm text-slate-500">No messages yet.</p>
                @endforelse
            </div>
        @endif

        <form wire:submit="sendMessage" class="space-y-2 border-t border-slate-200 pt-4">
            <label for="messageBody" class="sr-only">Message</label>
            <textarea
                id="messageBody"
                wire:model="body"
                rows="3"
                maxlength="2000"
                class="w-full rounded-lg border-slate-300 text-sm focus:border-slate-500 focus:ring-slate-500"
                placeholder="Write a message..."
            ></textarea>
            @error('body')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
            <div class="flex justify-end">
                <button
                    type="submit"
                    class="inline-flex items-center rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700"
                >
                    Send
                </button>
            </div>
        </form>
    </div>
</div>
