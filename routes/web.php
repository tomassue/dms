<?php

use App\Livewire\Shared\Accomplishments;
use App\Livewire\Shared\Settings\AccomplishmentCategory;
use App\Livewire\Shared\Settings\Divisions;
use App\Livewire\Shared\Settings\Signatories;
use App\Livewire\Shared\Settings\UserManagement;
use App\Livewire\SuperAdmin\RolesAndPermissions;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Auth::routes();

Route::group(['middleware' => ['role:Super Admin|APO']], function () {
    // Routes accessible only by admin of team 1
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
});

//TODO: Add middlewares based on teams and permissions for grouped routes

Route::middleware(['auth'])->group(function () {

    /* ------------------------------ SHARED ROUTES ----------------------------- */
    # Dashboard

    # Accomplishments
    Route::get('/accomplishments', Accomplishments::class)->name('accomplishments')->middleware('permission:accomplishments.read');

    # Settings.Accomplishment Category
    Route::get('/settings/accomplishment-category', AccomplishmentCategory::class)->name('accomplishment-category')->middleware('permission:reference.accomplishmentCategory.read');
    # Settings.Signatories
    Route::get('/settings/signatories', Signatories::class)->name('settings.signatories')->middleware('permission:reference.signatories.read');
    # Settings.Divisions
    Route::get('/settings/divisions', Divisions::class)->name('settings.divisions')->middleware('permission:reference.divisions.read');
    # Settings.User Management
    Route::get('/settings/user-management', UserManagement::class)->name('settings.user-management')->middleware('permission:reference.userManagement.read');

    /* --------------------------- SUPER ADMIN ROUTES --------------------------- */
    Route::group(['middleware' => ['role:Super Admin']], function () {
        Route::get('/roles-and-permissions', RolesAndPermissions::class)->name('roles-and-permissions');
    });
});
