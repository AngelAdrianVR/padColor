<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChangeRequestController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CustomsAgentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\ProductionReportController;
use App\Http\Controllers\ProductSheetStructureController;
use App\Http\Controllers\RawMaterialController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketHistoryController;
use App\Http\Controllers\TicketSolutionController;
use App\Http\Controllers\UserController;
use App\Models\Product;
use App\Models\Production;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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
Route::get('productions-dashboard', [ProductionController::class, 'dashboard'])->name('productions.dashboard')->middleware('auth');

Route::prefix('productions-report')->name('productions.')->middleware('auth')->group(function () {
    Route::get('/', [ProductionController::class, 'report'])->name('report');
    Route::get('/get-data', [ProductionReportController::class, 'getReportData'])->name('get-report-data');
    Route::get('/printable', [ProductionReportController::class, 'printableReport'])->name('report.printable');
});

Route::get('productions-get-by-page', [ProductionController::class, 'getByPage'])->name('productions.get-by-page')->middleware('auth');
Route::get('productions-export-report-excel', [ProductionController::class, 'exportExcelReport'])->name('productions.export-report-excel')->middleware('auth');
Route::get('productions-export-excel', [ProductionController::class, 'exportExcel'])->name('productions.export-excel')->middleware('auth');
Route::post('productions-import-excel', [ProductionController::class, 'importExcel'])->name('productions.import-excel')->middleware('auth');
Route::put('productions-update-machine/{production}', [ProductionController::class, 'updateMachine'])->name('productions.update-machine')->middleware('auth');
Route::post('productions-clone/{production}', [ProductionController::class, 'clone'])->name('productions.clone')->middleware('auth');
Route::get('productions-hoja-viajera/{production}', [ProductionController::class, 'hojaViajera'])->name('productions.hoja-viajera')->middleware('auth');
Route::post('productions-backfill', [ProductionController::class, 'backfillStationTimes'])->name('productions.backfill');

// --- STATION TIME TRACKING ---
Route::prefix('productions/{production}/station-process')->name('productions.station-process.')->middleware('auth')->group(function () {
    Route::post('/start', [ProductionController::class, 'startStationProcess'])->name('start');
    Route::post('/pause', [ProductionController::class, 'pauseStationProcess'])->name('pause');
    Route::post('/resume', [ProductionController::class, 'resumeStationProcess'])->name('resume');
    Route::post('/finish-and-move', [ProductionController::class, 'finishAndMoveStation'])->name('finishAndMove');
    Route::post('/skip-and-move', [ProductionController::class, 'skipAndMoveStation'])->name('skipAndMove');
    Route::post('/register-delivery', [ProductionController::class, 'registerDelivery'])->name('register-delivery');
});
Route::post('productions-return-station/{production}', [ProductionController::class, 'returnStation'])->name('productions.return-station')->middleware('auth');



//products routes---------------------------------------------------------------------------
//------------------------------------------------------------------------------------------
Route::resource('products', ProductController::class)->middleware('auth');
Route::get('products-get-all', [ProductController::class, 'getAll'])->name('products.get-all')->middleware('auth');
Route::get('products-clone/{product}', [ProductController::class, 'clone'])->name('products.clone')->middleware('auth');
Route::get('products/get-matches/{query}', [ProductController::class, 'getMatches'])->name('products.get-matches');
Route::get('products/get-match-list/{query}', [ProductController::class, 'getMatch'])->name('products.get-match'); // para crear una produccion
Route::post('products/update-with-media/{product}', [ProductController::class, 'updateWithMedia'])->name('products.update-with-media')->middleware('auth');
Route::delete('products/{id}/media/{fileId}', [ProductController::class, 'deleteFile'])->name('products.delete-file');
Route::post('/products/{product}/sheet', [ProductController::class, 'updateSheetData'])->name('products.sheet.update');
Route::get('/products/{product}/export-sheet', [ProductController::class, 'exportSheet'])->name('products.export-sheet');


//machines routes---------------------------------------------------------------------------
//------------------------------------------------------------------------------------------
Route::resource('machines', MachineController::class)->middleware('auth');
Route::get('machines-get-all', [MachineController::class, 'getAll'])->name('machines.get-all')->middleware('auth');
Route::post('machines/update-with-media/{machine}', [MachineController::class, 'updateWithMedia'])->name('machines.update-with-media')->middleware('auth');
Route::post('machines/get-matches', [MachineController::class, 'getMatches'])->name('machines.get-matches');


//clients routes---------------------------------------------------------------------------
//------------------------------------------------------------------------------------------
Route::resource('clients', ClientController::class)->middleware('auth');
Route::get('clients-get-all', [ClientController::class, 'getAll'])->name('clients.get-all')->middleware('auth');


//comments routes---------------------------------------------------------------------------
//------------------------------------------------------------------------------------------
Route::resource('comments', CommentController::class)->middleware('auth');


//artisan commands -------------------
Route::get('/clear-all', function () {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    return 'cleared.';
});

// Route::get('/notifications-management', [NotificationController::class, 'index'])
//     ->middleware(['auth', 'verified'])
//     ->name('notifications.management');

// Rutas para manejar las acciones de suscripción
Route::post('/subscriptions/toggle-user', [NotificationController::class, 'toggleUserSubscription'])
    ->middleware(['auth', 'verified'])
    ->name('subscriptions.user.toggle');

Route::post('/subscriptions/add-external', [NotificationController::class, 'addExternalSubscription'])
    ->middleware(['auth', 'verified'])
    ->name('subscriptions.external.add');

Route::delete('/subscriptions/remove-external', [NotificationController::class, 'removeExternalSubscription'])
    ->middleware(['auth', 'verified'])
    ->name('subscriptions.external.remove');

Route::resource('suppliers', SupplierController::class);

Route::resource('raw-materials', RawMaterialController::class);

Route::resource('customs-agents', CustomsAgentController::class);

// ===================================================================
// RUTAS DEL MÓDULO DE IMPORTACIONES
// ===================================================================
Route::middleware(['auth', 'verified'])->group(function () {
    // Ruta para el reporte de Excel
    // Se define antes del resource para que no choque con la ruta "show"
    Route::get('imports/export', [ImportController::class, 'export'])->name('imports.export');

    // --- ACCIONES DE LAS PESTAÑAS (Detalle de Importación) ---

    // Para Materia Prima (adjuntar y quitar de la importación)
    Route::post('imports/{import}/raw-materials', [ImportController::class, 'attachRawMaterial'])->name('imports.raw-materials.attach');
    Route::delete('imports/{import}/raw-materials/{rawMaterial}', [ImportController::class, 'detachRawMaterial'])->name('imports.raw-materials.detach');

    // Para Costos
    Route::post('imports/{import}/costs', [ImportController::class, 'storeCost'])->name('imports.costs.store');
    Route::delete('costs/{cost}', [ImportController::class, 'destroyCost'])->name('imports.costs.destroy');

    // Para Pagos
    Route::post('imports/{import}/payments', [ImportController::class, 'storePayment'])->name('imports.payments.store');
    Route::delete('payments/{payment}', [ImportController::class, 'destroyPayment'])->name('imports.payments.destroy');

    // Para Documentos (Media Library)
    Route::post('imports/{import}/documents', [ImportController::class, 'storeDocument'])->name('imports.documents.store');
    // Usaremos el ID del registro de media para borrarlo
    Route::delete('documents/{media}', [ImportController::class, 'destroyDocument'])->name('imports.documents.destroy');

    // --- ACCIONES PRINCIPALES ---

    // Ruta específica para actualizar el estado del Kanban (Drag and Drop)
    Route::patch('imports/{import}/status', [ImportController::class, 'updateStatus'])->name('imports.status.update');

    // Controlador de Recursos para el CRUD principal de Importaciones
    // Gestiona: index, create, store, show, edit, update, destroy
    Route::resource('imports', ImportController::class);

    // rutas de change requests
    Route::post('/change-requests/{changeRequest}/decide', [ChangeRequestController::class, 'decide'])->name('change-requests.decide');
});


Route::middleware(['auth:sanctum', 'verified'])
    ->prefix('product-sheet-structure')
    ->name('product-sheet-structure.')
    ->group(function () {
        // La página principal para gestionar la estructura
        Route::get('/', [ProductSheetStructureController::class, 'index'])->name('index');

        // Rutas para gestionar Pestañas (Tabs)
        Route::post('/tabs', [ProductSheetStructureController::class, 'storeTab'])->name('tabs.store');
        Route::put('/tabs/{tab}', [ProductSheetStructureController::class, 'updateTab'])->name('tabs.update');
        Route::delete('/tabs/{tab}', [ProductSheetStructureController::class, 'destroyTab'])->name('tabs.destroy');

        // Rutas para gestionar Campos (Fields)
        Route::post('/fields', [ProductSheetStructureController::class, 'storeField'])->name('fields.store');
        Route::put('/fields/{field}', [ProductSheetStructureController::class, 'updateField'])->name('fields.update');
        Route::delete('/fields/{field}', [ProductSheetStructureController::class, 'destroyField'])->name('fields.destroy');

        // Rutas para gestionar Opciones (Options)
        Route::post('/options', [ProductSheetStructureController::class, 'storeOption'])->name('options.store');
        Route::put('/options/{option}', [ProductSheetStructureController::class, 'updateOption'])->name('options.update');
        Route::delete('/options/{option}', [ProductSheetStructureController::class, 'destroyOption'])->name('options.destroy');
    });
