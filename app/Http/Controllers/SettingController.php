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
            'users' => $usersQuery->get(),
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
            'name' => 'required|string|max:255',
            'allowed_departments' => 'nullable|array' // VALIDACIÓN NUEVO CAMPO
        ]);

        $category = Category::create($validated);

        return response()->json(['item' => $category]);
    }

    public function updateCategory(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'allowed_departments' => 'nullable|array' // VALIDACIÓN NUEVO CAMPO
        ]);

        $category->update($validated);

        return response()->json(['item' => $category]);
    }

    // Obtener las reglas actuales
    public function getTicketAssignmentRules()
    {
        $rules = \App\Models\TicketAssignmentRule::all();
        return response()->json(['items' => $rules]);
    }

    // Guardar las reglas
    public function updateTicketAssignmentRules(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'rules' => 'required|array'
        ]);

        foreach ($request->rules as $department => $allowed) {
            \App\Models\TicketAssignmentRule::updateOrCreate(
                ['department' => $department],
                ['allowed_departments' => $allowed]
            );
        }

        return response()->json(['message' => 'Reglas de asignación guardadas correctamente']);
    }

    /**
     * Página del Portal de Clientes para subir archivos .blade.php.
     */
    public function customerPortal()
    {
        return inertia('CustomerPortal/Index');
    }

    /**
     * Subir archivo .blade.php para el portal de clientes.
     * Solo usuarios con permiso 'Subir archivos a servidor para portal de clientes'.
     */
    public function uploadPortalFile(Request $request)
    {
        if (!auth()->user()->can('Subir archivos a servidor para portal de clientes')) {
            abort(403, 'No tienes permiso para subir archivos al portal.');
        }

        $request->validate([
            'route_key' => 'required|string|in:pedidos,tutorial,catalogo,buscador,credito,guias',
            'file' => [
                'required',
                'file',
                'max:15360',
                function ($attribute, $value, $fail) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    if ($ext !== 'php' && $ext !== 'html') {
                        $fail('El archivo debe ser de tipo .php (Laravel Blade) o .html.');
                    }
                },
            ],
        ], [
            'route_key.required' => 'Debes seleccionar una ruta.',
            'route_key.in' => 'La ruta seleccionada no es válida.',
            'file.required' => 'Debes seleccionar un archivo.',
            'file.max' => 'El archivo no debe superar los 15MB.',
        ]);

        // Mapeo de claves de ruta a nombres de archivo en el servidor
        $fileMap = [
            'pedidos' => 'pedidos.blade.php',
            'tutorial' => 'tutorial.blade.php',
            'catalogo' => 'catalogo.blade.php',
            'buscador' => 'buscador.blade.php',
            'credito' => 'credito.blade.php',
            'guias' => 'guias.blade.php',
        ];

        $filename = $fileMap[$request->route_key];
        $targetPath = resource_path('views/external');

        // Subir y reemplazar el archivo
        $request->file('file')->move($targetPath, $filename);

        return response()->json([
            'message' => "Archivo '$filename' actualizado correctamente en el servidor."
        ]);
    }
}