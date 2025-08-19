<?php

namespace App\Http\Controllers;

use App\Models\CustomsAgent;
use Illuminate\Http\Request;

class CustomsAgentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:suppliers|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|numeric',
        ]);

        CustomsAgent::create($request->all());

        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(CustomsAgent $customsAgent)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CustomsAgent $customsAgent)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CustomsAgent $customsAgent)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CustomsAgent $customsAgent)
    {
        //
    }
}
