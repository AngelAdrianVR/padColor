<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use Illuminate\Http\Request;

class MachineController extends Controller
{
    public function index()
    {
        return inertia('Machine/Index', [
            'machines' => Machine::latest('id')->paginate(30),
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

        Machine::create($request->all() + ['created_by' => auth()->user()->name]);
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
        //
    }

    public function destroy(Machine $machine)
    {
        //
    }

    public function getAll()
    {
        $items = Machine::latest('id')->get(['name', 'id']);

        return response()->json(compact('items'));
    }
}
