<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommentResource;
use App\Http\Resources\TicketHistoryResource;
use App\Http\Resources\TicketResource;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\User;
use App\Notifications\BasicNotification;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class TicketController extends Controller
{

    public function index()
    {
        $userCanSeeAllTickets = auth()->user()->can('Ver todos los tickets');
        if ($userCanSeeAllTickets) {
            $tickets = TicketResource::collection(Ticket::latest('id')
                ->with('category:id,name', 'responsible:id,name,profile_photo_path', 'user:id,name,profile_photo_path')
                ->take(20)
                ->get());
            $total_tickets = Ticket::all()->count();
        } else {
            $tickets = TicketResource::collection(Ticket::latest('id')
                ->with('category:id,name', 'responsible:id,name,profile_photo_path', 'user:id,name,profile_photo_path')
                ->where('user_id', auth()->id())
                ->take(20)
                ->get());
            $total_tickets = Ticket::where('user_id', auth()->id())->get()->count();
        }

        $categories = Category::all();

        return inertia('Ticket/Index', compact('tickets', 'total_tickets', 'categories'));
    }


    public function create()
    {
        $categories = Category::all();
        $permissionName = 'Responsable de ticket';

        $users = User::all(['id', 'name', 'profile_photo_path'])->filter(function ($user) use ($permissionName) {
            return $user->hasPermissionTo($permissionName) && $user->id !== 1;
        })->values();

        return inertia('Ticket/Create', compact('categories', 'users'));
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required',
            'responsible_id' => 'nullable',
            'title' => 'required|string|max:100',
            'ticket_type' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|string',
            'branch' => 'required|string',
            'priority' => 'required|string',
            'expired_date' => 'required|date',
        ]);

        $ticket = Ticket::create($validated + ['user_id' => auth()->id()]);

        // Guardar media
        $ticket->addAllMediaFromRequest()->each(fn ($file) => $file->toMediaCollection());

        //Crear registro de actividad
        TicketHistory::create([
            'description' =>  'creó el ticket #' . $ticket->id . ' "' . $ticket->title . '".',
            'user_id' =>  auth()->id(),
            'ticket_id' =>  $ticket->id,
        ]);

        // notificar a los demas usuarios
        $users = User::where('id', '!=', auth()->id())->get();
        $owner = auth()->user();
        $subject = "Nuevo ticket";
        $description = "ha creado un nuevo ticket";
        $users->each(fn ($user) => $user->notify(new BasicNotification($subject, $description, $owner->name, $owner->profile_photo_url, route('tickets.show', $ticket->id))));

        return to_route('tickets.index');
    }


    public function show($ticket_id)
    {
        $ticket = TicketResource::make(Ticket::withCount('ticketSolutions')->with('responsible:id,name,profile_photo_path', 'user:id,name,profile_photo_path', 'category:id,name')->find($ticket_id));

        return inertia('Ticket/Show', compact('ticket'));
    }


    public function edit(Ticket $ticket)
    {
        $categories = Category::all();
        $permissionName = 'Responsable de ticket';

        $users = User::all(['id', 'name', 'profile_photo_path'])->filter(function ($user) use ($permissionName) {
            return $user->hasPermissionTo($permissionName) && $user->id !== 1;
        })->values();

        $media = $ticket->getMedia()->all();

        return inertia('Ticket/Edit', compact('ticket', 'categories', 'users', 'media'));
    }


    public function update(Request $request, Ticket $ticket)
    {
        $request->validate([
            'category_id' => 'required',
            'responsible_id' => 'nullable',
            'title' => 'required|string|max:100',
            'ticket_type' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|string',
            'branch' => 'required|string',
            'priority' => 'required|string',
            'expired_date' => 'required|date',
        ]);

        //guarda al responsable antes de edición
        $current_responsible_id = $ticket->responsible_id;

        $ticket->update($request->all());

        //Crear registro de actividad si se cambio al responsable
        if ($current_responsible_id != $request->responsible_id) {
            $new_responsible = User::find($request->responsible_id);
            $description = $request->responsible_id
                ? "asignó a *$new_responsible->name* como responsable del ticket."
                : "removió al responsable del ticket.";
            TicketHistory::create([
                'description' =>  $description,
                'user_id' =>  auth()->id(),
                'ticket_id' =>  $ticket->id,
            ]);
        }

        // notificar a los demas usuarios
        $users = User::where('id', '!=', auth()->id())->get();
        $owner = auth()->user();
        $subject = "Ticket editado";
        $description = "ha editado el ticket #$ticket->id";
        $users->each(fn ($user) => $user->notify(new BasicNotification($subject, $description, $owner->name, $owner->profile_photo_url, route('tickets.show', $ticket->id))));

        return to_route('tickets.show', $ticket->id);
    }


    public function updateWithMedia(Request $request, Ticket $ticket)
    {
        $request->validate([
            'category_id' => 'required',
            'responsible_id' => 'nullable',
            'title' => 'required|string|max:100',
            'ticket_type' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|string',
            'branch' => 'required|string',
            'priority' => 'required|string',
            'expired_date' => 'required|date',
        ]);

        //guarda al responsable antes de edición
        $current_responsible_id = $ticket->responsible_id;

        $ticket->update($request->all());

        // Guardar media
        $ticket->addAllMediaFromRequest()->each(fn ($file) => $file->toMediaCollection());

        //Crear registro de actividad si se cambio al responsable
        if ($current_responsible_id != $request->responsible_id) {
            $new_responsible = User::find($request->responsible_id);

            TicketHistory::create([
                'description' =>  'asignó a "' . $new_responsible->name . '" como responsable del ticket.',
                'user_id' =>  auth()->id(),
                'ticket_id' =>  $ticket->id,
            ]);
        }

        // notificar a los demas usuarios
        $users = User::where('id', '!=', auth()->id())->get();
        $owner = auth()->user();
        $subject = "Ticket editado";
        $description = "ha editado el ticket #$ticket->id";
        $users->each(fn ($user) => $user->notify(new BasicNotification($subject, $description, $owner->name, $owner->profile_photo_url, route('tickets.show', $ticket->id))));

        return to_route('tickets.show', $ticket->id);
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

        //Crear registro de actividad
        TicketHistory::create([
            'description' =>  'cambió el estado del ticket a "' . $ticket->status . '".',
            'user_id' =>  auth()->id(),
            'ticket_id' =>  $ticket->id,
        ]);

        // notificar a los demas usuarios
        $users = User::where('id', '!=', auth()->id())->get();
        $owner = auth()->user();
        $subject = "Cambio de status de ticket";
        $description = "ha cambiado el estatus del ticket #$ticket->id";
        $users->each(fn ($user) => $user->notify(new BasicNotification($subject, $description, $owner->name, $owner->profile_photo_url, route('tickets.show', $ticket->id))));

        return response()->json(['item' => TicketResource::make($ticket->refresh())]);
    }

    public function comment(Request $request, Ticket $ticket)
    {
        $comment = new Comment([
            'body' => $request->comment,
            'user_id' => auth()->id(),
        ]);

        $ticket->comments()->save($comment);

        $mentions = $request->mentions;
        foreach ($mentions as $mention) {
            $user = User::find($mention['id']);
            $owner = auth()->user();
            $subject = "Mención en comentario";
            $description = "te ha mencionado en un comentario del ticket #$ticket->id";
            $user->notify(new BasicNotification($subject, $description, $owner->name, $owner->profile_photo_url, route('tickets.show', $ticket->id)));
        }

        //Crear registro de actividad
        TicketHistory::create([
            'description' =>  'realizó un comentario "' . $comment->body . '".',
            'user_id' =>  auth()->id(),
            'ticket_id' =>  $ticket->id,
        ]);


        return response()->json(['item' => $comment->fresh('user')]);
    }

    public function fetchConversation($ticket)
    {
        $conversation = CommentResource::collection(Comment::where('commentable_type', 'App\Models\Ticket')->where('commentable_id', $ticket)->with('user:id,name,profile_photo_path')->get());


        return response()->json(['items' => $conversation]);
    }

    public function fetchHistory($ticket)
    {
        $ticket_history = TicketHistoryResource::collection(TicketHistory::with('user:id,name,profile_photo_path', 'ticket')->where('ticket_id', $ticket)->get());

        return response()->json(['items' => $ticket_history]);
    }

    public function getMatches($query)
    {
        $userCanSeeAllTickets = auth()->user()->can('Ver todos los tickets');

        if ($userCanSeeAllTickets) {
            $tickets = TicketResource::collection(Ticket::with('category:id,name', 'responsible:id,name,profile_photo_path', 'user:id,name,profile_photo_path')
                ->where('id', 'LIKE', "%$query%")
                ->orWhere('title', 'LIKE', "%$query%")
                ->orWhere('description', 'LIKE', "%$query%")
                ->get());
        } else {
            $tickets = TicketResource::collection(Ticket::with('category:id,name', 'responsible:id,name,profile_photo_path', 'user:id,name,profile_photo_path')
                ->where('user_id', auth()->id())
                ->where('id', 'LIKE', "%$query%")
                ->orWhere('title', 'LIKE', "%$query%")
                ->orWhere('description', 'LIKE', "%$query%")
                ->get());
        }

        return response()->json(['items' => $tickets]);
    }

    public function getFilters($prop, $value)
    {
        if ($prop === 'created_at' || $prop === 'expired_date') {
            $tickets = TicketResource::collection($this->getTicketsByDate($prop, $value));
        } else {
            $tickets = TicketResource::collection(Ticket::with('category:id,name', 'responsible:id,name,profile_photo_path', 'user:id,name,profile_photo_path')
                ->where($prop, $value)
                ->get());
        }

        return response()->json(['items' => $tickets]);
    }

    public function getItemsByPage($currentPage)
    {
        $offset = $currentPage * 20;
        $userCanSeeAllTickets = auth()->user()->can('Ver todos los tickets');

        if ($userCanSeeAllTickets) {
            $tickets = TicketResource::collection(Ticket::latest('id')
                ->with('category:id,name', 'responsible:id,name,profile_photo_path', 'user:id,name,profile_photo_path')
                ->skip($offset)
                ->take(20)
                ->get());
        } else {
            $tickets = TicketResource::collection(Ticket::latest('id')
                ->where('user_id', auth()->id())
                ->with('category:id,name', 'responsible:id,name,profile_photo_path', 'user:id,name,profile_photo_path')
                ->skip($offset)
                ->take(20)
                ->get());
        }

        return response()->json(['items' => $tickets]);
    }

    private function getTicketsByDate($prop, $value)
    {
        if ($value === 'Hoy') {
            $tickets = Ticket::with('category:id,name', 'responsible:id,name,profile_photo_path', 'user:id,name,profile_photo_path')->whereDate($prop, now())->get();
        } else if ($value === 'Esta semana') {
            $start = now()->startOfWeek(); // Obtener el inicio de la semana actual
            $end = now()->endOfWeek(); // Obtener el final de la semana actual

            $tickets = Ticket::with('category:id,name', 'responsible:id,name,profile_photo_path', 'user:id,name,profile_photo_path')->whereBetween($prop, [$start, $end])->get();
        } else if ($value === 'Este mes') {
            $start = now()->startOfMonth(); // Obtener el inicio del mes actual
            $end = now()->endOfMonth(); // Obtener el final del mes actual

            $tickets = Ticket::with('category:id,name', 'responsible:id,name,profile_photo_path', 'user:id,name,profile_photo_path')->whereBetween($prop, [$start, $end])->get();
        } else if ($value === 'Mes pasado') {
            $start = now()->subMonth()->startOfMonth();
            $end = now()->subMonth()->endOfMonth();

            $tickets = Ticket::with('category:id,name', 'responsible:id,name,profile_photo_path', 'user:id,name,profile_photo_path')->whereBetween($prop, [$start, $end])->get();
        } else if ($value === 'Este año') {
            $start = now()->startOfYear();
            $end = now()->endOfYear();

            $tickets = Ticket::with('category:id,name', 'responsible:id,name,profile_photo_path', 'user:id,name,profile_photo_path')->whereBetween($prop, [$start, $end])->get();
        } else if ($value === 'Año pasado') {
            $start = now()->subYear()->startOfYear();
            $end = now()->subYear()->endOfYear();

            $tickets = Ticket::with('category:id,name', 'responsible:id,name,profile_photo_path', 'user:id,name,profile_photo_path')->whereBetween($prop, [$start, $end])->get();
        }

        return $tickets;
    }
}
