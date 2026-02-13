<?php

namespace App\Services;

use App\Models\Couple;
use App\Models\Message;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ChatService
{
    public function __construct(
        protected XpService $xpService,
        protected NotificationService $notificationService
    ) {}

    protected const LOVE_BUTTONS = [
        'heart' => ['emoji' => '❤️', 'label' => 'Thinking of you', 'xp' => 5],
        'kiss' => ['emoji' => '💋', 'label' => 'Sending a kiss', 'xp' => 5],
        'hug' => ['emoji' => '🤗', 'label' => 'Virtual hug', 'xp' => 5],
        'smile' => ['emoji' => '😊', 'label' => 'You made me smile', 'xp' => 5],
        'support' => ['emoji' => '💪', 'label' => "I'm here for you", 'xp' => 5],
    ];

    protected const RATE_LIMIT_HOURS = 1;

    protected const MAX_LOVE_BUTTONS_PER_HOUR = 5;

    /**
     * Send a text message
     */
    public function sendMessage(Couple $couple, User $user, string $content): Message
    {
        $this->assertCoupleMember($couple, $user);

        return DB::transaction(function () use ($couple, $user, $content) {
            $message = Message::create([
                'couple_id' => $couple->id,
                'user_id' => $user->id,
                'content' => trim($content),
                'type' => 'text',
            ]);

            // Award XP for first message of the day
            $todayMessages = Message::where('couple_id', $couple->id)
                ->where('user_id', $user->id)
                ->where('type', 'text')
                ->whereDate('created_at', today())
                ->count();

            if ($todayMessages === 1) {
                $this->xpService->awardXp(
                    $couple,
                    'chat',
                    $user,
                    5,
                    ['reason' => 'first_message_of_day']
                );
            }

            return $message;
        });
    }

    /**
     * Send a love button
     */
    public function sendLoveButton(Couple $couple, User $user, string $buttonType): Message
    {
        $this->assertCoupleMember($couple, $user);

        if (! isset(self::LOVE_BUTTONS[$buttonType])) {
            throw new \InvalidArgumentException("Invalid love button type: {$buttonType}");
        }

        if (! $this->canSendLoveButton($user)) {
            throw new \Exception('Love button rate limit exceeded. You can send 5 per hour.');
        }

        return DB::transaction(function () use ($couple, $user, $buttonType) {
            $button = self::LOVE_BUTTONS[$buttonType];

            // Create message
            $message = Message::create([
                'couple_id' => $couple->id,
                'user_id' => $user->id,
                'content' => $button['label'],
                'type' => 'love_button',
                'metadata' => [
                    'button_type' => $buttonType,
                    'emoji' => $button['emoji'],
                ],
            ]);

            // Track for rate limiting
            DB::table('love_button_sends')->insert([
                'user_id' => $user->id,
                'couple_id' => $couple->id,
                'button_type' => $buttonType,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Award XP
            $this->xpService->awardXp(
                $couple,
                'chat',
                $user,
                $button['xp'],
                ['reason' => 'love_button', 'type' => $buttonType]
            );

            // Notify partner
            $partner = $couple->users()->where('users.id', '!=', $user->id)->first();
            if ($partner) {
                $this->notificationService->createNotification(
                    $partner,
                    $couple,
                    'love_button',
                    "{$button['emoji']} Love Button",
                    "{$user->name} sent you: {$button['label']}",
                    ['button_type' => $buttonType, 'emoji' => $button['emoji']]
                );
            }

            return $message;
        });
    }

    /**
     * Get messages for a couple
     */
    public function getMessages(Couple $couple, User $user, int $limit = 50): Collection
    {
        $this->assertCoupleMember($couple, $user);

        return Message::forCouple($couple)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();
    }

    /**
     * Mark messages as read for a user
     */
    public function markMessagesAsRead(Couple $couple, User $user): int
    {
        $this->assertCoupleMember($couple, $user);

        return Message::forCouple($couple)
            ->forUser($user)
            ->unread()
            ->update(['read_at' => now()]);
    }

    /**
     * Check if user can send a love button (rate limit)
     */
    public function canSendLoveButton(User $user): bool
    {
        $sentInLastHour = DB::table('love_button_sends')
            ->where('user_id', $user->id)
            ->where('created_at', '>=', now()->subHours(self::RATE_LIMIT_HOURS))
            ->count();

        return $sentInLastHour < self::MAX_LOVE_BUTTONS_PER_HOUR;
    }

    /**
     * Get remaining love buttons for user
     */
    public function getRemainingLoveButtons(User $user): int
    {
        $sentInLastHour = DB::table('love_button_sends')
            ->where('user_id', $user->id)
            ->where('created_at', '>=', now()->subHours(self::RATE_LIMIT_HOURS))
            ->count();

        return max(0, self::MAX_LOVE_BUTTONS_PER_HOUR - $sentInLastHour);
    }

    /**
     * Get next available time for love button
     */
    public function getNextLoveButtonAvailableAt(User $user): ?\Carbon\Carbon
    {
        if ($this->canSendLoveButton($user)) {
            return null;
        }

        $oldestSend = DB::table('love_button_sends')
            ->where('user_id', $user->id)
            ->where('created_at', '>=', now()->subHours(self::RATE_LIMIT_HOURS))
            ->orderBy('created_at', 'asc')
            ->first();

        return $oldestSend ? \Carbon\Carbon::parse($oldestSend->created_at)->addHours(self::RATE_LIMIT_HOURS) : null;
    }

    /**
     * Get love button types
     */
    public static function getLoveButtons(): array
    {
        return self::LOVE_BUTTONS;
    }

    protected function assertCoupleMember(Couple $couple, User $user): void
    {
        if (! $couple->isActive()) {
            throw new AuthorizationException('Unauthorized couple access.');
        }

        $isMember = $couple->users()
            ->where('users.id', $user->id)
            ->where('couple_user.is_active', true)
            ->exists();

        if (! $isMember) {
            throw new AuthorizationException('Unauthorized couple access.');
        }
    }
}
