<?php

namespace App\Http\Controllers;

use App\Http\Resources\PermissionResource;
use App\Http\Resources\RoleResource;
use App\Models\User;
use App\Models\NotificationEvent;
use App\Models\NotificationSubscription;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class NotificationController extends Controller
{
    // public function index(Request $request)
    // {
    //     $request->validate([
    //         'event_id' => 'nullable|integer|exists:notification_events,id',
    //     ]);

    //     $roles = RoleResource::collection(Role::all());
    //     $permissions = PermissionResource::collection(Permission::all()->groupBy(function ($data) {
    //         return $data->category;
    //     }));

    //     $allEvents = NotificationEvent::orderBy('name')->get();
    //     $selectedEventId = $request->input('event_id', $allEvents->first()?->id);

    //     $userSubscriptions = [];
    //     $externalSubscriptions = [];

    //     if ($selectedEventId) {
    //         $userSubscriptions = NotificationSubscription::where('notification_event_id', $selectedEventId)
    //             ->where('notifiable_type', User::class)
    //             ->pluck('notifiable_id')
    //             ->toArray();

    //         $externalSubscriptions = NotificationSubscription::where('notification_event_id', $selectedEventId)
    //             ->where('notifiable_type', 'external')
    //             ->pluck('notifiable_id')
    //             ->toArray();
    //     }

    //     $usersQuery = User::query()
    //         ->when($request->input('search'), function ($query, $search) {
    //             $query->where(fn($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
    //         })
    //         ->when($request->input('department'), fn($q, $d) => $q->where('employee_properties->department', 'like', "%{$d}%"))
    //         ->when($request->input('company'), fn($q, $c) => $q->where('employee_properties->company', 'like', "%{$c}%"));

    //     return inertia('Setting/Index', [
    //         'notificationEvents' => $allEvents,
    //         'users' => $usersQuery->paginate(9)->withQueryString(),
    //         'initialSubscriptions' => [
    //             'users' => $userSubscriptions,
    //             'external' => $externalSubscriptions,
    //         ],
    //         'filters' => $request->only(['search', 'department', 'company', 'event_id']),
    //         'roles' => $roles,
    //         'permissions' => $permissions,
    //     ]);
    // }

    public function toggleUserSubscription(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:notification_events,id',
            'user_id' => 'required|exists:users,id',
        ]);

        $subscription = NotificationSubscription::where('notification_event_id', $request->event_id)
            ->where('notifiable_id', $request->user_id)
            ->where('notifiable_type', User::class)
            ->first();

        if ($subscription) {
            $subscription->delete();
        } else {
            NotificationSubscription::create([
                'notification_event_id' => $request->event_id,
                'notifiable_id' => $request->user_id,
                'notifiable_type' => User::class,
            ]);
        }
    }

    public function addExternalSubscription(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:notification_events,id',
            'email' => [
                'required',
                'email',
                Rule::unique('notification_subscriptions', 'notifiable_id')->where(function ($query) use ($request) {
                    return $query->where('notification_event_id', $request->event_id)
                        ->where('notifiable_type', 'external');
                }),
            ],
        ], ['email.unique' => 'Este correo ya está suscrito a esta notificación.']);

        NotificationSubscription::create([
            'notification_event_id' => $request->event_id,
            'notifiable_id' => $request->email,
            'notifiable_type' => 'external',
        ]);

        return back()->with('success', 'Correo externo añadido correctamente.');
    }

    public function removeExternalSubscription(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:notification_events,id',
            'email' => 'required|email',
        ]);

        $subscription = NotificationSubscription::where('notification_event_id', $request->event_id)
            ->where('notifiable_id', $request->email)
            ->where('notifiable_type', 'external')
            ->first();

        if ($subscription) {
            $subscription->delete();
        }

        return back()->with('success', 'Suscripción externa eliminada.');
    }
}
