<?php

namespace App\Http\Controllers;

use App\Models\ChangeRequest;
use Illuminate\Http\Request;

class ChangeRequestController extends Controller
{
    public function index()
    {
        // Logic to show a list of change requests
    }

    public function create()
    {
        // Logic to show the form for creating a new change request
    }

    public function store(Request $request)
    {
        // Logic to save a new change request
    }

    public function show(ChangeRequest $changeRequest)
    {
        // Logic to show a single change request
    }

    public function update(Request $request, ChangeRequest $changeRequest)
    {
        // Logic to update a change request (e.g., approve/reject)
    }

    public function destroy(ChangeRequest $changeRequest)
    {
        // Logic to delete a change request
    }
}
