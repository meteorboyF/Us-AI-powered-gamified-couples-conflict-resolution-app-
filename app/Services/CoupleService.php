<?php

namespace App\Services;

use App\Models\Couple;
use App\Models\User;
use App\Models\World;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CoupleService
{
    /**
     * Create a new couple with an invite code
     */
    public function createCouple(User $user, array $preferences = []): Couple
    {
        if ($user->couples()->where('status', 'active')->where('couple_user.is_active', true)->exists()) {
            throw new \Exception('You are already in an active couple.');
        }

        return DB::transaction(function () use ($user, $preferences) {
            $couple = Couple::create([
                'invite_code' => $this->generateUniqueInviteCode(),
                'created_by' => $user->id,
                'status' => 'active',
            ]);

            // Attach the creator to the couple
            $couple->users()->attach($user->id, [
                'role' => 'partner',
                'is_active' => true,
                'joined_at' => now(),
            ]);

            // Create the couple's world
            $this->createWorld($couple, $preferences);

            return $couple->fresh();
        });
    }

    /**
     * Join a couple using an invite code
     */
    public function joinCouple(User $user, string $inviteCode): Couple
    {
        $couple = Couple::where('invite_code', strtoupper($inviteCode))
            ->where('status', 'active')
            ->firstOrFail();

        // Check if couple already has 2 members
        if ($couple->users()->count() >= 2) {
            throw new \Exception('This couple already has 2 members.');
        }

        // Check if user is already in a couple
        if ($user->couples()->where('status', 'active')->exists()) {
            throw new \Exception('You are already in an active couple.');
        }

        // Attach the user to the couple
        $couple->users()->attach($user->id, [
            'role' => 'partner',
            'is_active' => true,
            'joined_at' => now(),
        ]);

        return $couple->fresh();
    }

    /**
     * Generate a unique invite code
     */
    protected function generateUniqueInviteCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (Couple::where('invite_code', $code)->exists());

        return $code;
    }

    /**
     * Create a world for the couple
     */
    protected function createWorld(Couple $couple, array $preferences = []): World
    {
        $worldType = $preferences['theme'] ?? 'garden';

        $world = World::create([
            'couple_id' => $couple->id,
            'theme_type' => $worldType,
            'world_type' => $worldType,
            'level' => 1,
            'xp_total' => 0,
            'ambience_state' => 'bright',
            'cosmetics' => [],
        ]);

        app(WorldBuildingService::class)->initializeStarterState($couple, $world);

        return $world;
    }

    /**
     * Get the user's active couple
     */
    public function getUserCouple(User $user): ?Couple
    {
        return $user->couples()
            ->where('status', 'active')
            ->where('couple_user.is_active', true)
            ->first();
    }

    /**
     * Unlink a couple (soft delete)
     */
    public function unlinkCouple(Couple $couple): void
    {
        $couple->update(['status' => 'unlinked']);

        // Deactivate memberships in the couple pivot.
        $couple->users()
            ->newPivotStatement()
            ->where('couple_id', $couple->id)
            ->update([
                'is_active' => false,
                'updated_at' => now(),
            ]);
    }
}
