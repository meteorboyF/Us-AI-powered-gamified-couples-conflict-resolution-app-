<?php

namespace App\Http\Controllers;

use App\Models\Couple;
use App\Models\CoupleWorldItem;
use App\Models\CoupleWorldState;
use App\Models\WorldItem;
use App\Support\CoupleContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\HttpException;

class WorldController extends Controller
{
    public function page(Request $request, CoupleContext $context): View
    {
        try {
            $payload = $this->buildWorldPayload($request, $context);

            return view('world.page', [
                'world' => $payload,
                'statusCode' => null,
                'statusMessage' => null,
            ]);
        } catch (HttpException $exception) {
            return view('world.page', [
                'world' => null,
                'statusCode' => $exception->getStatusCode(),
                'statusMessage' => $exception->getMessage(),
            ]);
        }
    }

    public function index(Request $request, CoupleContext $context): JsonResponse
    {
        return response()->json($this->buildWorldPayload($request, $context));
    }

    public function updateVibe(Request $request, CoupleContext $context): JsonResponse
    {
        $validated = $request->validate([
            'vibe' => ['required', 'string', 'in:neutral,warm,playful,tense,repair'],
        ]);

        $couple = $this->resolveCouple($request, $context);
        $state = $this->resolveState($couple);

        $this->authorize('update', $state);

        $state->forceFill(['vibe' => $validated['vibe']])->save();

        return response()->json([
            'vibe' => $state->vibe,
            'updated' => true,
        ]);
    }

    public function unlock(Request $request, CoupleContext $context): JsonResponse
    {
        $validated = $request->validate([
            'key' => ['required', 'string'],
        ]);

        $couple = $this->resolveCouple($request, $context);
        $state = $this->resolveState($couple);

        $this->authorize('update', $state);

        $item = WorldItem::query()
            ->where('key', $validated['key'])
            ->where('is_active', true)
            ->firstOrFail();

        CoupleWorldItem::query()->updateOrCreate(
            [
                'couple_id' => $couple->id,
                'world_item_id' => $item->id,
            ],
            [
                'unlocked_at' => now(),
            ]
        );

        return response()->json([
            'key' => $item->key,
            'unlocked' => true,
        ]);
    }

    private function resolveCouple(Request $request, CoupleContext $context): Couple
    {
        $user = $request->user();

        if ($user->current_couple_id && ! $user->couples()->whereKey($user->current_couple_id)->exists()) {
            abort(403, 'Current couple is not accessible.');
        }

        $couple = $context->resolve();

        if (! $couple) {
            abort(409, 'No active couple selected.');
        }

        return $couple;
    }

    private function resolveState(Couple $couple): CoupleWorldState
    {
        return CoupleWorldState::query()->firstOrCreate(
            ['couple_id' => $couple->id],
            [
                'vibe' => 'neutral',
                'level' => 1,
                'xp' => 0,
            ]
        );
    }

    /**
     * @return array{vibe:string,level:int,xp:int,items:\Illuminate\Support\Collection<int,array{key:string,title:string,description:?string,unlocked:bool}>}
     */
    private function buildWorldPayload(Request $request, CoupleContext $context): array
    {
        $couple = $this->resolveCouple($request, $context);
        $state = $this->resolveState($couple);

        $this->authorize('view', $state);

        $unlockedItemIds = $couple->worldItems()->pluck('world_items.id')->all();

        $items = WorldItem::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function (WorldItem $item) use ($unlockedItemIds) {
                return [
                    'key' => $item->key,
                    'title' => $item->title,
                    'description' => $item->description,
                    'unlocked' => in_array($item->id, $unlockedItemIds, true),
                ];
            })
            ->values();

        return [
            'vibe' => $state->vibe,
            'level' => $state->level,
            'xp' => $state->xp,
            'items' => $items,
        ];
    }
}
