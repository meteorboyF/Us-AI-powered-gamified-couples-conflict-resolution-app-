<?php

namespace App\Services;

use App\Models\Couple;
use App\Models\Mission;
use App\Models\MissionAssignment;
use App\Models\MissionCompletion;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MissionService
{
    public function __construct(
        protected XpService $xpService
    ) {
    }

    /**
     * Assign daily missions to a couple
     */
    public function assignDailyMissions(Couple $couple, int $count = 3): Collection
    {
        $today = today();

        // Check if missions already assigned for today
        $existing = MissionAssignment::where('couple_id', $couple->id)
            ->whereDate('assigned_for_date', $today)
            ->count();

        if ($existing >= $count) {
            return $this->getMissionsForCouple($couple, $today);
        }

        // Get random daily missions
        $missions = Mission::active()
            ->ofType('daily')
            ->inRandomOrder()
            ->limit($count - $existing)
            ->get();

        // Assign them
        foreach ($missions as $mission) {
            MissionAssignment::firstOrCreate([
                'couple_id' => $couple->id,
                'mission_id' => $mission->id,
                'assigned_for_date' => $today,
            ]);
        }

        return $this->getMissionsForCouple($couple, $today);
    }

    /**
     * Complete a mission
     */
    public function completeMission(
        MissionAssignment $assignment,
        User $user,
        ?string $notes = null
    ): MissionCompletion {
        // Verify user is in the couple
        if (!$assignment->couple->users()->where('users.id', $user->id)->exists()) {
            throw new \Exception('User is not part of this couple.');
        }

        // Check if already completed by this user
        $existing = $assignment->completions()
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($assignment, $user, $notes) {
            // Create completion record
            $completion = MissionCompletion::create([
                'mission_assignment_id' => $assignment->id,
                'user_id' => $user->id,
                'completed_at' => now(),
                'notes' => $notes,
            ]);

            // Mark assignment as completed
            $assignment->markCompleted();

            // Award XP
            $this->xpService->awardXp(
                $assignment->couple,
                'mission',
                $user,
                $assignment->mission->xp_reward,
                [
                    'mission_id' => $assignment->mission->id,
                    'mission_title' => $assignment->mission->title,
                ]
            );

            return $completion;
        });
    }

    /**
     * Get missions for a couple on a specific date
     */
    public function getMissionsForCouple(Couple $couple, ?\DateTime $date = null): Collection
    {
        $date = $date ?? today();

        return MissionAssignment::with(['mission', 'completions.user'])
            ->where('couple_id', $couple->id)
            ->whereDate('assigned_for_date', $date)
            ->get();
    }

    /**
     * Get active missions for today
     */
    public function getTodayMissions(Couple $couple): Collection
    {
        return $this->getMissionsForCouple($couple, today())
            ->filter(fn($assignment) => $assignment->status === 'pending');
    }

    /**
     * Acknowledge partner's mission completion
     */
    public function acknowledgeMission(MissionCompletion $completion, User $partner): void
    {
        // Verify partner is in the couple
        $couple = $completion->assignment->couple;
        if (!$couple->users()->where('users.id', $partner->id)->exists()) {
            throw new \Exception('User is not part of this couple.');
        }

        // Verify partner is not the one who completed it
        if ($completion->user_id === $partner->id) {
            throw new \Exception('Cannot acknowledge your own completion.');
        }

        $completion->acknowledge();
    }

    /**
     * Expire old pending missions
     */
    public function expireOldMissions(): int
    {
        return MissionAssignment::where('status', 'pending')
            ->where('assigned_for_date', '<', today())
            ->update(['status' => 'expired']);
    }
}
