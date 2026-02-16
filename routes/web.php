<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Home;

Route::get('/', function () {
    return view('welcome'); // Make sure this says 'welcome'
});

Route::get('/dashboard', function () {
    return view('dashboard');
});

Route::get('/chat', function () {
    return view('chat');
});