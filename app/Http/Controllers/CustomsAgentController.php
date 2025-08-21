<?php

namespace App\Http\Controllers;

use App\Models\CustomsAgent;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class CustomsAgentController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
        ]);

        $customsAgents = CustomsAgent::query()
            ->with('user') // Cargar la relación con el usuario creador
            ->when($request->filled('search'), function ($query) use ($request) {
                $searchTerm = '%' . $request->search . '%';
                $query->where('name', 'like', $searchTerm)
                    ->orWhere('contact_person', 'like', $searchTerm);
            })
            ->latest()
            ->paginate(10);

        return Inertia::render('CustomsAgent/Index', [
            'customs_agents' => $customsAgents,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('CustomsAgent/Create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ]);

        $validatedData['user_id'] = Auth::id();

        CustomsAgent::create($validatedData);

        return redirect()->route('customs-agents.index');
    }

    public function edit(CustomsAgent $customsAgent)
    {
        return Inertia::render('CustomsAgent/Edit', [
            'customs_agent' => $customsAgent,
        ]);
    }

    public function update(Request $request, CustomsAgent $customsAgent)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ]);

        $customsAgent->update($validatedData);

        return redirect()->route('customs-agents.index');
    }

    public function destroy(CustomsAgent $customsAgent)
    {
        $customsAgent->delete();
        return redirect()->route('customs-agents.index');
    }
}
