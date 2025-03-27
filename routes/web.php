<?php

use App\Livewire\SuperAdmin\TeamsAndPermissions;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('/teams-and-permissions', TeamsAndPermissions::class)->name('teams-and-permissions');
});
