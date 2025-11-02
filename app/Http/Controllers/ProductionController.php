<?php

namespace App\Http\Controllers;

use App\Exports\ProductionReportExport;
use App\Models\Production;
use Illuminate\Http\Request;
use App\Exports\ProductionsExport;
use App\Imports\ProductionsImport;
use App\Notifications\ProductionForwardedNotification;
use App\Traits\NotifiesViaEvents;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
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
        // Esta lógica de "nextFolio" sigue funcionando porque busca el último folio,
        // y los folios solo existen en los padres.
        $nextProduction = Production::whereNotNull('folio')->latest('folio')->first();
        $nextProduction = $nextProduction ? $nextProduction->folio + 1 : 1;

        return inertia('Production/Create', compact('nextProduction'));
    }

   public function store(Request $request)
    {
        // --- REGLAS DE VALIDACIÓN MODIFICADAS ---
        $validated = $request->validate([
            // 'folio' es requerido y único SÓLO si es un padre (parent_id es NULL)
            'folio' => 'required|numeric|unique:productions,folio,NULL,id,parent_id,NULL',
            'type' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'estimated_date' => 'required|date',
            'client' => 'required|string|max:255',
            'changes' => 'required|numeric|min:0',
            'product_id' => 'required|min:1|integer|exists:products,id',
            'quantity' => 'required|numeric|min:1', // Cantidad de la orden padre
            'materials' => 'nullable|string|max:255',
            
            // Estación y máquina son requeridas si NO tiene componentes
            'station' => 'required_if:has_components,false|nullable|string|max:255',
            'machine_id' => 'required_if:has_components,false|nullable|min:1|integer|exists:machines,id',
            
            'notes' => 'nullable|string|max:300',
            'dfi' => 'nullable|string|max:255',
            'material' => 'nullable|string|max:255',
            'width' => 'nullable|string|max:255',
            'gauge' => 'nullable|string|max:255',
            'large' => 'nullable|string|max:255',
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

            // Validar los componentes
            'has_components' => 'required|boolean',
            'components' => 'nullable|array|required_if:has_components,true',
            'components.*.name' => 'required|string|max:255',
            'components.*.quantity' => 'required|numeric|min:1',
            'components.*.station' => 'required|string|max:255',
            'components.*.machine_id' => 'required|min:1|integer|exists:machines,id',

        ], [
            'varnish_type.required_if_accepted' => 'El tipo de barniz es requerido.',
            'station.required_if' => 'La estación es requerida si la orden no tiene componentes.',
            'machine_id.required_if' => 'La máquina es requerida si la orden no tiene componentes.',
            'components.required_if' => 'Debe agregar al menos un componente.',
            'components.*.name.required' => 'El nombre del componente es requerido.',
            'components.*.quantity.required' => 'La cantidad del componente es requerida.',
            'components.*.station.required' => 'La estación del componente es requerida.',
            'components.*.machine_id.required' => 'La máquina del componente es requerida.',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['modified_user_id'] = auth()->id();
        $validated['materials'] = [$validated['materials']];
        $now = now();

        // --- LÓGICA DE CREACIÓN MODIFICADA ---

        if (!$validated['has_components']) {
            // --- CASO 1: ORDEN SIMPLE (como antes) ---
            $validated['station_times'] = [
                $this->createNewStationTimeRecord($validated['station'], $now, auth()->id(), 'Creación de la orden de producción')
            ];
            $production = Production::create($validated);

            // Notificar si va a 'Material pendiente'
            if ($validated['station'] == 'Material pendiente') {
                $notificationInstance = new ProductionForwardedNotification(
                    $production,
                    'Material pendiente',
                    $production->quantity
                );
                $this->sendNotification('production.forwarded.pendent_material', $notificationInstance);
            }
        } else {
            // --- CASO 2: ORDEN CON COMPONENTES ---
            
            // 1. Crear la Orden Padre
            $parentData = $validated;
            $parentData['station'] = 'Producción dividida'; // Estación "virtual" para el padre
            // Asignamos la máquina del primer componente como referencia, o la dejamos null
            $parentData['machine_id'] = $validated['components'][0]['machine_id']; 
            $parentData['station_times'] = [
                $this->createNewStationTimeRecord($parentData['station'], $now, auth()->id(), 'Creación de orden padre (dividida)')
            ];

            $parentProduction = Production::create($parentData);

            // 2. Crear las Órdenes Hijo (Componentes)
            foreach ($validated['components'] as $component) {
                $childData = $parentData; // Copiamos datos comunes (cliente, producto, fechas, etc.)
                
                $childData['parent_id'] = $parentProduction->id;
                $childData['folio'] = null; // Los hijos no tienen folio
                $childData['component_name'] = $component['name'];
                $childData['quantity'] = $component['quantity']; // Cantidad específica del componente
                $childData['station'] = $component['station'];
                $childData['machine_id'] = $component['machine_id'];
                
                // Cada hijo tiene su propio historial de estaciones
                $childData['station_times'] = [
                    $this->createNewStationTimeRecord($component['station'], $now, auth()->id(), 'Creación de componente: ' . $component['name'])
                ];

                $childProduction = Production::create($childData);

                // Notificar si algún componente va a 'Material pendiente'
                if ($childProduction->station == 'Material pendiente') {
                     $notificationInstance = new ProductionForwardedNotification(
                        $childProduction, // Notificar sobre el componente específico
                        'Material pendiente',
                        $childProduction->quantity,
                        $parentProduction->folio // Añadir el folio padre como referencia
                    );
                    $this->sendNotification('production.forwarded.pendent_material', $notificationInstance);
                }
            }
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
        // TO-DO: A futuro, el edit debería cargar los children si es un parent.
        return inertia('Production/Edit', compact('production', 'product'));
    }

    public function update(Request $request, Production $production)
    {
        // --- VALIDACIÓN DE UPDATE MODIFICADA ---
        $validated = $request->validate([
            // Validar unique folio SÓLO si es un padre
            'folio' => 'required|numeric|unique:productions,folio,' . $production->id . ',id,parent_id,NULL',
            'type' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'estimated_date' => 'required|date',
            'client' => 'required|string|max:255',
            'changes' => 'required|numeric|min:0',
            'product_id' => 'required|min:1|integer|exists:products,id',
            'quantity' => 'required|numeric|min:1',
            'materials' => 'nullable|string|max:255',
            'station' => 'required|string|max:255', // En edit, el padre ya tiene "Producción dividida"
            'machine_id' => 'required|min:1|integer|exists:machines,id',
            'notes' => 'nullable|string|max:300',
            'dfi' => 'nullable|string|max:255',
            'material' => 'nullable|string|max:255',
            'width' => 'nullable|numeric|min:0',
            'gauge' => 'nullable|string|max:255',
            'large' => 'nullable|numeric|min:0',
            'look' => 'nullable|string|max:255',
            'faces' => 'nullable|numeric|min:0',
            'pps' => 'nullable|numeric|min:0',
            'adjust' => 'nullable|numeric|min:0',
            'sheets' => 'nullable|numeric|min:0',
            'ha' => 'nullable|numeric|min:0',
            'pf' => 'nullable|numeric|min:0',
            'ts' => 'nullable|numeric|min:0',
            'pf' => 'required|numeric|min:0',
            'tps' => 'nullable|numeric|min:0',
            'varnish_type' => 'required_if_accepted:has_varnish',
        ], [
            'varnish_type.required_if_accepted' => 'El tipo de barniz es requerido.',
        ]);

        // TO-DO: A futuro, la actualización debería manejar la actualización/creación/eliminación
        // de componentes hijos, lo cual es más complejo.
        // Por ahora, solo actualiza el padre.

        $validated['materials'] = [$validated['materials']];

        $production->update($validated + ['modified_user_id' => auth()->id()]);

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
        // Gracias al onDelete('cascade') de la migración,
        // si se borra un padre, se borrarán sus hijos automáticamente.
        $production->delete();
    }

    // --- INICIO DE NUEVO MÉTODO ---
    public function getChildren(Production $production)
    {
        // Carga los hijos con sus relaciones 'machine' y 'product'
        $children = $production->children()->with(['machine', 'product'])->get();

        return response()->json([
            'items' => $children
        ]);
    }
    // --- FIN DE NUEVO MÉTODO ---

    public function getByPage()
    {
        $page = request('page', 1);
        $search = request('search', null);

        $perPage = 30;
        $offset = ($page - 1) * $perPage;

        // --- MODIFICADO: Traer solo órdenes padre ---
        $query = Production::with(['user', 'product', 'machine'])
                    ->whereNull('parent_id') // ¡¡CLAVE!!
                    ->latest('id');

        // Get allowed stations for the user
        $user = auth()->user();
        $permissions = $user->getAllPermissions()
            ->filter(fn ($permission) => str_starts_with($permission->name, 'Ver en estacion'))
            ->map(fn ($permission) => str_replace('Ver en estacion ', '', $permission->name))
            ->toArray();
        
        // Incluir la nueva estación virtual si el usuario puede ver cualquier estación
        if (!empty($permissions)) {
            $permissions[] = 'Producción dividida'; 
            $query->whereIn('station', $permissions);
        }


        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('folio', 'like', "%{$search}%")
                    ->orWhere('client', 'like', "%{$search}%")
                    ->orWhere('station', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('season', 'like', "%{$search}%");
                    });
            });
        }

        $items = $query->offset($offset)
            ->limit($perPage)
            ->get();

        $total = $query->count();

        return response()->json(compact('items', 'total'));
    }

    public function clone(Production $production)
    {
        // Esta función de clonar clonará el padre.
        // TO-DO: A futuro, debería clonar también los hijos.
        // Por ahora, clona el padre como una orden nueva simple.
        
        $lastProductionFolio = Production::whereNotNull('folio')->latest('folio')->first()?->folio;
        $newProduction = $production->replicate([
            'finish_date', 'close_production_date', 'quality_released_date', 'estimated_package_date',
            'estimated_date', 'production_close_type', 'quality_quantity', 'current_quantity',
            'close_quantity', 'scrap_quantity', 'production_scrap', 'quality_scrap', 'inspection_scrap',
            'shortage_quantity', 'production_shortage', 'quality_shortage', 'inspection_shortage',
            'close_production_notes', 'inspection_notes', 'quality_notes', 'partials', 'start_date',
            'packing_close_type', 'packing_notes', 'packing_scrap', 'packing_shortage', 'packing_partials',
            'packing_received_quantity', 'packing_received_date', 'packing_finished_date', 'station_times',
            'parent_id', 'component_name' // No clonar estatus de componente
        ]);
        $newProduction->folio = $lastProductionFolio + 1;
        $newProduction->type = 'Repetido';
        $newProduction->station = 'Material pendiente'; // Inicia simple
        $newProduction->start_date = now();

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
        // Esta función se aplica a un 'hijo' o a una orden 'simple'.
        // El modal de detalles deberá llamar a esto con el ID del hijo.
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
        // Esta función se aplica a un 'hijo' o a una orden 'simple'.
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

        $query = Production::with(['user', 'product', 'machine', 'modifiedUser'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->when($season !== 'Todas', fn ($q) => $q->whereHas('product', fn ($q) => $q->where('season', $season)));

        // Modificación: Si se filtra por estación, incluir hijos Y padres de esa estación.
        if ($station !== 'Todos') {
             $query->where(function ($q) use ($station) {
                $q->where('station', $station) // Padres o hijos en esa estación
                    ->orWhereHas('children', fn($sq) => $sq->where('station', $station)); // Padres cuyos hijos estén en esa estación
            });
        } else {
            // Si es "Todos", mostrar solo los padres
             $query->whereNull('parent_id');
        }

        $productions = $query->get();

        return Excel::download(new ProductionsExport($productions), 'producciones.xlsx');
    }

    public function exportExcelReport()
    {
        $folio = request('folio');
        // Traer el padre Y sus hijos para el reporte
        $productions = Production::with(['user', 'product', 'machine', 'modifiedUser'])
                            ->where('folio', $folio)
                            ->whereNull('parent_id') // El padre
                            ->with('children.machine') // Cargar hijos con sus máquinas
                            ->get();
                            
        // Podríamos necesitar cargar los hijos también, o el reporte debe modificarse
        // Dejémoslo así por ahora, asumiendo que el reporte se basa en el padre.
        
        return Excel::download(new ProductionReportExport($productions), 'reporte_produccion.xlsx');
    }

    public function importExcel(Request $request)
    {
        Excel::import(new ProductionsImport, $request->file('excel')[0]);
    }

    public function hojaViajera(Production $production)
    {
        // Cargar el padre Y sus hijos
        $production->load(['product', 'machine', 'modifiedUser', 'children.machine']);
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
    // (Estos métodos ahora aplican a la 'production' que se les pase,
    // ya sea un padre, un hijo o una orden simple)
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
        // Este 'production' es el 'hijo' o 'simple' que se está moviendo
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
                $validated['quantity'] ?? $production->quantity, // Use passed quantity or fallback to total
                $production->parent?->folio // Añadir folio padre si existe
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
        if (!$current_record) return;

        // If it was paused, resume it virtually to calculate time correctly
        if ($current_record['status'] === 'En pausa') {
            $last_pause_key = count($current_record['pauses']) - 1;
            if(isset($current_record['pauses'][$last_pause_key])) {
                $current_record['pauses'][$last_pause_key]['resumed_at'] = $now->toDateTimeString();
            }
        }

        $current_record['status'] = 'Finalizada';
        $current_record['finished_at'] = $now->toDateTimeString();
        $current_record['history'][] = ['event' => "Finalizada ({$mode})", 'timestamp' => $now->toDateTimeString(), 'user_id' => $user_id, 'details' => $details];

        if ($mode === 'skip') { // Moved from 'En espera'
            $current_record['times']['waiting_seconds'] = $now->diffInSeconds(Carbon::parse($current_record['entered_at']));
            $current_record['times']['effective_seconds'] = 0;
            $current_record['times']['paused_seconds'] = 0;
        } else { // Moved from 'En proceso' or 'En pausa'
            if (!empty($current_record['started_at'])) {
                $current_record['times']['waiting_seconds'] = Carbon::parse($current_record['started_at'])->diffInSeconds(Carbon::parse($current_record['entered_at']));
                $current_record['times']['paused_seconds'] = $this->calculateTotalPausedTime($current_record);
                $current_record['times']['effective_seconds'] = $this->calculateEffectiveTime($current_record, $now);
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
}