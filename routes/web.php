<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SettingsController;

Route::get('/', function () {
    return view('welcome');
});

// Settings routes
Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
