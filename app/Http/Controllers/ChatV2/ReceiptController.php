<?php

namespace App\Http\Controllers\ChatV2;

use App\Events\ChatV2\MessageDelivered;
use App\Events\ChatV2\MessageRead;
use App\Http\Controllers\Controller;
use App\Models\ChatV2\Message;
use App\Services\ChatV2\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    public function __construct(
        private readonly ChatService $chatService
    ) {}

    public function delivered(Request $request, Message $message): JsonResponse
    {
        $receipt = $this->chatService->markDelivered($message, $request->user());

        broadcast(new MessageDelivered(
            $message->conversation_id,
            $message->id,
            $request->user()->id,
            $receipt->delivered_at?->toISOString() ?? now()->toISOString()
        ))->toOthers();

        return response()->json([
            'message_id' => $message->id,
            'user_id' => $request->user()->id,
            'delivered_at' => $receipt->delivered_at,
        ]);
    }

    public function read(Request $request, Message $message): JsonResponse
    {
        $receipt = $this->chatService->markRead($message, $request->user());

        broadcast(new MessageRead(
            $message->conversation_id,
            $message->id,
            $request->user()->id,
            $receipt->read_at?->toISOString() ?? now()->toISOString()
        ))->toOthers();

        return response()->json([
            'message_id' => $message->id,
            'user_id' => $request->user()->id,
            'read_at' => $receipt->read_at,
        ]);
    }
}
