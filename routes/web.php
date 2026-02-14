<?php

use App\Http\Controllers\ChatV2\ConversationController as ChatV2ConversationController;
use App\Http\Controllers\ChatV2\ReceiptController as ChatV2ReceiptController;
use App\Livewire\Couple\CreateOrJoin;
use App\Livewire\Dashboard\CoupleWorld;
use App\Livewire\Mission\Board;
use App\Livewire\MoodCheckin\Create;
use App\Livewire\MoodCheckin\PartnerView;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Dashboard
    Route::get('/dashboard', CoupleWorld::class)->name('dashboard');

    // Couple Management
    Route::get('/couple/create-or-join', CreateOrJoin::class)->name('couple.create-or-join');

    // Mood Check-ins (requires couple)
    Route::middleware(['ensure.has.couple'])->group(function () {
        Route::get('/checkin', Create::class)->name('checkin.create');
        Route::get('/partner-mood', PartnerView::class)->name('checkin.partner');
    });

    // Missions (requires couple)
    Route::middleware(['ensure.has.couple'])->group(function () {
        Route::get('/missions', Board::class)->name('missions.board');
        Route::get('/chat', \App\Livewire\Chat\Room::class)->name('chat.room');
        Route::get('/chat-v2', [ChatV2ConversationController::class, 'show'])->name('chatv2.room');
        Route::prefix('/chat-v2')->name('chatv2.')->group(function () {
            Route::get('/conversation', [ChatV2ConversationController::class, 'index'])->name('conversation.index');
            Route::get('/conversations/{conversation}', [ChatV2ConversationController::class, 'showConversation'])->name('conversation.show');
            Route::post('/messages', [ChatV2ConversationController::class, 'send'])->name('messages.send');
            Route::post('/messages/{message}/delivered', [ChatV2ReceiptController::class, 'delivered'])->name('messages.delivered');
            Route::post('/messages/{message}/read', [ChatV2ReceiptController::class, 'read'])->name('messages.read');
        });

        // Repair Flow
        Route::get('/repair/initiate', \App\Livewire\Repair\Initiate::class)->name('repair.initiate');
        Route::get('/repair/wizard/{sessionId}', \App\Livewire\Repair\Wizard::class)->name('repair.wizard');
        Route::get('/repair/history', \App\Livewire\Repair\History::class)->name('repair.history');

        // Vault
        Route::get('/vault', \App\Livewire\Vault\Gallery::class)->name('vault.gallery');
        Route::get('/vault/upload', \App\Livewire\Vault\Upload::class)->name('vault.upload');
        Route::get('/vault/memory/{memoryId}', \App\Livewire\Vault\MemoryView::class)->name('vault.memory');

        // AI Coach
        Route::get('/coach', \App\Livewire\Coach\Chat::class)->name('coach.chat');

        // Gifts
        Route::get('/gifts', \App\Livewire\Gifts\Index::class)->name('gifts.index');
    });
});
