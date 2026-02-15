<?php

use App\Livewire\Chat\ChatThread;
use App\Livewire\Home;
use Illuminate\Support\Facades\Route;

Route::get('/', Home::class);
Route::get('/chat', ChatThread::class)
    ->middleware('auth')
    ->name('chat.thread');
