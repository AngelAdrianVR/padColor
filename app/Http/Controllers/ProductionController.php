<?php

namespace App\Http\Controllers;

use App\Models\Production;
use Illuminate\Http\Request;

class ProductionController extends Controller
{
    public function index()
    {
        $productions = Production::with(['user', 'product', 'machine'])->latest()->get();

        return inertia('Production/Index', compact('productions'));
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'folio' => 'required|string|max:255',
            'client' => 'required|string|max:255',
            'season' => 'required|string|max:255',
            'station' => 'required|string|max:255',
            'quantity' => 'required|numeric|min:1',
            'materials' => 'nullable|array',
            'description' => 'nullable|string|max:300',
            'product_id' => 'required|min:1|integer|exists:products,id',
            'machine_id' => 'required|min:1|integer|exists:machines,id',
            'estimated_date' => 'required|date',
        ]);

        // cambiar un poco el folio
        $lastProductionId = Production::latest('id')->first()?->id ?? 0;
        $validated['folio'] = $request->type . $lastProductionId+1 . '-' . today()->format('dmY');
        $validated['user_id'] = auth()->id();

        $production = Production::create($validated);
    }

    public function show(Production $production)
    {
        //
    }

    public function edit(Production $production)
    {
        return inertia('Production/Edit', compact('production'));
    }

    public function update(Request $request, Production $production)
    {
        //
    }

    public function destroy(Production $production)
    {
        //
    }

    public function getByPage()
    {
        $page = request('page', 1);
        $perPage = 30;
        $offset = ($page - 1) * $perPage;
        $items = Production::with(['user', 'product', 'machine'])
            ->latest('id')
            ->offset($offset)
            ->limit($perPage)
            ->get();
        $total = Production::count();

        return response()->json(compact('items', 'total'));
    }
}
