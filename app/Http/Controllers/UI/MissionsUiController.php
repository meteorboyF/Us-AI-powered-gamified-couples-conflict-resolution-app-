<?php

namespace App\Http\Controllers\UI;

use App\Http\Controllers\Controller;
use App\Models\Couple;
use App\Models\CoupleMission;
use App\Models\DailyCheckin;
use App\Models\MissionCompletion;
use App\Support\CoupleContext;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MissionsUiController extends Controller
{
    public function page(Request $request, CoupleContext $context): View
    {
        $user = $request->user();
        $today = Carbon::today()->toDateString();

        if ($user->current_couple_id && ! $user->couples()->whereKey($user->current_couple_id)->exists()) {
            return view('missions.page', [
                'errorType' => 'forbidden',
                'errorMessage' => 'Not authorized for this couple.',
                'missions' => collect(),
                'ownCheckin' => null,
                'partnerCheckin' => null,
            ]);
        }

        $couple = $context->resolve();

        if (! $couple) {
            return view('missions.page', [
                'errorType' => 'no_couple',
                'errorMessage' => 'No couple selected.',
                'missions' => collect(),
                'ownCheckin' => null,
                'partnerCheckin' => null,
            ]);
        }

        $missions = CoupleMission::query()
            ->where('couple_id', $couple->id)
            ->with('missionTemplate:id,key,title,cadence')
            ->orderByDesc('id')
            ->get()
            ->map(function (CoupleMission $mission) use ($today) {
                return [
                    'id' => $mission->id,
                    'title' => $mission->missionTemplate->title,
                    'key' => $mission->missionTemplate->key,
                    'cadence' => $mission->missionTemplate->cadence,
                    'today_completed' => $mission->completions()->whereDate('completed_on', $today)->exists(),
                ];
            });

        $own = DailyCheckin::query()
            ->where('couple_id', $couple->id)
            ->where('user_id', $user->id)
            ->whereDate('checkin_date', $today)
            ->first();

        $partner = DailyCheckin::query()
            ->where('couple_id', $couple->id)
            ->where('user_id', '!=', $user->id)
            ->whereDate('checkin_date', $today)
            ->with('user:id,name')
            ->first();

        return view('missions.page', [
            'errorType' => null,
            'errorMessage' => null,
            'missions' => $missions,
            'ownCheckin' => $own,
            'partnerCheckin' => $partner,
        ]);
    }

    public function complete(Request $request, CoupleContext $context): RedirectResponse
    {
        $validated = $request->validate([
            'couple_mission_id' => ['required', 'integer', 'exists:couple_missions,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $couple = $this->resolveCurrentCouple($request, $context);
        if (! $couple) {
            return redirect()->route('missions.ui')->withErrors([
                'missions' => 'No couple selected.',
            ]);
        }

        $mission = CoupleMission::query()
            ->whereKey($validated['couple_mission_id'])
            ->where('couple_id', $couple->id)
            ->first();

        if (! $mission) {
            return redirect()->route('missions.ui')->withErrors([
                'missions' => 'Mission is not available for your current couple.',
            ]);
        }

        $this->authorize('update', $mission);

        $today = Carbon::today()->toDateString();

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

        return redirect()->route('missions.ui')->with('status', 'Mission marked as completed for today.');
    }

    public function checkin(Request $request, CoupleContext $context): RedirectResponse
    {
        $validated = $request->validate([
            'mood' => ['required', 'string', 'in:great,good,okay,low,bad'],
            'note' => ['nullable', 'string'],
        ]);

        $couple = $this->resolveCurrentCouple($request, $context);
        if (! $couple) {
            return redirect()->route('missions.ui')->withErrors([
                'checkin' => 'No couple selected.',
            ]);
        }

        $user = $request->user();
        $today = Carbon::today()->toDateString();

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
            DailyCheckin::query()->create([
                'couple_id' => $couple->id,
                'user_id' => $user->id,
                'checkin_date' => $today,
                'mood' => $validated['mood'],
                'note' => $validated['note'] ?? null,
            ]);
        }

        return redirect()->route('missions.ui')->with('status', 'Daily check-in saved.');
    }

    private function resolveCurrentCouple(Request $request, CoupleContext $context): ?Couple
    {
        $user = $request->user();

        if ($user->current_couple_id && ! $user->couples()->whereKey($user->current_couple_id)->exists()) {
            return null;
        }

        return $context->resolve();
    }
}
