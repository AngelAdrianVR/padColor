<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\NotificationEvent;
use App\Models\NotificationSubscription;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        // --- 1. Validar y obtener el evento seleccionado ---
        $request->validate([
            'event_id' => 'nullable|integer|exists:notification_events,id',
        ]);

        $allEvents = NotificationEvent::orderBy('name')->get();
        $selectedEventId = $request->input('event_id', $allEvents->first()->id);

        // --- 2. Obtener las suscripciones para el evento actual ---
        $userSubscriptions = NotificationSubscription::where('notification_event_id', $selectedEventId)
            ->where('notifiable_type', User::class)
            ->pluck('notifiable_id')
            ->toArray(); // [101, 105]

        $externalSubscriptions = NotificationSubscription::where('notification_event_id', $selectedEventId)
            ->where('notifiable_type', 'external')
            ->pluck('notifiable_id')
            ->toArray(); // ['proveedor@externo.com']

        // --- 3. Construir la consulta de usuarios con filtros ---
        $usersQuery = User::query()
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->input('department'), function ($query, $department) {
                // Asumiendo que 'employee_properties' es una columna JSON en tu tabla de usuarios
                $query->where('employee_properties->department', 'like', "%{$department}%");
            })
            ->when($request->input('company'), function ($query, $company) {
                $query->where('employee_properties->company', 'like', "%{$company}%");
            });

        // --- 4. Renderizar la vista de Inertia con todos los datos (props) ---
        return Inertia::render('Notifications/NotificationManager', [
            'notificationEvents' => $allEvents,
            'users' => $usersQuery->paginate(10)->withQueryString(),
            'initialSubscriptions' => [
                'users' => $userSubscriptions,
                'external' => $externalSubscriptions,
            ],
            'filters' => $request->only(['search', 'department', 'company', 'event_id']),
        ]);
    }

    // Aquí irían los métodos para guardar y borrar suscripciones
    // public function toggleUserSubscription(Request $request) { ... }
    // public function addExternalSubscription(Request $request) { ... }
    // public function removeExternalSubscription(NotificationSubscription $subscription) { ... }
}
