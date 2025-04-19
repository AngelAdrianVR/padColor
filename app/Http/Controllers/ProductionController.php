<?php

namespace App\Http\Controllers;

use App\Models\Production;
use Illuminate\Http\Request;

class ProductionController extends Controller
{
    public function index()
    {
        $productions = Production::with(['user', 'product', 'machine'])->latest()->get();
        $next_production = Production::latest()->first();
        $next_production = $next_production ? $next_production->id + 1 : 1;

        return inertia('Production/Index', compact('productions', 'next_production'));
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'folio' => 'required|unique:productions,id',
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
            'pf' => 'nullable|numeric|min:0',
            'ts' => 'nullable|numeric|min:0',
            'ps' => 'nullable|numeric|min:0',
            'tps' => 'nullable|numeric|min:0',
        ]);

        // cambiar un poco el folio
        // $lastProductionId = Production::latest('id')->first()?->id ?? 0;
        // $validated['folio'] = $request->type . $lastProductionId + 1;
        $validated['folio'] = $request->type . $validated['folio'];
        $validated['user_id'] = auth()->id();
        $validated['materials'] = [$validated['materials']];

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
        $validated = $request->validate([
            'folio' => 'required|unique:productions,id,' . $production->id,
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
            'gauge' => 'nullable|numeric|min:0',
            'large' => 'nullable|numeric|min:0',
            'look' => 'nullable|string|max:255',
            'faces' => 'nullable|numeric|min:0',
            'pps' => 'nullable|numeric|min:0',
            'adjust' => 'nullable|numeric|min:0',
            'sheets' => 'nullable|numeric|min:0',
            'ha' => 'nullable|numeric|min:0',
            'pf' => 'nullable|numeric|min:0',
            'ts' => 'nullable|numeric|min:0',
            'ps' => 'nullable|numeric|min:0',
            'tps' => 'nullable|numeric|min:0',
        ]);

        // cambiar un poco el folio
        // $lastProductionId = Production::latest('id')->first()?->id ?? 0;
        // $validated['folio'] = $request->type . $lastProductionId + 1;
        $validated['folio'] = $request->type . $validated['folio'];
        $validated['materials'] = [$validated['materials']];

        $production->update($validated);

        return to_route('productions.index', ["currentTab" => 2]);
    }

    public function updateStation(Request $request, Production $production)
    {
        $production->station = $request->station;
        $production->save();
    }

    public function updateMachine(Request $request, Production $production)
    {
        $production->machine_id = $request->machine_id;
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

        $query = Production::with(['user', 'product', 'machine'])
            ->latest('id');

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

        $total = $search ? $query->count() : Production::count();

        return response()->json(compact('items', 'total'));
    }

    public function clone(Production $production)
    {
        $lastProductionId = Production::latest('id')->first()?->id;
        $newProduction = $production->replicate();
        $newProduction->folio = 'R' . $lastProductionId + 1;
        $newProduction->save();

        return to_route('productions.edit', ['production' => $newProduction->id]);
    }

    public function close(Request $request, Production $production)
    {
        $production->update([
            'station' => 'Inspección',
            'close_quantity' => $request->close_quantity,
            'close_date' => $request->close_date,
        ]);
    }
}
