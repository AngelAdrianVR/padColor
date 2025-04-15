<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketHistoryController;
use App\Http\Controllers\TicketSolutionController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion' => PHP_VERSION,
//     ]);
// });
Route::redirect('/', 'login');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/tickets-report', [DashboardController::class, 'ticketsReport'])->name('dashboard.tickets-report');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/notifications', function () {
        return inertia('Notifications');
    })->name('notifications');
});


//Tickets routes---------------------------------------------------------------------------
//------------------------------------------------------------------------------------------
Route::resource('tickets', TicketController::class)->middleware('auth');
Route::put('tickets-update-status/{ticket}', [TicketController::class, 'updateStatus'])->name('tickets.update-status')->middleware('auth');
Route::post('tickets/update-with-media/{ticket}', [TicketController::class, 'updateWithMedia'])->name('tickets.update-with-media')->middleware('auth');
Route::post('tickets-massive-delete', [TicketController::class, 'massiveDelete'])->name('tickets.massive-delete')->middleware('auth');
Route::post('tickets/{ticket}/comment', [TicketController::class, 'comment'])->name('tickets.comment')->middleware('auth');
Route::get('tickets-fetch-all-comments/{ticket}', [TicketController::class, 'fetchConversation'])->name('tickets.fetch-conversation')->middleware('auth');
Route::get('tickets-fetch-history/{ticket}', [TicketController::class, 'fetchHistory'])->name('tickets.fetch-history')->middleware('auth');
Route::post('ticket-massive-delete', [TicketController::class, 'massiveDelete'])->name('tickets.massive-delete')->middleware('auth');
Route::get('tickets-get-by-page/{currentPage}', [TicketController::class, 'getItemsByPage'])->name('tickets.get-by-page')->middleware('auth');
Route::get('tickets-get-matches/{query}', [TicketController::class, 'getMatches'])->name('tickets.get-matches');
Route::get('tickets-get-filters/{prop}/{value}', [TicketController::class, 'getFilters'])->name('tickets.get-filters');


//Tickets-solutions routes---------------------------------------------------------------------------
//------------------------------------------------------------------------------------------
Route::resource('ticket-solutions', TicketSolutionController::class)->middleware('auth');
Route::post('ticket-solutions/update-with-media/{ticket_solution}', [TicketSolutionController::class, 'updateWithMedia'])->name('ticket-solutions.update-with-media')->middleware('auth');
Route::get('ticket-solutions-fetch-all-solutions/{ticket}', [TicketSolutionController::class, 'fetchSolutions'])->name('ticket-solutions.fetch-solutions')->middleware('auth');


//Tickets-histories routes---------------------------------------------------------------------------
//------------------------------------------------------------------------------------------
Route::resource('ticket-histories', TicketHistoryController::class)->middleware('auth');

//users routes---------------------------------------------------------------------------
//------------------------------------------------------------------------------------------
Route::resource('users', UserController::class)->middleware('auth');
Route::post('users/update-with-media/{user}', [UserController::class, 'updateWithMedia'])->name('users.update-with-media')->middleware('auth');
Route::post('users/massive-delete', [UserController::class, 'massiveDelete'])->name('users.massive-delete');
Route::get('users-get-matches/{query}', [UserController::class, 'getMatches'])->name('users.get-matches');
Route::get('users-get-notifications', [UserController::class, 'getNotifications'])->middleware('auth')->name('users.get-notifications');
Route::post('users-read-notifications', [UserController::class, 'readNotifications'])->middleware('auth')->name('users.read-user-notifications');
Route::post('users-delete-notifications', [UserController::class, 'deleteNotifications'])->middleware('auth')->name('users.delete-user-notifications');
Route::get('users-get-all', [UserController::class, 'getAll'])->name('users.get-all');
Route::get('users-get-by-page/{currentPage}', [UserController::class, 'getItemsByPage'])->name('users.get-by-page')->middleware('auth');
Route::put('users-reset-password/{user}', [UserController::class, 'resetPassword'])->name('users.reset-password')->middleware('auth');


//settings routes---------------------------------------------------------------------------
//------------------------------------------------------------------------------------------
Route::resource('settings', SettingController::class)->middleware('auth');
Route::get('role-permission', [SettingController::class, 'index'])->middleware('auth')->name('settings.role-permission.index');
Route::put('role-permission/{role}/edit-role', [SettingController::class, 'updateRole'])->middleware('auth')->name('settings.role-permission.update-role');
Route::post('role-permission/store-role', [SettingController::class, 'storeRole'])->middleware('auth')->name('settings.role-permission.store-role');
Route::delete('role-permission/{role}/destroy-role', [SettingController::class, 'deleteRole'])->middleware('auth')->name('settings.role-permission.delete-role');
Route::put('role-permission/{permission}/edit-permission', [SettingController::class, 'updatePermission'])->middleware('auth')->name('settings.role-permission.update-permission');
Route::post('role-permission/store-permission', [SettingController::class, 'storePermission'])->middleware('auth')->name('settings.role-permission.store-permission');
Route::delete('role-permission/{permission}/destroy-permission', [SettingController::class, 'deletePermission'])->middleware('auth')->name('settings.role-permission.delete-permission');
Route::post('role-permission/roles-massive-delete', [SettingController::class, 'rolesMassiveDelete'])->name('settings.role-permission.roles-massive-delete');
Route::post('role-permission/permissions-massive-delete', [SettingController::class, 'permissionsMassiveDelete'])->name('settings.role-permission.permissions-massive-delete');
Route::post('categories/massive-delete', [SettingController::class, 'categoriesMassiveDelete'])->name('settings.categories.massive-delete');
Route::get('categories/get-all', [SettingController::class, 'getAllCategories'])->middleware('auth')->name('settings.categories.get-all');
Route::post('categories/store', [SettingController::class, 'storeCategory'])->middleware('auth')->name('settings.categories.store');


//categories routes---------------------------------------------------------------------------
//------------------------------------------------------------------------------------------
Route::resource('categories', CategoryController::class)->middleware('auth');


//production routes---------------------------------------------------------------------------
//------------------------------------------------------------------------------------------
Route::resource('productions', ProductionController::class)->middleware('auth');
Route::get('productions-get-by-page', [ProductionController::class, 'getByPage'])->name('productions.get-by-page')->middleware('auth');
Route::put('productions-update-machine/{production}', [ProductionController::class, 'updateMachine'])->name('productions.update-machine')->middleware('auth');
Route::put('productions-update-station/{production}', [ProductionController::class, 'updateStation'])->name('productions.update-station')->middleware('auth');


//products routes---------------------------------------------------------------------------
//------------------------------------------------------------------------------------------
Route::resource('products', ProductController::class)->middleware('auth');
Route::get('products-get-all', [ProductController::class, 'getAll'])->name('products.get-all')->middleware('auth');
Route::get('products-clone/{product}', [ProductController::class, 'clone'])->name('products.clone')->middleware('auth');
Route::post('products/get-matches', [ProductController::class, 'getMatches'])->name('products.get-matches');


//machines routes---------------------------------------------------------------------------
//------------------------------------------------------------------------------------------
Route::resource('machines', MachineController::class)->middleware('auth');
Route::get('machines-get-all', [MachineController::class, 'getAll'])->name('machines.get-all')->middleware('auth');


//comments routes---------------------------------------------------------------------------
//------------------------------------------------------------------------------------------
Route::resource('comments', CommentController::class)->middleware('auth');
