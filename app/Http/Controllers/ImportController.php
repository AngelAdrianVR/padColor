<?php

namespace App\Http\Controllers;

use App\Models\CustomsAgent;
use App\Models\Import;
use App\Models\ImportCost;
use App\Models\ImportPayment;
use App\Models\RawMaterial;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Exports\ImportsReportExport;
use App\Models\NotificationEvent;
use App\Models\User;
use App\Notifications\ImportArrivedAtDestination;
use App\Traits\NotifiesViaEvents;
use Illuminate\Support\Facades\Notification;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    use NotifiesViaEvents;

    public function index(Request $request)
    {
        // 1. Validamos los filtros que vienen de la URL
        $request->validate([
            'search' => 'nullable|string|max:255',
            'supplier' => 'nullable|integer|exists:suppliers,id',
            'dates' => 'nullable|array|size:2',
            'dates.*' => 'nullable|date_format:Y-m-d',
        ]);

        // 2. Construimos la consulta, añadiendo 'media' para cargar los archivos
        $importsQuery = Import::with('supplier', 'customsAgent', 'user', 'rawMaterials', 'media', 'costs.payments.media', 'activities.causer');

        // 3. Aplicamos los filtros si existen en la petición
        $importsQuery->when($request->filled('search'), function ($query) use ($request) {
            $query->where('id', 'like', '%' . $request->search . '%');
        });

        $importsQuery->when($request->filled('supplier'), function ($query) use ($request) {
            $query->where('supplier_id', $request->supplier);
        });

        $importsQuery->when($request->filled('dates'), function ($query) use ($request) {
            $startDate = $request->dates[0];
            $endDate = $request->dates[1] . ' 23:59:59';
            $query->whereBetween('created_at', [$startDate, $endDate]);
        });

        // 4. Ejecutamos la consulta
        $importsCollection = $importsQuery->get();

        // 5. Formateamos los documentos para cada importación
        $importsCollection->each(function ($import) {
            $import->documents = $import->getMedia('*')->map(function ($media) {
                return [
                    'id' => $media->id,
                    'file_name' => $media->file_name,
                    'size' => $media->size,
                    'original_url' => $media->getUrl(),
                    'classification' => $media->collection_name,
                ];
            });

            // Verificamos si la relación 'costs' fue cargada y no está vacía
            if ($import->relationLoaded('costs') && $import->costs) {
                $import->costs->each(function ($cost) {
                    // La relación 'payments' ya fue cargada por el with()
                    // Sumamos el campo 'amount' de la colección de pagos
                    // y lo añadimos como un nuevo atributo al objeto de costo.
                    $cost->payments_sum_amount = $cost->payments->sum('amount');
                });
            }

            $costIds = $import->costs->pluck('id');
            $paymentIds = $import->costs->flatMap(fn($cost) => $cost->payments->pluck('id'));

            $importActivities = $import->activities()->with('causer')->get();
            $costActivities = Activity::whereIn('subject_id', $costIds)->where('subject_type', \App\Models\ImportCost::class)->with('causer')->get();
            $paymentActivities = Activity::whereIn('subject_id', $paymentIds)->where('subject_type', \App\Models\ImportPayment::class)->with('causer')->get();

            $fullHistory = $importActivities->merge($costActivities)->merge($paymentActivities)->sortByDesc('created_at');

            $import->history = $fullHistory->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'log_message' => $activity->description, // La descripción completa del modelo
                    'event' => $activity->event, // El evento crudo: 'created', 'updated', 'deleted'
                    'properties' => $activity->properties,
                    'causer' => $activity->causer ? [
                        'name' => $activity->causer->name,
                        'avatar' => $activity->causer->profile_photo_url,
                    ] : ['name' => 'Sistema', 'avatar' => null],
                    'created_at' => $activity->created_at->toDateTimeString(),
                ];
            })->values();
        });

        // 6. Agrupamos por estado para el Kanban
        $imports = $importsCollection->groupBy('status');

        // 7. Obtenemos los proveedores para el dropdown del filtro
        $suppliers = Supplier::all(['id', 'name']);

        // 8. Renderizamos la vista de Inertia
        return Inertia::render('Import/Index', [
            'imports' => $imports,
            'suppliers' => $suppliers,
            'filters' => $request->only(['search', 'supplier', 'dates']),
        ]);
    }

    public function create()
    {
        // Obtenemos los datos necesarios para los selects del formulario
        $suppliers = Supplier::all(['id', 'name']);
        $customsAgents = CustomsAgent::all(['id', 'name']);
        $rawMaterials = RawMaterial::all(['id', 'name', 'sku']);

        return Inertia::render('Import/Create', [
            'suppliers' => $suppliers,
            'customsAgents' => $customsAgents,
            'rawMaterials' => $rawMaterials,
        ]);
    }

    public function store(Request $request)
    {
        // 1. Validación de los datos del formulario
        $validatedData = $request->validate([
            'supplier_id' => 'required',
            'customs_agent_id' => 'required',
            'incoterm' => 'required|string|max:255',
            'estimated_ship_date' => 'nullable|date',
            'estimated_arrival_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'products' => 'required|array|min:1',
            'products.*.raw_material_id' => 'required|exists:raw_materials,id',
            'products.*.quantity' => 'required|numeric|min:0.01',
            'products.*.unit_cost' => 'required|numeric|min:0.01',
            'documents' => 'nullable|array',
            'documents.*.file' => 'required|file|max:10240', // max 10MB
            'documents.*.classification' => 'required|string',
        ]);

        // 2. Usamos una transacción para asegurar la integridad de los datos
        DB::transaction(function () use ($validatedData, $request) {

            // 3. Crear la importación principal
            $import = Import::create([
                'supplier_id' => $validatedData['supplier_id'],
                'customs_agent_id' => $validatedData['customs_agent_id'],
                'incoterm' => $validatedData['incoterm'],
                'estimated_ship_date' => $validatedData['estimated_ship_date'],
                'estimated_arrival_date' => $validatedData['estimated_arrival_date'],
                'notes' => $validatedData['notes'],
                'user_id' => auth()->id(), // Asignar el usuario que la crea
                'status' => 'Con proveedor', // Estado inicial del Kanban
                // Generar un folio único. Puedes personalizar esta lógica.
                'folio' => 'IMP-' . (Import::latest('id')->first()?->id + 1),
            ]);

            // 4. Adjuntar los productos a la importación (tabla pivote) y documentos
            foreach ($validatedData['products'] as $product) {
                $rawMaterial = RawMaterial::find($product['raw_material_id']);
                $this->attachRawMaterial(new Request($product), $import, $rawMaterial);
            }

            if ($request->has('documents')) {
                foreach ($request->documents as $document) {
                    $this->storeDocument(new Request($document), $import);
                }
            }
        });

        // 5. Redirigir al índice
        return redirect()->route('imports.index');
    }

    public function show(Import $import)
    {
        //
    }

    public function edit(Import $import)
    {
        // Cargamos las relaciones necesarias
        $import->load('rawMaterials', 'media');

        // Formateamos los productos para el frontend, incluyendo los datos de la tabla pivote
        $import->products = $import->rawMaterials->map(function ($rawMaterial) {
            return [
                'raw_material_id' => $rawMaterial->id,
                'quantity' => $rawMaterial->pivot->quantity,
                'unit_cost' => $rawMaterial->pivot->unit_cost,
                'currency' => $rawMaterial->pivot->currency,
            ];
        });

        // Formateamos los documentos para el frontend
        $import->documents = $import->getMedia('*')->map(function ($media) {
            return [
                'id' => $media->id,
                'file_name' => $media->file_name,
                'size' => $media->size,
                'original_url' => $media->getUrl(),
                'classification' => $media->collection_name,
            ];
        });

        return Inertia::render('Import/Edit', [
            'import' => $import,
            'suppliers' => Supplier::all(['id', 'name']),
            'customsAgents' => CustomsAgent::all(['id', 'name']),
            'rawMaterials' => RawMaterial::all(['id', 'name', 'sku']),
        ]);
    }

    public function update(Request $request, Import $import)
    {
        // 1. Validación (similar a store, pero permite campos opcionales para no forzar re-subir archivos)
        $validatedData = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'customs_agent_id' => 'nullable|exists:customs_agents,id',
            'incoterm' => 'required|string|max:255',
            'estimated_ship_date' => 'nullable|date',
            'estimated_arrival_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'products' => 'required|array|min:1',
            'products.*.raw_material_id' => 'required|exists:raw_materials,id',
            'products.*.quantity' => 'required|numeric|min:0.01',
            'products.*.unit_cost' => 'required|numeric|min:0.01',
            'new_documents' => 'nullable|array',
            'new_documents.*.file' => 'required|file|max:10240', // max 10MB
            'new_documents.*.classification' => 'required|string',
            'documents_to_delete' => 'nullable|array',
            'documents_to_delete.*' => 'integer|exists:media,id',
        ]);

        DB::transaction(function () use ($validatedData, $request, $import) {
            $import->update($request->except('products', 'new_documents', 'documents_to_delete'));

            $newProductsData = collect($validatedData['products'])->keyBy('raw_material_id');
            $currentProducts = $import->rawMaterials()->get()->keyBy('id');

            // 1. Desvincular productos que ya no están en la lista
            $idsToDetach = $currentProducts->keys()->diff($newProductsData->keys());
            foreach ($idsToDetach as $id) {
                $this->detachRawMaterial($import, $currentProducts[$id]);
            }

            // 2. Vincular nuevos productos y actualizar los existentes
            foreach ($newProductsData as $id => $productData) {
                $rawMaterial = RawMaterial::find($id);
                if ($currentProducts->has($id)) {
                    // El producto ya existe, verificar si hay cambios
                    $pivot = $currentProducts[$id]->pivot;
                    if ($pivot->quantity != $productData['quantity'] || $pivot->unit_cost != $productData['unit_cost']) {
                        $this->updateRawMaterial($import, $rawMaterial, $productData, $pivot);
                    }
                } else {
                    // Es un producto nuevo, vincularlo
                    $this->attachRawMaterial(new Request($productData), $import, $rawMaterial);
                }
            }

            // --- Lógica para documentos ---
            if (!empty($validatedData['documents_to_delete'])) {
                $mediaItems = Media::find($validatedData['documents_to_delete']);
                foreach ($mediaItems as $mediaItem) {
                    $this->destroyDocument($mediaItem);
                }
            }

            if ($request->has('new_documents')) {
                foreach ($request->new_documents as $document) {
                    $this->storeDocument(new Request($document), $import);
                }
            }
        });

        return redirect()->route('imports.index');
    }

    public function updateRawMaterial(Import $import, RawMaterial $rawMaterial, array $newData, $oldPivot)
    {
        $import->rawMaterials()->updateExistingPivot($rawMaterial->id, [
            'quantity' => $newData['quantity'],
            'unit_cost' => $newData['unit_cost'],
        ]);

        activity()->performedOn($import)->causedBy(auth()->user())
            ->withProperties([
                'materia_prima' => $rawMaterial->name,
                'old' => ['cantidad' => $oldPivot->quantity, 'costo_unitario' => $oldPivot->unit_cost],
                'new' => ['cantidad' => $newData['quantity'], 'costo_unitario' => $newData['unit_cost']]
            ])
            ->log('actualizó la materia prima');
    }

    public function destroy(Import $import)
    {
        //
    }

    public function updateStatus(Request $request, Import $import)
    {
        // 1. Validación
        // Los valores deben coincidir con los IDs de las columnas en el frontend
        $validatedData = $request->validate([
            'status' => 'required|in:Con proveedor,Puerto origen,En tránsito marítimo,Puerto destino,Entregado',
        ]);

        $oldStatus = $import->status;
        $newStatus = $validatedData['status'];

        // 2. Actualización del estado
        $import->status = $newStatus;

        // 3. Actualización automática de Fechas Clave
        // Asigna la fecha actual al campo correspondiente cuando la importación
        // entra en una nueva etapa por primera vez.
        switch ($newStatus) {
            case 'En tránsito marítimo':
                // Si la fecha de embarque no ha sido establecida, la registramos.
                if (is_null($import->estimated_ship_date)) {
                    $import->estimated_ship_date = now();
                }
                break;

            case 'Puerto destino':
                // Si la fecha de llegada real no ha sido establecida, la registramos.
                if (is_null($import->actual_arrival_date)) {
                    $import->actual_arrival_date = now();
                }

                if ($oldStatus != $newStatus) {
                    // notificar
                    $notificationInstance = new ImportArrivedAtDestination($import);
                    $this->sendNotification('import.new-status.destination-port', $notificationInstance);
                }

                break;

            case 'Entregado':
                // Si la fecha de entrega en bodega no ha sido establecida, la registramos.
                if (is_null($import->warehouse_delivery_date)) {
                    $import->warehouse_delivery_date = now();
                }
                break;
        }

        $import->save();

        // 4. Respuesta
        return back();
    }

    public function storeCost(Request $request, Import $import)
    {
        $validatedData = $request->validate([
            'concept' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|in:USD,MXN',
        ]);

        $import->costs()->create([
            'concept' => $validatedData['concept'],
            'amount' => $validatedData['amount'],
            'currency' => $validatedData['currency'],
            'pendent_amount' => $validatedData['amount'], // El saldo pendiente inicial es el monto total
        ]);

        return back();
    }

    public function storePayment(Request $request, Import $import)
    {
        // Nos aseguramos que el costo pertenezca a la importación actual para mayor seguridad
        $cost = $import->costs()->findOrFail($request->import_cost_id);

        $validatedData = $request->validate([
            'import_cost_id' => 'required|exists:import_costs,id',
            'amount' => 'required|numeric|min:0.01|max:' . $cost->pendent_amount,
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
            'document' => 'nullable|file|max:10240', // max 10MB
        ]);

        DB::transaction(function () use ($validatedData, $cost, $request) {
            // 1. Crear el pago
            $payment = $cost->payments()->create([
                'amount' => $validatedData['amount'],
                'payment_date' => $validatedData['payment_date'],
                'notes' => $validatedData['notes'],
            ]);

            // 2. Actualizar el saldo pendiente del costo
            $cost->update([
                'pendent_amount' => $cost->pendent_amount - $validatedData['amount'],
            ]);

            // 3. Si hay un documento, adjuntarlo al pago
            if ($request->hasFile('document')) {
                $payment->addMediaFromRequest('document')->toMediaCollection('payment_vouchers');
            }
        });

        return back();
    }

    public function destroyCost(ImportCost $cost)
    {
        DB::transaction(function () use ($cost) {
            activity()
                ->performedOn($cost->import)
                ->event('deleted')->causedBy(auth()->user())
                ->withProperties(['import_cost' => $cost->concept, 'amount' => $cost->amount, 'payments' => $cost->payments])
                ->log('eliminó un costo');

            // limpiar los archivos de cada pago
            foreach ($cost->payments as $payment) {
                foreach ($payment->getMediaCollections() as $collection) {
                    $payment->clearMediaCollection($collection->name);
                }
            }

            $cost->delete();
        });

        return back();
    }

    public function destroyPayment(ImportPayment $payment)
    {
        DB::transaction(function () use ($payment) {
            $cost = $payment->importCost;

            // Actualizar el saldo pendiente del costo sumando el monto del pago eliminado
            $cost->update([
                'pendent_amount' => $cost->pendent_amount + $payment->amount,
            ]);

            // Eliminar el pago
            // Itera sobre cada colección de medios y la limpia
            foreach ($payment->getMediaCollections() as $collection) {
                $payment->clearMediaCollection($collection->name);
            }
            $payment->delete();

            activity()
                ->performedOn($payment->importCost->import)
                ->event('deleted')->causedBy(auth()->user())
                ->withProperties(['import_cost' => $payment->importCost->concept, 'amount' => $payment->amount])
                ->log('eliminó un pago');
        });

        return back();
    }

    public function attachRawMaterial(Request $request, Import $import, RawMaterial $rawMaterial = null)
    {
        $rawMaterial = $rawMaterial ?? RawMaterial::find($request->raw_material_id);
        $import->rawMaterials()->attach($rawMaterial->id, [
            'quantity' => $request->quantity,
            'unit_cost' => $request->unit_cost,
        ]);

        // no registrar esta actividad si recién se creó la importación
        if ($import->created_at->toDateTimeString() != now()->toDateTimeString()) {
            activity()->performedOn($import)->event('created')->causedBy(auth()->user())->withProperties(['materia_prima' => $rawMaterial->name, 'cantidad' => $request->quantity])->log('agregó la materia prima');
        }
    }

    public function detachRawMaterial(Import $import, RawMaterial $rawMaterial)
    {
        $import->rawMaterials()->detach($rawMaterial->id);
        activity()->performedOn($import)->event('deleted')->causedBy(auth()->user())->withProperties(['materia_prima' => $rawMaterial->name])->log('eliminó la materia prima');
        return back()->with('success', 'Materia prima eliminada.');
    }

    public function storeDocument(Request $request, Import $import)
    {
        $media = $import->addMedia($request->file)->toMediaCollection($request->classification);
        if ($import->created_at->toDateTimeString() != now()->toDateTimeString()) {
            activity()->performedOn($import)->event('created')->causedBy(auth()->user())->withProperties(['documento' => $media->file_name, 'clasificacion' => $request->classification])->log('adjuntó el documento');
        }
    }

    public function destroyDocument(Media $media)
    {
        $import = $media->model;
        $documentName = $media->file_name;
        $media->delete();
        activity()->performedOn($import)->event('deleted')->causedBy(auth()->user())->withProperties(['documento' => $documentName])->log('eliminó el documento');
        return back()->with('success', 'Documento eliminado.');
    }

    public function export(Request $request)
    {
        // Validar los filtros (igual que en el método index)
        $request->validate([
            'search' => 'nullable|string|max:255',
            'supplier' => 'nullable|integer|exists:suppliers,id',
            'dates' => 'nullable|array|size:2',
            'dates.*' => 'nullable|date_format:Y-m-d',
        ]);

        // Construir la consulta con los filtros (igual que en el método index)
        $importsQuery = Import::with('supplier', 'rawMaterials', 'costs'); // Cargar relaciones necesarias

        $importsQuery->when($request->filled('search'), function ($query) use ($request) {
            $query->where('folio', 'like', '%' . $request->search . '%');
        });
        $importsQuery->when($request->filled('supplier'), function ($query) use ($request) {
            $query->where('supplier_id', $request->supplier);
        });
        $importsQuery->when($request->filled('dates'), function ($query) use ($request) {
            $startDate = $request->dates[0];
            $endDate = $request->dates[1] . ' 23:59:59';
            $query->whereBetween('created_at', [$startDate, $endDate]);
        });

        $imports = $importsQuery->latest()->get();

        // Generar y descargar el archivo Excel
        return Excel::download(new ImportsReportExport($imports), 'reporte_de_importaciones.xlsx');
    }
}
