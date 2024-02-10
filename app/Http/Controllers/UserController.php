<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{

    public function index()
    {
        $users = UserResource::collection(User::all());

        return inertia('User/Index', compact('users'));
    }


    public function create()
    {
        $roles = Role::all();

        return inertia('User/Create', compact('roles'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255|unique:users',
            'phone' => 'required|string|max:15',
            'employee_properties.department' => 'required|string|max:255',
            'employee_properties.job_position' => 'required|string|max:255',
            'roles' => 'required|array|min:1',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = User::create($request->all() + ['password' => bcrypt('padColor.')]);

        // guardar foto de perfil en caso de haberse seleccionado una
        if ($request->hasFile('image')) {
            $this->storeProfilePhoto($request, $user);
            // convertir a int los roles para que no ocurra error
            $roles = array_map('intval', $request->roles);
            $user->syncRoles($roles);
        } else {
            $user->syncRoles($request->roles);
        }

        return to_route('users.show', $user->id);
    }


    public function show(user $user)
    {
        $users = User::get(['id', 'name']);

        return inertia('User/Show', compact('user', 'users'));
    }


    public function edit(user $user)
    {
        $roles = Role::all();
        $user_roles = $user->roles->pluck('id');

        return inertia('User/Edit', compact('user', 'roles', 'user_roles'));
    }


    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:15',
            'is_active' => 'boolean',
            'employee_properties.department' => 'required|string|max:255',
            'employee_properties.job_position' => 'required|string|max:255',
            'roles' => 'required|array|min:1',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user->update($request->all());
        $user->syncRoles($request->roles);

        if (!$request->selectedImage) {
            $this->deleteProfilePhoto($user);
        }

        return to_route('users.show', $user->id);
    }

    public function updateWithMedia(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:15',
            'is_active' => 'boolean',
            'employee_properties.department' => 'required|string|max:255',
            'employee_properties.job_position' => 'required|string|max:255',
            'roles' => 'required|array|min:1',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user->update($request->all());
        // convertir a int los roles para que no ocurra error
        $roles = array_map('intval', $request->roles);
        $user->syncRoles($roles);

        $this->deleteProfilePhoto($user);
        $this->storeProfilePhoto($request, $user);

        return to_route('users.show', $user->id);
    }


    public function destroy(user $user)
    {
        //
    }

    public function storeProfilePhoto($request, User $user)
    {
        // Guarda la imagen en el sistema de archivos.
        $path = $request->file('image')->store('public/profile-photos');
        // Elimina el prefijo 'public' de la ruta.
        $path = str_replace('public/', '', $path);
        // Actualiza la propiedad 'profile_photo_path' del usuario.
        $user->update([
            'profile_photo_path' => $path,
        ]);
    }

    public function deleteProfilePhoto(User $user)
    {
        $currentPhoto = $user->profile_photo_path;

        if ($currentPhoto) {
            Storage::delete('public/' . $currentPhoto);
            $user->update([
                'profile_photo_path' => null,
            ]);
        }
    }

    public function massiveDelete(Request $request)
    {
        foreach ($request->items_ids as $id) {
            $item = User::find($id);
            $item?->delete();
        }

        return response()->json(['message' => 'usuario(s) eliminado(s)']);
    }

    public function getMatches($query)
    {
        $users = UserResource::collection(User::where('id', 'LIKE', "%$query%")
            ->orWhere('name', 'LIKE', "%$query%")
            ->orWhere('employee_properties->department', 'LIKE', "%$query%")
            ->orWhere('employee_properties->job_position', 'LIKE', "%$query%")
            ->orWhere('phone', 'LIKE', "%$query%")
            ->orWhere('email', 'LIKE', "%$query%")
            ->get());

        return response()->json(['items' => $users]);
    }

    public function getNotifications(Request $request)
    {
        $notifications = auth()->user()->notifications()->where('data->module', $request->module)->get();

        return response()->json(['items' => NotificationResource::collection($notifications)]);
    }

    public function readNotifications(Request $request)
    {
        $notifications = auth()->user()->notifications()->whereIn('id', $request->notifications_ids)->get();
        $notifications->markAsRead();

        return response()->json([]);
    }

    public function deleteNotifications(Request $request)
    {
        $notifications = auth()->user()->notifications()->whereIn('id', $request->notifications_ids)->delete();

        return response()->json([]);
    }
}
