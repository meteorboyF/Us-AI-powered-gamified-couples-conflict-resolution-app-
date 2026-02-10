<?php

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
    });
});
