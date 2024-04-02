<?php

namespace App\Http\Controllers;

use App\Http\Resources\TicketSolutionResource;
use App\Models\TicketHistory;
use App\Models\TicketSolution;
use App\Models\User;
use App\Notifications\BasicNotification;
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
            'description' => $request->description,
            'ticket_id' => $request->ticketId,
            'user_id' => auth()->id(),
        ]);

        // marcar como completado el ticket
        // $ticket_solution->ticket->update(['status' => 'Completado']); //quisieron quitar esta funcion el 02/Abr/2024 por Rmses

        // Guardar media
        $ticket_solution->addAllMediaFromRequest()->each(fn ($file) => $file->toMediaCollection());

        //Crear registro de actividad
        TicketHistory::create([
            'description' =>  'publicó una solución',
            'user_id' =>  auth()->id(),
            'ticket_id' =>  $request->ticketId,
        ]);

        // notificar a los demas usuarios
        $users = User::where('id', '!=', auth()->id())->get();
        $owner = auth()->user();
        $description = "agregó una solución al ticket #{$ticket_solution->ticket->id}";
        $subject = "Ticket cerrado";
        $users->each(fn ($user) => $user->notify(new BasicNotification($subject, $description, $owner->name, $owner->profile_photo_url, route('tickets.show', $ticket_solution->ticket->id))));

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
        $ticket_solutions = TicketSolutionResource::collection(TicketSolution::with('user:id,name,profile_photo_path')->where('ticket_id', $ticket)->get());

        return response()->json(['items' => $ticket_solutions]);
    }
}
