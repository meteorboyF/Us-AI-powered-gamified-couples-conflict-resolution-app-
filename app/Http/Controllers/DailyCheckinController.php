<?php

namespace App\Http\Controllers;

use App\Models\Couple;
use App\Models\DailyCheckin;
use App\Support\CoupleContext;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DailyCheckinController extends Controller
{
    public function today(Request $request, CoupleContext $context): JsonResponse
    {
        $couple = $this->resolveCouple($request, $context);
        $today = Carbon::today()->toDateString();
        $user = $request->user();

        $own = DailyCheckin::query()
            ->where('couple_id', $couple->id)
            ->where('user_id', $user->id)
            ->whereDate('checkin_date', $today)
            ->first();

        $partner = DailyCheckin::query()
            ->where('couple_id', $couple->id)
            ->where('user_id', '!=', $user->id)
            ->whereDate('checkin_date', $today)
            ->first();

        if ($own) {
            $this->authorize('view', $own);
        }

        if ($partner) {
            $this->authorize('view', $partner);
        }

        return response()->json([
            'own' => $own ? [
                'mood' => $own->mood,
                'note' => $own->note,
                'checkin_date' => $own->checkin_date->toDateString(),
            ] : null,
            'partner' => $partner ? [
                'mood' => $partner->mood,
                'note' => $partner->note,
                'checkin_date' => $partner->checkin_date->toDateString(),
            ] : null,
        ]);
    }

    public function store(Request $request, CoupleContext $context): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'mood' => ['required', 'string', 'in:great,good,okay,low,bad'],
            'note' => ['nullable', 'string'],
        ]);

        $couple = $this->resolveCouple($request, $context);
        $today = Carbon::today()->toDateString();
        $user = $request->user();

        $this->authorize('create', [DailyCheckin::class, $couple->id, $user->id]);

        $checkin = DailyCheckin::query()
            ->where('couple_id', $couple->id)
            ->where('user_id', $user->id)
            ->whereDate('checkin_date', $today)
            ->first();

        if ($checkin) {
            $checkin->fill([
                'mood' => $validated['mood'],
                'note' => $validated['note'] ?? null,
            ])->save();
        } else {
            $checkin = DailyCheckin::query()->create([
                'couple_id' => $couple->id,
                'user_id' => $user->id,
                'checkin_date' => $today,
                'mood' => $validated['mood'],
                'note' => $validated['note'] ?? null,
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'mood' => $checkin->mood,
                'note' => $checkin->note,
                'saved' => true,
            ]);
        }

        return redirect()->route('missions.ui')->with('status', 'Daily check-in saved.');
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
