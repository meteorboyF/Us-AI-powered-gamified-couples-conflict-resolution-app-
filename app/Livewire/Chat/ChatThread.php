<?php

namespace App\Livewire\Chat;

use App\Domain\Couples\CoupleContext;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ChatThread extends Component
{
    public string $body = '';

    public ?int $chatId = null;

    public ?int $currentCoupleId = null;

    public function mount(CoupleContext $coupleContext): void
    {
        abort_unless(config('us.features.chat_v1'), 404);

        /** @var User|null $user */
        $user = auth()->user();
        abort_unless($user !== null, 403);

        $this->currentCoupleId = $coupleContext->currentCoupleId();
        abort_if($this->currentCoupleId === null, 403);

        $chat = Chat::query()
            ->where('couple_id', $this->currentCoupleId)
            ->orderBy('id')
            ->first();

        if (! $chat) {
            return;
        }

        Gate::authorize('view', $chat);

        $this->chatId = $chat->id;
        $this->markUnreadMessagesAsRead($chat, $user->id);
    }

    public function startChat(): void
    {
        /** @var User|null $user */
        $user = auth()->user();
        abort_unless($user !== null, 403);
        abort_if($this->currentCoupleId === null, 403);

        $chat = $this->resolveOrCreateChat($user);

        Gate::authorize('view', $chat);
    }

    public function sendMessage(): void
    {
        $validated = $this->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        /** @var User|null $user */
        $user = auth()->user();
        abort_unless($user !== null, 403);
        abort_if($this->currentCoupleId === null, 403);

        $chat = $this->resolveOrCreateChat($user);
        Gate::authorize('sendMessage', $chat);

        $body = $this->normalizeMessageBody($validated['body']);
        if ($body === '') {
            $this->addError('body', 'Message cannot be empty.');

            return;
        }

        ChatMessage::query()->create([
            'chat_id' => $chat->id,
            'couple_id' => $chat->couple_id,
            'sender_id' => $user->id,
            'body' => $body,
            'sent_at' => now(),
        ]);

        $this->body = '';
        $this->markUnreadMessagesAsRead($chat, $user->id);
    }

    public function render()
    {
        return view('livewire.chat.chat-thread', [
            'chat' => $this->chat(),
            'messages' => $this->messages(),
            'currentUserId' => auth()->id(),
        ]);
    }

    private function resolveOrCreateChat(User $user): Chat
    {
        if ($this->chatId !== null) {
            $chat = Chat::query()->findOrFail($this->chatId);

            return $chat;
        }

        $chat = Chat::query()
            ->where('couple_id', $this->currentCoupleId)
            ->orderBy('id')
            ->first();

        if (! $chat) {
            $chat = Chat::query()->create([
                'couple_id' => $this->currentCoupleId,
            ]);

            $chat->participants()->create([
                'user_id' => $user->id,
                'joined_at' => now(),
            ]);
        }

        $this->chatId = $chat->id;

        return $chat;
    }

    private function markUnreadMessagesAsRead(Chat $chat, int $recipientId): void
    {
        ChatMessage::query()
            ->where('chat_id', $chat->id)
            ->where('sender_id', '!=', $recipientId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    private function chat(): ?Chat
    {
        if ($this->chatId === null) {
            return null;
        }

        return Chat::query()->find($this->chatId);
    }

    /**
     * @return Collection<int, ChatMessage>
     */
    private function messages(): Collection
    {
        if ($this->chatId === null) {
            return new Collection;
        }

        return ChatMessage::query()
            ->where('chat_id', $this->chatId)
            ->with('sender')
            ->orderBy('sent_at')
            ->limit(100)
            ->get();
    }

    private function normalizeMessageBody(string $body): string
    {
        return trim(preg_replace('/[ \t]+/', ' ', $body) ?? '');
    }
}
