<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    
    public function index()
    {
        $users = UserResource::collection(User::all());

        return inertia('User/Index', compact('users'));
    }

    
    public function create()
    {
        //
    }

    
    public function store(Request $request)
    {
        //
    }

    
    public function show(user $user)
    {
        //
    }

    
    public function edit(user $user)
    {
        //
    }

    
    public function update(Request $request, user $user)
    {
        //
    }

    
    public function destroy(user $user)
    {
        //
    }
}
