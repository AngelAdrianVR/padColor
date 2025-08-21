<?php

namespace App\Http\Controllers;

use App\Models\RawMaterial;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RawMaterialController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
        ]);

        $rawMaterials = RawMaterial::with('user')
            ->when($request->filled('search'), function ($query) use ($request) {
                $searchTerm = '%' . $request->search . '%';
                $query->where('sku', 'like', $searchTerm)
                    ->orWhere('name', 'like', $searchTerm);
            })
            ->latest()
            ->paginate(10); // Paginar de 10 en 10

        return Inertia::render('RawMaterial/Index', [
            'raw_materials' => $rawMaterials,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('RawMaterial/Create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:255|unique:raw_materials,sku',
            'measure_unit' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Añadir el user_id del usuario autenticado
        $validatedData['user_id'] = auth()->id();

        RawMaterial::create($validatedData);

        return redirect()->route('raw-materials.index')->with('success', 'Materia prima creada correctamente.');
    }

    public function edit(RawMaterial $rawMaterial)
    {
        return Inertia::render('RawMaterial/Edit', [
            'raw_material' => $rawMaterial,
        ]);
    }

    public function update(Request $request, RawMaterial $rawMaterial)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:255|unique:raw_materials,sku,' . $rawMaterial->id,
            'measure_unit' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $rawMaterial->update($validatedData);

        return redirect()->route('raw-materials.index')->with('success', 'Materia prima actualizada correctamente.');
    }

    public function destroy(RawMaterial $rawMaterial)
    {
        $rawMaterial->delete();
        return redirect()->route('raw-materials.index')->with('success', 'Materia prima eliminada correctamente.');
    }
}
