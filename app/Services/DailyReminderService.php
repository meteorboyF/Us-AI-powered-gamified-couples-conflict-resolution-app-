<?php

namespace App\Services;

use App\Models\Couple;
use App\Models\CoupleDate;
use App\Models\MissionAssignment;
use App\Models\MissionCompletion;
use App\Models\MoodCheckin;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class DailyReminderService
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public function sendForDate(CarbonInterface $date): int
    {
        $count = 0;

        $couples = Couple::query()
            ->where('status', 'active')
            ->with([
                'users' => fn ($query) => $query->where('couple_user.is_active', true),
                'coupleDates' => fn ($query) => $query->where('is_anniversary', true),
            ])
            ->get();

        foreach ($couples as $couple) {
            $pendingAssignments = MissionAssignment::query()
                ->where('couple_id', $couple->id)
                ->whereDate('assigned_for_date', $date->toDateString())
                ->where('status', 'pending')
                ->pluck('id');

            $anniversaryTitles = $this->anniversaryTitlesForDate($couple->coupleDates, $date);

            foreach ($couple->users as $user) {
                if ($user->reminder_daily_checkin_enabled && ! $this->hasCheckin($couple->id, $user->id, $date)) {
                    $created = $this->notificationService->createReminderIfNotExists(
                        $user,
                        $couple,
                        'daily_checkin_reminder',
                        'Daily check-in reminder',
                        'Take one minute to share how you feel today.',
                        ['date' => $date->toDateString()],
                        $date
                    );
                    if ($created) {
                        $count++;
                    }
                }

                if (
                    $user->reminder_mission_enabled &&
                    $pendingAssignments->isNotEmpty() &&
                    ! $this->hasCompletedAnyAssignment($user->id, $pendingAssignments)
                ) {
                    $created = $this->notificationService->createReminderIfNotExists(
                        $user,
                        $couple,
                        'mission_reminder',
                        'Mission reminder',
                        'You have a mission waiting today. Complete one small step together.',
                        ['date' => $date->toDateString()],
                        $date
                    );
                    if ($created) {
                        $count++;
                    }
                }

                if ($user->reminder_anniversary_enabled && ! empty($anniversaryTitles)) {
                    $created = $this->notificationService->createReminderIfNotExists(
                        $user,
                        $couple,
                        'anniversary_reminder',
                        'Special date reminder',
                        'Today is '.$anniversaryTitles[0].'. Consider a small celebration together.',
                        ['date' => $date->toDateString(), 'titles' => $anniversaryTitles],
                        $date
                    );
                    if ($created) {
                        $count++;
                    }
                }
            }
        }

        return $count;
    }

    protected function hasCheckin(int $coupleId, int $userId, CarbonInterface $date): bool
    {
        return MoodCheckin::query()
            ->where('couple_id', $coupleId)
            ->where('user_id', $userId)
            ->whereDate('date', $date->toDateString())
            ->exists();
    }

    protected function hasCompletedAnyAssignment(int $userId, Collection $assignmentIds): bool
    {
        return MissionCompletion::query()
            ->where('user_id', $userId)
            ->whereIn('mission_assignment_id', $assignmentIds->all())
            ->exists();
    }

    protected function anniversaryTitlesForDate(Collection $coupleDates, CarbonInterface $date): array
    {
        return $coupleDates
            ->filter(function (CoupleDate $coupleDate) use ($date) {
                return $coupleDate->event_date->format('m-d') === $date->format('m-d');
            })
            ->pluck('title')
            ->values()
            ->all();
    }
}
