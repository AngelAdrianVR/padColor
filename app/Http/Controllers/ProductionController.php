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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Maatwebsite\Excel\Facades\Excel;

class ProductionController extends Controller
{
    use NotifiesViaEvents;

    public function index()
    {
        $productions = Production::latest('folio')->get(['folio', 'station']);
        $next_production = Production::latest('folio')->first();
        $next_production = $next_production ? $next_production->folio + 1 : 1;

        return inertia('Production/Index', compact('productions', 'next_production'));
    }

    public function create()
    {
        //
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

        return to_route('productions.index', ["currentTab" => 2]);
    }

    public function updateStation(Request $request, Production $production)
    {
        $production->station = $request->station;
        $production->modified_user_id = auth()->id();
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
}
