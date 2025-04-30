<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use Illuminate\Http\Request;

class MachineController extends Controller
{
    public function index()
    {
        return inertia('Machine/Index', [
            'machines' => Machine::with('media')->latest('id')->paginate(30),
        ]);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $machine = Machine::create($request->all() + ['created_by' => auth()->user()->name]);
         
        // Guardar el archivo en la colección 'image'
        if ($request->hasFile('image')) {
            $machine->addMediaFromRequest('image')->toMediaCollection('image');
        }

        $machine->load('media');
        
        return response()->json(compact('machine'));
    }

    public function show(Machine $machine)
    {
        //
    }

    public function edit(Machine $machine)
    {
        //
    }

    public function update(Request $request, Machine $machine)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $machine->update($request->all());
         
        // media
        // Eliminar imágenes antiguas solo si se borró desde el input y no se agregó una nueva
        if ($request->imageCleared) {
            $machine->clearMediaCollection('image');
        }
    }

    public function updateWithMedia(Request $request, Machine $machine)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $machine->update($request->all());
         
        // media ------------
        // Eliminar imágenes antiguas solo si se proporcionan nuevas imágenes
        if ($request->hasFile('image')) {
            $machine->clearMediaCollection('image');
            $machine->addMediaFromRequest('image')->toMediaCollection('image');
        }
    }
    
    public function destroy(Machine $machine)
    {
        $machine->delete();    
    }

    public function getAll()
    {
        $items = Machine::latest('id')->get(['name', 'id']);

        return response()->json(compact('items'));
    }

    public function getMatches(Request $request)
    {
        $query = $request->input('query');

        // Realiza la búsqueda correctamente
        $machines = Machine::where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                ->orWhere('description', 'like', "%{$query}%");
            })
            ->paginate(200);

        // Devuelve los items encontrados
        return response()->json(['items' => $machines], 200);
    }
}
