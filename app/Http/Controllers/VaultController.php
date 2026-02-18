<?php

namespace App\Http\Controllers;

use App\Models\Couple;
use App\Models\VaultItem;
use App\Support\CoupleContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class VaultController extends Controller
{
    public function index(Request $request, CoupleContext $context): JsonResponse
    {
        $this->ensureFeatureEnabled();
        $couple = $this->resolveCouple($request, $context);

        $this->authorize('viewAny', [VaultItem::class, $couple->id]);

        $items = VaultItem::query()
            ->where('couple_id', $couple->id)
            ->orderByDesc('id')
            ->get()
            ->map(fn (VaultItem $item) => $this->serializeItem($item))
            ->values();

        return response()->json(['items' => $items]);
    }

    public function show(Request $request, CoupleContext $context, VaultItem $item): JsonResponse
    {
        $this->ensureFeatureEnabled();
        $couple = $this->resolveCouple($request, $context);
        $this->ensureItemInCouple($item, $couple);
        $this->authorize('view', $item);

        return response()->json([
            'item' => $this->serializeItem($item),
        ]);
    }

    public function store(Request $request, CoupleContext $context): JsonResponse
    {
        $this->ensureFeatureEnabled();

        $validated = $request->validate([
            'type' => ['required', 'string', 'in:note,photo,audio,reason,timeline'],
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'is_sensitive' => ['nullable', 'boolean'],
            'meta' => ['nullable', 'array'],
        ]);

        if (in_array($validated['type'], ['note', 'reason', 'timeline'], true) && empty($validated['body'])) {
            throw ValidationException::withMessages([
                'body' => 'The body field is required for this vault item type.',
            ]);
        }

        $couple = $this->resolveCouple($request, $context);
        $this->authorize('create', [VaultItem::class, $couple->id]);

        $meta = $validated['meta'] ?? [];
        if (! array_key_exists('consent_required', $meta)) {
            $meta['consent_required'] = (bool) config('us.vault.sensitive_requires_consent_default', true);
        }

        $item = VaultItem::query()->create([
            'couple_id' => $couple->id,
            'created_by_user_id' => $request->user()->id,
            'type' => $validated['type'],
            'title' => $validated['title'] ?? null,
            'body' => $validated['body'] ?? null,
            'is_sensitive' => (bool) ($validated['is_sensitive'] ?? false),
            'is_locked' => false,
            'meta' => $meta,
        ]);

        return response()->json([
            'item' => $this->serializeItem($item),
        ], 201);
    }

    public function upload(Request $request, CoupleContext $context, VaultItem $item): JsonResponse
    {
        $this->ensureFeatureEnabled();
        $couple = $this->resolveCouple($request, $context);
        $this->ensureItemInCouple($item, $couple);
        $this->authorize('upload', $item);

        $validated = $request->validate([
            'media' => ['required', 'file', 'max:'.((int) config('us.vault.max_upload_mb', 10) * 1024)],
        ]);

        $file = $validated['media'];
        $mime = $file->getMimeType() ?: $file->getClientMimeType();

        $allowed = [
            'image/jpeg',
            'image/png',
            'image/webp',
            'audio/mpeg',
            'audio/wav',
            'audio/x-wav',
            'audio/ogg',
            'audio/mp4',
            'audio/x-m4a',
        ];

        if (! in_array($mime, $allowed, true)) {
            throw ValidationException::withMessages([
                'media' => 'Unsupported media file type.',
            ]);
        }

        $disk = 'public';
        $path = $file->store('vault/'.$couple->id, $disk);
        $sha256 = hash_file('sha256', $file->getRealPath());

        $item->forceFill([
            'media_disk' => $disk,
            'media_path' => $path,
            'media_mime' => $mime,
            'media_size' => $file->getSize(),
            'sha256' => $sha256,
            'type' => str_starts_with($mime, 'image/') ? 'photo' : 'audio',
        ])->save();

        return response()->json([
            'item' => $this->serializeItem($item->fresh()),
        ]);
    }

    public function lock(Request $request, CoupleContext $context, VaultItem $item): JsonResponse
    {
        $this->ensureFeatureEnabled();
        $couple = $this->resolveCouple($request, $context);
        $this->ensureItemInCouple($item, $couple);
        $this->authorize('lock', $item);

        $validated = $request->validate([
            'pin' => ['required', 'string', 'min:4', 'max:32'],
        ]);

        $item->forceFill([
            'is_locked' => true,
            'locked_pin_hash' => Hash::make($validated['pin']),
        ])->save();

        return response()->json([
            'item_id' => $item->id,
            'is_locked' => true,
        ]);
    }

    private function resolveCouple(Request $request, CoupleContext $context): Couple
    {
        $user = $request->user();

        if ($user->current_couple_id && ! $user->couples()->whereKey($user->current_couple_id)->exists()) {
            abort(403, 'Current couple is not accessible.');
        }

        $couple = $context->resolve();

        if (! $couple) {
            abort(409, 'No couple selected');
        }

        return $couple;
    }

    private function ensureItemInCouple(VaultItem $item, Couple $couple): void
    {
        if ((int) $item->couple_id !== (int) $couple->id) {
            abort(404, 'Vault item not found.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeItem(VaultItem $item): array
    {
        $revealed = $this->canReveal($item);

        return [
            'id' => $item->id,
            'type' => $item->type,
            'title' => $item->title,
            'body' => $revealed ? $item->body : null,
            'is_sensitive' => $item->is_sensitive,
            'is_locked' => $item->is_locked,
            'redacted' => ! $revealed,
            'media' => [
                'disk' => $revealed ? $item->media_disk : null,
                'path' => $revealed ? $item->media_path : null,
                'mime' => $revealed ? $item->media_mime : null,
                'size' => $revealed ? $item->media_size : null,
            ],
            'meta' => $item->meta ?? [],
            'created_by_user_id' => $item->created_by_user_id,
            'created_at' => $item->created_at?->toIso8601String(),
            'updated_at' => $item->updated_at?->toIso8601String(),
        ];
    }

    private function canReveal(VaultItem $item): bool
    {
        if (! $item->is_sensitive && ! $item->is_locked) {
            return true;
        }

        $hasApprovedUnlock = $item->unlockRequests()
            ->where('status', 'approved')
            ->where('expires_at', '>', now())
            ->exists();

        if ($item->is_locked) {
            return $hasApprovedUnlock;
        }

        $consentRequired = (bool) data_get(
            $item->meta,
            'consent_required',
            (bool) config('us.vault.sensitive_requires_consent_default', true),
        );

        if ($item->is_sensitive && $consentRequired) {
            return $hasApprovedUnlock;
        }

        return false;
    }

    private function ensureFeatureEnabled(): void
    {
        if (! config('us.features.vault_v1', true)) {
            abort(404, 'Feature not available.');
        }
    }
}
