<?php

namespace App\Services;

use App\Models\Couple;
use App\Models\Notification;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class NotificationService
{
    public function __construct(
        protected XpService $xpService
    ) {}

    /**
     * Create a notification for a user
     */
    public function createNotification(
        User $user,
        Couple $couple,
        string $type,
        string $title,
        string $message,
        array $data = []
    ): Notification {
        return Notification::create([
            'user_id' => $user->id,
            'couple_id' => $couple->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);
    }

    public function createReminderIfNotExists(
        User $user,
        Couple $couple,
        string $type,
        string $title,
        string $message,
        array $data = [],
        ?CarbonInterface $date = null
    ): ?Notification {
        $date = $date ?? now();

        $alreadyExists = Notification::query()
            ->where('user_id', $user->id)
            ->where('couple_id', $couple->id)
            ->where('type', $type)
            ->whereDate('created_at', $date->toDateString())
            ->exists();

        if ($alreadyExists) {
            return null;
        }

        return $this->createNotification($user, $couple, $type, $title, $message, $data);
    }

    /**
     * Notify the partner (other user in couple)
     */
    public function notifyPartner(
        Couple $couple,
        User $sender,
        string $type,
        string $title,
        string $message,
        array $data = []
    ): ?Notification {
        $partner = $couple->users()->where('users.id', '!=', $sender->id)->first();

        if (! $partner) {
            return null;
        }

        return $this->createNotification($partner, $couple, $type, $title, $message, $data);
    }

    /**
     * Get unread notifications for a user
     */
    public function getUnreadNotifications(User $user, int $limit = 10): Collection
    {
        return Notification::forUser($user)
            ->unread()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get unread count for a user
     */
    public function getUnreadCount(User $user): int
    {
        return Notification::forUser($user)->unread()->count();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Notification $notification): void
    {
        $notification->markAsRead();

        // Award XP for reading mood alerts (encourages support)
        if ($notification->type === 'mood_alert' && ! $notification->wasRecentlyCreated) {
            $this->xpService->awardXp(
                $notification->couple,
                'checkin',
                $notification->user,
                2,
                ['reason' => 'read_mood_alert']
            );
        }
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead(User $user): int
    {
        return Notification::forUser($user)
            ->unread()
            ->update(['read_at' => now()]);
    }

    /**
     * Create mood alert notification
     */
    public function createMoodAlert(Couple $couple, User $user, int $moodLevel, array $needs = []): void
    {
        if ($moodLevel > 2) {
            return; // Only alert for low moods (1-2)
        }

        $moodEmoji = $moodLevel === 1 ? '😢' : '😕';
        $needsText = ! empty($needs) ? ' They need: '.implode(', ', $needs) : '';

        $this->notifyPartner(
            $couple,
            $user,
            'mood_alert',
            "{$moodEmoji} Partner needs support",
            "{$user->name} is feeling down today.{$needsText}",
            ['mood_level' => $moodLevel, 'needs' => $needs]
        );
    }

    /**
     * Create mission complete notification
     */
    public function createMissionCompleteNotification(Couple $couple, User $user, string $missionTitle, int $xpReward): void
    {
        $this->notifyPartner(
            $couple,
            $user,
            'mission_complete',
            '🎯 Mission Completed!',
            "{$user->name} completed: {$missionTitle} (+{$xpReward} XP)",
            ['mission_title' => $missionTitle, 'xp_reward' => $xpReward]
        );
    }

    /**
     * Create level up notification
     */
    public function createLevelUpNotification(Couple $couple, int $newLevel): void
    {
        foreach ($couple->users as $user) {
            $this->createNotification(
                $user,
                $couple,
                'level_up',
                '🎉 Level Up!',
                "Your world reached Level {$newLevel}! Keep growing together.",
                ['level' => $newLevel]
            );
        }
    }
}
