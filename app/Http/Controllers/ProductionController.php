<?php

namespace App\Http\Controllers;

use App\Exports\ProductionReportExport;
use App\Models\Production;
use Illuminate\Http\Request;
use App\Exports\ProductionsExport;
use App\Imports\ProductionsImport;
use App\Notifications\ProductionForwardedNotification;
use App\Notifications\ProductionReturnedNotification; // Asegúrate que esta notificación exista
use App\Traits\NotifiesViaEvents;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ProductionController extends Controller
{
    use NotifiesViaEvents;

    // --- INICIO: NUEVAS CONSTANTES DE HORARIO LABORAL ---
    /**
     * Define la hora de inicio de la jornada laboral (formato HH:mm:ss).
     */
    private const WORK_START_TIME = '08:00:00';

    /**
     * Define la hora de fin de la jornada laboral (formato HH:mm:ss).
     */
    private const WORK_END_TIME = '17:30:00';
    // --- FIN: NUEVAS CONSTANTES DE HORARIO LABORAL ---


    public function dashboard()
    {
        $productions = Production::latest('folio')->get(['folio', 'station']);

        return inertia('Production/Dashboard', compact('productions'));
    }

    public function index()
    {
        // Solo trae producciones padre (o simples)
        return inertia('Production/Index');
    }

    public function report()
    {
        return inertia('Production/Report');
    }

    public function create()
    {
        $nextProduction = Production::whereNull('parent_id')->latest('folio')->first();
        $nextProduction = $nextProduction ? $nextProduction->folio + 1 : 1;

        return inertia('Production/Create', compact('nextProduction'));
    }

    public function store(Request $request)
    {
        // Validación adaptada para componentes
        $validated = $request->validate([
            'folio' => 'required_if:has_components,false|nullable|numeric|unique:productions,folio,NULL,id,parent_id,NULL',
            'type' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'estimated_date' => 'required|date',
            'client' => 'required|string|max:255',
            'changes' => 'required|numeric|min:0',
            'product_id' => 'required|min:1|integer|exists:products,id',
            'quantity' => 'required_if:has_components,false|nullable|numeric|min:1',
            'materials' => 'nullable|string|max:255',
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
            // Validación de componentes
            'has_components' => 'required|boolean',
            'components' => 'exclude_if:has_components,false|required|array|min:1', // Permitir 1 o más componentes
            'components.*.name' => 'required_if:has_components,true|string|max:255',
            'components.*.quantity' => 'required_if:has_components,true|numeric|min:1',
            'components.*.station' => 'required_if:has_components,true|string|max:255',
            'components.*.machine_id' => 'required_if:has_components,true|integer|exists:machines,id',
        ], [
            'varnish_type.required_if_accepted' => 'El tipo de barniz es requerido.',
            'folio.required_if' => 'El folio es requerido.',
            'folio.unique' => 'El folio ya ha sido registrado.',
            'components.min' => 'Debe tener al menos 1 componente.',
            'components.*.name.required_if' => 'El nombre del componente es requerido.',
            'components.*.quantity.required_if' => 'La cantidad del componente es requerida.',
            'components.*.station.required_if' => 'La estación del componente es requerida.',
            'components.*.machine_id.required_if' => 'La máquina del componente es requerida.',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['materials'] = [$validated['materials']];
        $now = now();

        DB::transaction(function () use ($validated, $now) {
            // --- Caso 1: Tiene componentes (Orden Padre + Hijos) ---
            if ($validated['has_components']) {
                $totalQuantity = collect($validated['components'])->sum('quantity');

                // Crear la orden "Padre"
                $parentData = $validated;
                unset($parentData['components'], $parentData['has_components']);
                $parentData['station'] = 'Producción dividida'; // Estación virtual para el padre
                $parentData['quantity'] = $totalQuantity;
                $parentData['machine_id'] = null; // El padre no tiene máquina, la tienen los hijos
                $parentData['folio'] = $validated['folio']; // Folio se guarda en el padre
                $parentData['station_times'] = [
                    $this->createFinalizedStationTimeRecord('Producción dividida', $now, auth()->id(), 'Orden creada como padre de componentes.')
                ];

                $parentProduction = Production::create($parentData + ['modified_user_id' => auth()->id()]);

                // Crear las órdenes "Hijo" (componentes)
                foreach ($validated['components'] as $component) {
                    $childData = $parentData; // Hereda la mayoría de los datos del padre
                    unset($childData['folio'], $childData['notes']); // Los hijos no tienen folio ni notas principales

                    $childData['parent_id'] = $parentProduction->id;
                    $childData['component_name'] = $component['name'];
                    $childData['quantity'] = $component['quantity'];
                    $childData['station'] = $component['station'];
                    $childData['machine_id'] = $component['machine_id'];
                    $childData['station_times'] = [
                        $this->createNewStationTimeRecord($component['station'], $now, auth()->id(), 'Creación de componente.')
                    ];

                    $childProduction = Production::create($childData);

                    // Notificar si el *hijo* va a 'Material pendiente'
                    if ($childProduction->station == 'Material pendiente') {
                        $notificationInstance = new ProductionForwardedNotification(
                            $childProduction, // Notificar sobre el hijo
                            'Material pendiente',
                            $childProduction->quantity,
                            $parentProduction->folio // Incluir el folio del padre para referencia
                        );
                        $this->sendNotification('production.forwarded.pendent_material', $notificationInstance);
                    }
                }
            } else {
                // --- Caso 2: Orden Simple (como antes) ---
                unset($validated['components'], $validated['has_components']);
                $validated['station_times'] = [
                    $this->createNewStationTimeRecord($validated['station'], $now, auth()->id(), 'Creación de la orden de producción')
                ];

                $production = Production::create($validated + ['modified_user_id' => auth()->id()]);

                // Notificar si va a 'Material pendiente'
                if ($validated['station'] == 'Material pendiente') {
                    $notificationInstance = new ProductionForwardedNotification(
                        $production,
                        'Material pendiente',
                        $production->quantity,
                        $production->folio
                    );
                    $this->sendNotification('production.forwarded.pendent_material', $notificationInstance);
                }
            }
        });

        return to_route('productions.index');
    }


    public function show(Production $production)
    {
        //
    }

    public function edit(Production $production)
    {
        // Si el usuario intenta editar un hijo, redirigirlo al padre
        if ($production->parent_id) {
            return to_route('productions.edit', $production->parent_id);
        }

        // Cargar el producto principal
        $production->load('product');

        // Si es una orden dividida, cargar los hijos y sus máquinas
        if ($production->station === 'Producción dividida') {
            $production->load('children.machine');
        }

        // El prop 'product' es esperado por el frontend
        $product = $production->product;

        return inertia('Production/Edit', compact('production', 'product'));
    }

    public function update(Request $request, Production $production)
    {
        // No permitir la actualización directa de un hijo
        if ($production->parent_id) {
            abort(403, 'Los componentes se editan desde la orden padre.');
        }

        $validated = $request->validate([
            // Regla de folio: único para padres (parent_id NULL), ignorando el ID actual
            'folio' => 'required|numeric|unique:productions,folio,' . $production->id . ',id,parent_id,NULL',
            'type' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'estimated_date' => 'required|date',
            'client' => 'required|string|max:255',
            'changes' => 'required|numeric|min:0',
            'product_id' => 'required|min:1|integer|exists:products,id',
            'quantity' => 'required_if:has_components,false|nullable|numeric|min:1', // Cantidad requerida solo si es simple
            'materials' => 'nullable|string|max:255',
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
            'pf' => 'required|numeric|min:0', // Se mantiene 'pf' requerido como en 'create'
            'ts' => 'nullable|numeric|min:0',
            'ps' => 'nullable|numeric|min:0',
            'tps' => 'nullable|numeric|min:0',
            'varnish_type' => 'required_if_accepted:has_varnish',

            // --- Validación de Lógica de Componentes ---
            'has_components' => 'required|boolean',
            // Estación y máquina principal son requeridas solo si NO tiene componentes
            'station' => 'required_if:has_components,false|nullable|string|max:255',
            'machine_id' => 'required_if:has_components,false|nullable|min:1|integer|exists:machines,id',

            // Componentes: requerido si 'has_components' es true, y debe tener al menos 1
            'components' => 'exclude_if:has_components,false|required|array|min:1',
            'components.*.id' => 'nullable|integer|exists:productions,id', // ID del hijo (si ya existe)
            'components.*.name' => 'required|string|max:255',
            'components.*.quantity' => 'required|numeric|min:1',
            'components.*.station' => 'required|string|max:255',
            'components.*.machine_id' => 'required|integer|exists:machines,id',
        ], [
            'varnish_type.required_if_accepted' => 'El tipo de barniz es requerido.',
            'folio.unique' => 'El folio ya ha sido registrado.',
            'components.min' => 'Una orden dividida debe tener al menos un componente.',
            'components.*.name.required' => 'El nombre del componente es requerido.',
            'components.*.quantity.required' => 'La cantidad del componente es requerida.',
            'components.*.station.required' => 'La estación del componente es requerida.',
            'components.*.machine_id.required' => 'La máquina del componente es requerida.',
        ]);

        $now = now();
        $user_id = auth()->id();

        DB::transaction(function () use ($production, $validated, $now, $user_id) {

            $parentData = $validated;
            $parentData['materials'] = [$validated['materials']];
            $parentData['modified_user_id'] = $user_id;

            // --- Lógica para Orden Dividida (Padre) ---
            if ($validated['has_components']) {

                $totalQuantity = 0;
                $existingChildIds = [];

                foreach ($validated['components'] as $component) {
                    $totalQuantity += $component['quantity'];

                    $childData = [
                        'component_name' => $component['name'],
                        'quantity' => $component['quantity'],
                        'station' => $component['station'],
                        'machine_id' => $component['machine_id'],
                        'modified_user_id' => $user_id,
                        // Heredar campos del padre
                        'product_id' => $validated['product_id'],
                        'client' => $validated['client'],
                        'start_date' => $validated['start_date'],
                        'estimated_date' => $validated['estimated_date'],
                        // ... (heredar otros campos relevantes si es necesario)
                    ];

                    if (isset($component['id'])) {
                        // --- 1. Actualizar Hijo Existente ---
                        $childProduction = Production::find($component['id']);

                        // Doble chequeo de seguridad
                        if ($childProduction && $childProduction->parent_id === $production->id) {

                            // Si la estación cambió en el formulario, registrar el cambio
                            if ($childProduction->station !== $component['station']) {
                                $this->finalizeCurrentStation($childProduction, $now, $user_id, "Estación cambiada manualmente a " . $component['station']);
                                $station_times = $childProduction->station_times;
                                $station_times[] = $this->createNewStationTimeRecord($component['station'], $now, $user_id, 'Cambio manual de estación desde edición.');
                                $childData['station_times'] = $station_times;
                            }

                            $childProduction->update($childData);
                            $existingChildIds[] = $childProduction->id;
                        }
                    } else {
                        // --- 2. Crear Nuevo Hijo ---
                        // Heredar todos los datos del padre, excepto los que son únicos del padre/hijo
                        $childBaseData = $production->toArray();

                        // Limpiar campos que no debe heredar
                        unset(
                            $childBaseData['id'],
                            $childBaseData['folio'],
                            $childBaseData['notes'],
                            $childBaseData['station_times'],
                            $childBaseData['created_at'],
                            $childBaseData['updated_at'],
                            $childBaseData['children'],
                            $childBaseData['parent_id'],
                            $childBaseData['component_name'],
                            $childBaseData['quantity'],
                            $childBaseData['station'],
                            $childBaseData['machine_id']
                        );

                        $newChildData = array_merge($childBaseData, $childData, [
                            'parent_id' => $production->id,
                            'user_id' => $production->user_id, // Asegurar que hereda el creador original
                            'station_times' => [$this->createNewStationTimeRecord($component['station'], $now, $user_id, 'Componente agregado en edición.')],
                        ]);

                        $newChildProduction = Production::create($newChildData);
                        $existingChildIds[] = $newChildProduction->id;
                    }
                }

                // --- 3. Eliminar Hijos Borrados ---
                $production->children()->whereNotIn('id', $existingChildIds)->delete();

                // Actualizar la cantidad total del padre
                $parentData['quantity'] = $totalQuantity;

                // Limpiar campos de componente del array de datos del padre
                unset($parentData['components'], $parentData['has_components']);
            } else {
                // --- Lógica para Orden Simple ---
                // (Se asume que no se puede degradar una orden dividida a simple)
                unset($parentData['components'], $parentData['has_components']);
            }

            // --- 4. Actualizar el Padre (o la Orden Simple) ---
            $production->update($parentData);
        });

        // Redirigir de vuelta al índice
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
        // Si es un padre, borrar también a los hijos
        if ($production->parent_id === null) {
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

        // --- LÓGICA DE FILTRADO DE PADRES ---
        // Traer solo los que son 'padre' (parent_id IS NULL)
        $query = Production::with(['user', 'product', 'machine'])
            ->whereNull('parent_id')
            ->latest('id');

        // Get allowed stations for the user
        $user = auth()->user();
        $permissions = $user->getAllPermissions()
            ->filter(fn($permission) => str_starts_with($permission->name, 'Ver en estacion'))
            ->map(fn($permission) => str_replace('Ver en estacion ', '', $permission->name))
            ->toArray();

        // Añadir permisos para ver estaciones "divididas" si tienen permiso de ver cualquier estación de producción
        if (!empty($permissions)) {
            $permissions[] = 'Producción dividida'; // Siempre permitir ver padres si tienen algún permiso
            $query->whereIn('station', $permissions);
        }
        // Si no tiene permisos de estación, no debería ver nada (la query fallará y devolverá 0)
        // O si es un admin sin esa estructura de permisos, $permissions estará vacío y no se aplicará el filtro WhereIn

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('folio', 'like', "%{$search}%")
                    ->orWhere('client', 'like', "%{$search}%")
                    ->orWhere('station', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('season', 'like', "%{$search}%");
                    })
                    // Buscar también en hijos
                    ->orWhereHas('children', function ($q) use ($search) {
                        $q->where('component_name', 'like', "%{$search}%")
                            ->orWhere('station', 'like', "%{$search}%");
                    });
            });
        }

        $items = $query->offset($offset)
            ->limit($perPage)
            ->get();

        $total = $query->count();

        return response()->json(compact('items', 'total'));
    }

    /**
     * Obtiene los componentes (hijos) de una orden de producción padre.
     */
    public function getChildren(Production $production)
    {
        // Asegurarse de que solo se puedan obtener hijos de un padre
        if ($production->parent_id !== null) {
            abort(403, 'Esta no es una orden padre.');
        }

        $items = $production->children()->with('machine')->get();

        return response()->json(compact('items'));
    }


    public function clone(Production $production)
    {
        // Solo se pueden clonar padres
        if ($production->parent_id) {
            abort(403, 'No se pueden clonar componentes individuales.');
        }

        $lastProductionFolio = Production::whereNull('parent_id')->latest('folio')->first()?->folio;
        $now = now();
        $user_id = auth()->id();

        $newProduction = DB::transaction(function () use ($production, $lastProductionFolio, $now, $user_id) {
            // 1. Replicar el Padre
            $newParent = $production->replicate([
                'finish_date',
                'close_production_date',
                'quality_released_date',
                'estimated_package_date',
                'estimated_date',
                'production_close_type',
                'quality_quantity',
                'current_quantity',
                'close_quantity',
                'scrap_quantity',
                'production_scrap',
                'quality_scrap',
                'inspection_scrap',
                'shortage_quantity',
                'production_shortage',
                'quality_shortage',
                'inspection_shortage',
                'close_production_notes',
                'inspection_notes',
                'quality_notes',
                'partials',
                'start_date',
                'packing_close_type',
                'packing_notes',
                'packing_scrap',
                'packing_shortage',
                'packing_partials',
                'packing_received_quantity',
                'packing_received_date',
                'packing_finished_date',
                'station_times'
            ]);
            $newParent->folio = $lastProductionFolio + 1;
            $newParent->type = 'Repetido';
            $newParent->start_date = $now;

            // Si era una orden simple
            if ($production->station !== 'Producción dividida') {
                $newParent->station = 'Material pendiente';
                $newParent->station_times = [
                    $this->createNewStationTimeRecord('Material pendiente', $now, $user_id, 'Registro inicial de orden clonada.')
                ];
            } else {
                // Si era una orden dividida (padre)
                $newParent->station = 'Producción dividida';
                $newParent->station_times = [
                    $this->createFinalizedStationTimeRecord('Producción dividida', $now, $user_id, 'Orden (padre) clonada.')
                ];
            }

            $newParent->save();

            // 2. Replicar los Hijos (si los tenía)
            if ($production->station === 'Producción dividida') {
                foreach ($production->children as $child) {
                    $newChild = $child->replicate([
                        'finish_date',
                        'close_production_date',
                        'quality_released_date',
                        'estimated_package_date',
                        'estimated_date',
                        'production_close_type',
                        'quality_quantity',
                        'current_quantity',
                        'close_quantity',
                        'scrap_quantity',
                        'production_scrap',
                        'quality_scrap',
                        'inspection_scrap',
                        'shortage_quantity',
                        'production_shortage',
                        'quality_shortage',
                        'inspection_shortage',
                        'close_production_notes',
                        'inspection_notes',
                        'quality_notes',
                        'partials',
                        'start_date',
                        'packing_close_type',
                        'packing_notes',
                        'packing_scrap',
                        'packing_shortage',
                        'packing_partials',
                        'packing_received_quantity',
                        'packing_received_date',
                        'packing_finished_date',
                        'station_times',
                        'folio' // Los hijos no tienen folio
                    ]);

                    $newChild->parent_id = $newParent->id;
                    $newChild->station = 'Material pendiente'; // Todos los hijos clonados empiezan en pendiente
                    $newChild->start_date = $now;
                    $newChild->station_times = [
                        $this->createNewStationTimeRecord('Material pendiente', $now, $user_id, 'Registro inicial de componente clonado.')
                    ];
                    $newChild->save();
                }
            }

            return $newParent;
        });

        // Notificar si va a 'Material pendiente'
        if ($newProduction->id) {
            $notificationInstance = new ProductionForwardedNotification(
                $newProduction,
                'Material pendiente',
                $newProduction->quantity,
                $newProduction->folio
            );
            $this->sendNotification('production.forwarded.pendent_material', $notificationInstance);
        }

        return to_route('productions.edit', ['production' => $newProduction->id]);
    }

    public function returnStation(Request $request, Production $production)
    {
        $validated = $request->validate([
            'next_station' => 'required|string',
            'quantity' => 'required|numeric|min:0',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string', // Añadido para validación
        ]);

        $old_station = $production->station;
        $now = now();
        $user_id = auth()->id();

        // --- CORRECCIÓN: Pasar 'return' como modo ---
        $this->finalizeCurrentStation($production, $now, $user_id, "Regresado a {$validated['next_station']}", 'return');

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

        // --- INICIO: LÓGICA DE NOTIFICACIÓN MOVIDA AQUÍ ---
        Log::info('modo return. ' . $validated['next_station']);
        $folio = $production->parent->folio ?? $production->folio;

        $notificationInstance = new ProductionReturnedNotification(
            $production,
            $validated['next_station'],
            $validated['quantity'] ?? $production->quantity,
            $validated['reason'] ?? $validated['notes'] ?? '-No especificado-',
            $folio
        );

        if ($validated['next_station'] === 'X Reproceso') {
            $this->sendNotification('production.returned.reprocess', $notificationInstance);
        } elseif ($validated['next_station'] === 'Calidad') {
            $this->sendNotification('production.returned.quality', $notificationInstance);
        }
        // --- FIN: LÓGICA DE NOTIFICACIÓN MOVIDA ---

        $production->save();

        return back(); // Añadido return
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
                'quantity' => $data['quantity'],
                'date' => $data['date'],
                'notes' => $data['notes'],
                'is_last_delivery' => $data['is_last_delivery']
            ];
            $production->partials = $partials;
            $production->current_quantity += $data['quantity'];
        }

        $isLastDelivery = $data['type'] === 'Única' || $data['is_last_delivery'];
        if ($isLastDelivery) {
            if ($data['is_last_delivery']) {
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
                'quantity' => $data['quantity'],
                'date' => $data['date'],
                'notes' => $data['notes'],
                'is_last_delivery' => $data['is_last_delivery']
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
            ->whereNull('parent_id') // Solo exportar padres
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);

        if ($season !== 'Todas') {
            $query->whereHas('product', fn($q) => $q->where('season', $season));
        }

        if ($station !== 'Todos') {
            if ($station === 'Producción dividida') {
                $query->where('station', $station);
            } else {
                // Buscar padres que tengan *algún hijo* en esa estación
                $query->where(function ($q) use ($station) {
                    $q->where('station', $station) // O el padre está en esa estación (si es simple)
                        ->orWhereHas('children', fn($cq) => $cq->where('station', $station)); // O un hijo está ahí
                });
            }
        }

        $productions = $query->get();

        return Excel::download(new ProductionsExport($productions), 'producciones.xlsx');
    }

    public function exportExcelReport()
    {
        $folio = request('folio');
        // Buscar el padre por el folio
        $parentProduction = Production::where('folio', $folio)->whereNull('parent_id')->firstOrFail();

        // Cargar las producciones (padre + hijos)
        $productions = Production::with(['user', 'product', 'machine', 'modifiedUser'])
            ->where('id', $parentProduction->id)
            ->orWhere('parent_id', $parentProduction->id)
            ->get();

        return Excel::download(new ProductionReportExport($productions), 'reporte_produccion.xlsx');
    }

    public function importExcel(Request $request)
    {
        Excel::import(new ProductionsImport, $request->file('excel')[0]);
    }

    public function hojaViajera(Production $production)
    {
        // Si es un hijo, redirigir a la hoja viajera del padre
        if ($production->parent_id) {
            return to_route('productions.hoja-viajera', $production->parent_id);
        }

        // Cargar hijos con sus relaciones para la hoja viajera
        $production->load(['product', 'machine', 'modifiedUser', 'children.machine', 'children.product']);
        return inertia('Production/HojaViajera', compact('production'));
    }

    public function backfillStationTimes()
    {
        $updatedCount = 0;
        $adminUserId = auth()->id() ?? 1; // Usar admin o fallback
        Production::where(fn($q) => $q->whereNull('station_times')->orWhere('station_times', '=', '[]'))
            ->chunk(200, function ($productions) use (&$updatedCount, $adminUserId) {
                foreach ($productions as $production) {
                    $entryDate = $production->updated_at ?? $production->created_at;
                    $finalStations = ['Terminadas', 'Empaques terminado'];

                    $station_times = [];
                    if (in_array($production->station, $finalStations)) {
                        $station_times[] = $this->createFinalizedStationTimeRecord($production->station, $entryDate, $adminUserId, 'Registro final creado por script.');
                    } else if ($production->station === 'Producción dividida') {
                        $station_times[] = $this->createFinalizedStationTimeRecord($production->station, $entryDate, $adminUserId, 'Registro de padre creado por script.');
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
        // --- CÁLCULO DE HORARIO LABORAL ---
        $current_record['times']['waiting_seconds'] = $this->calculateBusinessSeconds($entered_at, $now);
        // --- FIN DE CÁLCULO ---

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

        // --- CÁLCULO DE HORARIO LABORAL ---
        // El tiempo efectivo se calcula desde 'started_at' hasta 'now', restando pausas.
        $current_record['times']['effective_seconds'] = $this->calculateEffectiveTime($current_record, $now);
        // --- FIN DE CÁLCULO ---

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

        // --- CÁLCULO DE HORARIO LABORAL ---
        // El tiempo total en pausa se recalcula sumando todas las pausas completadas.
        $current_record['times']['paused_seconds'] = $this->calculateTotalPausedTime($current_record);
        // --- FIN DE CÁLCULO ---

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
            'next_station' => 'required|string|max:255',
            'machine_id' => 'nullable|integer|exists:machines,id',
            'notes' => 'nullable|string|max:255',
            'reason' => 'nullable|string|max:500', //motivo de regreso de estación
            'quantity' => 'nullable|numeric|min:0',
            'date' => 'nullable|date',
            'scrap_quantity' => 'nullable|numeric|min:0',
            'shortage_quantity' => 'nullable|numeric|min:0',
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
            $production->close_quantity = $validated['quantity'];
            $production->close_production_date = $validated['date'];
            $production->production_scrap = $validated['scrap_quantity'];
            $production->production_shortage = $validated['shortage_quantity'];
            $production->scrap_quantity += $validated['scrap_quantity'];
            $production->shortage_quantity += $validated['shortage_quantity'];
        } elseif ($validated['next_station'] === 'Inspección') {
            $production->quality_quantity = $validated['quantity'];
            $production->quality_released_date = $validated['date'];
            $production->quality_scrap = $validated['scrap_quantity'];
            $production->quality_shortage = $validated['shortage_quantity'];
            $production->scrap_quantity += $validated['scrap_quantity'];
            $production->shortage_quantity += $validated['shortage_quantity'];
        } elseif ($validated['next_station'] === 'Empaques') {
            $production->packing_received_quantity = $validated['quantity'];
            $production->packing_received_date = $validated['date'];
        }

        // --- NEW NOTIFICATION LOGIC ---
        // --- CORRECCIÓN: Lógica de 'return' eliminada de aquí ---
        if ($mode === 'skip' || $mode === 'finish') {
            Log::info('modo siguiente: ' . $validated['next_station']);
            $folio = $production->parent->folio ?? $production->folio; // Obtener folio del padre si existe

            $notificationInstance = new ProductionForwardedNotification(
                $production,
                $validated['next_station'],
                $validated['quantity'] ?? $production->quantity, // Use passed quantity or fallback to total
                $folio // Pasar el folio
            );

            if ($validated['next_station'] === 'X Compra') {
                $this->sendNotification('production.forwarded.purchase', $notificationInstance);
            } elseif ($validated['next_station'] === 'Calidad') {
                $this->sendNotification('production.forwarded.quality', $notificationInstance);
            } elseif ($validated['next_station'] === 'Inspección') {
                $this->sendNotification('production.forwarded.inspection', $notificationInstance);
            } elseif ($validated['next_station'] === 'Terminadas') {
                $this->sendNotification('production.forwarded.finished_product', $notificationInstance);
            } elseif ($validated['next_station'] === 'Empaques') {
                $this->sendNotification('production.forwarded.packing', $notificationInstance);
            } elseif ($validated['next_station'] === 'Material pendiente') {
                $this->sendNotification('production.forwarded.pendent_material', $notificationInstance);
            } elseif ($validated['next_station'] === 'Surtido') {
                $this->sendNotification('production.forwarded.assortment', $notificationInstance);
            }
        }

        $production->save();
    }

    private function finalizeCurrentStation(Production &$production, Carbon $now, $user_id, $details = '', $mode = 'finish')
    {
        $current_record = $this->getCurrentStationRecord($production);
        if (!$current_record) return;

        // Si estaba en pausa, reanudarla virtualmente en 'now' para el cálculo
        if ($current_record['status'] === 'En pausa') {
            $last_pause_key = count($current_record['pauses']) - 1;
            if (isset($current_record['pauses'][$last_pause_key]) && $current_record['pauses'][$last_pause_key]['resumed_at'] === null) {
                $current_record['pauses'][$last_pause_key]['resumed_at'] = $now->toDateTimeString();
            }
        }

        $current_record['status'] = 'Finalizada';
        $current_record['finished_at'] = $now->toDateTimeString();
        $current_record['history'][] = ['event' => "Finalizada ({$mode})", 'timestamp' => $now->toDateTimeString(), 'user_id' => $user_id, 'details' => $details];

        // --- CÁLCULO DE HORARIO LABORAL (CORREGIDO) ---
        // Si se "salta" o se "regresa", el tiempo en esta estación fue "en espera".
        if ($mode === 'skip' || $mode === 'return') {
            $current_record['times']['waiting_seconds'] = $this->calculateBusinessSeconds(Carbon::parse($current_record['entered_at']), $now);
            // Asegurarse de que el tiempo de pausa se sume si se saltó/regresó desde una pausa
            $current_record['times']['paused_seconds'] = $this->calculateTotalPausedTime($current_record);
            $current_record['times']['effective_seconds'] = $this->calculateEffectiveTime($current_record, $now); // Calcular efectivo (debería ser 0 o muy poco si estaba en espera)

            // Ajuste final: si estaba en espera, el tiempo efectivo debe ser 0.
            if ($current_record['started_at'] === null) {
                $current_record['times']['effective_seconds'] = 0;
            }
        } else { // 'finish' - Movido desde 'En proceso' o 'En pausa'
            if (!empty($current_record['started_at'])) {
                $current_record['times']['waiting_seconds'] = $this->calculateBusinessSeconds(Carbon::parse($current_record['entered_at']), Carbon::parse($current_record['started_at']));
                $current_record['times']['paused_seconds'] = $this->calculateTotalPausedTime($current_record); // Helper ya usa horario laboral
                $current_record['times']['effective_seconds'] = $this->calculateEffectiveTime($current_record, $now); // Helper ya usa horario laboral
            } else {
                // Fallback si se finaliza sin haber iniciado (ej. script, error)
                $current_record['times']['waiting_seconds'] = $this->calculateBusinessSeconds(Carbon::parse($current_record['entered_at']), $now);
                $current_record['times']['effective_seconds'] = 0;
                $current_record['times']['paused_seconds'] = 0;
            }
        }
        // --- FIN DE CÁLCULO ---

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
            'station_name' => $station_name,
            'status' => 'En espera',
            'entered_at' => $now->toDateTimeString(),
            'started_at' => null,
            'finished_at' => null,
            'pauses' => [],
            'history' => [['event' => 'En espera', 'timestamp' => $now->toDateTimeString(), 'user_id' => $user_id, 'details' => $details]],
            'times' => ['waiting_seconds' => 0, 'paused_seconds' => 0, 'effective_seconds' => 0]
        ];
    }

    private function createFinalizedStationTimeRecord($station_name, Carbon $now, $user_id, $details = null)
    {
        return [
            'station_name' => $station_name,
            'status' => 'Finalizada',
            'entered_at' => $now->toDateTimeString(),
            'started_at' => $now->toDateTimeString(),
            'finished_at' => $now->toDateTimeString(),
            'pauses' => [],
            'history' => [['event' => 'Finalizada', 'timestamp' => $now->toDateTimeString(), 'user_id' => $user_id, 'details' => $details]],
            'times' => ['waiting_seconds' => 0, 'paused_seconds' => 0, 'effective_seconds' => 0]
        ];
    }

    /**
     * Calcula el tiempo total en pausa (en segundos laborales) para un registro de estación.
     */
    private function calculateTotalPausedTime($record): int
    {
        return (int) collect($record['pauses'] ?? [])->reduce(function ($carry, $pause) {
            if (isset($pause['resumed_at'])) {
                // --- CÁLCULO DE HORARIO LABORAL ---
                return $carry + $this->calculateBusinessSeconds(Carbon::parse($pause['paused_at']), Carbon::parse($pause['resumed_at']));
                // --- FIN DE CÁLCULO ---
            }
            return $carry;
        }, 0);
    }

    /**
     * Calcula el tiempo efectivo (en segundos laborales) para un registro de estación hasta un $end_time.
     */
    private function calculateEffectiveTime($record, Carbon $end_time): int
    {
        if (empty($record['started_at'])) return 0;

        // --- CÁLCULO DE HORARIO LABORAL ---
        $started_at = Carbon::parse($record['started_at']);

        // 1. Calcular el tiempo laboral total desde el inicio hasta el fin.
        $total_span_business_seconds = $this->calculateBusinessSeconds($started_at, $end_time);

        // 2. Calcular el tiempo total en pausa (que ya usa horario laboral).
        $total_paused_seconds = $this->calculateTotalPausedTime($record);

        // 3. El tiempo efectivo es el total menos las pausas.
        $effective_seconds = $total_span_business_seconds - $total_paused_seconds;
        // --- FIN DE CÁLCULO ---

        return max(0, $effective_seconds); // Ensure it's not negative
    }

    // --- INICIO: NUEVO HELPER DE HORARIO LABORAL ---
    /**
     * Calcula los segundos transcurridos entre dos fechas, contando solo el horario laboral.
     * Horario: Lunes a Viernes, de 08:00:00 a 17:30:00.
     *
     * @param Carbon $start Fecha y hora de inicio.
     * @param Carbon $end Fecha y hora de fin.
     * @return int Segundos laborales transcurridos.
     */
    private function calculateBusinessSeconds(Carbon $start, Carbon $end): int
    {
        // Si el inicio es después del fin, no hay tiempo transcurrido.
        if ($start->greaterThanOrEqualTo($end)) {
            return 0;
        }

        $totalBusinessSeconds = 0;

        // Iterar por cada día en el rango
        $period = CarbonPeriod::create($start->copy()->startOfDay(), '1 day', $end->copy()->startOfDay());

        foreach ($period as $day) {
            // Omitir fines de semana
            if (!$day->isWeekday()) {
                continue;
            }

            // Establecer las horas de inicio y fin laboral para ESTE día
            $workdayStart = $day->copy()->setTimeFromTimeString(self::WORK_START_TIME);
            $workdayEnd = $day->copy()->setTimeFromTimeString(self::WORK_END_TIME);

            // Determinar el inicio efectivo para el cálculo de este día
            // Es el más tardío entre el inicio de la jornada y el inicio real ($start)
            $effectiveStart = $start->greaterThan($workdayStart) ? $start : $workdayStart;

            // Determinar el fin efectivo para el cálculo de este día
            // Es el más temprano entre el fin de la jornada y el fin real ($end)
            $effectiveEnd = $end->lessThan($workdayEnd) ? $end : $workdayEnd;

            // Si el inicio efectivo es ANTES del fin efectivo, significa que hay un solapamiento
            // de tiempo laboral en este día.
            if ($effectiveStart->lessThan($effectiveEnd)) {
                $totalBusinessSeconds += $effectiveStart->diffInSeconds($effectiveEnd);
            }
        }

        return (int) $totalBusinessSeconds;
    }
    // --- FIN: NUEVO HELPER DE HORARIO LABORAL ---
}
