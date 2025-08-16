<?php

namespace App\Http\Controllers;

use App\Http\Resources\PermissionResource;
use App\Http\Resources\RoleResource;
use App\Models\Category;
use App\Models\NotificationEvent;
use App\Models\NotificationSubscription;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SettingController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'event_id' => 'nullable|integer|exists:notification_events,id',
        ]);

        $roles = RoleResource::collection(Role::all());
        $permissions = PermissionResource::collection(Permission::all()->groupBy(function ($data) {
            return $data->category;
        }));

        $allEvents = NotificationEvent::orderBy('name')->get();
        $selectedEventId = $request->input('event_id', $allEvents->first()?->id);

        $userSubscriptions = [];
        $externalSubscriptions = [];

        if ($selectedEventId) {
            $userSubscriptions = NotificationSubscription::where('notification_event_id', $selectedEventId)
                ->where('notifiable_type', User::class)
                ->pluck('notifiable_id')
                ->toArray();

            $externalSubscriptions = NotificationSubscription::where('notification_event_id', $selectedEventId)
                ->where('notifiable_type', 'external')
                ->pluck('notifiable_id')
                ->toArray();
        }

        $usersQuery = User::query()
            ->when($request->input('search'), function ($query, $search) {
                $query->where(fn($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            })
            ->when($request->input('department'), fn($q, $d) => $q->where('employee_properties->department', 'like', "%{$d}%"))
            ->when($request->input('company'), fn($q, $c) => $q->where('employee_properties->company', 'like', "%{$c}%"));

        return inertia('Setting/Index', [
            'notificationEvents' => $allEvents,
            'users' => $usersQuery->paginate(9)->withQueryString(),
            'initialSubscriptions' => [
                'users' => $userSubscriptions,
                'external' => $externalSubscriptions,
            ],
            'filters' => $request->only(['search', 'department', 'company', 'event_id']),
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    public function storeRole(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:191',
            'permissions' => 'array|min:1'
        ]);

        $role = Role::create(['name' => $request->name]);
        $role->syncPermissions($request->permissions);

        return response()->json(['item' => RoleResource::make($role)]);
    }

    public function updateRole(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'array|min:1'
        ]);

        $role->name = $request->name;
        $role->save();
        $role->syncPermissions($request->permissions);

        return response()->json(['item' => RoleResource::make($role)]);
    }

    public function deleteRole(Role $role)
    {
        $role->delete();

        return response()->json(['message' => "Rol: *$role->name* eliminado"]);
    }

    public function storePermission(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:191',
            'category' => 'required|string|max:191'
        ]);

        $permission = Permission::create($request->all());

        return response()->json(['item' => PermissionResource::make($permission)]);
    }

    public function updatePermission(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => 'required|string|max:191',
            'category' => 'required|string|max:191'
        ]);

        $permission->update($request->all());

        return response()->json(['item' => PermissionResource::make($permission)]);
    }

    public function deletePermission(Permission $permission)
    {
        $permission->delete();

        return response()->json(['message' => "Permiso: *$permission->name* eliminado"]);
    }

    public function rolesMassiveDelete(Request $request)
    {
        foreach ($request->items_ids as $id) {
            $item = Role::find($id);
            $item?->delete();
        }

        return response()->json(['message' => 'rol(es) eliminado(s)']);
    }

    public function permissionsMassiveDelete(Request $request)
    {
        foreach ($request->items_ids as $id) {
            $item = Permission::find($id);
            $item?->delete();
        }

        return response()->json(['message' => 'Permiso(s) eliminado(s)']);
    }

    public function categoriesMassiveDelete(Request $request)
    {
        foreach ($request->items_ids as $id) {
            $item = Category::find($id);
            $item?->delete();
        }

        return response()->json(['message' => 'categoría(s) eliminada(s)']);
    }

    public function getAllCategories()
    {
        $categories = Category::all();

        return response()->json(['items' => $categories]);
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $category = Category::create($validated);

        return response()->json(['item' => $category]);
    }
}
