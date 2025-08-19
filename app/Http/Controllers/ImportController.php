<?php

namespace App\Http\Controllers;

use App\Models\CustomsAgent;
use App\Models\Import;
use App\Models\RawMaterial;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ImportController extends Controller
{
    public function index(Request $request)
    {
        // 1. Validamos los filtros que vienen de la URL
        $request->validate([
            'search' => 'nullable|string|max:255',
            'supplier' => 'nullable|integer|exists:suppliers,id',
            'dates' => 'nullable|array|size:2',
            'dates.*' => 'nullable|date_format:Y-m-d',
        ]);

        // 2. Construimos la consulta a la base de datos
        $importsQuery = Import::with('supplier', 'customsAgent', 'user');

        // 3. Aplicamos los filtros si existen en la petición
        $importsQuery->when($request->filled('search'), function ($query) use ($request) {
            $query->where('folio', 'like', '%' . $request->search . '%');
        });

        $importsQuery->when($request->filled('supplier'), function ($query) use ($request) {
            $query->where('supplier_id', $request->supplier);
        });

        $importsQuery->when($request->filled('dates'), function ($query) use ($request) {
            // Aseguramos que la fecha final incluya todo el día
            $startDate = $request->dates[0];
            $endDate = $request->dates[1] . ' 23:59:59';
            $query->whereBetween('created_at', [$startDate, $endDate]);
        });

        // 4. Ejecutamos la consulta y agrupamos por estado para el Kanban
        $imports = $importsQuery->get()->groupBy('status');

        // 5. Obtenemos los proveedores para el dropdown del filtro
        $suppliers = Supplier::all(['id', 'name']);

        // 6. Renderizamos la vista de Inertia, pasando los datos y los filtros actuales
        return Inertia::render('Import/Index', [
            'imports' => $imports,
            'suppliers' => $suppliers,
            'filters' => $request->only(['search', 'supplier', 'dates']), // Devolvemos los filtros para mantener el estado en la UI
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
            'products.*.currency' => 'required|string|in:USD,MXN',
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
                'status' => 'proveedor', // Estado inicial del Kanban
                // Generar un folio único. Puedes personalizar esta lógica.
                'folio' => 'IMP-' . (Import::latest('id')->first()?->id + 1),
            ]);

            // 4. Adjuntar los productos a la importación (tabla pivote)
            foreach ($validatedData['products'] as $product) {
                $import->rawMaterials()->attach($product['raw_material_id'], [
                    'quantity' => $product['quantity'],
                    'unit_cost' => $product['unit_cost'],
                    'currency' => $product['currency'],
                ]);
            }

            // 5. Adjuntar los documentos usando Spatie Media Library
            if ($request->has('documents')) {
                foreach ($request->documents as $document) {
                    $import->addMedia($document['file'])
                        // Usamos la clasificación como el nombre de la colección
                        ->toMediaCollection($document['classification']);
                }
            }
        });

        // 6. Redirigir al índice
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

            // 2. Actualizar los datos principales de la importación
            $import->update([
                'supplier_id' => $validatedData['supplier_id'],
                'customs_agent_id' => $validatedData['customs_agent_id'],
                'incoterm' => $validatedData['incoterm'],
                'estimated_ship_date' => $validatedData['estimated_ship_date'],
                'estimated_arrival_date' => $validatedData['estimated_arrival_date'],
                'notes' => $validatedData['notes'],
            ]);


            // 3. Sincronizar los productos
            // Preparamos el array para el método sync()
            $productsToSync = [];
            foreach ($validatedData['products'] as $product) {
                $productsToSync[$product['raw_material_id']] = [
                    'quantity' => $product['quantity'],
                    'unit_cost' => $product['unit_cost'],
                    // 'currency' => $product['currency'],
                ];
            }
            $import->rawMaterials()->sync($productsToSync);

            // 4. Eliminar los documentos marcados para borrado
            if (!empty($validatedData['documents_to_delete'])) {
                $import->media()->whereIn('id', $validatedData['documents_to_delete'])->delete();
            }

            // 5. Adjuntar los nuevos documentos
            if ($request->has('new_documents')) {
                foreach ($request->new_documents as $document) {
                    $import->addMedia($document['file'])
                        ->toMediaCollection($document['classification']);
                }
            }
        });

        return redirect()->route('imports.index');
    }

    public function destroy(Import $import)
    {
        //
    }

    /**
     * Actualiza el estado de una importación desde el tablero Kanban.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Import  $import
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request, Import $import)
    {
        // 1. Validación
        // Nos aseguramos de que el 'status' enviado sea uno de los valores permitidos.
        $request->validate([
            'status' => 'required|in:proveedor,puerto_origen,mar,puerto_destino,entregado',
        ]);

        // 2. Actualización del Modelo
        // Asignamos el nuevo estado al campo 'status' de la importación.
        $import->status = $request->status;
        $import->save();

        // 3. Respuesta
        // Redirigimos al usuario a la página anterior (el tablero Kanban).
        // Inertia se encargará de recargar las props (la lista de importaciones)
        // para que el cambio se refleje visualmente.
        return back();
    }
}
