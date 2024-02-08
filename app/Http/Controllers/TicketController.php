<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommentResource;
use App\Http\Resources\TicketResource;
use App\Http\Resources\TicketSolutionResource;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Ticket;
use App\Models\TicketSolution;
use App\Models\User;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    
    public function index()
    {
        $tickets = TicketResource::collection(Ticket::latest()->with('category:id,name', 'responsible:id,name,profile_photo_path','user:id,name,profile_photo_path')->get());

        // return $ticket_solutions;
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
            'responsible_id' => 'nullable',
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

    
    public function show($ticket_id)
    {
        $ticket = TicketResource::make(Ticket::with('responsible:id,name,profile_photo_path', 'user:id,name,profile_photo_path')->find($ticket_id));
        $users = User::all(['id', 'name', 'profile_photo_path']);
        
        // return $conversation;
        return inertia('Ticket/Show', compact('ticket', 'users'));
    }

    
    public function edit(Ticket $ticket)
    {
        $categories = Category::all();
        $users = User::all(['id', 'name', 'profile_photo_path']);

        return inertia('Ticket/Edit', compact('ticket', 'categories', 'users'));
    }

    
    public function update(Request $request, Ticket $ticket)
    {
        $request->validate([
            'category_id' => 'required',
            'responsible_id' => 'nullable',
            'title' => 'required|string|max:100',
            'description' => 'required|string',
            'status' => 'required|string',
            'priority' => 'required|string',
            'expired_date' => 'required|date|after:yesterday',
        ]);

        $ticket->update($request->all());

        return to_route('tickets.index');
    }


    public function updateWithMedia(Request $request, Ticket $ticket)
    {
        $request->validate([
            'category_id' => 'required',
            'responsible_id' => 'nullable',
            'title' => 'required|string|max:100',
            'description' => 'required|string',
            'status' => 'required|string',
            'priority' => 'required|string',
            'expired_date' => 'required|date|after:yesterday',
        ]);

        $ticket->update($request->all());

        // Guardar media
        $ticket->addMediaFromRequest('media')->toMediaCollection();

        return to_route('tickets.index');
    }

    
    public function destroy(Ticket $ticket)
    {
        //
    }


    public function massiveDelete(Request $request)
    {
        foreach ($request->tickets as $ticket) {
            $ticket = Ticket::find($ticket);
            $ticket?->delete();
        }
    }


    public function updateStatus(Ticket $ticket)
    {        
        $ticket->update([
            'status' => request('status'),
            'updated_at' => now(),
        ]);

        return response()->json(['item' => TicketResource::make($ticket->refresh())]);
    }

    public function comment(Request $request, Ticket $ticket)
    {
        $comment = new Comment([
            'body' => $request->comment,
            'user_id' => auth()->id(),
        ]);

        $ticket->comments()->save($comment);

        // $mentions = $request->mentions;
        // foreach ($mentions as $mention) {
        //     $user = User::find($mention['id']);
        //     $user->notify(new MentionNotification($oportunity_task, "", 'opportunities'));
        // }
        
        return response()->json(['item' => $comment->fresh('user')]);
    }

    public function fetchConversation($ticket)
    {
        $conversation = CommentResource::collection(Comment::where('commentable_type', 'App\Models\Ticket')->where('commentable_id', $ticket)->with('user:id,name,profile_photo_path')->get());


        return response()->json(['items' => $conversation]);
    }
}
