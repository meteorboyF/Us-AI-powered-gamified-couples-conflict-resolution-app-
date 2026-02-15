<?php

use App\Domain\Couples\CoupleContext;
use App\Livewire\Chat\ChatThread;
use App\Livewire\Home;
use Illuminate\Support\Facades\Route;

Route::get('/', Home::class);
Route::get('/chat', ChatThread::class)
    ->middleware('auth')
    ->name('chat.thread');

Route::get('/_dev/set-couple/{coupleId}', function (string $coupleId, CoupleContext $coupleContext) {
    abort_unless(app()->environment('local'), 404);

    $resolvedCoupleId = (int) $coupleId;

    $user = request()->user();
    abort_unless($user !== null, 403);

    $isAllowed = $user->chats()
        ->where('couple_id', $resolvedCoupleId)
        ->exists();

    abort_unless($isAllowed, 403);

    $coupleContext->setCurrentCoupleId($resolvedCoupleId);

    return response()->json([
        'current_couple_id' => $resolvedCoupleId,
    ]);
})
    ->whereNumber('coupleId')
    ->middleware('auth')
    ->name('dev.set-couple');
