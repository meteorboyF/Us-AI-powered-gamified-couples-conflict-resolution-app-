<?php

namespace App\Http\Controllers\UI;

use App\Http\Controllers\Controller;
use App\Models\CoupleMission;
use App\Models\DailyCheckin;
use App\Support\CoupleContext;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MissionsUiController extends Controller
{
    public function page(Request $request, CoupleContext $context): Response
    {
        $user = $request->user();
        $today = Carbon::today()->toDateString();

        if ($user->current_couple_id && ! $user->couples()->whereKey($user->current_couple_id)->exists()) {
            return response()->view(
                'missions.page',
                $this->errorViewData('Not authorized for this couple.'),
                Response::HTTP_FORBIDDEN
            );
        }

        $couple = $context->resolve();
        if (! $couple) {
            return response()->view(
                'missions.page',
                $this->errorViewData('No couple selected.'),
                Response::HTTP_CONFLICT
            );
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

        return response()->view('missions.page', [
            'errorCode' => null,
            'errorMessage' => null,
            'missions' => $missions,
            'ownCheckin' => $own,
            'partnerCheckin' => $partner,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function errorViewData(string $message): array
    {
        return [
            'errorCode' => true,
            'errorMessage' => $message,
            'missions' => collect(),
            'ownCheckin' => null,
            'partnerCheckin' => null,
        ];
    }
}
