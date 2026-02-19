<?php

namespace App\Http\Controllers\UI;

use App\Domain\Gifts\Fallback\GiftSuggestionGenerator;
use App\Http\Controllers\Controller;
use App\Models\Couple;
use App\Models\GiftRequest;
use App\Models\GiftSuggestion;
use App\Support\CoupleContext;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GiftsUiController extends Controller
{
    public function page(Request $request, CoupleContext $context): View
    {
        $couple = $context->resolve();

        if (! $couple) {
            return view('gifts.page', [
                'coupleId' => null,
                'requests' => collect(),
                'selectedRequest' => null,
                'suggestions' => collect(),
            ]);
        }

        $requests = GiftRequest::query()
            ->where('couple_id', $couple->id)
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        $selectedRequest = null;
        $selectedRequestId = $request->integer('request_id');
        if ($selectedRequestId) {
            $selectedRequest = GiftRequest::query()->find($selectedRequestId);
            if ($selectedRequest) {
                $this->ensureRequestInCouple($selectedRequest, $couple);
                $this->authorize('view', $selectedRequest);
            }
        }

        $suggestions = $selectedRequest
            ? $selectedRequest->suggestions()->orderBy('id')->get()
            : collect();

        return view('gifts.page', [
            'coupleId' => (int) $couple->id,
            'requests' => $requests,
            'selectedRequest' => $selectedRequest,
            'suggestions' => $suggestions,
        ]);
    }

    public function createRequest(Request $request, CoupleContext $context): RedirectResponse
    {
        $couple = $context->resolve();

        if (! $couple) {
            return redirect()->route('gifts.ui')->with('status', 'No couple selected');
        }

        $validated = $request->validate([
            'occasion' => ['required', 'string', 'in:anniversary,sorry,comfort,surprise,birthday,date_night'],
            'budget_min' => ['nullable', 'integer', 'min:0'],
            'budget_max' => ['nullable', 'integer', 'min:0'],
            'time_constraint' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $this->authorize('create', [GiftRequest::class, $couple->id]);

        $giftRequest = GiftRequest::query()->create([
            'couple_id' => $couple->id,
            'created_by_user_id' => $request->user()->id,
            'occasion' => $validated['occasion'],
            'budget_min' => $validated['budget_min'] ?? null,
            'budget_max' => $validated['budget_max'] ?? null,
            'time_constraint' => $validated['time_constraint'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'meta' => [],
        ]);

        return redirect()
            ->route('gifts.ui', ['request_id' => $giftRequest->id])
            ->with('status', 'Gift request created.');
    }

    public function generate(
        Request $request,
        CoupleContext $context,
        GiftRequest $giftRequest,
        GiftSuggestionGenerator $generator
    ): RedirectResponse {
        $couple = $context->resolve();

        if (! $couple) {
            return redirect()->route('gifts.ui')->with('status', 'No couple selected');
        }

        $this->ensureRequestInCouple($giftRequest, $couple);
        $this->authorize('update', $giftRequest);

        if (! $giftRequest->suggestions()->exists()) {
            foreach ($generator->generate($giftRequest) as $payload) {
                $giftRequest->suggestions()->create($payload);
            }
        }

        return redirect()
            ->route('gifts.ui', ['request_id' => $giftRequest->id])
            ->with('status', 'Suggestions ready.');
    }

    public function favorite(
        Request $request,
        CoupleContext $context,
        GiftSuggestion $suggestion
    ): RedirectResponse {
        $couple = $context->resolve();

        if (! $couple) {
            return redirect()->route('gifts.ui')->with('status', 'No couple selected');
        }

        $suggestion->loadMissing('giftRequest');
        $this->ensureSuggestionInCouple($suggestion, $couple);
        $this->authorize('updateFavorite', $suggestion);

        $suggestion->forceFill([
            'is_favorite' => ! $suggestion->is_favorite,
        ])->save();

        return redirect()
            ->route('gifts.ui', ['request_id' => $suggestion->gift_request_id])
            ->with('status', 'Favorite updated.');
    }

    private function ensureRequestInCouple(GiftRequest $giftRequest, Couple $couple): void
    {
        if ((int) $giftRequest->couple_id !== (int) $couple->id) {
            abort(403, 'Forbidden');
        }
    }

    private function ensureSuggestionInCouple(GiftSuggestion $suggestion, Couple $couple): void
    {
        if ((int) $suggestion->giftRequest->couple_id !== (int) $couple->id) {
            abort(403, 'Forbidden');
        }
    }
}
