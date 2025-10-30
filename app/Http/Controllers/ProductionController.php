<?php

namespace App\Http\Controllers;

use App\Exports\ProductionReportExport;
use App\Models\Production;
use Illuminate\Http\Request;
use App\Exports\ProductionsExport;
use App\Imports\ProductionsImport;
use App\Models\NotificationEvent;
use App\Models\User;
use App\Notifications\ProductionForwardedNotification;
use App\Notifications\ProductionReturnedNotification;
use App\Traits\NotifiesViaEvents;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Auth; // Import añadido
use Maatwebsite\Excel\Facades\Excel;

class ProductionController extends Controller
{
    use NotifiesViaEvents;

    public function dashboard()
    {
        $productions = Production::latest('folio')->get(['folio', 'station']);

        return inertia('Production/Dashboard', compact('productions'));
    }

    public function index()
    {
        return inertia('Production/Index');
    }

    public function report()
    {
        return inertia('Production/Report');
    }

    public function create()
    {
        $nextProduction = Production::latest('folio')->first();
        $nextProduction = $nextProduction ? $nextProduction->folio + 1 : 1;

        return inertia('Production/Create', compact('nextProduction'));
    }

   public function store(Request $request)
    {
        $validated = $request->validate([
            'folio' => 'required|numeric|unique:productions',
            'type' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'estimated_date' => 'required|date',
            'client' => 'required|string|max:255',
            'changes' => 'required|numeric|min:0',
            'product_id' => 'required|min:1|integer|exists:products,id',
            'quantity' => 'required|numeric|min:1',
            'materials' => 'nullable|string|max:255',
            'station' => 'required|string|max:255',
            'machine_id' => 'required|min:1|integer|exists:machines,id',
            'notes' => 'nullable|string|max:300',
            'dfi' => 'nullable|string|max:255',
            'material' => 'nullable|string|max:255',
            'width' => 'nullable|string|max:255', // Tu validación original (string)
            'gauge' => 'nullable|string|max:255',
            'large' => 'nullable|string|max:255', // Tu validación original (string)
            'look' => 'nullable|string|max:255',
            'faces' => 'nullable|numeric|min:0',
            'pps' => 'nullable|numeric|min:0',
            'adjust' => 'nullable|numeric|min:0',
            'sheets' => 'nullable|numeric|min:0',
            'ha' => 'nullable|numeric|min:0',
            'pf' => 'required|numeric|min:0',
            'ts' => 'nullable|numeric|min:0',
            'ps' => 'nullable|numeric|min:0',
            'tps' => 'nullable|numeric|min:0',
            'varnish_type' => 'required_if_accepted:has_varnish',
        ], [
            'varnish_type.required_if_accepted' => 'El tipo de barniz es requerido.',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['materials'] = [$validated['materials']];

        // --- LÓGICA DE COMPONENTES AÑADIDA ---
        // Inicializa la cantidad no asignada
        $validated['unassigned_quantity'] = $validated['quantity'];
        // --- FIN LÓGICA DE COMPONENTES ---

        // Initialize the station_times record for the first station.
        $now = now();
        $validated['station_times'] = [
            $this->createNewStationTimeRecord($validated['station'], $now, auth()->id(), 'Creación de la orden de producción')
        ];

        $production = Production::create($validated + ['modified_user_id' => auth()->id()]);

        // Notify if it goes to 'Material pendiente'
        if ($validated['station'] == 'Material pendiente') {
            $notificationInstance = new ProductionForwardedNotification(
                $production,
                'Material pendiente',
                $production->quantity
            );
            $this->sendNotification('production.forwarded.pendent_material', $notificationInstance);
        }

        return to_route('productions.index');
    }

    public function show(Production $production)
    {
        //
    }

    public function edit(Production $production)
    {
        $product = $production->product;
        return inertia('Production/Edit', compact('production', 'product'));
    }

    public function update(Request $request, Production $production)
    {
        $validated = $request->validate([
            'folio' => 'required|numeric|unique:productions,folio,' . $production->id,
            'type' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'estimated_date' => 'required|date',
            'client' => 'required|string|max:255',
            'changes' => 'required|numeric|min:0',
            'product_id' => 'required|min:1|integer|exists:products,id',
            'quantity' => 'required|numeric|min:1',
            'materials' => 'nullable|string|max:255',
            'station' => 'required|string|max:255',
            'machine_id' => 'required|min:1|integer|exists:machines,id',
            'notes' => 'nullable|string|max:300',
            'dfi' => 'nullable|string|max:255',
            'material' => 'nullable|string|max:255',
            'width' => 'nullable|numeric|min:0', // Tu validación (numeric)
            'gauge' => 'nullable|string|max:255',
            'large' => 'nullable|numeric|min:0', // Tu validación (numeric)
            'look' => 'nullable|string|max:255',
            'faces' => 'nullable|numeric|min:0',
            'pps' => 'nullable|numeric|min:0',
            'adjust' => 'nullable|numeric|min:0',
            'sheets' => 'nullable|numeric|min:0',
            'ha' => 'nullable|numeric|min:0',
            // 'pf' => 'nullable|numeric|min:0', // Estaba duplicado en tu validación original
            'ts' => 'nullable|numeric|min:0',
            'pf' => 'required|numeric|min:0',
            'tps' => 'nullable|numeric|min:0',
            'varnish_type' => 'required_if_accepted:has_varnish',
        ], [
            'varnish_type.required_if_accepted' => 'El tipo de barniz es requerido.',
        ]);

        $validated['materials'] = [$validated['materials']];

        $production->update($validated + ['modified_user_id' => auth()->id()]);

        // --- LÓGICA DE COMPONENTES AÑADIDA ---
        // Recalcular 'unassigned_quantity' si la cantidad total cambia y NO es un hijo
        if ($request->has('quantity') && !$production->parent_production_id) {
            
            // Obtenemos la cantidad MÁXIMA de "juego" ya asignada a los hijos
            $max_child_qty = $production->children()->max('part_quantity') ?? 0;
            
            // La nueva cantidad no asignada es el total nuevo MENOS lo ya asignado
            $new_unassigned = $validated['quantity'] - $max_child_qty;

            // Nos aseguramos de que no sea negativo
            $production->unassigned_quantity = max(0, $new_unassigned);
            
            // Si la nueva cantidad total es CERO y no tiene hijos, la no asignada es 0
            if ($validated['quantity'] <= 0 && $max_child_qty == 0) {
                    $production->unassigned_quantity = 0;
            }
            
            $production->save();
        }
        // --- FIN LÓGICA DE COMPONENTES ---

        return to_route('productions.index');
    }

    public function updateMachine(Request $request, Production $production)
    {
        $production->machine_id = $request->machine_id;
        $production->modified_user_id = auth()->id();
        $production->save();
    }

    public function destroy(Production $production)
    {
        // Opcional: Borrar hijos si se borra el padre
        if (!$production->parent_production_id && $production->children()->count() > 0) {
            $production->children()->delete();
        }
        
        $production->delete();
    }

    public function getByPage()
    {
        $page = request('page', 1);
        $search = request('search', null);

        $perPage = 30;
        $offset = ($page - 1) * $perPage;

        // --- MERGE ---
        // Tu query base
        $query = Production::with(['user', 'product', 'machine'])->latest('id');

        // --- LÓGICA DE COMPONENTES AÑADIDA ---
        // Filtro para mostrar solo Padres (no divididos) e Hijos (no terminados)
        $query->where(function ($q) {
            $q->where(function ($sub) {
                $sub->whereNull('parent_production_id')
                    ->where('station', '!=', 'Dividida'); // 'Dividida' es el nuevo estado para padres inactivos
            })
            ->orWhere(function ($sub) {
                $sub->whereNotNull('parent_production_id')
                    ->where('station', '!=', 'Terminadas');
            });
        });
        // --- FIN LÓGICA DE COMPONENTES ---

        // Tu lógica de permisos de estación
        $user = auth()->user();
        $permissions = $user->getAllPermissions()
            ->filter(fn ($permission) => str_starts_with($permission->name, 'Ver en estacion'))
            ->map(fn ($permission) => str_replace('Ver en estacion ', '', $permission->name))
            ->toArray();

        if (!empty($permissions)) {
            $query->whereIn('station', $permissions);
        }
        
        if ($search) {
            // --- MERGE (Search) ---
            $query->where(function ($q) use ($search) {
                $q->where('folio', 'like', "%{$search}%")
                    ->orWhere('part_identifier', 'like', "%{$search}%") // Buscar por nombre de componente
                    ->orWhere('client', 'like', "%{$search}%")
                    ->orWhere('station', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('season', 'like', "%{$search}%");
                    });
            });
            // --- FIN MERGE ---
        }

        // --- Conteo correcto (antes de offset/limit) ---
        $total = $query->count();

        $items = $query->offset($offset)
            ->limit($perPage)
            ->get();
        // --- FIN Conteo ---

        return response()->json(compact('items', 'total'));
    }

    public function clone(Production $production)
    {
        $lastProductionFolio = Production::latest('folio')->first()?->folio;
        $newProduction = $production->replicate([
            'finish_date', 'close_production_date', 'quality_released_date', 'estimated_package_date',
            'estimated_date', 'production_close_type', 'quality_quantity', 'current_quantity',
            'close_quantity', 'scrap_quantity', 'production_scrap', 'quality_scrap', 'inspection_scrap',
            'shortage_quantity', 'production_shortage', 'quality_shortage', 'inspection_shortage',
            'close_production_notes', 'inspection_notes', 'quality_notes', 'partials', 'start_date',
            'packing_close_type', 'packing_notes', 'packing_scrap', 'packing_shortage', 'packing_partials',
            'packing_received_quantity', 'packing_received_date', 'packing_finished_date', 'station_times',
            // --- LÓGICA DE COMPONENTES AÑADIDA ---
            'parent_production_id', 'part_identifier', 'part_quantity', 'unassigned_quantity'
            // --- FIN LÓGICA DE COMPONENTES ---
        ]);
        $newProduction->folio = $lastProductionFolio + 1;
        $newProduction->type = 'Repetido';
        $newProduction->station = 'Material pendiente';
        $newProduction->start_date = now();

        // --- LÓGICA DE COMPONENTES AÑADIDA ---
        $newProduction->unassigned_quantity = $newProduction->quantity; // Reiniciar cantidad no asignada
        // --- FIN LÓGICA DE COMPONENTES ---

        // Initialize the station_times record for the cloned production
        $now = now();
        $newProduction->station_times = [
            $this->createNewStationTimeRecord(
                'Material pendiente',
                $now,
                auth()->id(),
                'Registro inicial de orden clonada.'
            )
        ];

        $newProduction->save();

        return to_route('productions.edit', ['production' => $newProduction->id]);
    }

    public function returnStation(Request $request, Production $production)
    {
        $validated = $request->validate([
            'next_station' => 'required|string',
            'quantity' => 'required|numeric|min:0',
            'reason' => 'required|string|max:255',
        ]);

        $old_station = $production->station;
        $now = now();
        $user_id = auth()->id();
        
        $this->finalizeCurrentStation($production, $now, $user_id, "Regresado a {$validated['next_station']}");

        $returns = $production->returns ?? [];
        $returns[] = [
            'old_station' => $old_station,
            'new_station' => $validated['next_station'],
            'date' => $now,
            'quantity' => $validated['quantity'],
            'reason' => $validated['reason'],
            'user' => auth()->user()->name,
        ];
        $production->returns = $returns;
        
        // Add new station record
        $station_times = $production->station_times;
        $station_times[] = $this->createNewStationTimeRecord($validated['next_station'], $now, $user_id);
        $production->station_times = $station_times;

        $production->station = $validated['next_station'];
        $production->modified_user_id = $user_id;
        $production->save();
    }
    
    // --- NEW UNIFIED METHOD FOR DELIVERIES ---
    public function registerDelivery(Request $request, Production $production)
    {
        $validated = $request->validate([
            'context' => 'required|string|in:inspection,packing',
            'type' => 'required|string|in:Única,Parcialidades',
            'quantity' => 'required|numeric|min:0',
            'date' => 'required|date',
            'notes' => 'nullable|string',
            'is_last_delivery' => 'boolean',
            'scrap_quantity' => 'nullable|numeric|min:0',
            'shortage_quantity' => 'nullable|numeric|min:0',
        ]);

        $production->modified_user_id = auth()->id();

        if ($validated['context'] === 'inspection') {
            $this->handleInspectionDelivery($production, $validated);
        } elseif ($validated['context'] === 'packing') {
            $this->handlePackingDelivery($production, $validated);
        }

        $production->save();
        return back();
    }

    private function handleInspectionDelivery(Production &$production, array $data)
    {
        $production->production_close_type = $data['type'];
        $production->inspection_notes = $data['notes'];
        
        if ($data['type'] === 'Única') {
            $production->current_quantity = $data['quantity'];
            $production->inspection_scrap = $data['scrap_quantity'] ?? 0;
            $production->inspection_shortage = $data['shortage_quantity'] ?? 0;
            $production->partials = [['quantity' => $data['quantity'], 'date' => $data['date'], 'notes' => $data['notes']]];
        } else { // Parcialidades
            $partials = $production->partials ?? [];
            $partials[] = [
                'quantity' => $data['quantity'], 'date' => $data['date'],
                'notes' => $data['notes'], 'is_last_delivery' => $data['is_last_delivery']
            ];
            $production->partials = $partials;
            $production->current_quantity += $data['quantity'];
        }

        $isLastDelivery = $data['type'] === 'Única' || $data['is_last_delivery'];
        if ($isLastDelivery) {
            if($data['is_last_delivery']){
                 $production->inspection_scrap += $data['scrap_quantity'] ?? 0;
                 $production->inspection_shortage += $data['shortage_quantity'] ?? 0;
            }
            $production->scrap_quantity = $production->production_scrap + $production->quality_scrap + $production->inspection_scrap;
            $production->shortage_quantity = $production->production_shortage + $production->quality_shortage + $production->inspection_shortage;
            
            // --- TIME TRACKING UPDATE ---
            $now = Carbon::parse($data['date']);
            $user_id = auth()->id();
            
            $current_record = $this->getCurrentStationRecord($production);
            $mode = ($current_record && $current_record['status'] === 'En espera') ? 'skip' : 'finish';

            $this->finalizeCurrentStation($production, $now, $user_id, "Entrega final registrada. Movido a Terminadas.", $mode);
            
            $station_times = $production->station_times;
            $station_times[] = $this->createFinalizedStationTimeRecord('Terminadas', $now, $user_id, 'Producción finalizada tras última entrega de inspección.');
            $production->station_times = $station_times;
            // --- END TIME TRACKING UPDATE ---

            $production->station = 'Terminadas';
            $production->finish_date = $data['date'];
        }
    }

    private function handlePackingDelivery(Production &$production, array $data)
    {
        $production->packing_close_type = $data['type'];
        $production->packing_notes = $data['notes'];

        if ($data['type'] === 'Única') {
            $production->current_quantity = $data['quantity'];
            $production->packing_partials = [['quantity' => $data['quantity'], 'date' => $data['date'], 'notes' => $data['notes']]];
        } else { // Parcialidades
            $partials = $production->packing_partials ?? [];
            $partials[] = [
                'quantity' => $data['quantity'], 'date' => $data['date'],
                'notes' => $data['notes'], 'is_last_delivery' => $data['is_last_delivery']
            ];
            $production->packing_partials = $partials;
            $production->current_quantity += $data['quantity'];
        }

        $isLastDelivery = $data['type'] === 'Única' || $data['is_last_delivery'] || $production->current_quantity >= $production->packing_received_quantity;
        if ($isLastDelivery) {
            // --- TIME TRACKING UPDATE ---
            $now = Carbon::parse($data['date']);
            $user_id = auth()->id();

            $current_record = $this->getCurrentStationRecord($production);
            $mode = ($current_record && $current_record['status'] === 'En espera') ? 'skip' : 'finish';

            $this->finalizeCurrentStation($production, $now, $user_id, "Entrega final registrada. Movido a Empaques terminado.", $mode);
           
            $station_times = $production->station_times;
            $station_times[] = $this->createFinalizedStationTimeRecord('Empaques terminado', $now, $user_id, 'Proceso de empaque finalizado.');
            $production->station_times = $station_times;
            // --- END TIME TRACKING UPDATE ---

            $production->station = 'Empaques terminado';
            $production->packing_finished_date = $data['date'];
        }
    }


    public function exportExcel()
    {
        $startDate = request('startDate');
        $endDate = request('endDate');
        $season = request('season');
        $station = request('station');

        $productions = Production::with(['user', 'product', 'machine', 'modifiedUser'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->when($season !== 'Todas', fn ($q) => $q->whereHas('product', fn ($q) => $q->where('season', $season)))
            ->when($station !== 'Todos', fn ($q) => $q->where('station', $station))
            ->get();

        return Excel::download(new ProductionsExport($productions), 'producciones.xlsx');
    }

    public function exportExcelReport()
    {
        $folio = request('folio');
        $productions = Production::with(['user', 'product', 'machine', 'modifiedUser'])->where('folio', $folio)->get();
        return Excel::download(new ProductionReportExport($productions), 'reporte_produccion.xlsx');
    }

    public function importExcel(Request $request)
    {
        Excel::import(new ProductionsImport, $request->file('excel')[0]);
    }

    public function hojaViajera(Production $production)
    {
        $production->load(['product', 'machine', 'modifiedUser']);
        return inertia('Production/HojaViajera', compact('production'));
    }

    public function backfillStationTimes()
    {
        $updatedCount = 0;
        $adminUserId = auth()->id(); 
        Production::where(fn ($q) => $q->whereNull('station_times')->orWhere('station_times', '=', '[]'))
            ->chunk(200, function ($productions) use (&$updatedCount, $adminUserId) {
            foreach ($productions as $production) {
                $entryDate = $production->updated_at ?? $production->created_at;
                $finalStations = ['Terminadas', 'Empaques terminado'];
                
                $station_times = [];
                if (in_array($production->station, $finalStations)) {
                    $station_times[] = $this->createFinalizedStationTimeRecord($production->station, $entryDate, $adminUserId, 'Registro final creado por script.');
                } else {
                    $station_times[] = $this->createNewStationTimeRecord($production->station, $entryDate, $adminUserId, 'Registro inicial creado por script.');
                }
                
                DB::table('productions')->where('id', $production->id)->update(['station_times' => json_encode($station_times)]);
                $updatedCount++;
            }
        });

        return "Script ejecutado: Se procesaron y actualizaron {$updatedCount} producciones.";
    }

    // ===================================================================
    // STATION TIME CONTROL METHODS
    // ===================================================================

    public function startStationProcess(Production $production)
    {
        $current_record = $this->getCurrentStationRecord($production);
        if (!$current_record || $current_record['status'] !== 'En espera') {
            return back()->with('error', 'La estación no está en espera.');
        }

        $now = now();
        $current_record['status'] = 'En proceso';
        $current_record['started_at'] = $now->toDateTimeString();
        $current_record['history'][] = ['event' => 'Iniciada', 'timestamp' => $now->toDateTimeString(), 'user_id' => auth()->id(), 'details' => null];

        $entered_at = Carbon::parse($current_record['entered_at']);
        $current_record['times']['waiting_seconds'] = $now->diffInSeconds($entered_at);

        $this->updateCurrentStationRecord($production, $current_record);
        $production->save();
        return back();
    }

    public function pauseStationProcess(Request $request, Production $production)
    {
        $validated = $request->validate(['reason' => 'required|string|max:255']);

        $current_record = $this->getCurrentStationRecord($production);
        if (!$current_record || $current_record['status'] !== 'En proceso') {
            return back()->with('error', 'La estación no está en proceso.');
        }

        $now = now();
        $current_record['status'] = 'En pausa';
        $current_record['pauses'][] = ['paused_at' => $now->toDateTimeString(), 'resumed_at' => null, 'reason' => $validated['reason'], 'user_id' => auth()->id()];
        $current_record['history'][] = ['event' => 'Pausada', 'timestamp' => $now->toDateTimeString(), 'user_id' => auth()->id(), 'details' => $validated['reason']];
        
        $current_record['times']['effective_seconds'] = $this->calculateEffectiveTime($current_record, $now);

        $this->updateCurrentStationRecord($production, $current_record);
        $production->save();
        return back();
    }

    public function resumeStationProcess(Production $production)
    {
        $current_record = $this->getCurrentStationRecord($production);
        if (!$current_record || $current_record['status'] !== 'En pausa') {
            return back()->with('error', 'La estación no está en pausa.');
        }

        $now = now();
        $current_record['status'] = 'En proceso';

        $last_pause_key = count($current_record['pauses']) - 1;
        if (isset($current_record['pauses'][$last_pause_key])) {
            $current_record['pauses'][$last_pause_key]['resumed_at'] = $now->toDateTimeString();
        }

        $current_record['history'][] = ['event' => 'Reanudada', 'timestamp' => $now->toDateTimeString(), 'user_id' => auth()->id(), 'details' => null];
        $current_record['times']['paused_seconds'] = $this->calculateTotalPausedTime($current_record);

        $this->updateCurrentStationRecord($production, $current_record);
        $production->save();
        return back();
    }
    
    public function finishAndMoveStation(Request $request, Production $production)
    {
        $this->handleMoveLogic($request, $production, 'finish');
        return back();
    }

    public function skipAndMoveStation(Request $request, Production $production)
    {
        $this->handleMoveLogic($request, $production, 'skip');
        return back();
    }

    // ===================================================================
    // HELPER METHODS
    // ===================================================================

    private function handleMoveLogic(Request $request, Production $production, $mode)
    {
        $validated = $request->validate([
            'next_station' => 'required|string|max:255', 'machine_id' => 'nullable|integer|exists:machines,id',
            'notes' => 'nullable|string|max:255', 'quantity' => 'nullable|numeric|min:0', 'date' => 'nullable|date',
            'scrap_quantity' => 'nullable|numeric|min:0', 'shortage_quantity' => 'nullable|numeric|min:0',
        ]);
        
        $now = now();
        $user_id = auth()->id();
        $old_station_name = $production->station;

        $this->finalizeCurrentStation($production, $now, $user_id, "Movido a {$validated['next_station']}", $mode);

        $station_times = $production->station_times;
        $station_times[] = $this->createNewStationTimeRecord($validated['next_station'], $now, $user_id);
        $production->station_times = $station_times;
        
        $production->station = $validated['next_station'];
        if ($request->filled('machine_id')) {
            $production->machine_id = $validated['machine_id'];
        }

        // Handle additional data for specific station transitions
        if ($validated['next_station'] === 'Calidad' && $old_station_name !== 'Inspección') {
            $production->close_quantity = $validated['quantity']; $production->close_production_date = $validated['date'];
            $production->production_scrap = $validated['scrap_quantity']; $production->production_shortage = $validated['shortage_quantity'];
            $production->scrap_quantity += $validated['scrap_quantity']; $production->shortage_quantity += $validated['shortage_quantity'];
        } elseif ($validated['next_station'] === 'Inspección') {
            $production->quality_quantity = $validated['quantity']; $production->quality_released_date = $validated['date'];
            $production->quality_scrap = $validated['scrap_quantity']; $production->quality_shortage = $validated['shortage_quantity'];
            $production->scrap_quantity += $validated['scrap_quantity']; $production->shortage_quantity += $validated['shortage_quantity'];
        } elseif ($validated['next_station'] === 'Empaques') {
             $production->packing_received_quantity = $validated['quantity']; $production->packing_received_date = $validated['date'];
        }

        // --- NEW NOTIFICATION LOGIC ---
        if (in_array($validated['next_station'], ['X Compra', 'Surtido'])) {
            $notificationInstance = new ProductionForwardedNotification(
                $production,
                $validated['next_station'],
                $validated['quantity'] ?? $production->quantity // Use passed quantity or fallback to total
            );

            if ($validated['next_station'] === 'X Compra') {
                $this->sendNotification('production.forwarded.purchase', $notificationInstance);
            } elseif ($validated['next_station'] === 'Surtido') {
                $this->sendNotification('production.forwarded.assortment', $notificationInstance);
            }
        }
        // --- END NOTIFICATION LOGIC ---

        $production->save();
    }

    private function finalizeCurrentStation(Production &$production, Carbon $now, $user_id, $details = '', $mode = 'finish')
    {
        $current_record = $this->getCurrentStationRecord($production);
        if (!$current_record || $current_record['status'] === 'Finalizada') { // Añadida comprobación de finalizada
            return;
        }

        // If it was paused, resume it virtually to calculate time correctly
        if ($current_record['status'] === 'En pausa') {
            $last_pause_key = count($current_record['pauses']) - 1;
            if(isset($current_record['pauses'][$last_pause_key]) && $current_record['pauses'][$last_pause_key]['resumed_at'] === null) { // Comprobar que no esté ya resumida
                $current_record['pauses'][$last_pause_key]['resumed_at'] = $now->toDateTimeString();
            }
        }

        $current_record['status'] = 'Finalizada';
        $current_record['finished_at'] = $now->toDateTimeString();
        $current_record['history'][] = ['event' => "Finalizada ({$mode})", 'timestamp' => $now->toDateTimeString(), 'user_id' => $user_id, 'details' => $details];

        if ($mode === 'skip') { // Moved from 'En espera'
            if (!empty($current_record['entered_at'])) { // Comprobar que entered_at exista
                $current_record['times']['waiting_seconds'] = $now->diffInSeconds(Carbon::parse($current_record['entered_at']));
            } else {
                $current_record['times']['waiting_seconds'] = 0;
            }
            $current_record['times']['effective_seconds'] = 0;
            $current_record['times']['paused_seconds'] = 0;
        } else { // Moved from 'En proceso' or 'En pausa'
            if (!empty($current_record['started_at'])) {
                $current_record['times']['waiting_seconds'] = Carbon::parse($current_record['started_at'])->diffInSeconds(Carbon::parse($current_record['entered_at']));
                $current_record['times']['paused_seconds'] = $this->calculateTotalPausedTime($current_record);
                $current_record['times']['effective_seconds'] = $this->calculateEffectiveTime($current_record, $now);
            } else {
                // Si se finaliza pero nunca se inició (ej. se pauso desde espera? - Lógica de seguridad)
                if (!empty($current_record['entered_at'])) {
                    $current_record['times']['waiting_seconds'] = $now->diffInSeconds(Carbon::parse($current_record['entered_at']));
                }
            }
        }

        $this->updateCurrentStationRecord($production, $current_record);
    }

    private function getCurrentStationRecord(Production &$production)
    {
        $station_times = $production->station_times ?? [];
        if (empty($station_times)) return null;
        return $station_times[count($station_times) - 1];
    }

    private function updateCurrentStationRecord(Production &$production, $updated_record)
    {
        $station_times = $production->station_times ?? [];
        if (empty($station_times)) return;
        $station_times[count($station_times) - 1] = $updated_record;
        $production->station_times = $station_times;
    }

    private function createNewStationTimeRecord($station_name, Carbon $now, $user_id, $details = null)
    {
        return [
            'station_name' => $station_name, 'status' => 'En espera', 'entered_at' => $now->toDateTimeString(),
            'started_at' => null, 'finished_at' => null, 'pauses' => [],
            'history' => [['event' => 'En espera', 'timestamp' => $now->toDateTimeString(), 'user_id' => $user_id, 'details' => $details]],
            'times' => ['waiting_seconds' => 0, 'paused_seconds' => 0, 'effective_seconds' => 0]
        ];
    }

    private function createFinalizedStationTimeRecord($station_name, Carbon $now, $user_id, $details = null)
    {
        return [
            'station_name' => $station_name, 'status' => 'Finalizada', 'entered_at' => $now->toDateTimeString(),
            'started_at' => $now->toDateTimeString(), 'finished_at' => $now->toDateTimeString(), 'pauses' => [],
            'history' => [['event' => 'Finalizada', 'timestamp' => $now->toDateTimeString(), 'user_id' => $user_id, 'details' => $details]],
            'times' => ['waiting_seconds' => 0, 'paused_seconds' => 0, 'effective_seconds' => 0]
        ];
    }

    private function calculateTotalPausedTime($record)
    {
        return collect($record['pauses'] ?? [])->reduce(function ($carry, $pause) {
            if (isset($pause['resumed_at'])) {
                return $carry + Carbon::parse($pause['resumed_at'])->diffInSeconds(Carbon::parse($pause['paused_at']));
            }
            return $carry;
        }, 0);
    }

    private function calculateEffectiveTime($record, Carbon $end_time)
    {
        if (empty($record['started_at'])) return 0;
        $started_at = Carbon::parse($record['started_at']);
        $total_paused_seconds = $this->calculateTotalPausedTime($record);
        $effective_seconds = $end_time->diffInSeconds($started_at) - $total_paused_seconds;
        return max(0, $effective_seconds); // Ensure it's not negative
    }

    // ===================================================================
    // --- MÉTODOS AÑADIDOS PARA LÓGICA DE COMPONENTES ---
    // ===================================================================

    /**
     * Divide una orden de producción principal en múltiples partes (componentes).
     * --- LÓGICA CORREGIDA ---
     */
    public function splitProduction(Request $request, Production $production)
    {
        // Validación
        $validated = $request->validate([
            'parts' => 'required|array|min:1',
            'parts.*.identifier' => 'required|string|max:100', // Nombre del componente (Base, Tapa)
            'parts.*.quantity' => 'required|numeric|min:0.01',
            'parts.*.station' => 'required|string', // TODO: Validar que exista
            'parts.*.machine_id' => 'nullable|integer|exists:machines,id',
        ]);

        // --- LÓGICA DE CANTIDAD CORREGIDA (Tu Punto 1) ---
        // La cantidad a deducir del padre es el MÁXIMO de las partes creadas.
        $quantityToDeduct = collect($validated['parts'])->max('quantity');
        $currentUnassigned = $production->unassigned_quantity ?? $production->quantity;

        // Validar que la cantidad MÁXIMA de las partes no exceda la cantidad no asignada
        if ($quantityToDeduct > $currentUnassigned) {
            return back()->withErrors(['parts' => 'La cantidad de las partes (' . $quantityToDeduct . ') no puede exceder la cantidad no asignada ('. $currentUnassigned .').']);
        }
        // --- FIN LÓGICA DE CANTIDAD CORREGIDA ---

        try {
            DB::transaction(function () use ($validated, $production, $quantityToDeduct, $currentUnassigned) {
                
                foreach ($validated['parts'] as $partData) {
                    // Replicamos la orden padre, excluyendo los campos que se reiniciarán
                    $child = $production->replicate([
                        'station', 'station_times', 'machine_id', 'finish_date', 'close_production_date', 
                        'quality_released_date', 'production_close_type', 'quality_quantity', 'current_quantity',
                        'close_quantity', 'scrap_quantity', 'production_scrap', 'quality_scrap', 'inspection_scrap',
                        'shortage_quantity', 'production_shortage', 'quality_shortage', 'inspection_shortage',
                        'close_production_notes', 'inspection_notes', 'quality_notes', 'partials',
                        'packing_close_type', 'packing_notes', 'packing_scrap', 'packing_shortage', 'packing_partials',
                        'packing_received_quantity', 'packing_received_date', 'packing_finished_date', 'returns',
                        // También excluimos los campos de componentes
                        'parent_production_id', 'part_identifier', 'part_quantity', 'unassigned_quantity'
                    ]);
                    
                    $child->parent_production_id = $production->id;
                    $child->part_identifier = $partData['identifier']; // Ej. "Base"
                    $child->part_quantity = $partData['quantity']; // Cantidad del componente
                    $child->unassigned_quantity = 0; // Los hijos no se pueden dividir
                    $child->station = $partData['station'];
                    $child->machine_id = $partData['machine_id'] ?? $production->machine_id; // Hereda la máquina si no se especifica

                    // Creamos el historial de estación inicial para el hijo
                    $child->station_times = [
                        $this->createNewStationTimeRecord($partData['station'], now(), auth()->id(), "Componente '{$partData['identifier']}' creado")
                    ];

                    $child->save();
                }

                // Actualizamos la orden padre
                $newUnassigned = $currentUnassigned - $quantityToDeduct;
                $production->unassigned_quantity = $newUnassigned;

                // Si ya no queda cantidad por asignar, marcamos el padre como 'Dividida'
                if ($newUnassigned <= 0.01) { // Margen para floats
                    $production->unassigned_quantity = 0;
                    $production->station = 'Dividida';
                    // Finalizamos cualquier registro de tiempo abierto en el padre
                    $this->finalizeCurrentStation($production, now(), auth()->id(), 'Orden dividida en componentes');
                }
                
                $production->save();
            });
        } catch (\Exception $e) {
            Log::error('Error en splitProduction: ' . $e->getMessage());
            return back()->withErrors(['general' => 'Error al dividir la orden: ' . $e->getMessage()]);
        }

        // Usamos redirect()->back() para que Inertia recargue las props en el modal/página
        return redirect()->back()->with('success', 'Orden dividida exitosamente.');
    }

    /**
     * Obtiene los hijos (componentes) de una orden padre.
     */
    public function getChildren(Production $production)
    {
        $children = $production->children()->with('machine')->get();
        return response()->json($children);
    }

    /**
     * Mueve todos los hijos especificados a una nueva estación.
     */
    public function moveAllChildren(Request $request)
    {
        $validated = $request->validate([
            'child_ids' => 'required|array',
            'child_ids.*' => 'integer|exists:productions,id',
            'next_station' => 'required|string',
            'machine_id' => 'nullable|integer|exists:machines,id',
            'notes' => 'nullable|string',
        ]);

        $userId = auth()->id();
        $notes = $validated['notes'] ?? '';

        try {
            DB::transaction(function () use ($validated, $userId, $notes) {
                $children = Production::findMany($validated['child_ids']);
                
                foreach ($children as $child) {
                    // Solo movemos si la estación de destino es diferente a la actual
                    if ($child->station !== $validated['next_station']) {
                        $this->skipToStation(
                            $child,
                            $validated['next_station'],
                            $validated['machine_id'], // Puede ser null
                            $userId,
                            $notes
                        );
                    }
                }
            });
        } catch (\Exception $e) {
            Log::error('Error en moveAllChildren: ' . $e->getMessage());
            return back()->withErrors(['general' => 'Error al mover los componentes: ' . $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'Componentes movidos.');
    }

    /**
     * Lógica central para mover una orden a una nueva estación (tipo "skip").
     * Esta es reutilizable para el movimiento masivo.
     */
    private function skipToStation(Production $production, string $nextStation, ?int $machineId, int $userId, string $notes = '')
    {
        $stationTimes = $production->station_times ?? [];
        $now = now();

        if (!empty($stationTimes)) {
            $lastIndex = count($stationTimes) - 1;
            $currentRecord = $stationTimes[$lastIndex];

            // Solo finalizamos si no está ya finalizada
            if ($currentRecord['status'] !== 'Finalizada') {
                $currentRecord['status'] = 'Finalizada';
                $currentRecord['finished_at'] = $now->toDateTimeString();
                
                $status = $currentRecord['status']; // El estado *antes* de finalizarlo

                // Calculamos el tiempo transcurrido en el estado actual
                if ($status === 'En espera' && !empty($currentRecord['entered_at'])) {
                    $startTime = Carbon::parse($currentRecord['entered_at']);
                    $currentRecord['times']['waiting_seconds'] += $now->diffInSeconds($startTime);

                } else if ($status === 'En proceso' && !empty($currentRecord['started_at'])) {
                     $currentRecord['times']['effective_seconds'] = $this->calculateEffectiveTime($currentRecord, $now);

                } else if ($status === 'En pausa' && !empty($currentRecord['pauses'])) {
                    $lastPauseKey = count($currentRecord['pauses']) - 1;
                    if(isset($currentRecord['pauses'][$lastPauseKey]) && $currentRecord['pauses'][$lastPauseKey]['resumed_at'] === null) {
                        $currentRecord['pauses'][$lastPauseKey]['resumed_at'] = $now->toDateTimeString();
                    }
                }
                
                // Calculamos los totales finales de pausa y efectivo (en caso de que viniera de "En Proceso" o "Pausa")
                $currentRecord['times']['paused_seconds'] = $this->calculateTotalPausedTime($currentRecord);
                if ($status !== 'En espera' && !empty($currentRecord['started_at'])) {
                     $currentRecord['times']['effective_seconds'] = $this->calculateEffectiveTime($currentRecord, $now);
                }


                $currentRecord['history'][] = [
                    'event' => 'Finalizada (Omitida)',
                    'timestamp' => $now->toDateTimeString(),
                    'user_id' => $userId,
                    'details' => 'Movido a ' . $nextStation . '. ' . $notes,
                ];
                $stationTimes[$lastIndex] = $currentRecord;
            }
        }

        // Crear el nuevo registro de estación (inicia "En espera")
        $newRecordIdentifier = $production->part_identifier ?? 'Orden';
        $stationTimes[] = $this->createNewStationTimeRecord($nextStation, $now, $userId, "Movimiento masivo: {$notes}");
        
        $production->station_times = $stationTimes;
        $production->station = $nextStation;
        if ($machineId) { // Solo actualiza la máquina si se proporciona una nueva
            $production->machine_id = $machineId;
        }
        $production->save();
    }
}