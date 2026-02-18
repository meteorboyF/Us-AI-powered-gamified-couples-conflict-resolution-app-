<?php

namespace App\Http\Controllers\UI;

use App\Http\Controllers\Controller;
use App\Models\Couple;
use App\Models\VaultItem;
use App\Models\VaultUnlockApproval;
use App\Models\VaultUnlockRequest;
use App\Support\CoupleContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VaultUiController extends Controller
{
    public function page(Request $request, CoupleContext $context)
    {
        [$couple, $error] = $this->resolveCoupleForUi($request, $context);

        if (! $couple) {
            return view('vault.page', [
                'couple' => null,
                'items' => collect(),
                'errorState' => $error,
                'pendingByItem' => [],
                'currentUserId' => (int) $request->user()->id,
            ]);
        }

        $this->authorize('viewAny', [VaultItem::class, $couple->id]);

        $items = VaultItem::query()
            ->where('couple_id', $couple->id)
            ->with('creator:id,name')
            ->orderByDesc('id')
            ->get()
            ->map(function (VaultItem $item) {
                $isUnlocked = $this->hasActiveApprovedUnlock($item);
                $redacted = $this->isRedacted($item, $isUnlocked);

                return [
                    'id' => $item->id,
                    'type' => $item->type,
                    'title' => $item->title,
                    'body' => $redacted ? null : $item->body,
                    'is_sensitive' => $item->is_sensitive,
                    'is_locked' => $item->is_locked,
                    'redacted' => $redacted,
                    'created_at' => $item->created_at,
                    'creator_name' => $item->creator?->name ?? 'Unknown',
                    'media_url' => $redacted ? null : $this->mediaUrl($item),
                    'media_mime' => $redacted ? null : $item->media_mime,
                ];
            })
            ->values();

        $pendingByItem = VaultUnlockRequest::query()
            ->whereHas('item', fn ($q) => $q->where('couple_id', $couple->id))
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->orderByDesc('id')
            ->get()
            ->groupBy('vault_item_id')
            ->map(fn ($group) => $group->first());

        return view('vault.page', [
            'couple' => $couple,
            'items' => $items,
            'errorState' => null,
            'pendingByItem' => $pendingByItem,
            'currentUserId' => (int) $request->user()->id,
        ]);
    }

    public function create(Request $request, CoupleContext $context): RedirectResponse
    {
        $couple = $this->resolveCoupleOrRedirect($request, $context);
        if (! $couple) {
            return redirect()->route('vault.ui');
        }

        $this->authorize('create', [VaultItem::class, $couple->id]);

        $validated = $request->validate([
            'type' => ['required', 'string', 'in:note,reason'],
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        VaultItem::query()->create([
            'couple_id' => $couple->id,
            'created_by_user_id' => $request->user()->id,
            'type' => $validated['type'],
            'title' => $validated['title'] ?? null,
            'body' => $validated['body'],
            'is_sensitive' => $request->boolean('is_sensitive'),
            'is_locked' => false,
            'meta' => [
                'consent_required' => true,
            ],
        ]);

        return redirect()->route('vault.ui')->with('status', 'Vault item created.');
    }

    public function upload(Request $request, CoupleContext $context, VaultItem $item): RedirectResponse
    {
        $couple = $this->resolveCoupleOrRedirect($request, $context);
        if (! $couple) {
            return redirect()->route('vault.ui');
        }

        if ((int) $item->couple_id !== (int) $couple->id) {
            abort(404);
        }

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
            return redirect()->route('vault.ui')->withErrors(['media' => 'Unsupported media file type.']);
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

        return redirect()->route('vault.ui')->with('status', 'Media uploaded.');
    }

    public function requestUnlock(Request $request, CoupleContext $context, VaultItem $item): RedirectResponse
    {
        $couple = $this->resolveCoupleOrRedirect($request, $context);
        if (! $couple) {
            return redirect()->route('vault.ui');
        }

        if ((int) $item->couple_id !== (int) $couple->id) {
            abort(404);
        }

        $this->authorize('requestUnlock', $item);

        $existing = VaultUnlockRequest::query()
            ->where('vault_item_id', $item->id)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->first();

        if (! $existing) {
            VaultUnlockRequest::query()->create([
                'vault_item_id' => $item->id,
                'requested_by_user_id' => $request->user()->id,
                'status' => 'pending',
                'expires_at' => now()->addMinutes((int) config('us.vault.unlock_window_minutes', 30)),
            ]);
        }

        return redirect()->route('vault.ui')->with('status', 'Unlock request submitted.');
    }

    public function approve(Request $request, CoupleContext $context, VaultUnlockRequest $unlockRequest): RedirectResponse
    {
        $couple = $this->resolveCoupleOrRedirect($request, $context);
        if (! $couple) {
            return redirect()->route('vault.ui');
        }

        if ((int) $unlockRequest->item->couple_id !== (int) $couple->id) {
            abort(404);
        }

        $this->authorize('respond', $unlockRequest);

        VaultUnlockApproval::query()->updateOrCreate(
            [
                'vault_unlock_request_id' => $unlockRequest->id,
                'user_id' => $request->user()->id,
            ],
            [
                'decision' => 'approved',
                'decided_at' => now(),
            ]
        );

        $unlockRequest->forceFill(['status' => 'approved'])->save();

        return redirect()->route('vault.ui')->with('status', 'Unlock request approved.');
    }

    public function reject(Request $request, CoupleContext $context, VaultUnlockRequest $unlockRequest): RedirectResponse
    {
        $couple = $this->resolveCoupleOrRedirect($request, $context);
        if (! $couple) {
            return redirect()->route('vault.ui');
        }

        if ((int) $unlockRequest->item->couple_id !== (int) $couple->id) {
            abort(404);
        }

        $this->authorize('respond', $unlockRequest);

        VaultUnlockApproval::query()->updateOrCreate(
            [
                'vault_unlock_request_id' => $unlockRequest->id,
                'user_id' => $request->user()->id,
            ],
            [
                'decision' => 'rejected',
                'decided_at' => now(),
            ]
        );

        $unlockRequest->forceFill(['status' => 'rejected'])->save();

        return redirect()->route('vault.ui')->with('status', 'Unlock request rejected.');
    }

    /**
     * @return array{0: ?Couple, 1: ?string}
     */
    private function resolveCoupleForUi(Request $request, CoupleContext $context): array
    {
        $user = $request->user();

        if ($user->current_couple_id && ! $user->couples()->whereKey($user->current_couple_id)->exists()) {
            return [null, 'not_authorized'];
        }

        $couple = $context->resolve();

        if (! $couple) {
            return [null, 'no_couple'];
        }

        return [$couple, null];
    }

    private function resolveCoupleOrRedirect(Request $request, CoupleContext $context): ?Couple
    {
        [$couple, $error] = $this->resolveCoupleForUi($request, $context);

        if (! $couple) {
            if ($error === 'not_authorized') {
                session()->flash('error', 'Not authorized');
            } else {
                session()->flash('error', 'No couple selected');
            }
        }

        return $couple;
    }

    private function hasActiveApprovedUnlock(VaultItem $item): bool
    {
        return $item->unlockRequests()
            ->where('status', 'approved')
            ->where('expires_at', '>', now())
            ->exists();
    }

    private function isRedacted(VaultItem $item, bool $isUnlocked): bool
    {
        if (! $item->is_sensitive && ! $item->is_locked) {
            return false;
        }

        $consentRequired = (bool) data_get(
            $item->meta,
            'consent_required',
            (bool) config('us.vault.sensitive_requires_consent_default', true),
        );

        if ($item->is_locked || ($item->is_sensitive && $consentRequired)) {
            return ! $isUnlocked;
        }

        return false;
    }

    private function mediaUrl(VaultItem $item): ?string
    {
        if (! $item->media_disk || ! $item->media_path) {
            return null;
        }

        return Storage::disk($item->media_disk)->url($item->media_path);
    }
}
