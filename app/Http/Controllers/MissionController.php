<?php

namespace App\Http\Controllers;

use App\Models\Couple;
use App\Models\CoupleMission;
use App\Models\MissionCompletion;
use App\Models\MissionTemplate;
use App\Support\CoupleContext;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MissionController extends Controller
{
    public function index(Request $request, CoupleContext $context): JsonResponse
    {
        $couple = $this->resolveCouple($request, $context);
        $today = Carbon::today()->toDateString();

        $templates = MissionTemplate::query()
            ->where('is_active', true)
            ->orderBy('title')
            ->get(['id', 'key', 'title', 'description', 'cadence']);

        $missions = CoupleMission::query()
            ->where('couple_id', $couple->id)
            ->with('missionTemplate:id,key,title,cadence')
            ->get()
            ->map(function (CoupleMission $mission) use ($today) {
                return [
                    'id' => $mission->id,
                    'status' => $mission->status,
                    'started_at' => $mission->started_at?->toDateString(),
                    'completed_at' => $mission->completed_at?->toDateString(),
                    'today_completed' => $mission->completions()->whereDate('completed_on', $today)->exists(),
                    'template' => [
                        'id' => $mission->missionTemplate->id,
                        'key' => $mission->missionTemplate->key,
                        'title' => $mission->missionTemplate->title,
                        'cadence' => $mission->missionTemplate->cadence,
                    ],
                ];
            })
            ->values();

        return response()->json([
            'templates' => $templates,
            'missions' => $missions,
        ]);
    }

    public function assign(Request $request, CoupleContext $context): JsonResponse
    {
        $validated = $request->validate([
            'key' => ['required', 'string'],
        ]);

        $couple = $this->resolveCouple($request, $context);
        $this->authorize('create', [CoupleMission::class, $couple->id]);

        $template = MissionTemplate::query()
            ->where('key', $validated['key'])
            ->where('is_active', true)
            ->firstOrFail();

        $mission = CoupleMission::query()->firstOrCreate(
            [
                'couple_id' => $couple->id,
                'mission_template_id' => $template->id,
            ],
            [
                'status' => 'active',
                'started_at' => Carbon::today(),
            ]
        );

        return response()->json([
            'mission_id' => $mission->id,
            'assigned' => true,
        ]);
    }

    public function complete(Request $request, CoupleContext $context): JsonResponse
    {
        $validated = $request->validate([
            'couple_mission_id' => ['required', 'integer', 'exists:couple_missions,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $couple = $this->resolveCouple($request, $context);
        $today = Carbon::today()->toDateString();

        $mission = CoupleMission::query()
            ->whereKey($validated['couple_mission_id'])
            ->where('couple_id', $couple->id)
            ->firstOrFail();

        $this->authorize('update', $mission);

        $completion = MissionCompletion::query()
            ->where('couple_mission_id', $mission->id)
            ->whereDate('completed_on', $today)
            ->first();

        if ($completion) {
            $completion->fill([
                'completed_by_user_id' => $request->user()->id,
                'notes' => $validated['notes'] ?? null,
            ])->save();
        } else {
            MissionCompletion::query()->create([
                'couple_mission_id' => $mission->id,
                'completed_on' => $today,
                'completed_by_user_id' => $request->user()->id,
                'notes' => $validated['notes'] ?? null,
            ]);
        }

        return response()->json([
            'mission_id' => $mission->id,
            'completed_on' => $today,
            'completed' => true,
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
}
