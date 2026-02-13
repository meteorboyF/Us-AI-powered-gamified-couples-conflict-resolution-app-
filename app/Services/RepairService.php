<?php

namespace App\Services;

use Illuminate\Auth\Access\AuthorizationException;
use App\Models\Couple;
use App\Models\RepairAgreement;
use App\Models\RepairSession;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RepairService
{
    public function __construct(
        protected XpService $xpService,
        protected NotificationService $notificationService
    ) {
    }

    protected const SHARED_GOALS = [
        'communicate' => 'Communicate more openly',
        'listen' => 'Listen without interrupting',
        'appreciate' => 'Show more appreciation',
        'quality_time' => 'Spend quality time together',
        'support' => "Support each other's needs",
        'patience' => 'Be more patient',
    ];

    /**
     * Initiate a new repair session
     */
    public function initiateRepair(Couple $couple, User $user, string $topic): RepairSession
    {
        $this->assertCoupleMember($couple, $user);

        // Check for active sessions
        $activeSession = RepairSession::forCouple($couple)
            ->active()
            ->first();

        if ($activeSession) {
            throw new \Exception('There is already an active repair session. Please complete or abandon it first.');
        }

        return DB::transaction(function () use ($couple, $user, $topic) {
            $session = RepairSession::create([
                'couple_id' => $couple->id,
                'initiated_by' => $user->id,
                'status' => 'pending',
                'conflict_topic' => $topic,
            ]);

            // Notify partner
            $partner = $couple->users()->where('users.id', '!=', $user->id)->first();
            if ($partner) {
                $this->notificationService->createNotification(
                    $partner,
                    $couple,
                    'repair_initiated',
                    '🛠️ Repair Session Started',
                    "{$user->name} started a repair session about: {$topic}",
                    ['session_id' => $session->id, 'topic' => $topic]
                );
            }

            return $session;
        });
    }

    /**
     * Partner joins the repair session
     */
    public function joinRepair(RepairSession $session, User $user): RepairSession
    {
        $this->assertCoupleMember($session->couple, $user);

        if (!$session->canBeJoined($user)) {
            throw new \Exception('You cannot join this repair session.');
        }

        $session->start();
        return $session;
    }

    /**
     * Update user's perspective
     */
    public function updatePerspective(RepairSession $session, User $user, string $perspective): RepairSession
    {
        $this->assertCoupleMember($session->couple, $user);

        $field = $session->initiated_by === $user->id ? 'initiator_perspective' : 'partner_perspective';

        $session->update([$field => trim($perspective)]);

        return $session->fresh();
    }

    /**
     * Select shared goals
     */
    public function selectSharedGoals(RepairSession $session, User $user, array $goals): RepairSession
    {
        $this->assertCoupleMember($session->couple, $user);

        // Validate goals
        foreach ($goals as $goal) {
            if (!isset(self::SHARED_GOALS[$goal])) {
                throw new \InvalidArgumentException("Invalid goal: {$goal}");
            }
        }

        if (count($goals) < 3 || count($goals) > 5) {
            throw new \Exception('Please select 3-5 shared goals.');
        }

        $session->update(['shared_goals' => $goals]);

        return $session->fresh();
    }

    /**
     * Create a new agreement
     */
    public function createAgreement(RepairSession $session, User $user, string $text): RepairAgreement
    {
        $this->assertCoupleMember($session->couple, $user);

        return RepairAgreement::create([
            'repair_session_id' => $session->id,
            'couple_id' => $session->couple_id,
            'agreement_text' => trim($text),
            'created_by' => $user->id,
        ]);
    }

    /**
     * Partner acknowledges an agreement
     */
    public function acknowledgeAgreement(RepairAgreement $agreement, User $user): RepairAgreement
    {
        $this->assertCoupleMember($agreement->couple, $user);

        // Verify user is the partner (not the creator)
        if ($agreement->created_by === $user->id) {
            throw new \Exception('You cannot acknowledge your own agreement.');
        }

        if ($agreement->partner_acknowledged) {
            return $agreement->fresh();
        }

        return DB::transaction(function () use ($agreement, $user) {
            $agreement->acknowledge();

            // Award XP for acknowledgment
            $this->xpService->awardXp(
                $agreement->couple,
                'repair',
                $user,
                10,
                ['reason' => 'agreement_acknowledged', 'agreement_id' => $agreement->id]
            );

            return $agreement;
        });
    }

    /**
     * Complete the repair session
     */
    public function completeRepair(RepairSession $session, User $user): RepairSession
    {
        $this->assertCoupleMember($session->couple, $user);

        if ($session->status === 'completed') {
            throw new \Exception('This repair session is already completed.');
        }

        if (blank($session->initiator_perspective) || blank($session->partner_perspective)) {
            throw new \Exception('Both partners must share their perspective before completing.');
        }

        $goals = $session->shared_goals ?? [];
        if (count($goals) < 3) {
            throw new \Exception('Please select shared goals before completing the repair.');
        }

        // Verify both partners have at least one agreement
        $initiatorAgreements = $session->agreements()
            ->where('created_by', $session->initiated_by)
            ->count();

        $partner = $session->getPartner();
        if (!$partner) {
            throw new \Exception('A partner must join before completing the repair.');
        }
        $partnerAgreements = $session->agreements()
            ->where('created_by', $partner->id)
            ->count();

        if ($initiatorAgreements === 0 || $partnerAgreements === 0) {
            throw new \Exception('Both partners must create at least one agreement to complete the repair.');
        }

        // Verify all agreements are acknowledged
        $unacknowledged = $session->agreements()->unacknowledged()->count();
        if ($unacknowledged > 0) {
            throw new \Exception('All agreements must be acknowledged by your partner before completing.');
        }

        return DB::transaction(function () use ($session) {
            $session->complete();

            // Award completion XP (big reward!)
            $this->xpService->awardXp(
                $session->couple,
                'repair',
                null, // Both partners contributed
                50,
                ['reason' => 'repair_completed', 'session_id' => $session->id]
            );

            // Notify both partners
            foreach ($session->couple->users as $user) {
                $this->notificationService->createNotification(
                    $user,
                    $session->couple,
                    'repair_completed',
                    '✨ Repair Complete!',
                    "You successfully completed a repair session together! +50 XP",
                    ['session_id' => $session->id, 'xp_reward' => 50]
                );
            }

            // Check if this is their first repair - unlock repair missions
            $previousRepairs = RepairSession::forCouple($session->couple)
                ->completed()
                ->where('id', '!=', $session->id)
                ->count();

            if ($previousRepairs === 0) {
                // Award bonus XP for first repair
                $this->xpService->awardXp(
                    $session->couple,
                    'repair',
                    null,
                    20,
                    ['reason' => 'first_repair_bonus']
                );
            }

            return $session->fresh();
        });
    }

    /**
     * Abandon the repair session
     */
    public function abandonRepair(RepairSession $session, User $user): RepairSession
    {
        $this->assertCoupleMember($session->couple, $user);

        $session->abandon();

        // Notify partner
        $partner = $session->couple->users()
            ->where('users.id', '!=', $user->id)
            ->first();

        if ($partner) {
            $this->notificationService->createNotification(
                $partner,
                $session->couple,
                'repair_abandoned',
                '🛠️ Repair Session Ended',
                "{$user->name} ended the repair session. You can start a new one anytime.",
                ['session_id' => $session->id]
            );
        }

        return $session;
    }

    /**
     * Get shared goals options
     */
    public static function getSharedGoals(): array
    {
        return self::SHARED_GOALS;
    }

    /**
     * Get active session for couple
     */
    public function getActiveSession(Couple $couple): ?RepairSession
    {
        return RepairSession::forCouple($couple)
            ->active()
            ->first();
    }

    /**
     * Get completed sessions for couple
     */
    public function getCompletedSessions(Couple $couple, int $limit = 10)
    {
        return RepairSession::forCouple($couple)
            ->completed()
            ->with(['agreements', 'initiator'])
            ->orderBy('completed_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Check if couple has completed at least one repair
     */
    public function hasCompletedRepair(Couple $couple): bool
    {
        return RepairSession::forCouple($couple)
            ->completed()
            ->exists();
    }

    protected function assertCoupleMember(Couple $couple, User $user): void
    {
        if (!$couple->isActive()) {
            throw new AuthorizationException('Unauthorized couple access.');
        }

        $isMember = $couple->users()
            ->where('users.id', $user->id)
            ->where('couple_user.is_active', true)
            ->exists();

        if (!$isMember) {
            throw new AuthorizationException('Unauthorized couple access.');
        }
    }
}
