<?php

namespace App\Http\Controllers\UI;

use App\Http\Controllers\AiCoachController;
use App\Http\Controllers\Controller;
use App\Models\AiDraft;
use App\Models\AiMessage;
use App\Models\AiSession;
use App\Support\CoupleContext;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
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
        $couple = app(CoupleContext::class)->resolve();

        if (! $couple) {
            return redirect()->route('ai.coach.page')->with('status', 'No couple selected.');
        }

        $validated = $request->validate([
            'mode' => ['required', 'string', 'in:vent,bridge,repair'],
        ]);

        $this->authorize('create', [AiSession::class, $couple->id]);

        $session = AiSession::query()->create([
            'couple_id' => $couple->id,
            'created_by_user_id' => $request->user()->id,
            'mode' => $validated['mode'],
            'title' => null,
            'status' => 'active',
            'safety_flags' => [],
            'meta' => [],
        ]);

        return redirect()
            ->route('ai.coach.page', ['session' => $session->id])
            ->with('status', 'Session created.');
    }

    public function send(Request $request, AiSession $session): RedirectResponse
    {
        $response = app()->call([app(AiCoachController::class), 'message'], [
            'request' => $request,
            'session' => $session,
        ]);

        return $this->redirectFromApiResponse($response, $session->id, 'Sent.');
    }

    public function accept(Request $request, AiSession $session, AiDraft $draft): RedirectResponse
    {
        $response = app()->call([app(AiCoachController::class), 'acceptDraft'], [
            'request' => $request,
            'session' => $session,
            'draft' => $draft,
        ]);

        return $this->redirectFromApiResponse($response, $session->id, 'Draft accepted.');
    }

    public function discard(Request $request, AiSession $session, AiDraft $draft): RedirectResponse
    {
        $response = app()->call([app(AiCoachController::class), 'discardDraft'], [
            'request' => $request,
            'session' => $session,
            'draft' => $draft,
        ]);

        return $this->redirectFromApiResponse($response, $session->id, 'Draft discarded.');
    }

    private function redirectFromApiResponse(mixed $response, int $sessionId, string $successMessage): RedirectResponse
    {
        if ($response instanceof JsonResponse && in_array($response->getStatusCode(), [200, 201], true)) {
            return redirect()
                ->route('ai.coach.page', ['session' => $sessionId])
                ->with('status', $successMessage);
        }

        return redirect()
            ->route('ai.coach.page', ['session' => $sessionId])
            ->withErrors(['ai_coach' => 'Action failed. Please try again.']);
    }
}
