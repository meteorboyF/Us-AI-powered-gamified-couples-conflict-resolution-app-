<?php

namespace App\Services;

use App\Models\Couple;
use App\Models\Memory;
use App\Models\MemoryReaction;
use App\Models\User;
use App\Models\VaultUnlock;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VaultService
{
    public const DUAL_UNLOCK_TTL_MINUTES = 30;

    public const MEDIA_DISK = 'public';

    public function __construct(
        protected XpService $xpService
    ) {}

    /**
     * Upload a photo
     */
    public function uploadPhoto(Couple $couple, User $user, UploadedFile $file, array $data): Memory
    {
        $this->assertCoupleMember($couple, $user);
        $this->validateFileType($file, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
        $this->validateFileSize($file, 5 * 1024); // 5MB in KB
        $isFirstPhoto = ! $this->hasUploadedType($couple, 'photo');

        return DB::transaction(function () use ($couple, $user, $file, $data, $isFirstPhoto) {
            $path = $this->storeFile($file, $couple->id, 'photo');

            $memory = Memory::create([
                'couple_id' => $couple->id,
                'created_by' => $user->id,
                'type' => 'photo',
                'title' => $data['title'] ?? null,
                'description' => $data['description'] ?? null,
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'visibility' => $data['visibility'] ?? 'shared',
                'comfort' => (bool) ($data['comfort'] ?? false),
                'metadata' => $this->getImageMetadata($file),
            ]);

            // Award XP for first photo
            if ($isFirstPhoto) {
                $this->xpService->awardXp(
                    $couple,
                    'vault',
                    $user,
                    5,
                    ['reason' => 'first_photo']
                );
            }

            return $memory;
        });
    }

    /**
     * Upload a video
     */
    public function uploadVideo(Couple $couple, User $user, UploadedFile $file, array $data): Memory
    {
        $this->assertCoupleMember($couple, $user);
        $this->validateFileType($file, ['mp4', 'mov', 'avi', 'webm']);
        $this->validateFileSize($file, 50 * 1024); // 50MB in KB
        $isFirstVideo = ! $this->hasUploadedType($couple, 'video');

        return DB::transaction(function () use ($couple, $user, $file, $data, $isFirstVideo) {
            $path = $this->storeFile($file, $couple->id, 'video');

            $memory = Memory::create([
                'couple_id' => $couple->id,
                'created_by' => $user->id,
                'type' => 'video',
                'title' => $data['title'] ?? null,
                'description' => $data['description'] ?? null,
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'visibility' => $data['visibility'] ?? 'shared',
                'comfort' => (bool) ($data['comfort'] ?? false),
                'metadata' => ['duration' => null], // Could extract with FFmpeg
            ]);

            // Award XP for first video
            if ($isFirstVideo) {
                $this->xpService->awardXp(
                    $couple,
                    'vault',
                    $user,
                    10,
                    ['reason' => 'first_video']
                );
            }

            return $memory;
        });
    }

    /**
     * Upload a voice note
     */
    public function uploadVoiceNote(Couple $couple, User $user, UploadedFile $file, array $data): Memory
    {
        $this->assertCoupleMember($couple, $user);
        $this->validateFileType($file, ['mp3', 'wav', 'm4a', 'ogg']);
        $this->validateFileSize($file, 10 * 1024); // 10MB in KB
        $isFirstVoiceNote = ! $this->hasUploadedType($couple, 'voice_note');

        return DB::transaction(function () use ($couple, $user, $file, $data, $isFirstVoiceNote) {
            $path = $this->storeFile($file, $couple->id, 'voice_note');

            $memory = Memory::create([
                'couple_id' => $couple->id,
                'created_by' => $user->id,
                'type' => 'voice_note',
                'title' => $data['title'] ?? null,
                'description' => $data['description'] ?? null,
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'visibility' => $data['visibility'] ?? 'shared',
                'comfort' => (bool) ($data['comfort'] ?? false),
                'metadata' => ['duration' => null],
            ]);

            // Award XP for first voice note
            if ($isFirstVoiceNote) {
                $this->xpService->awardXp(
                    $couple,
                    'vault',
                    $user,
                    5,
                    ['reason' => 'first_voice_note']
                );
            }

            return $memory;
        });
    }

    /**
     * Create a text memory
     */
    public function createTextMemory(Couple $couple, User $user, array $data): Memory
    {
        $this->assertCoupleMember($couple, $user);

        return DB::transaction(function () use ($couple, $user, $data) {
            $memory = Memory::create([
                'couple_id' => $couple->id,
                'created_by' => $user->id,
                'type' => 'text',
                'title' => $data['title'] ?? null,
                'description' => $data['description'],
                'visibility' => $data['visibility'] ?? 'shared',
                'comfort' => (bool) ($data['comfort'] ?? false),
            ]);

            // Award XP for text memory
            $this->xpService->awardXp(
                $couple,
                'vault',
                $user,
                3,
                ['reason' => 'text_memory']
            );

            return $memory;
        });
    }

    /**
     * Update memory
     */
    public function updateMemory(Memory $memory, User $user, array $data): Memory
    {
        $this->assertCoupleMember($memory->couple, $user);

        if ($memory->created_by !== $user->id) {
            throw new \Exception('You can only edit your own memories.');
        }

        $memory->update([
            'title' => $data['title'] ?? $memory->title,
            'description' => $data['description'] ?? $memory->description,
        ]);

        return $memory->fresh();
    }

    /**
     * Delete memory
     */
    public function deleteMemory(Memory $memory, User $user): void
    {
        $this->assertCoupleMember($memory->couple, $user);

        if (! $memory->canBeDeletedBy($user)) {
            throw new \Exception('You cannot delete this memory.');
        }

        DB::transaction(function () use ($memory) {
            // Delete file if exists
            if ($memory->file_path) {
                Storage::disk(self::MEDIA_DISK)->delete($memory->file_path);
            }
            if ($memory->thumbnail_path) {
                Storage::disk(self::MEDIA_DISK)->delete($memory->thumbnail_path);
            }

            $memory->delete();
        });
    }

    /**
     * Lock memory (make it special)
     */
    public function lockMemory(Memory $memory, User $user): Memory
    {
        $this->assertCoupleMember($memory->couple, $user);

        if ($memory->created_by !== $user->id) {
            throw new \Exception('Only the creator can lock this memory.');
        }

        DB::transaction(function () use ($memory) {
            $memory->lock();
            $memory->unlockApprovals()->delete();

            // Award XP for locking special memory
            $this->xpService->awardXp(
                $memory->couple,
                'vault',
                null, // Both partners
                10,
                ['reason' => 'locked_memory', 'memory_id' => $memory->id]
            );
        });

        return $memory->fresh();
    }

    /**
     * Unlock memory (requires both partners)
     */
    public function unlockMemory(Memory $memory, User $user): Memory
    {
        return $this->approveDualUnlock($memory, $user);
    }

    public function approveDualUnlock(Memory $memory, User $user): Memory
    {
        $this->assertCoupleMember($memory->couple, $user);
        Gate::forUser($user)->authorize('approveUnlock', $memory);

        $now = now();
        $expiresAt = $now->copy()->addMinutes(self::DUAL_UNLOCK_TTL_MINUTES);

        VaultUnlock::updateOrCreate(
            [
                'memory_id' => $memory->id,
                'user_id' => $user->id,
            ],
            [
                'approved_at' => $now,
                'expires_at' => $expiresAt,
            ]
        );

        return $memory->fresh();
    }

    /**
     * Change visibility
     */
    public function changeVisibility(Memory $memory, User $user, string $visibility): Memory
    {
        $this->assertCoupleMember($memory->couple, $user);

        if ($memory->created_by !== $user->id) {
            throw new \Exception('Only the creator can change visibility.');
        }

        if ($memory->isDual()) {
            throw new \Exception('Dual-consent memories cannot have their visibility changed.');
        }

        if (! in_array($visibility, ['private', 'shared', 'dual'], true)) {
            throw new \InvalidArgumentException('Invalid visibility option.');
        }

        $memory->update(['visibility' => $visibility]);

        return $memory->fresh();
    }

    public function toggleComfort(Memory $memory, User $user): Memory
    {
        $this->assertCoupleMember($memory->couple, $user);
        Gate::forUser($user)->authorize('toggleComfort', $memory);

        $memory->update([
            'comfort' => ! $memory->comfort,
        ]);

        return $memory->fresh();
    }

    public function getUnlockStatus(Memory $memory, User $user): array
    {
        $this->assertCoupleMember($memory->couple, $user);

        $memberIds = $memory->couple->users()
            ->where('couple_user.is_active', true)
            ->pluck('users.id');

        $approvals = $memory->unlockApprovals()
            ->whereIn('user_id', $memberIds)
            ->get()
            ->keyBy('user_id');

        $perUser = [];
        foreach ($memberIds as $memberId) {
            $approval = $approvals->get($memberId);
            $perUser[$memberId] = [
                'approved' => $approval !== null
                    && $approval->approved_at !== null
                    && ($approval->expires_at === null || $approval->expires_at->isFuture()),
                'approved_at' => $approval?->approved_at,
                'expires_at' => $approval?->expires_at,
            ];
        }

        $isUnlocked = $memory->hasActiveDualUnlock();
        $expiresAt = $isUnlocked
            ? collect($perUser)->pluck('expires_at')->filter()->sort()->first()
            : null;

        return [
            'is_dual' => $memory->isDual(),
            'is_unlocked' => $isUnlocked,
            'approvals' => $perUser,
            'expires_at' => $expiresAt,
        ];
    }

    /**
     * Add or update reaction
     */
    public function addReaction(Memory $memory, User $user, string $reaction): MemoryReaction
    {
        $this->assertCoupleMember($memory->couple, $user);
        if (! $memory->canBeViewedBy($user)) {
            throw new AuthorizationException('You cannot react to this memory.');
        }

        $validReactions = array_keys(MemoryReaction::getReactionTypes());
        if (! in_array($reaction, $validReactions)) {
            throw new \InvalidArgumentException('Invalid reaction type.');
        }

        return MemoryReaction::updateOrCreate(
            [
                'memory_id' => $memory->id,
                'user_id' => $user->id,
            ],
            ['reaction' => $reaction]
        );
    }

    /**
     * Remove reaction
     */
    public function removeReaction(Memory $memory, User $user): void
    {
        $this->assertCoupleMember($memory->couple, $user);
        if (! $memory->canBeViewedBy($user)) {
            throw new AuthorizationException('You cannot modify reactions for this memory.');
        }

        MemoryReaction::where('memory_id', $memory->id)
            ->where('user_id', $user->id)
            ->delete();
    }

    /**
     * Get memories for couple
     */
    public function getMemories(Couple $couple, User $user, ?string $type = null, bool $comfortOnly = false)
    {
        $this->assertCoupleMember($couple, $user);

        $query = Memory::forCouple($couple)
            ->visible($user)
            ->with(['creator', 'reactions', 'unlockApprovals'])
            ->recent();

        if ($type) {
            $query->ofType($type);
        }

        if ($comfortOnly) {
            $query->where('comfort', true);
        }

        return $query->get();
    }

    /**
     * Get locked memories
     */
    public function getLockedMemories(Couple $couple, ?User $user = null)
    {
        if ($user) {
            $this->assertCoupleMember($couple, $user);
        }

        return Memory::forCouple($couple)
            ->locked()
            ->with(['creator', 'reactions', 'unlockApprovals'])
            ->recent()
            ->get();
    }

    /**
     * Get storage statistics
     */
    public function getStorageStats(Couple $couple, ?User $user = null): array
    {
        if ($user) {
            $this->assertCoupleMember($couple, $user);
        }

        $memories = Memory::forCouple($couple)->get();

        return [
            'total_count' => $memories->count(),
            'total_size' => $memories->sum('file_size'),
            'photos' => $memories->where('type', 'photo')->count(),
            'videos' => $memories->where('type', 'video')->count(),
            'voice_notes' => $memories->where('type', 'voice_note')->count(),
            'text' => $memories->where('type', 'text')->count(),
            'locked' => $memories->whereIn('visibility', ['dual', 'locked'])->count(),
            'comfort' => $memories->where('comfort', true)->count(),
        ];
    }

    /**
     * Store file in storage
     */
    protected function storeFile(UploadedFile $file, int $coupleId, string $type): string
    {
        $filename = Str::random(40).'.'.$file->getClientOriginalExtension();
        $path = "memories/{$coupleId}/{$type}/{$filename}";

        Storage::disk(self::MEDIA_DISK)->putFileAs(
            "memories/{$coupleId}/{$type}",
            $file,
            $filename
        );

        return $path;
    }

    /**
     * Validate file type
     */
    protected function validateFileType(UploadedFile $file, array $allowedExtensions): void
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, $allowedExtensions)) {
            throw new \InvalidArgumentException(
                'Invalid file type. Allowed: '.implode(', ', $allowedExtensions)
            );
        }
    }

    /**
     * Validate file size
     */
    protected function validateFileSize(UploadedFile $file, int $maxSizeKB): void
    {
        $fileSizeKB = $file->getSize() / 1024;

        if ($fileSizeKB > $maxSizeKB) {
            throw new \InvalidArgumentException(
                "File too large. Maximum size: {$maxSizeKB}KB"
            );
        }
    }

    /**
     * Get image metadata
     */
    protected function getImageMetadata(UploadedFile $file): array
    {
        try {
            [$width, $height] = getimagesize($file->getRealPath());

            return [
                'width' => $width,
                'height' => $height,
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Check if couple has uploaded this type before
     */
    protected function hasUploadedType(Couple $couple, string $type): bool
    {
        return Memory::forCouple($couple)
            ->ofType($type)
            ->exists();
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
