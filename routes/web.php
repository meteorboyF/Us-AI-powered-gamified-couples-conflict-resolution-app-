<?php

use App\Http\Controllers\CoupleController;
use App\Http\Controllers\DailyCheckinController;
use App\Http\Controllers\MissionController;
use App\Http\Controllers\ProfileController;
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

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
