<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommentResource;
use App\Http\Resources\TicketHistoryResource;
use App\Http\Resources\TicketResource;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\TicketSolution;
use App\Models\User;
use App\Notifications\BasicNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    // Campos ligeros para evitar cargar la descripción pesada en listas
    private $selectFields = [
        'id',
        'title',
        'status',
        'priority',
        'ticket_type',
        'user_id',
        'responsible_id',
        'department', // CAMPO AGREGADO
        'category_id',
        'created_at',
        'updated_at',
        'expired_date',
        'branch',
        'opened_at',
        'paused_at',
        'closed_at',
        'solution_minutes'
    ];

    public function index()
    {
        $userCanSeeAllTickets = auth()->user()->can('Ver todos los tickets');
        $userDepartment = auth()->user()->employee_properties['department'] ?? null;

        if ($userCanSeeAllTickets) {
            $tickets = TicketResource::collection(Ticket::select($this->selectFields) // Optimización
                ->latest('id')
                ->with('category:id,name', 'responsible:id,name,profile_photo_path', 'user:id,name,profile_photo_path,phone')
                ->take(20)
                ->get());
            $total_tickets = Ticket::all()->count();
        } else {
            $tickets = TicketResource::collection(Ticket::select($this->selectFields) // Optimización
                ->latest('id')
                ->with('category:id,name', 'responsible:id,name,profile_photo_path', 'user:id,name,profile_photo_path,phone')
                ->where(function($query) use ($userDepartment) {
                    $query->where('user_id', auth()->id())
                          ->orWhere('department', $userDepartment);
                })
                ->take(20)
                ->get());
            $total_tickets = Ticket::where(function($query) use ($userDepartment) {
                    $query->where('user_id', auth()->id())
                          ->orWhere('department', $userDepartment);
                })->count();
        }

        $categories = Category::all();

        return inertia('Ticket/Index', compact('tickets', 'total_tickets', 'categories'));
    }


    public function create()
    {
        $categories = Category::all();
        
        // Se traen todos los usuarios (excepto el id 1) con sus propiedades para agruparlos
        $users = User::where('id', '!=', 1)->get(['id', 'name', 'profile_photo_path', 'employee_properties']);

        return inertia('Ticket/Create', compact('categories', 'users'));
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required',
            'responsible_id' => 'nullable|exists:users,id',
            'department' => 'nullable|string|max:255',
            'title' => 'required|string|max:100',
            'ticket_type' => 'required|string|max:255',
            'description' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (preg_match('/data:image\/[^;]+;base64/', $value)) {
                        $fail('No se permite pegar imágenes directamente. Usa la sección de archivos.');
                    }
                },
            ],
            'status' => 'required|string',
            'branch' => 'required|string',
            'priority' => 'required|string',
            'expired_date' => 'required|date',
        ]);

        $ticket = Ticket::create($validated + ['user_id' => auth()->id(), 'opened_at' => now()->toDateTimeString()]);

        // Guardar media
        $ticket->addAllMediaFromRequest()->each(fn($file) => $file->toMediaCollection());

        //Crear registro de actividad
        $assignedTo = $ticket->responsible_id ? 'un usuario' : ($ticket->department ? 'un departamento' : 'sin asignar');
        TicketHistory::create([
            'description' =>  'creó el ticket #' . $ticket->id . ' "' . $ticket->title . '".',
            'user_id' =>  auth()->id(),
            'ticket_id' =>  $ticket->id,
        ]);

        // NOTIFICACIONES
        $owner = auth()->user();
        $subject = "Nuevo ticket";
        $description = "ha creado un nuevo ticket (#$ticket->id). Resolver lo antes posible.";
        $url = route('tickets.show', $ticket->id);

        if ($request->responsible_id && $request->responsible_id != auth()->id()) {
            $user = User::find($request->responsible_id);
            $user->notify(new BasicNotification($subject, $description, $owner->name, $owner->profile_photo_url, $url));
        } elseif ($request->department) {
            // Filtrando usando JSON
            $departmentUsers = User::where('employee_properties->department', $request->department)->where('id', '!=', auth()->id())->get();
            Notification::send($departmentUsers, new BasicNotification($subject, "ha creado un nuevo ticket asignado a tu departamento (#$ticket->id). Resolver lo antes posible.", $owner->name, $owner->profile_photo_url, $url));
        }

        return to_route('tickets.index');
    }


    public function show($ticket_id)
    {
        // En SHOW sí necesitamos todo, incluido description
        $ticket = TicketResource::make(Ticket::withCount('ticketSolutions')->with('responsible:id,name,profile_photo_path', 'user:id,name,email,profile_photo_path,phone', 'category:id,name')->find($ticket_id));

        return inertia('Ticket/Show', compact('ticket'));
    }


    public function edit(Ticket $ticket)
    {
        $categories = Category::all();
        
        // Se traen todos los usuarios (excepto el id 1) con sus propiedades para agruparlos
        $users = User::where('id', '!=', 1)->get(['id', 'name', 'profile_photo_path', 'employee_properties']);

        $media = $ticket->getMedia()->all();

        return inertia('Ticket/Edit', compact('ticket', 'categories', 'users', 'media'));
    }


    public function update(Request $request, Ticket $ticket)
    {
        $request->validate([
            'category_id' => 'required',
            'responsible_id' => 'nullable|exists:users,id',
            'department' => 'nullable|string|max:255',
            'title' => 'required|string|max:100',
            'ticket_type' => 'required|string|max:255',
            'description' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (preg_match('/data:image\/[^;]+;base64/', $value)) {
                        $fail('No se permite pegar imágenes directamente. Usa la sección de archivos.');
                    }
                },
            ],
            'status' => 'required|string',
            'branch' => 'required|string',
            'priority' => 'required|string',
            'expired_date' => 'required|date',
        ]);

        //guarda al responsable antes de edición
        $current_responsible_id = $ticket->responsible_id;
        $current_department = $ticket->department;

        $ticket->update($request->all());

        // Manejo de historial y notificaciones si cambió la asignación
        if ($current_responsible_id != $request->responsible_id || $current_department != $request->department) {
            
            $descriptionHistory = "removió la asignación del ticket.";
            if ($request->responsible_id) {
                $new_responsible = User::find($request->responsible_id);
                $descriptionHistory = "asignó a *$new_responsible->name* como responsable del ticket.";
            } elseif ($request->department) {
                $descriptionHistory = "asignó el ticket al departamento de *$request->department*.";
            }

            TicketHistory::create([
                'description' =>  $descriptionHistory,
                'user_id' =>  auth()->id(),
                'ticket_id' =>  $ticket->id,
            ]);

            $owner = auth()->user();
            $url = route('tickets.show', $ticket->id);

            // Notificar a NUEVOS responsables
            if ($request->responsible_id && $request->responsible_id != $current_responsible_id) {
                $user = User::find($request->responsible_id);
                $user->notify(new BasicNotification("Ticket asignado", "te ha asignado como responsable del ticket #$ticket->id", $owner->name, $owner->profile_photo_url, $url));
            } elseif ($request->department && $request->department != $current_department) {
                $departmentUsers = User::where('employee_properties->department', $request->department)->where('id', '!=', auth()->id())->get();
                Notification::send($departmentUsers, new BasicNotification("Ticket asignado a tu departamento", "asignó el ticket #$ticket->id a tu departamento", $owner->name, $owner->profile_photo_url, $url));
            }

            // Notificar a ANTIGUOS responsables (si fueron removidos)
            if ($current_responsible_id && $current_responsible_id != $request->responsible_id) {
                $user = User::find($current_responsible_id);
                $user->notify(new BasicNotification("Ticket removido", "te ha removido como responsable del ticket #$ticket->id", $owner->name, $owner->profile_photo_url, $url));
            } elseif ($current_department && $current_department != $request->department) {
                $departmentUsers = User::where('employee_properties->department', $current_department)->where('id', '!=', auth()->id())->get();
                Notification::send($departmentUsers, new BasicNotification("Ticket removido", "removió el ticket #$ticket->id de tu departamento", $owner->name, $owner->profile_photo_url, $url));
            }

        } else if ($current_responsible_id || $current_department) {
            // Notificar que hubo una edición general
            $owner = auth()->user();
            $url = route('tickets.show', $ticket->id);
            if ($current_responsible_id) {
                $user = User::find($current_responsible_id);
                $user->notify(new BasicNotification("Ticket editado", "ha editado el ticket #$ticket->id", $owner->name, $owner->profile_photo_url, $url));
            } elseif ($current_department) {
                $departmentUsers = User::where('employee_properties->department', $current_department)->where('id', '!=', auth()->id())->get();
                Notification::send($departmentUsers, new BasicNotification("Ticket editado", "ha editado el ticket #$ticket->id", $owner->name, $owner->profile_photo_url, $url));
            }
        }

        return to_route('tickets.show', $ticket->id);
    }


    public function updateWithMedia(Request $request, Ticket $ticket)
    {
        $request->validate([
            'category_id' => 'required',
            'responsible_id' => 'nullable|exists:users,id',
            'department' => 'nullable|string|max:255',
            'title' => 'required|string|max:100',
            'ticket_type' => 'required|string|max:255',
            'description' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (preg_match('/data:image\/[^;]+;base64/', $value)) {
                        $fail('No se permite pegar imágenes directamente. Usa la sección de archivos.');
                    }
                },
            ],
            'status' => 'required|string',
            'branch' => 'required|string',
            'priority' => 'required|string',
            'expired_date' => 'required|date',
        ]);

        //guarda al responsable antes de edición
        $current_responsible_id = $ticket->responsible_id;
        $current_department = $ticket->department;

        $ticket->update($request->all());

        // Guardar media
        $ticket->addAllMediaFromRequest()->each(fn($file) => $file->toMediaCollection());

        // Manejo de historial y notificaciones si cambió la asignación
        if ($current_responsible_id != $request->responsible_id || $current_department != $request->department) {
            
            $descriptionHistory = "removió la asignación del ticket.";
            if ($request->responsible_id) {
                $new_responsible = User::find($request->responsible_id);
                $descriptionHistory = "asignó a *$new_responsible->name* como responsable del ticket.";
            } elseif ($request->department) {
                $descriptionHistory = "asignó el ticket al departamento de *$request->department*.";
            }

            TicketHistory::create([
                'description' =>  $descriptionHistory,
                'user_id' =>  auth()->id(),
                'ticket_id' =>  $ticket->id,
            ]);

            $owner = auth()->user();
            $url = route('tickets.show', $ticket->id);

            // Notificar a NUEVOS responsables
            if ($request->responsible_id && $request->responsible_id != $current_responsible_id) {
                $user = User::find($request->responsible_id);
                $user->notify(new BasicNotification("Ticket asignado", "te ha asignado como responsable del ticket #$ticket->id", $owner->name, $owner->profile_photo_url, $url));
            } elseif ($request->department && $request->department != $current_department) {
                $departmentUsers = User::where('employee_properties->department', $request->department)->where('id', '!=', auth()->id())->get();
                Notification::send($departmentUsers, new BasicNotification("Ticket asignado a tu departamento", "asignó el ticket #$ticket->id a tu departamento", $owner->name, $owner->profile_photo_url, $url));
            }

            // Notificar a ANTIGUOS responsables (si fueron removidos)
            if ($current_responsible_id && $current_responsible_id != $request->responsible_id) {
                $user = User::find($current_responsible_id);
                $user->notify(new BasicNotification("Ticket removido", "te ha removido de responsable del ticket #$ticket->id", $owner->name, $owner->profile_photo_url, $url));
            } elseif ($current_department && $current_department != $request->department) {
                $departmentUsers = User::where('employee_properties->department', $current_department)->where('id', '!=', auth()->id())->get();
                Notification::send($departmentUsers, new BasicNotification("Ticket removido", "removió el ticket #$ticket->id de tu departamento", $owner->name, $owner->profile_photo_url, $url));
            }

        } else if ($current_responsible_id || $current_department) {
            // Notificar a responsable si lo hay de que se editó
            $owner = auth()->user();
            $url = route('tickets.show', $ticket->id);
            if ($current_responsible_id) {
                $user = User::find($current_responsible_id);
                $user->notify(new BasicNotification("Ticket editado", "ha editado el ticket #$ticket->id", $owner->name, $owner->profile_photo_url, $url));
            } elseif ($current_department) {
                $departmentUsers = User::where('employee_properties->department', $current_department)->where('id', '!=', auth()->id())->get();
                Notification::send($departmentUsers, new BasicNotification("Ticket editado", "ha editado el ticket #$ticket->id", $owner->name, $owner->profile_photo_url, $url));
            }
        }

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
        $additional = [];
        if (request('status') == 'Completado') {
            $additional = [
                'solution_minutes' => $ticket->getSolutionMinutes(),
                'closed_at' => now()->toDateTimeString(),
                'paused_at' => null,
            ];
        } elseif (request('status') == 'Abierto' || request('status') == 'Re-abierto') {
            $additional = [
                'opened_at' => now()->toDateTimeString(),
                'closed_at' => null,
                'paused_at' => null,
            ];
        } elseif (request('status') == 'En espera de 3ro' || request('status') == 'En espera') {
            $additional = [
                'solution_minutes' => $ticket->getSolutionMinutes(),
                'paused_at' => now()->toDateTimeString(),
            ];
        } elseif (request('status') == 'En proceso' && $ticket->paused_at) {
            $additional = [
                'opened_at' => now()->toDateTimeString(),
                'paused_at' => null,
            ];
        }

        $ticket->update([
            'status' => request('status'),
            'updated_at' => now(),
        ] + $additional);

        //Crear registro de actividad
        TicketHistory::create([
            'description' =>  'cambió el estado del ticket a "' . $ticket->status . '".',
            'user_id' =>  auth()->id(),
            'ticket_id' =>  $ticket->id,
        ]);

        // notificar solo cuando se completa el ticket
        if ($ticket->status == 'Completado') {
            // notificar a creador de ticket
            $user = User::find($ticket->user_id);
            $owner = auth()->user();
            $subject = "Ticket completado";
            $description = "ha cambiado el estatus del ticket #$ticket->id a completado. Puedes ir a revisarlo";
            $user->notify(new BasicNotification($subject, $description, $owner->name, $owner->profile_photo_url, route('tickets.show', $ticket->id)));
        }

        return response()->json(['item' => TicketResource::make($ticket->refresh())]);
    }

    public function comment(Request $request, Ticket $ticket)
    {
        // 1. Validar que no haya imágenes en base64 en el cuerpo del comentario
        if (preg_match('/data:image\/[^;]+;base64/', $request->comment)) {
            throw ValidationException::withMessages([
                'comment' => 'No se permite pegar imágenes directamente en el texto. Por favor, usa el botón de "Adjuntar archivos".'
            ]);
        }

        $comment = new Comment([
            'body' => $request->comment,
            'user_id' => auth()->id(),
        ]);

        $ticket->comments()->save($comment);

        // 2. Guardar archivos adjuntos (MediaLibrary)
        // addAllMediaFromRequest busca automáticamente archivos en la request
        $comment->addAllMediaFromRequest()->each(fn($file) => $file->toMediaCollection());

        // 3. Procesar menciones
        // Como enviamos FormData, 'mentions' podría llegar como string JSON
        $mentionsInput = $request->mentions;
        if (is_string($mentionsInput)) {
            $mentions = json_decode($mentionsInput, true) ?? [];
        } else {
            $mentions = $mentionsInput ?? [];
        }

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
        // Se agrega 'media' al with() para cargar los archivos adjuntos
        $conversation = CommentResource::collection(Comment::where('commentable_type', 'App\Models\Ticket')
            ->where('commentable_id', $ticket)
            ->with(['user:id,name,profile_photo_path', 'media'])
            ->get());


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
        $userDepartment = auth()->user()->employee_properties['department'] ?? null;

        if ($userCanSeeAllTickets) {
            $tickets = TicketResource::collection(Ticket::select($this->selectFields) // Optimización
                ->with('category:id,name', 'responsible:id,name,profile_photo_path', 'user:id,name,profile_photo_path')
                ->where(function ($q) use ($query) {
                    $q->where('id', 'LIKE', "%$query%")
                        ->orWhere('title', 'LIKE', "%$query%")
                        ->orWhere('description', 'LIKE', "%$query%"); // Puedes filtrar por descripción, pero no seleccionarla
                })
                ->get());
        } else {
            $tickets = TicketResource::collection(Ticket::select($this->selectFields) // Optimización
                ->with('category:id,name', 'responsible:id,name,profile_photo_path', 'user:id,name,profile_photo_path')
                ->where(function($q) use ($userDepartment) {
                    $q->where('user_id', auth()->id())
                      ->orWhere('department', $userDepartment);
                })
                ->where(function ($q) use ($query) {
                    $q->where('id', 'LIKE', "%$query%")
                        ->orWhere('title', 'LIKE', "%$query%")
                        ->orWhere('description', 'LIKE', "%$query%");
                })
                ->get());
        }

        return response()->json(['items' => $tickets]);
    }

    public function getFilters($prop, $value)
    {
        if ($prop === 'created_at' || $prop === 'expired_date') {
            $tickets = TicketResource::collection($this->getTicketsByDate($prop, $value));
        } else {
            // Se usa select para evitar cargar description
            $tickets = TicketResource::collection(Ticket::select($this->selectFields) // Optimización
                ->with('category:id,name', 'responsible:id,name,profile_photo_path', 'user:id,name,profile_photo_path')
                ->where($prop, $value)
                ->get());
        }

        return response()->json(['items' => $tickets]);
    }

    public function getItemsByPage($currentPage)
    {
        $offset = $currentPage * 20;
        $userCanSeeAllTickets = auth()->user()->can('Ver todos los tickets');
        $userDepartment = auth()->user()->employee_properties['department'] ?? null;

        if ($userCanSeeAllTickets) {
            $tickets = TicketResource::collection(Ticket::select($this->selectFields) // Optimización
                ->latest('id')
                ->with('category:id,name', 'responsible:id,name,profile_photo_path', 'user:id,name,profile_photo_path')
                ->skip($offset)
                ->take(20)
                ->get());
        } else {
            $tickets = TicketResource::collection(Ticket::select($this->selectFields) // Optimización
                ->latest('id')
                ->where(function($q) use ($userDepartment) {
                    $q->where('user_id', auth()->id())
                      ->orWhere('department', $userDepartment);
                })
                ->with('category:id,name', 'responsible:id,name,profile_photo_path', 'user:id,name,profile_photo_path')
                ->skip($offset)
                ->take(20)
                ->get());
        }

        return response()->json(['items' => $tickets]);
    }

    private function getTicketsByDate($prop, $value)
    {
        // Base de la consulta con select optimizado
        $query = Ticket::select($this->selectFields) // Optimización
            ->with('category:id,name', 'responsible:id,name,profile_photo_path', 'user:id,name,profile_photo_path');

        if ($value === 'Hoy') {
            $tickets = $query->whereDate($prop, now())->get();
        } else if ($value === 'Esta semana') {
            $start = now()->startOfWeek();
            $end = now()->endOfWeek();
            $tickets = $query->whereBetween($prop, [$start, $end])->get();
        } else if ($value === 'Este mes') {
            $start = now()->startOfMonth();
            $end = now()->endOfMonth();
            $tickets = $query->whereBetween($prop, [$start, $end])->get();
        } else if ($value === 'Mes pasado') {
            $start = now()->subMonth()->startOfMonth();
            $end = now()->subMonth()->endOfMonth();
            $tickets = $query->whereBetween($prop, [$start, $end])->get();
        } else if ($value === 'Este año') {
            $start = now()->startOfYear();
            $end = now()->endOfYear();
            $tickets = $query->whereBetween($prop, [$start, $end])->get();
        } else if ($value === 'Año pasado') {
            $start = now()->subYear()->startOfYear();
            $end = now()->subYear()->endOfYear();
            $tickets = $query->whereBetween($prop, [$start, $end])->get();
        } else {
            $tickets = collect([]);
        }

        return $tickets;
    }

    // --------------------------------------------------------
    // MANTENIMIENTO: Migración de imágenes Base64 a Archivos
    // --------------------------------------------------------
    public function migrateImages()
    {
        $processed = 0;
        
        // 1. Procesar Tickets (5 registros a la vez)
        $tickets = Ticket::where('description', 'LIKE', '%src="data:image/%')
            ->take(100)
            ->get();

        foreach ($tickets as $ticket) {
            $count = $this->extractAndUploadImages($ticket, 'description');
            if ($count > 0) $processed++;
        }

        // 2. Procesar Soluciones (5 registros a la vez)
        $solutions = TicketSolution::where('description', 'LIKE', '%src="data:image/%')
            ->take(100)
            ->get();

        foreach ($solutions as $solution) {
            $count = $this->extractAndUploadImages($solution, 'description');
            if ($count > 0) $processed++;
        }

        // 3. Procesar Comentarios (5 registros a la vez)
        $comments = Comment::where('body', 'LIKE', '%src="data:image/%')
            ->take(100)
            ->get();

        foreach ($comments as $comment) {
            $count = $this->extractAndUploadImages($comment, 'body');
            if ($count > 0) $processed++;
        }

        return response()->json([
            'message' => 'Proceso ejecutado',
            'processed_records' => $processed,
            'remaining_tickets' => Ticket::where('description', 'LIKE', '%src="data:image/%')->count(),
            'remaining_solutions' => TicketSolution::where('description', 'LIKE', '%src="data:image/%')->count(),
            'remaining_comments' => Comment::where('body', 'LIKE', '%src="data:image/%')->count(),
        ]);
    }

    /**
     * Extrae imágenes base64, las sube a MediaLibrary y limpia el texto.
     */
    private function extractAndUploadImages($model, $field)
    {
        $text = $model->$field;
        // Expresión regular para encontrar etiquetas img con src en base64
        // Captura: grupo 1 (extensión), grupo 2 (datos base64)
        $pattern = '/<img[^>]+src="data:image\/([^;]+);base64,([^"]+)"[^>]*>/i';
        
        $count = 0;

        if (preg_match_all($pattern, $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $fullTag = $match[0];
                $extension = $match[1]; // png, jpeg, etc.
                $base64Data = $match[2];

                // Decodificar imagen
                $image = base64_decode($base64Data);
                
                if ($image === false) continue; // Si falla la decodificación, saltar

                // Crear nombre de archivo temporal único
                $fileName = 'migrated_' . Str::random(10) . '.' . $extension;
                $tempPath = sys_get_temp_dir() . '/' . $fileName;

                // Guardar en temporal
                file_put_contents($tempPath, $image);

                try {
                    // Subir usando Spatie MediaLibrary
                    // Se usa la colección predeterminada o una específica 'default'
                    $model->addMedia($tempPath)
                          ->usingFileName($fileName)
                          ->toMediaCollection(); 
                    
                    // Limpiar el texto: Reemplazar toda la etiqueta <img> por nada
                    // (Opcional: podrías poner un texto como [Imagen adjunta])
                    $text = str_replace($fullTag, '', $text);
                    $count++;

                } catch (\Exception $e) {
                    // Si falla la subida, no modificamos el texto para no perder la imagen original
                    Log::error("Error migrando imagen en Ticket/Solution ID {$model->id}: " . $e->getMessage());
                }

                // Eliminar archivo temporal
                if (file_exists($tempPath)) {
                    unlink($tempPath);
                }
            }

            // Guardar el modelo con el texto limpio solo si hubo cambios
            if ($count > 0) {
                $model->$field = $text;
                $model->saveQuietly(); // saveQuietly evita disparar eventos de update (notificaciones, updated_at, etc.)
            }
        }

        return $count;
    }
}