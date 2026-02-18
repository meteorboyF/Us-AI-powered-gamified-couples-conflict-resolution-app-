<?php

namespace App\Http\Controllers;

use App\Models\Couple;
use App\Models\VaultItem;
use App\Models\VaultUnlockApproval;
use App\Models\VaultUnlockRequest;
use App\Support\CoupleContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VaultUnlockController extends Controller
{
    public function request(Request $request, CoupleContext $context, VaultItem $item): JsonResponse
    {
        $this->ensureFeatureEnabled();
        $couple = $this->resolveCouple($request, $context);
        $this->ensureItemInCouple($item, $couple);
        $this->authorize('requestUnlock', $item);

        $pending = VaultUnlockRequest::query()
            ->where('vault_item_id', $item->id)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->first();

        if ($pending) {
            return response()->json([
                'unlock_request_id' => $pending->id,
                'status' => $pending->status,
                'expires_at' => $pending->expires_at?->toIso8601String(),
            ]);
        }

        $unlockRequest = VaultUnlockRequest::query()->create([
            'vault_item_id' => $item->id,
            'requested_by_user_id' => $request->user()->id,
            'status' => 'pending',
            'expires_at' => now()->addMinutes((int) config('us.vault.unlock_window_minutes', 30)),
        ]);

        return response()->json([
            'unlock_request_id' => $unlockRequest->id,
            'status' => $unlockRequest->status,
            'expires_at' => $unlockRequest->expires_at?->toIso8601String(),
        ], 201);
    }

    public function approve(Request $request, CoupleContext $context, VaultUnlockRequest $unlockRequest): JsonResponse
    {
        $this->ensureFeatureEnabled();
        $couple = $this->resolveCouple($request, $context);
        $this->ensureRequestInCouple($unlockRequest, $couple);
        $this->authorize('respond', $unlockRequest);

        if ($unlockRequest->expires_at->isPast()) {
            $unlockRequest->forceFill(['status' => 'expired'])->save();

            return response()->json([
                'unlock_request_id' => $unlockRequest->id,
                'status' => 'expired',
            ], 422);
        }

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

        return response()->json([
            'unlock_request_id' => $unlockRequest->id,
            'status' => 'approved',
        ]);
    }

    public function reject(Request $request, CoupleContext $context, VaultUnlockRequest $unlockRequest): JsonResponse
    {
        $this->ensureFeatureEnabled();
        $couple = $this->resolveCouple($request, $context);
        $this->ensureRequestInCouple($unlockRequest, $couple);
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

        return response()->json([
            'unlock_request_id' => $unlockRequest->id,
            'status' => 'rejected',
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

    private function ensureRequestInCouple(VaultUnlockRequest $unlockRequest, Couple $couple): void
    {
        if ((int) $unlockRequest->item->couple_id !== (int) $couple->id) {
            abort(404, 'Vault unlock request not found.');
        }
    }

    private function ensureFeatureEnabled(): void
    {
        if (! config('us.features.vault_v1', true)) {
            abort(404, 'Feature not available.');
        }
    }
}
