<?php

namespace App\Http\Controllers;

use App\Http\Resources\TicketSolutionResource;
use App\Models\TicketHistory;
use App\Models\TicketSolution;
use Illuminate\Http\Request;

class TicketSolutionController extends Controller
{
    
    public function index()
    {
        //
    }

   
    public function create()
    {
        //
    }

    
    public function store(Request $request)
    {
        $ticket_solution = TicketSolution::create([
            'description' => $request->solutionDescription,
            'ticket_id' => $request->ticketId,
            'user_id' => auth()->id(),
        ]);

        // Guardar la media si existe
        if ($request->hasFile('solution_media')) {
            $ticket_solution->addMediaFromRequest('solution_media')->toMediaCollection();
        }
        
        //Crear registro de actividad
        TicketHistory::create([
            'description' =>  'publicó una solución',
            'user_id' =>  auth()->id(),  
            'ticket_id' =>  $request->ticketId,  
        ]);
        
        return response()->json(['item' => $ticket_solution]);
    }

    
    public function show(TicketSolution $ticket_solution)
    {
        //
    }

    
    public function edit(TicketSolution $ticketSolution)
    {
        //
    }

    
    public function update(Request $request, TicketSolution $ticket_solution)
    {
        //
    }

    
    public function destroy(TicketSolution $ticket_solution)
    {
        $ticket_solution->delete();   
    }


    public function fetchSolutions($ticket)
    {
        $ticket_solutions = TicketSolutionResource::collection(TicketSolution::latest()->with('user:id,name,profile_photo_path')->where('ticket_id', $ticket)->get());

        return response()->json(['items' => $ticket_solutions]);
    }
}
