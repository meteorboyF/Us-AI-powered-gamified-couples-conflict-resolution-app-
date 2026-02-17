<?php

namespace App\Http\Controllers;

use App\Models\Couple;
use App\Models\CoupleMember;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CoupleController extends Controller
{
    public function manage(Request $request): View
    {
        $user = $request->user();

        return view('couples.manage', [
            'couples' => $user->couples()->get(['couples.id', 'couples.name']),
            'currentCoupleId' => $user->current_couple_id,
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
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

        if ($request->expectsJson()) {
            return response()->json([
                'couple_id' => $couple->id,
                'invite_code' => $couple->invite_code,
            ], 201);
        }

        return redirect('/dashboard')->with('status', 'Couple created successfully.');
    }

    public function join(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'invite_code' => ['required', 'string', 'max:32'],
        ]);

        $couple = Couple::query()
            ->where('invite_code', Str::upper(trim($validated['invite_code'])))
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

        if ($request->expectsJson()) {
            return response()->json([
                'couple_id' => $couple->id,
                'joined' => true,
            ]);
        }

        return redirect('/dashboard')->with('status', 'Joined couple successfully.');
    }

    public function switch(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'couple_id' => ['required', 'integer', 'exists:couples,id'],
        ]);

        $user = $request->user();
        $coupleId = (int) $validated['couple_id'];

        $couple = Couple::query()->findOrFail($coupleId);
        $this->authorize('view', $couple);

        $user->forceFill(['current_couple_id' => $coupleId])->save();

        if ($request->expectsJson()) {
            return response()->json([
                'couple_id' => $coupleId,
                'switched' => true,
            ]);
        }

        return redirect('/dashboard')->with('status', 'Current couple switched.');
    }

    private function generateInviteCode(): string
    {
        do {
            $code = Str::upper(Str::random(8));
        } while (Couple::query()->where('invite_code', $code)->exists());

        return $code;
    }
}
