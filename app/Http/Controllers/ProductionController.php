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
        ]);

        // Validación adicional para asegurar que los números cuadren
        $totalDifference = $production->quantity - $validatedData['close_quantity'];
        $justifiedDifference = $validatedData['scrap_quantity'] + $validatedData['shortage_quantity'];

        if ($totalDifference > 0 && abs($totalDifference - $justifiedDifference) > 0.01) { // Se usa una tolerancia pequeña
            return back()->withErrors(['scrap_quantity' => 'La suma de la merma y el faltante no coincide con la diferencia total.']);
        }

        $production->update([
            'station' => 'Calidad',
            'close_quantity' => $validatedData['close_quantity'],
            'scrap_quantity' => $validatedData['scrap_quantity'],
            'shortage_quantity' => $validatedData['shortage_quantity'],
            'close_production_date' => $validatedData['close_production_date'],
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
        // 1. Validar los datos de entrada
        $validatedData = $request->validate([
            'quality_quantity' => 'required|numeric|min:0',
            'quality_released_date' => 'required|date',
        ]);

        // 2. Calcular la merma generada específicamente en la etapa de Calidad
        $scrapFromQuality = $production->close_quantity - $validatedData['quality_quantity'];

        // 3. Calcular la nueva merma total.
        // Se suma:
        // - La merma que ya venía de Producción ($production->scrap_quantity).
        // - La nueva merma generada en Calidad ($scrapFromQuality).
        $totalScrap = $production->scrap_quantity + $scrapFromQuality;

        // 4. Actualizar el registro de producción
        $production->update([
            'station' => 'Inspección',
            'quality_quantity' => $validatedData['quality_quantity'],
            'scrap_quantity' => $totalScrap,
            'quality_released_date' => $validatedData['quality_released_date'],
            'modified_user_id' => auth()->id(),
        ]);

        // 5. Enviar la notificación
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
        $production->production_close_type = $request->production_close_type;
        $production->modified_user_id = auth()->id();
        $production->current_quantity += $request->close_quantity;

        if ($request->production_close_type === 'Parcialidades') {
            $partials = $production->partilas;
            $partials[] = [
                'quantity' => $request->close_quantity,
                'date' => $request->close_production_date,
            ];

            $production->partials = $partials;
            $production->save();
        }

        if ($production->current_quantity >= $production->quantity) {
            $this->finishProduction($request, $production);
        }
    }

    public function finishProduction(Request $request, Production $production)
    {
        $production->update([
            'station' => 'Terminadas',
            'finish_date' => today(),
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
        $partials = $production->partials ?? [];
        $partials[] = [
            'quantity' => $request->quantity,
            'date' => $request->date,
        ];

        $production->partials = $partials;
        $production->modified_user_id = auth()->id();
        $production->current_quantity += $request->quantity;

        // revisar si la cantidad actual entregada es mayor o igual a la cantidad total para cambiar la estación a 'Terminadas'
        if ($production->current_quantity >= $production->quantity) {
            $production->station = 'Terminadas';
            $production->finish_date = $request->date;
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
        // $request->validate([
        //     'excel' => 'required|mimes:xlsx,xls,csv',
        // ]);
        // dd($request->excel[0]);
        // Log::info('excel: ', $request->excel);

        Excel::import(new ProductionsImport, $request->file('excel')[0]);
    }

    public function hojaViajera(Production $production)
    {
        $production->load(['product', 'machine', 'modifiedUser']);

        return inertia('Production/HojaViajera', compact('production'));
    }
}
