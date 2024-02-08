<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketHistoryController;
use App\Http\Controllers\TicketSolutionController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
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


//settings routes---------------------------------------------------------------------------
//------------------------------------------------------------------------------------------
Route::resource('settings', SettingController::class)->middleware('auth');


//categories routes---------------------------------------------------------------------------
//------------------------------------------------------------------------------------------
Route::resource('categories', CategoryController::class)->middleware('auth');



//comments routes---------------------------------------------------------------------------
//------------------------------------------------------------------------------------------
Route::resource('comments', CommentController::class)->middleware('auth');

