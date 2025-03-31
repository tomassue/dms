<?php

use App\Livewire\Shared\Settings\UserManagement;
use App\Livewire\SuperAdmin\TeamsAndPermissions;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Auth::routes();

Route::group(['middleware' => ['role:Admin|1']], function () {
    // Routes accessible only by admin of team 1
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
});

//TODO: Add middlewares based on teams and permissions for grouped routes

Route::middleware(['auth'])->group(function () {

    // Super admin routes
    Route::get('/teams-and-permissions', TeamsAndPermissions::class)->name('teams-and-permissions');

    //* SHARED ROUTES
    // Admin routes
    Route::get('/settings/user-management', UserManagement::class)->name('settings.user-management');
});
