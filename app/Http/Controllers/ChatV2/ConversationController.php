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

        return view('chat-v2.room', [
            'conversationId' => $conversation->id,
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $couple = $this->coupleService->getUserCouple($request->user());
        abort_if(! $couple, 403);

        $conversation = $this->chatService->getOrCreateConversationForCouple($couple);
        Gate::authorize('view', $conversation);

        $messages = Message::query()
            ->where('conversation_id', $conversation->id)
            ->with(['sender:id,name', 'receipts'])
            ->orderByDesc('id')
            ->limit(50)
            ->get()
            ->reverse()
            ->values();

        return response()->json([
            'conversation_id' => $conversation->id,
            'messages' => $messages,
        ]);
    }

    public function showConversation(Request $request, Conversation $conversation): JsonResponse
    {
        Gate::authorize('view', $conversation);

        $messages = Message::query()
            ->where('conversation_id', $conversation->id)
            ->with(['sender:id,name', 'receipts'])
            ->orderByDesc('id')
            ->limit(50)
            ->get()
            ->reverse()
            ->values();

        return response()->json([
            'conversation_id' => $conversation->id,
            'messages' => $messages,
        ]);
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
}
