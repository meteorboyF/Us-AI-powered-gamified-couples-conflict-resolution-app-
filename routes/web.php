<?php

use App\Http\Controllers\AiCoachController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CoupleController;
use App\Http\Controllers\DailyCheckinController;
use App\Http\Controllers\GiftController;
use App\Http\Controllers\MissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UI\AiCoachUiController;
use App\Http\Controllers\UI\AppHubController;
use App\Http\Controllers\UI\ChatUiController;
use App\Http\Controllers\UI\GiftsUiController;
use App\Http\Controllers\UI\MissionsUiController;
use App\Http\Controllers\UI\VaultUiController;
use App\Http\Controllers\VaultController;
use App\Http\Controllers\VaultUnlockController;
use App\Http\Controllers\WorldController;
use App\Livewire\Home;
use Illuminate\Support\Facades\Route;

Route::get('/', Home::class);

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/app', [AppHubController::class, 'page'])->name('app.home');
    Route::post('/couples', [CoupleController::class, 'store'])->name('couples.store');
    Route::post('/couples/join', [CoupleController::class, 'join'])->name('couples.join');
    Route::post('/couples/switch', [CoupleController::class, 'switch'])->name('couples.switch');
    Route::get('/world-ui', [WorldController::class, 'page'])->name('world.ui');
    Route::get('/world', [WorldController::class, 'index'])->name('world.index');
    Route::post('/world/vibe', [WorldController::class, 'updateVibe'])->name('world.vibe');
    Route::post('/world/unlock', [WorldController::class, 'unlock'])->name('world.unlock');
    Route::get('/missions', [MissionController::class, 'index'])->name('missions.index');
    Route::post('/missions/assign', [MissionController::class, 'assign'])->name('missions.assign');
    Route::post('/missions/complete', [MissionController::class, 'complete'])->name('missions.complete');
    Route::get('/missions-ui', [MissionsUiController::class, 'page'])->name('missions.ui');
    Route::post('/missions-ui/complete', [MissionsUiController::class, 'complete'])->name('missions.ui.complete');
    Route::post('/missions-ui/checkin', [MissionsUiController::class, 'checkin'])->name('missions.ui.checkin');
    Route::get('/checkins/today', [DailyCheckinController::class, 'today'])->name('checkins.today');
    Route::post('/checkins', [DailyCheckinController::class, 'store'])->name('checkins.store');
    Route::get('/chat', [ChatUiController::class, 'page'])->name('chat.page');
    Route::get('/chat-v1', [ChatController::class, 'thread'])->name('chat-v1.thread');
    Route::get('/chat-v1/messages', [ChatController::class, 'messages'])->name('chat-v1.messages');
    Route::post('/chat-v1/messages', [ChatController::class, 'send'])->middleware('throttle:chat-send')->name('chat-v1.send');
    Route::post('/chat-v1/read', [ChatController::class, 'markRead'])->name('chat-v1.read');
    Route::delete('/chat-v1/messages/{message}', [ChatController::class, 'delete'])->name('chat-v1.delete');
    Route::get('/vault', [VaultController::class, 'index'])->name('vault.index');
    Route::get('/vault/{item}', [VaultController::class, 'show'])->name('vault.show');
    Route::post('/vault', [VaultController::class, 'store'])->name('vault.store');
    Route::post('/vault/{item}/media', [VaultController::class, 'upload'])->name('vault.upload');
    Route::post('/vault/{item}/lock', [VaultController::class, 'lock'])->name('vault.lock');
    Route::post('/vault/{item}/unlock-request', [VaultUnlockController::class, 'request'])->name('vault.unlock.request');
    Route::post('/vault/unlock/{unlockRequest}/approve', [VaultUnlockController::class, 'approve'])->name('vault.unlock.approve');
    Route::post('/vault/unlock/{unlockRequest}/reject', [VaultUnlockController::class, 'reject'])->name('vault.unlock.reject');
    Route::get('/vault-ui', [VaultUiController::class, 'page'])->name('vault.ui');
    Route::post('/vault-ui/create', [VaultUiController::class, 'create'])->name('vault.ui.create');
    Route::post('/vault-ui/{item}/upload', [VaultUiController::class, 'upload'])->name('vault.ui.upload');
    Route::post('/vault-ui/{item}/unlock-request', [VaultUiController::class, 'requestUnlock'])->name('vault.ui.requestUnlock');
    Route::post('/vault-ui/unlock/{unlockRequest}/approve', [VaultUiController::class, 'approve'])->name('vault.ui.approve');
    Route::post('/vault-ui/unlock/{unlockRequest}/reject', [VaultUiController::class, 'reject'])->name('vault.ui.reject');
    Route::get('/ai/sessions', [AiCoachController::class, 'index'])->name('ai.sessions.index');
    Route::post('/ai/sessions', [AiCoachController::class, 'store'])->name('ai.sessions.store');
    Route::get('/ai/sessions/{session}', [AiCoachController::class, 'show'])->name('ai.sessions.show');
    Route::post('/ai/sessions/{session}/user-message', [AiCoachController::class, 'message'])->middleware('throttle:ai-coach')->name('ai.sessions.message');
    Route::post('/ai/sessions/{session}/close', [AiCoachController::class, 'close'])->name('ai.sessions.close');
    Route::post('/ai/sessions/{session}/drafts/{draft}/accept', [AiCoachController::class, 'acceptDraft'])->name('ai.sessions.drafts.accept');
    Route::post('/ai/sessions/{session}/drafts/{draft}/discard', [AiCoachController::class, 'discardDraft'])->name('ai.sessions.drafts.discard');
    Route::get('/ai-coach', [AiCoachUiController::class, 'page'])->name('ai.coach.page');
    Route::post('/ai-coach/sessions', [AiCoachUiController::class, 'createSession'])->name('ai.coach.session.create');
    Route::post('/ai-coach/sessions/{session}/send', [AiCoachUiController::class, 'send'])->name('ai.coach.send');
    Route::post('/ai-coach/sessions/{session}/drafts/{draft}/accept', [AiCoachUiController::class, 'accept'])->name('ai.coach.draft.accept');
    Route::post('/ai-coach/sessions/{session}/drafts/{draft}/discard', [AiCoachUiController::class, 'discard'])->name('ai.coach.draft.discard');
    Route::post('/gifts/requests', [GiftController::class, 'store'])->name('gifts.requests.store');
    Route::get('/gifts/requests/{giftRequest}', [GiftController::class, 'show'])->name('gifts.requests.show');
    Route::post('/gifts/requests/{giftRequest}/generate', [GiftController::class, 'generate'])->name('gifts.requests.generate');
    Route::post('/gifts/suggestions/{suggestion}/favorite', [GiftController::class, 'toggleFavorite'])->name('gifts.suggestions.favorite');
    Route::get('/gifts-ui', [GiftsUiController::class, 'page'])->name('gifts.ui');
    Route::post('/gifts-ui/request', [GiftsUiController::class, 'createRequest'])->name('gifts.ui.request');
    Route::post('/gifts-ui/{giftRequest}/generate', [GiftsUiController::class, 'generate'])->name('gifts.ui.generate');
    Route::post('/gifts-ui/suggestions/{suggestion}/favorite', [GiftsUiController::class, 'favorite'])->name('gifts.ui.favorite');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
