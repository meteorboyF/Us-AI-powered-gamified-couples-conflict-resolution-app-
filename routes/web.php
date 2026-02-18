<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\CoupleController;
use App\Http\Controllers\DailyCheckinController;
use App\Http\Controllers\MissionController;
use App\Http\Controllers\ProfileController;
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
    Route::post('/couples', [CoupleController::class, 'store'])->name('couples.store');
    Route::post('/couples/join', [CoupleController::class, 'join'])->name('couples.join');
    Route::post('/couples/switch', [CoupleController::class, 'switch'])->name('couples.switch');
    Route::get('/world', [WorldController::class, 'index'])->name('world.index');
    Route::post('/world/vibe', [WorldController::class, 'updateVibe'])->name('world.vibe');
    Route::post('/world/unlock', [WorldController::class, 'unlock'])->name('world.unlock');
    Route::get('/missions', [MissionController::class, 'index'])->name('missions.index');
    Route::post('/missions/assign', [MissionController::class, 'assign'])->name('missions.assign');
    Route::post('/missions/complete', [MissionController::class, 'complete'])->name('missions.complete');
    Route::get('/checkins/today', [DailyCheckinController::class, 'today'])->name('checkins.today');
    Route::post('/checkins', [DailyCheckinController::class, 'store'])->name('checkins.store');
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

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
