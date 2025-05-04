<?php

use App\Http\Controllers\FileHandler;
use App\Livewire\Apo\Meetings;
use App\Livewire\Shared\Accomplishments;
use App\Livewire\Shared\Calendar;
use App\Livewire\Shared\Dashboard;
use App\Livewire\Shared\Incoming\Documents;
use App\Livewire\Shared\Incoming\Requests;
use App\Livewire\Shared\Outgoing;
use App\Livewire\Shared\Settings\AccomplishmentCategory;
use App\Livewire\Shared\Settings\Divisions;
use App\Livewire\Shared\Settings\IncomingDocumentCategory;
use App\Livewire\Shared\Settings\IncomingRequestCategory;
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
    Route::get('/dashboard', Dashboard::class)->name('dashboard')->middleware('permission:dashboard.read');

    # Incoming.Requests
    Route::get('/incoming/requests', Requests::class)->name('incoming-requests')->middleware('permission:incoming.requests.read');

    # Incoming.Documents
    Route::get('/incoming/documents', Documents::class)->name('incoming-documents')->middleware('permission:incoming.documents.read');

    # Outgoing
    Route::get('/outgoing', Outgoing::class)->name('outgoing')->middleware('permission:outgoing.read');

    # Calendar
    Route::get('/calendar', Calendar::class)->name('calendar')->middleware('permission:calendar.read');

    # Accomplishments
    Route::get('/accomplishments', Accomplishments::class)->name('accomplishments')->middleware('permission:accomplishments.read');

    # Settings.Accomplishment Category
    Route::get('/settings/accomplishment-category', AccomplishmentCategory::class)->name('accomplishment-category')->middleware('permission:reference.accomplishmentCategory.read');
    # Settings.Incoming Document Category
    Route::get('/settings/incoming-document-categories', IncomingDocumentCategory::class)->name('incoming-document-categories')->middleware('permission:reference.incomingDocumentCategory.read');
    # Settings.Incoming Request Category
    Route::get('/settings/incoming-request-categories', IncomingRequestCategory::class)->name('incoming-request-categories')->middleware('permission:reference.incomingRequestCategory.read');
    # Settings.Signatories
    Route::get('/settings/signatories', Signatories::class)->name('settings.signatories')->middleware('permission:reference.signatories.read');
    # Settings.Divisions
    Route::get('/settings/divisions', Divisions::class)->name('settings.divisions')->middleware('permission:reference.divisions.read');
    # Settings.User Management
    Route::get('/settings/user-management', UserManagement::class)->name('settings.user-management')->middleware('permission:reference.userManagement.read');

    # File Handler
    Route::get('/file/view/{id}', [FileHandler::class, 'viewFile'])->name('file.view')->middleware('signed');




    /* ------------------------------- APO ROUTES ------------------------------- */
    Route::get('/meetings', Meetings::class)->name('meetings')->middleware('permission:meeting.read');




    /* --------------------------- SUPER ADMIN ROUTES --------------------------- */
    Route::group(['middleware' => ['role:Super Admin']], function () {
        Route::get('/roles-and-permissions', RolesAndPermissions::class)->name('roles-and-permissions');
    });

    /* -------------------------------------------------------------------------- */

    //* Always uncomment this when uploading to production

    // Livewire::setScriptRoute(function ($handle) {
    //     return Route::get('/cdo-dms/livewire/livewire.js', $handle);
    // });
    // Livewire::setUpdateRoute(function ($handle) {
    //     return Route::post('/cdo-dms/livewire/update', $handle);
    // });
});
