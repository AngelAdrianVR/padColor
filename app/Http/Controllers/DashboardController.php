<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $tickets = Ticket::with(['category', 'ticketSolutions'])->get();

        return inertia('Dashboard', compact('tickets'));
    }
}
