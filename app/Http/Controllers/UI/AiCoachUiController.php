<?php

namespace App\Http\Controllers\UI;

use App\Http\Controllers\Controller;
use App\Models\AiDraft;
use App\Models\AiMessage;
use App\Models\AiSession;
use App\Support\CoupleContext;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AiCoachUiController extends Controller
{
    public function page(Request $request, CoupleContext $context): View
    {
        $couple = $context->resolve();

        if (! $couple) {
            return view('ai-coach.page', [
                'coupleId' => null,
                'sessions' => collect(),
                'currentSession' => null,
                'messages' => collect(),
                'draft' => null,
            ]);
        }

        $sessions = AiSession::query()
            ->where('couple_id', $couple->id)
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        $requestedSessionId = $request->integer('session');
        $currentSession = $requestedSessionId
            ? $sessions->firstWhere('id', $requestedSessionId)
            : $sessions->first();

        $messages = collect();
        $draft = null;

        if ($currentSession) {
            $messages = AiMessage::query()
                ->where('ai_session_id', $currentSession->id)
                ->orderByDesc('id')
                ->limit(50)
                ->get()
                ->reverse()
                ->values();

            $draft = AiDraft::query()
                ->where('ai_session_id', $currentSession->id)
                ->where('status', 'draft')
                ->latest('id')
                ->first();
        }

        return view('ai-coach.page', [
            'coupleId' => (int) $couple->id,
            'sessions' => $sessions,
            'currentSession' => $currentSession,
            'messages' => $messages,
            'draft' => $draft,
        ]);
    }

    public function createSession(Request $request): RedirectResponse
    {
        return redirect()->route('ai.coach.page');
    }

    public function send(Request $request, AiSession $session): RedirectResponse
    {
        return redirect()->route('ai.coach.page', ['session' => $session->id]);
    }

    public function accept(Request $request, AiSession $session, AiDraft $draft): RedirectResponse
    {
        return redirect()->route('ai.coach.page', ['session' => $session->id]);
    }

    public function discard(Request $request, AiSession $session, AiDraft $draft): RedirectResponse
    {
        return redirect()->route('ai.coach.page', ['session' => $session->id]);
    }
}
