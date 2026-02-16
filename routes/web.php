<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Home;

Route::get('/', function () {
    return view('welcome'); // Make sure this says 'welcome'
});