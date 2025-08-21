<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
        ]);

        $suppliers = Supplier::query()
            ->with('user') // Cargar la relación con el usuario creador
            ->when($request->filled('search'), function ($query) use ($request) {
                $searchTerm = '%' . $request->search . '%';
                $query->where('name', 'like', $searchTerm)
                    ->orWhere('contact_person', 'like', $searchTerm)
                    ->orWhere('email', 'like', $searchTerm);
            })
            ->latest()
            ->paginate(10);

        return Inertia::render('Supplier/Index', [
            'suppliers' => $suppliers,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Supplier/Create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        $validatedData['user_id'] = Auth::id();

        $supplier = Supplier::create($validatedData);

        // Si la petición espera una respuesta JSON, devuelve el nuevo proveedor
        if ($request->wantsJson()) {
            return response()->json($supplier);
        }

        // Si no, redirige a la vista de índice
        return redirect()->route('suppliers.index')->with('success', 'Proveedor creado correctamente.');
    }

    public function edit(Supplier $supplier)
    {
        return Inertia::render('Supplier/Edit', [
            'supplier' => $supplier,
        ]);
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        $supplier->update($validatedData);

        return redirect()->route('suppliers.index');
    }

    public function destroy(Supplier $supplier)
    {
        // Opcional: Añadir lógica para verificar si el proveedor está en uso antes de eliminar.
        $supplier->delete();
        return redirect()->route('suppliers.index');
    }
}
