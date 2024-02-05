<?php

namespace App\Http\Controllers;

use App\Http\Resources\TicketResource;
use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    
    public function index()
    {
        $tickets = TicketResource::collection(Ticket::latest()->with('media')->get());

        return inertia('Ticket/Index', compact('tickets'));
    }

    
    public function create()
    {
        $categories = Category::all();
        $users = User::all(['id', 'name', 'profile_photo_path']);

        return inertia('Ticket/Create', compact('categories', 'users'));
    }

   
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required',
            'responsible_id' => 'required',
            'title' => 'required|string|max:100',
            'description' => 'required|string',
            'status' => 'required|string',
            'priority' => 'required|string',
            'expired_date' => 'required|date|after:yesterday',
        ]);

        $ticket = Ticket::create($request->all() + ['user_id' => auth()->id()]);

        // Guardar media si existe
        if ($request->hasFile('media')) {
            $ticket->addMediaFromRequest('media')->toMediaCollection();
        }

        return to_route('tickets.index');
    }

    
    public function show(Ticket $ticket)
    {
        //
    }

    
    public function edit(Ticket $ticket)
    {
        //
    }

    
    public function update(Request $request, Ticket $ticket)
    {
        //
    }

    
    public function destroy(Ticket $ticket)
    {
        //
    }
}