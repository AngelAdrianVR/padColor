<?php

namespace App\Http\Controllers;

use App\Models\CustomsAgent;
use App\Models\Import;
use App\Models\RawMaterial;
use App\Models\Supplier;
use Illuminate\Http\Request;
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
        $importsQuery = Import::with('supplier', 'customsAgent');

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
        //
    }

    public function show(Import $import)
    {
        //
    }

    public function edit(Import $import)
    {
        //
    }

    public function update(Request $request, Import $import)
    {
        //
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
