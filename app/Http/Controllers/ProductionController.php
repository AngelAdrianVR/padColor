<?php

namespace App\Http\Controllers;

use App\Models\Production;
use Illuminate\Http\Request;

class ProductionController extends Controller
{
    public function index()
    {
        $productions = Production::with(['user', 'category'])->latest()->get();

        return inertia('Production/Index', compact('productions'));
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show(Production $production)
    {
        //
    }

    public function edit(Production $production)
    {
        //
    }

    public function update(Request $request, Production $production)
    {
        //
    }

    public function destroy(Production $production)
    {
        //
    }
}
