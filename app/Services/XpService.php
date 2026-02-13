<?php

namespace App\Services;

use App\Models\Couple;
use App\Models\CoupleWallet;
use App\Models\User;
use App\Models\XpEvent;
use Illuminate\Support\Facades\DB;

class XpService
{
    /**
     * Valid XP event types and their default rewards
     */
    protected const XP_REWARDS = [
        'checkin' => 10,
        'mission' => 20,
        'repair' => 50,
        'vault' => 15,
        'chat' => 5,
    ];

    protected const LOVE_SEED_REWARDS = [
        'checkin' => 3,
        'mission' => 5,
        'repair' => 12,
        'vault' => 4,
        'chat' => 1,
    ];

    /**
     * Award XP to a couple for a specific event
     */
    public function awardXp(
        Couple $couple,
        string $type,
        ?User $user = null,
        ?int $customAmount = null,
        array $metadata = []
    ): XpEvent {
        // Validate event type
        if (! isset(self::XP_REWARDS[$type])) {
            throw new \InvalidArgumentException("Invalid XP event type: {$type}");
        }

        $xpAmount = $customAmount ?? self::XP_REWARDS[$type];

        // Create XP event and update world in a transaction
        return DB::transaction(function () use ($couple, $type, $user, $xpAmount, $metadata) {
            // Create the XP event
            $event = XpEvent::create([
                'couple_id' => $couple->id,
                'user_id' => $user?->id,
                'type' => $type,
                'xp_amount' => $xpAmount,
                'metadata' => $metadata,
            ]);

            // Update the couple's world
            $world = $couple->world;
            if ($world) {
                $world->addXp($xpAmount);
            }

            $wallet = CoupleWallet::firstOrCreate(
                ['couple_id' => $couple->id],
                ['love_seeds_balance' => config('world.starting_love_seeds', 0)]
            );

            $wallet->increment('love_seeds_balance', self::LOVE_SEED_REWARDS[$type] ?? 0);

            return $event;
        });
    }

    /**
     * Get XP history for a couple
     */
    public function getXpHistory(Couple $couple, int $limit = 20)
    {
        return $couple->xpEvents()
            ->with('user')
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get XP earned today for a couple
     */
    public function getTodayXp(Couple $couple): int
    {
        return $couple->xpEvents()
            ->whereDate('created_at', today())
            ->sum('xp_amount');
    }

    /**
     * Get XP breakdown by type for a couple
     */
    public function getXpBreakdown(Couple $couple): array
    {
        return $couple->xpEvents()
            ->select('type', DB::raw('SUM(xp_amount) as total'))
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();
    }

    /**
     * Calculate XP needed for next level
     */
    public function xpForNextLevel(int $currentLevel): int
    {
        // 100 XP per level
        return $currentLevel * 100;
    }

    /**
     * Get valid XP event types
     */
    public static function getValidTypes(): array
    {
        return array_keys(self::XP_REWARDS);
    }

    /**
     * Get default XP reward for a type
     */
    public static function getDefaultReward(string $type): ?int
    {
        return self::XP_REWARDS[$type] ?? null;
    }
}
