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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
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
        $next_production = Production::latest('folio')->first();
        $next_production = $next_production ? $next_production->folio + 1 : 1;

        return inertia('Production/Create', compact('next_production'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'folio' => 'required|numeric|unique:productions',
            'type' => 'required|string|max:255',
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
        ], [
            'varnish_type.required_if_accepted' => 'El tipo de barniz es requerido.',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['materials'] = [$validated['materials']];

        $production = Production::create($validated + ['modified_user_id' => auth()->id()]);

        // notificar si pasa a Empaques
        if ($validated['station'] == 'Material pendiente') {
            $notificationInstance = new ProductionForwardedNotification(
                $production,
                'Material pendiente',
                $production->current_quantity
            );
            $this->sendNotification('production.forwarded.pendent_material', $notificationInstance);
        }
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
            'type' => 'required|string|max:255',
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

        $validated['materials'] = [$validated['materials']];

        $production->update($validated + ['modified_user_id' => auth()->id()]);

        return to_route('productions.index');
    }

    public function updateStation(Request $request, Production $production)
    {
        $old_station = $production->station;
        $new_station = $request->station;
        $user_id = auth()->id();
        $now = now();

        $station_times = $production->station_times ?? [];
        $last_station_key = count($station_times) - 1;

        if ($last_station_key >= 0) {
            $station_times[$last_station_key]['status'] = 'Finalizada';
            $station_times[$last_station_key]['finished_at'] = $now;
            // Add history event for finishing the old station
            $station_times[$last_station_key]['history'][] = [
                'event' => 'Finalizada (movimiento manual)',
                'timestamp' => $now->toDateTimeString(),
                'user_id' => $user_id,
                'details' => "Movido de {$old_station} a {$new_station}"
            ];
        }

        // Add new station record
        $station_times[] = [
            'station_name' => $new_station,
            'status' => 'En espera',
            'entered_at' => $now->toDateTimeString(),
            'started_at' => null,
            'finished_at' => null,
            'pauses' => [],
            'history' => [
                ['event' => 'En espera', 'timestamp' => $now->toDateTimeString(), 'user_id' => $user_id, 'details' => null]
            ],
            'times' => ['waiting_seconds' => 0, 'paused_seconds' => 0, 'effective_seconds' => 0]
        ];

        $production->station = $new_station;
        $production->station_times = $station_times;
        $production->modified_user_id = $user_id;
        $production->save();
    }

    public function updateMachine(Request $request, Production $production)
    {
        $production->machine_id = $request->machine_id;
        $production->modified_user_id = auth()->id();
        $production->save();
    }

    public function destroy(Production $production)
    {
        $production->delete();
    }

    public function getByPage()
    {
        $page = request('page', 1);
        $search = request('search', null);

        $perPage = 30;
        $offset = ($page - 1) * $perPage;

        $query = Production::with(['user', 'product', 'machine'])->latest('id');

        // Obtener los estacion permitidos para el usuario
        $user = auth()->user();
        $permissions = $user->getAllPermissions()
            ->filter(function ($permission) {
                return str_starts_with($permission->name, 'Ver en estacion');
            })
            ->map(function ($permission) {
                return str_replace('Ver en estacion ', '', $permission->name);
            })
            ->toArray();

        // Si el usuario tiene permisos específicos, filtrar por esos estación
        if (!empty($permissions)) {
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
        $lastProductionFolio = Production::latest('folio')->first()?->folio;
        $newProduction = $production->replicate([
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
        ]);
        $newProduction->folio = $lastProductionFolio + 1;
        $newProduction->type = 'Repetido';
        $newProduction->station = 'Solicitado';
        $newProduction->start_date = now();
        $newProduction->save();

        return to_route('productions.edit', ['production' => $newProduction->id]);
    }

    public function returnStation(Request $request, Production $production)
    {
        if ($production->station === 'Calidad') {
            $newStation = 'X Reproceso';
            $scrap = 0;
            $eventKey = 'production.returned.reprocess';
        } else if ($production->station === 'Inspección') {
            $newStation = 'Calidad';
            $scrap = $production->scrap_quantity;
            $eventKey = 'production.returned.quality';
        }

        $returns = $production->returns ?? [];
        $returns[] = [
            'old_station' => $production->station,
            'new_station' => $newStation,
            'date' => now(),
            'quantity' => $request->quantity,
            'reason' => $request->reason,
            'user' => auth()->user()->name,
        ];

        $production->update([
            'station' => $newStation,
            'scrap_quantity' => $scrap,
            'returns' => $returns,
            'modified_user_id' => auth()->id(),
        ]);

        $notificationInstance = new ProductionReturnedNotification(
            $production,
            $newStation,
            $request->quantity,
            $request->reason ?? 'No se especificó un motivo.'
        );

        $this->sendNotification($eventKey, $notificationInstance);
    }

    public function productionRelease(Request $request, Production $production)
    {
        $validatedData = $request->validate([
            'close_quantity' => 'required|numeric|min:0',
            'close_production_date' => 'required|date',
            'scrap_quantity' => 'required|numeric|min:0',
            'shortage_quantity' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $production->update([
            'station' => 'Calidad',
            'close_quantity' => $validatedData['close_quantity'],
            'close_production_date' => $validatedData['close_production_date'],
            'close_production_notes' => $validatedData['notes'],
            // Guardar valores específicos de la estación de producción
            'production_scrap' => $validatedData['scrap_quantity'],
            'production_shortage' => $validatedData['shortage_quantity'],
            // Actualizar los totales generales (en esta etapa son los mismos)
            'scrap_quantity' => $validatedData['scrap_quantity'],
            'shortage_quantity' => $validatedData['shortage_quantity'],
            'modified_user_id' => auth()->id(),
        ]);

        $notificationInstance = new ProductionForwardedNotification(
            $production,
            'Calidad',
            $request->close_quantity
        );
        $this->sendNotification('production.forwarded.quality', $notificationInstance);
    }

    public function qualityRelease(Request $request, Production $production)
    {
        $validatedData = $request->validate([
            'quality_quantity' => 'required|numeric|min:0',
            'quality_released_date' => 'required|date',
            'notes' => 'nullable|string',
            'scrap_quantity' => 'required|numeric|min:0',
            'shortage_quantity' => 'required|numeric|min:0',
        ]);

        $production->update([
            'station' => 'Inspección',
            'quality_quantity' => $validatedData['quality_quantity'],
            'quality_released_date' => $validatedData['quality_released_date'],
            'quality_notes' => $validatedData['notes'],
            // Guardar valores específicos de la estación de calidad
            'quality_scrap' => $validatedData['scrap_quantity'],
            'quality_shortage' => $validatedData['shortage_quantity'],
            // Actualizar los totales sumando los de la estación anterior
            'scrap_quantity' => $production->production_scrap + $validatedData['scrap_quantity'],
            'shortage_quantity' => $production->production_shortage + $validatedData['shortage_quantity'],
            'modified_user_id' => auth()->id(),
        ]);

        // Enviar la notificación
        $notificationInstance = new ProductionForwardedNotification(
            $production,
            'Inspección',
            $validatedData['quality_quantity']
        );
        $eventKey = 'production.forwarded.inspection';
        $this->sendNotification($eventKey, $notificationInstance);
    }

    public function inspectionRelease(Request $request, Production $production)
    {
        // 1. Validar todos los datos de entrada para mayor seguridad
        $validatedData = $request->validate([
            'production_close_type' => 'required|string|in:Única,Parcialidades',
            'quantity' => 'required|numeric|min:0',
            'date' => 'required',
            'scrap_quantity' => 'requiredif:production_close_type,Única|numeric|min:0',
            'shortage_quantity' => 'requiredif:production_close_type,Única|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // 2. Acumular los valores de la estación de inspección
        $production->current_quantity += $validatedData['quantity'];
        $production->inspection_scrap += $validatedData['scrap_quantity'];
        $production->inspection_shortage += $validatedData['shortage_quantity'];

        // 3. Recalcular los totales generales sumando todas las estaciones
        $production->scrap_quantity = $production->production_scrap + $production->quality_scrap + $production->inspection_scrap;
        $production->shortage_quantity = $production->production_shortage + $production->quality_shortage + $production->inspection_shortage;

        // 4. Actualizar los campos generales y de parcialidades
        $production->production_close_type = $validatedData['production_close_type'];
        $production->inspection_notes = $validatedData['notes'];
        $production->modified_user_id = auth()->id();

        if ($validatedData['production_close_type'] === 'Parcialidades') {
            $partials = $production->partials ?? [];
            $partials[] = [
                'quantity' => $validatedData['quantity'],
                'date' => $validatedData['date'],
                'notes' => $validatedData['notes'],
            ];
            $production->partials = $partials;
        }

        // 5. Guardar todos los cambios en una sola operación
        $production->save();

        // 6. Determinar si la producción debe finalizar
        $isFinished = ($production->current_quantity >= $production->quantity) || ($validatedData['production_close_type'] === 'Única');

        if ($isFinished) {
            $this->finishProduction($request, $production);
        }
    }

    // --- NUEVO MÉTODO ---
    // Registra la cantidad inicial que se mueve a la estación de empaques
    public function moveToPacking(Request $request, Production $production)
    {
        $validatedData = $request->validate([
            'packing_received_quantity' => 'required|numeric|min:0',
            'packing_received_date' => 'required|date',
        ]);

        $production->update([
            'station' => 'Empaques',
            'packing_received_quantity' => $validatedData['packing_received_quantity'],
            'packing_received_date' => $validatedData['packing_received_date'],
            'current_quantity' => 0, // Reiniciar la cantidad actual para el proceso de empaque
            'modified_user_id' => auth()->id(),
        ]);

        // notificar si pasa a Empaques
        $notificationInstance = new ProductionForwardedNotification(
            $production,
            'Empaques',
            $validatedData['packing_received_quantity']
        );
        $this->sendNotification('production.forwarded.packing', $notificationInstance);
    }

    // --- NUEVO MÉTODO ---
    // Procesa las entregas (únicas o parciales) desde la estación de empaques
    public function packingRelease(Request $request, Production $production)
    {
        $validatedData = $request->validate([
            'packing_close_type' => 'required|string|in:Única,Parcialidades',
            'quantity' => 'required|numeric|min:0',
            'date' => 'required|date',
            'scrap_quantity' => 'required_if:packing_close_type,Única|numeric|min:0',
            'shortage_quantity' => 'required_if:packing_close_type,Única|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $production->packing_close_type = $validatedData['packing_close_type'];
        $production->packing_notes = $validatedData['notes'];
        $production->packing_scrap = $validatedData['scrap_quantity'] ?? 0;
        $production->packing_shortage = $validatedData['shortage_quantity'] ?? 0;
        $production->modified_user_id = auth()->id();

        if ($validatedData['packing_close_type'] === 'Parcialidades') {
            $partials = $production->packing_partials ?? [];
            $partials[] = ['quantity' => $validatedData['quantity'], 'date' => $validatedData['date'], 'notes' => $validatedData['notes']];
            $production->packing_partials = $partials;
            $production->current_quantity += $validatedData['quantity'];
        } else { // 'Única'
            $production->current_quantity = $validatedData['quantity'];
        }

        $isFinished = ($production->current_quantity >= $production->packing_received_quantity);
        if ($isFinished) {
            $production->station = 'Empaques terminado';
            $production->packing_finished_date = now();
        }

        $production->save();
    }

    // --- NUEVO MÉTODO ---
    // Agrega una nueva entrega parcial a empaques
    public function addPackingPartial(Request $request, Production $production)
    {
        $validatedData = $request->validate([
            'quantity' => 'required|numeric|min:0',
            'date' => 'required|date',
            'notes' => 'nullable|string',
            'is_last_delivery' => 'boolean',
        ]);

        $partials = $production->packing_partials ?? [];
        $partials[] = ['quantity' => $validatedData['quantity'], 'date' => $validatedData['date'], 'notes' => $validatedData['notes'], 'is_last_delivery' => $validatedData['is_last_delivery']];

        $production->packing_partials = $partials;
        $production->modified_user_id = auth()->id();
        $production->current_quantity += $validatedData['quantity'];

        if ($validatedData['is_last_delivery'] || $production->current_quantity >= $production->packing_received_quantity) {
            $production->station = 'Empaques terminado';
            $production->packing_finished_date = $validatedData['date'];
        }

        $production->save();
    }


    public function finishProduction(Request $request, Production $production)
    {
        $production->update([
            'station' => 'Terminadas',
            'finish_date' => now(),
            'modified_user_id' => auth()->id(),
        ]);

        $notificationInstance = new ProductionForwardedNotification(
            $production,
            'Terminadas',
            $production->current_quantity
        );
        $eventKey = 'production.forwarded.finished_product';
        $this->sendNotification($eventKey, $notificationInstance);
    }

    public function addPartial(Request $request, Production $production)
    {

        $validatedData = $request->validate([
            'quantity' => 'required|numeric|min:0',
            'date' => 'required|date',
            'notes' => 'nullable|string',
            'scrap_quantity' => 'required|numeric|min:0',
            'shortage_quantity' => 'required|numeric|min:0',
            'is_last_delivery' => 'boolean',
        ]);

        $partials = $production->partials ?? [];
        $partials[] = [
            'quantity' => $validatedData['quantity'],
            'date' => $validatedData['date'],
            'notes' => $validatedData['notes'],
            'scrap' => $validatedData['scrap_quantity'],
            'difference' => $validatedData['shortage_quantity'],
            'is_last_delivery' => $validatedData['is_last_delivery'],
        ];

        // Sumar a los totales de la estación de inspección
        $inspectionScrap = ($production->inspection_scrap ?? 0) + $validatedData['scrap_quantity'];
        $inspectionShortage = ($production->inspection_shortage ?? 0) + $validatedData['shortage_quantity'];

        $production->partials = $partials;
        $production->modified_user_id = auth()->id();
        $production->current_quantity += $validatedData['quantity'];

        // Actualizar los totales de inspección
        $production->inspection_scrap = $inspectionScrap;
        $production->inspection_shortage = $inspectionShortage;

        // Actualizar los totales generales
        $production->scrap_quantity = $production->production_scrap + $production->quality_scrap + $inspectionScrap;
        $production->shortage_quantity = $production->production_shortage + $production->quality_shortage + $inspectionShortage;

        if ($validatedData['is_last_delivery'] || $production->current_quantity >= $production->quantity) {
            $production->station = 'Terminadas';
            $production->finish_date = $validatedData['date'];
        }

        $production->save();
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
            ->when($season !== 'Todas', function ($query) use ($season) {
                return $query->whereHas('product', function ($query) use ($season) {
                    $query->where('season', $season);
                });
            })
            ->when($station !== 'Todos', function ($query) use ($station) {
                return $query->where('station', $station);
            })
            ->get();

        return Excel::download(new ProductionsExport($productions), 'producciones.xlsx');
    }

    public function exportExcelReport()
    {
        $folio = request('folio');

        $productions = Production::with(['user', 'product', 'machine', 'modifiedUser'])
            ->where('folio', $folio)
            ->get();

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

    // ===================================================================
    // NUEVOS MÉTODOS PARA EL CONTROL DE TIEMPOS
    // ===================================================================

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

    public function startStationProcess(Production $production)
    {
        $current_record = $this->getCurrentStationRecord($production);
        if (!$current_record || $current_record['status'] !== 'En espera') {
            return back()->with('error', 'La estación no está en espera.');
        }

        $now = now();
        $current_record['status'] = 'En proceso';
        $current_record['started_at'] = $now->toDateTimeString();
        $current_record['history'][] = [
            'event' => 'Iniciada',
            'timestamp' => $now->toDateTimeString(),
            'user_id' => auth()->id(),
            'details' => null,
        ];

        $this->updateCurrentStationRecord($production, $current_record);
        $production->save();
        return back();
    }

    public function pauseStationProcess(Request $request, Production $production)
    {
        $request->validate(['reason' => 'required|string|max:255']);

        $current_record = $this->getCurrentStationRecord($production);
        if (!$current_record || $current_record['status'] !== 'En proceso') {
            return back()->with('error', 'La estación no está en proceso.');
        }

        $now = now();
        $current_record['status'] = 'En pausa';
        $current_record['pauses'][] = [
            'paused_at' => $now->toDateTimeString(),
            'resumed_at' => null,
            'reason' => $request->reason,
            'user_id' => auth()->id(),
        ];
        $current_record['history'][] = [
            'event' => 'Pausada',
            'timestamp' => $now->toDateTimeString(),
            'user_id' => auth()->id(),
            'details' => $request->reason,
        ];

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

        // Find the last pause and set its resumed_at
        $last_pause_key = count($current_record['pauses']) - 1;
        if (isset($current_record['pauses'][$last_pause_key])) {
            $current_record['pauses'][$last_pause_key]['resumed_at'] = $now->toDateTimeString();
        }

        $current_record['history'][] = [
            'event' => 'Reanudada',
            'timestamp' => $now->toDateTimeString(),
            'user_id' => auth()->id(),
            'details' => null,
        ];

        $this->updateCurrentStationRecord($production, $current_record);
        $production->save();
        return back();
    }

    public function finishAndMoveStation(Request $request, Production $production)
    {
        $request->validate(['next_station' => 'required|string|max:255']);

        $current_record = $this->getCurrentStationRecord($production);
        if (!$current_record || !in_array($current_record['status'], ['En proceso', 'En pausa'])) {
            return back()->with('error', 'La estación no puede ser finalizada desde su estado actual.');
        }

        $now = now();
        // If it was paused, resume it first to calculate time correctly
        if ($current_record['status'] === 'En pausa') {
            $last_pause_key = count($current_record['pauses']) - 1;
            $current_record['pauses'][$last_pause_key]['resumed_at'] = $now->toDateTimeString();
        }

        $current_record['status'] = 'Finalizada';
        $current_record['finished_at'] = $now->toDateTimeString();
        $current_record['history'][] = [
            'event' => 'Finalizada',
            'timestamp' => $now->toDateTimeString(),
            'user_id' => auth()->id(),
            'details' => "Movido a {$request->next_station}",
        ];

        // Calculate times
        $entered_at = Carbon::parse($current_record['entered_at']);
        $started_at = Carbon::parse($current_record['started_at']);
        $finished_at = Carbon::parse($current_record['finished_at']);

        $total_paused_seconds = 0;
        foreach ($current_record['pauses'] as $pause) {
            $paused_at = Carbon::parse($pause['paused_at']);
            $resumed_at = Carbon::parse($pause['resumed_at']);
            $total_paused_seconds += $resumed_at->diffInSeconds($paused_at);
        }

        $current_record['times']['waiting_seconds'] = $started_at->diffInSeconds($entered_at);
        $current_record['times']['paused_seconds'] = $total_paused_seconds;
        $current_record['times']['effective_seconds'] = $finished_at->diffInSeconds($started_at) - $total_paused_seconds;

        $this->updateCurrentStationRecord($production, $current_record);

        // Create new station record
        $station_times = $production->station_times;
        $station_times[] = [
            'station_name' => $request->next_station,
            'status' => 'En espera',
            'entered_at' => $now->toDateTimeString(),
            'started_at' => null,
            'finished_at' => null,
            'pauses' => [],
            'history' => [
                ['event' => 'En espera', 'timestamp' => $now->toDateTimeString(), 'user_id' => auth()->id(), 'details' => null]
            ],
            'times' => ['waiting_seconds' => 0, 'paused_seconds' => 0, 'effective_seconds' => 0]
        ];

        $production->station_times = $station_times;
        $production->station = $request->next_station;
        $production->save();

        return back();
    }
}
