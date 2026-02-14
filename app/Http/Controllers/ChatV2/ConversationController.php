<?php

namespace App\Http\Controllers\ChatV2;

use App\Events\ChatV2\MessageSent;
use App\Http\Controllers\Controller;
use App\Http\Requests\ChatV2\SendMessageRequest;
use App\Models\ChatV2\Conversation;
use App\Models\ChatV2\Message;
use App\Services\ChatV2\ChatService;
use App\Services\CoupleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ConversationController extends Controller
{
    public function __construct(
        private readonly CoupleService $coupleService,
        private readonly ChatService $chatService
    ) {}

    public function show(Request $request)
    {
        $couple = $this->coupleService->getUserCouple($request->user());
        abort_if(! $couple, 403);

        $conversation = $this->chatService->getOrCreateConversationForCouple($couple);
        Gate::authorize('view', $conversation);

        $partner = $couple->users()
            ->where('users.id', '!=', $request->user()->id)
            ->first();

        return view('chat-v2.room', [
            'conversationId' => $conversation->id,
            'coupleId' => $couple->id,
            'userId' => $request->user()->id,
            'partnerName' => $partner?->name ?? 'Partner',
            'showDiagnostics' => app()->isLocal() || config('app.debug'),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $couple = $this->coupleService->getUserCouple($request->user());
        abort_if(! $couple, 403);

        $conversation = $this->chatService->getOrCreateConversationForCouple($couple);
        Gate::authorize('view', $conversation);

        return $this->conversationPayload($request, $conversation);
    }

    public function showConversation(Request $request, Conversation $conversation): JsonResponse
    {
        Gate::authorize('view', $conversation);

        return $this->conversationPayload($request, $conversation);
    }

    public function send(SendMessageRequest $request): JsonResponse
    {
        $couple = $this->coupleService->getUserCouple($request->user());
        abort_if(! $couple, 403);

        $conversation = $this->chatService->getOrCreateConversationForCouple($couple);
        Gate::authorize('send', $conversation);

        $message = $this->chatService->sendMessage(
            $conversation,
            $request->user(),
            $request->string('type')->toString(),
            $request->input('body'),
            $request->file('attachment'),
            $request->integer('duration_ms') ?: null,
            $request->integer('reply_to_message_id') ?: null
        );

        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'message' => $message,
        ], 201);
    }

    private function conversationPayload(Request $request, Conversation $conversation): JsonResponse
    {
        $beforeId = $request->integer('before_id');
        $limit = min(100, max(10, $request->integer('limit', 30)));

        $query = Message::query()
            ->where('conversation_id', $conversation->id)
            ->with(['sender:id,name', 'receipts']);

        if ($beforeId) {
            $query->where('id', '<', $beforeId);
        }

        $messages = $query
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();

        $oldestId = $messages->first()->id ?? $beforeId;
        $hasMore = false;

        if ($oldestId) {
            $hasMore = Message::query()
                ->where('conversation_id', $conversation->id)
                ->where('id', '<', $oldestId)
                ->exists();
        }

        return response()->json([
            'conversation_id' => $conversation->id,
            'messages' => $messages,
            'has_more' => $hasMore,
        ]);
    }
}
