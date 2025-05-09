<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClientController;
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
use App\Models\Product;
use App\Models\Production;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;

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
Route::get('productions-export-excel', [ProductionController::class, 'exportExcel'])->name('productions.export-excel')->middleware('auth');
Route::post('productions-import-excel', [ProductionController::class, 'importExcel'])->name('productions.import-excel')->middleware('auth');
Route::put('productions-update-machine/{production}', [ProductionController::class, 'updateMachine'])->name('productions.update-machine')->middleware('auth');
Route::put('productions-update-station/{production}', [ProductionController::class, 'updateStation'])->name('productions.update-station')->middleware('auth');
Route::post('productions-clone/{production}', [ProductionController::class, 'clone'])->name('productions.clone')->middleware('auth');
Route::post('productions-close/{production}', [ProductionController::class, 'close'])->name('productions.close')->middleware('auth');
Route::post('productions-quality-release/{production}', [ProductionController::class, 'qualityRelease'])->name('productions.quality-release')->middleware('auth');
Route::post('productions-add-partial/{production}', [ProductionController::class, 'addPartial'])->name('productions.add-partial')->middleware('auth');
Route::get('productions-hoja-viajera/{production}', [ProductionController::class, 'hojaViajera'])->name('productions.hoja-viajera')->middleware('auth');



//products routes---------------------------------------------------------------------------
//------------------------------------------------------------------------------------------
Route::resource('products', ProductController::class)->middleware('auth');
Route::get('products-get-all', [ProductController::class, 'getAll'])->name('products.get-all')->middleware('auth');
Route::get('products-clone/{product}', [ProductController::class, 'clone'])->name('products.clone')->middleware('auth');
Route::post('products/get-matches', [ProductController::class, 'getMatches'])->name('products.get-matches');
Route::post('products/update-with-media/{product}', [ProductController::class, 'updateWithMedia'])->name('products.update-with-media')->middleware('auth');
Route::delete('products/{id}/media/{fileId}', [ProductController::class, 'deleteFile'])->name('products.delete-file');


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


// use Carbon\Carbon;
// Route::get('/import-productions', function () {
//     set_time_limit(300); // Por si acaso
//     $filePath = public_path('productions.csv');

//     if (!file_exists($filePath)) {
//         return response()->json(['error' => 'El archivo productions.csv no existe en la carpeta public'], 404);
//     }

//     // Leer el archivo CSV
//     $file = fopen($filePath, 'r');
//     $header = fgetcsv($file);

//     if (empty($header)) {
//         fclose($file);
//         return response()->json(['error' => 'El archivo CSV está vacío o no tiene encabezados'], 400);
//     }

//     $importedCount = 0;
//     $errors = [];
//     $lineNumber = 1;

//     while (($row = fgetcsv($file)) !== false) {
//         $lineNumber++;
//         $data = array_combine($header, $row);

//         try {
//             // // 1. Buscar el ID del producto por nombre
//             // $product = Product::
//             //             where('id', $data['p_name'])
//             //             ->first();

//             // if (!$product) {
//             //     throw new \Exception("Producto '{$data['p_name']}' no encontrado");
//             // }

//             // 2. Procesar materials (array o null)
//             $materials = !empty(trim($data['materials'])) ? [trim($data['materials'])] : null;

//             // 3. Procesar notes con rn
//             $notes = trim($data['notes']);
//             if (!empty(trim($data['rn']))) {
//                 $notes .= " (" . trim($data['rn']) . "). Producto: " . $data['p_name'];
//             }

//             // 4. Procesar ts (reemplazar coma por punto)
//             $ts = str_replace(',', '.', trim($data['ts']));

//             // 5. Procesar fechas
//             $startDate = Carbon::createFromFormat('Y/m/d', trim($data['start_date']))->format('Y-m-d');

//             $estimatedDate = !empty(trim($data['estimated_date'])) ?
//                 Carbon::createFromFormat('n/j/Y', trim($data['estimated_date']))->format('Y-m-d') :
//                 null;

//             $estimatedPackageDate = !empty(trim($data['estimated_package_date'])) ?
//                 Carbon::createFromFormat('n/j/Y', trim($data['estimated_package_date']))->format('Y-m-d') :
//                 null;

//             // 6. Procesar partials (array JSON o null)
//             $partials = null;
//             $partial1 = !empty(trim($data['partial1'])) ? (float)trim($data['partial1']) : 0;

//             if ($partial1 > 0) {
//                 $partials = [
//                     [
//                         'quantity' => $partial1,
//                         'date' => $startDate
//                     ]
//                 ];

//                 // Agregar partialn si existe
//                 $partialn = !empty(trim($data['partialn'])) ? (float)trim($data['partialn']) : 0;
//                 if ($partialn > 0) {
//                     $partials[] = [
//                         'quantity' => $partialn,
//                         'date' => $startDate
//                     ];
//                 }
//             }

//             // Insertar en la base de datos
//             Production::create([
//                 'product_id' => 1,
//                 'materials' => $materials ? [$data['materials']] : null,
//                 'notes' => $notes,
//                 'season' => $data['season'] ?? null,
//                 'folio' => $data['folio'],
//                 'client' => $data['client'],
//                 'station' => trim($data['station']),
//                 'material' => $data['material'] ?? null,
//                 'quantity' => (int) str_replace(['.', ','], ['', ''], $data['quantity']),
//                 'dfi' => $data['dfi'],
//                 'close_production_date' => $data['close_production_date'] ? explode(' ', $data['close_production_date'])[0] : null,
//                 'close_quantity' => $data['close_quantity'] ? (int) $data['close_quantity'] : 0.0,
//                 'finish_date' => $data['finish_date'] ? explode(' ', $data['finish_date'])[0] : null,
//                 'current_quantity' => $data['current_quantity'] == "" ? 0.0 : (int) $data['current_quantity'],
//                 'ts' => $ts,
//                 'start_date' => $startDate,
//                 'estimated_date' => $estimatedDate,
//                 'estimated_package_date' => $estimatedPackageDate,
//                 'partials' => $partials ? json_encode($partials) : null,
//                 'modified_user_id' => $data['modified_user_id'],
//                 'user_id' => $data['modified_user_id'],
//                 'created_at' => $startDate,
//                 'updated_at' => $data['updated_at'] ?? now(),
//                 'machine_id' => (int) trim($data['machine_id']),
//             ]);

//             $importedCount++;
//         } catch (\Exception $e) {
//             $errors[] = [
//                 'line' => $lineNumber,
//                 'machine_id' => (int) $data['machine_id'],
//                 'error' => $e->getMessage(),
//                 // 'data' => $data
//             ];
//         }
//     }

//     fclose($file);

//     return response()->json([
//         'message' => 'Importación completada',
//         'imported' => $importedCount,
//         'errors' => $errors,
//         'error_count' => count($errors)
//     ]);
// });
