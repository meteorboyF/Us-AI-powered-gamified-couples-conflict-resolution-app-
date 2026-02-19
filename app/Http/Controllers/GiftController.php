<?php

namespace App\Http\Controllers;

use App\Domain\Gifts\Fallback\GiftSuggestionGenerator;
use App\Models\Couple;
use App\Models\GiftRequest;
use App\Models\GiftSuggestion;
use App\Support\CoupleContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GiftController extends Controller
{
    public function store(Request $request, CoupleContext $context): JsonResponse
    {
        $this->ensureFeatureEnabled();
        $couple = $this->resolveCouple($request, $context);
        $this->authorize('create', [GiftRequest::class, $couple->id]);

        $validated = $request->validate([
            'occasion' => ['required', 'string', 'max:100'],
            'budget_min' => ['nullable', 'integer', 'min:0'],
            'budget_max' => ['nullable', 'integer', 'min:0'],
            'time_constraint' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'meta' => ['nullable', 'array'],
        ]);

        $giftRequest = GiftRequest::query()->create([
            'couple_id' => $couple->id,
            'created_by_user_id' => $request->user()->id,
            'occasion' => $validated['occasion'],
            'budget_min' => $validated['budget_min'] ?? null,
            'budget_max' => $validated['budget_max'] ?? null,
            'time_constraint' => $validated['time_constraint'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'meta' => $validated['meta'] ?? [],
        ]);

        return response()->json([
            'request' => $this->requestPayload($giftRequest),
        ], 201);
    }

    public function show(Request $request, CoupleContext $context, GiftRequest $giftRequest): JsonResponse
    {
        $this->ensureFeatureEnabled();
        $couple = $this->resolveCouple($request, $context);
        $this->ensureRequestInCouple($giftRequest, $couple);
        $this->authorize('view', $giftRequest);

        return response()->json([
            'request' => $this->requestPayload($giftRequest),
            'suggestions' => $giftRequest->suggestions()
                ->orderBy('id')
                ->get()
                ->map(fn (GiftSuggestion $suggestion) => $this->suggestionPayload($suggestion))
                ->values(),
        ]);
    }

    public function generate(
        Request $request,
        CoupleContext $context,
        GiftRequest $giftRequest,
        GiftSuggestionGenerator $generator
    ): JsonResponse {
        $this->ensureFeatureEnabled();
        $couple = $this->resolveCouple($request, $context);
        $this->ensureRequestInCouple($giftRequest, $couple);
        $this->authorize('update', $giftRequest);

        if (! $giftRequest->suggestions()->exists()) {
            foreach ($generator->generate($giftRequest) as $payload) {
                $giftRequest->suggestions()->create($payload);
            }
        }

        return response()->json([
            'request' => $this->requestPayload($giftRequest),
            'suggestions' => $giftRequest->suggestions()
                ->orderBy('id')
                ->get()
                ->map(fn (GiftSuggestion $suggestion) => $this->suggestionPayload($suggestion))
                ->values(),
        ]);
    }

    public function toggleFavorite(Request $request, CoupleContext $context, GiftSuggestion $suggestion): JsonResponse
    {
        $this->ensureFeatureEnabled();
        $couple = $this->resolveCouple($request, $context);
        $suggestion->loadMissing('giftRequest');
        $this->ensureSuggestionInCouple($suggestion, $couple);
        $this->authorize('updateFavorite', $suggestion);

        $suggestion->forceFill([
            'is_favorite' => ! $suggestion->is_favorite,
        ])->save();

        return response()->json([
            'suggestion' => $this->suggestionPayload($suggestion),
        ]);
    }

    private function ensureFeatureEnabled(): void
    {
        if (! config('us.features.gifts_v1', true)) {
            abort(404, 'Feature not available.');
        }
    }

    private function resolveCouple(Request $request, CoupleContext $context): Couple
    {
        $user = $request->user();

        if ($user->current_couple_id && ! $user->couples()->whereKey($user->current_couple_id)->exists()) {
            abort(403, 'Current couple is not accessible.');
        }

        $couple = $context->resolve();

        if (! $couple) {
            abort(response()->json(['message' => 'No couple selected'], 409));
        }

        return $couple;
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

    /**
     * @return array<string, mixed>
     */
    private function requestPayload(GiftRequest $giftRequest): array
    {
        return [
            'id' => $giftRequest->id,
            'couple_id' => $giftRequest->couple_id,
            'created_by_user_id' => $giftRequest->created_by_user_id,
            'occasion' => $giftRequest->occasion,
            'budget_min' => $giftRequest->budget_min,
            'budget_max' => $giftRequest->budget_max,
            'time_constraint' => $giftRequest->time_constraint,
            'notes' => $giftRequest->notes,
            'meta' => $giftRequest->meta ?? [],
            'created_at' => $giftRequest->created_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function suggestionPayload(GiftSuggestion $suggestion): array
    {
        return [
            'id' => $suggestion->id,
            'gift_request_id' => $suggestion->gift_request_id,
            'title' => $suggestion->title,
            'category' => $suggestion->category,
            'price_band' => $suggestion->price_band,
            'rationale' => $suggestion->rationale,
            'personalization_tip' => $suggestion->personalization_tip,
            'is_favorite' => $suggestion->is_favorite,
            'meta' => $suggestion->meta ?? [],
            'created_at' => $suggestion->created_at?->toIso8601String(),
        ];
    }
}
