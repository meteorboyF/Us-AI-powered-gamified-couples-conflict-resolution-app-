<?php

namespace App\Http\Controllers;

use App\Models\Couple;
use App\Models\CoupleMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CoupleController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        $user = $request->user();

        $couple = DB::transaction(function () use ($user, $validated) {
            $couple = Couple::query()->create([
                'name' => $validated['name'] ?? null,
                'invite_code' => $this->generateInviteCode(),
                'created_by_user_id' => $user->id,
            ]);

            CoupleMember::query()->create([
                'couple_id' => $couple->id,
                'user_id' => $user->id,
                'role' => 'owner',
                'joined_at' => now(),
            ]);

            $user->forceFill(['current_couple_id' => $couple->id])->save();

            return $couple;
        });

        return response()->json([
            'couple_id' => $couple->id,
            'invite_code' => $couple->invite_code,
        ], 201);
    }

    public function join(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'invite_code' => ['required', 'string'],
        ]);

        $couple = Couple::query()
            ->where('invite_code', $validated['invite_code'])
            ->firstOrFail();

        $user = $request->user();

        DB::transaction(function () use ($user, $couple) {
            CoupleMember::query()->firstOrCreate(
                [
                    'couple_id' => $couple->id,
                    'user_id' => $user->id,
                ],
                [
                    'role' => 'member',
                    'joined_at' => now(),
                ]
            );

            $user->forceFill(['current_couple_id' => $couple->id])->save();
        });

        return response()->json([
            'couple_id' => $couple->id,
            'joined' => true,
        ]);
    }

    public function switch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'couple_id' => ['required', 'integer', 'exists:couples,id'],
        ]);

        $user = $request->user();
        $coupleId = (int) $validated['couple_id'];

        $couple = Couple::query()->findOrFail($coupleId);
        $this->authorize('view', $couple);

        $user->forceFill(['current_couple_id' => $coupleId])->save();

        return response()->json([
            'couple_id' => $coupleId,
            'switched' => true,
        ]);
    }

    private function generateInviteCode(): string
    {
        do {
            $code = Str::upper(Str::random(8));
        } while (Couple::query()->where('invite_code', $code)->exists());

        return $code;
    }
}
