<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Ticket;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $tickets = Ticket::with(['category', 'ticketSolutions'])->get();
        $categories = Category::all();

        return inertia('Dashboard', compact('tickets', 'categories'));
    }
}
