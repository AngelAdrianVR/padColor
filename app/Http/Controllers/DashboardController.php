<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Ticket;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $tickets = Ticket::with(['category:id,name', 'ticketSolutions:id,created_at'])
            ->get(['id', 'status', 'priority', 'category_id', 'created_at', 'solution_minutes', 'expired_date', 'branch']);
        $categories = Category::all(['id', 'name']);

        return inertia('Dashboard', compact('tickets', 'categories'));
    }

    public function ticketsReport()
    {
        $startDate = request('startDate');
        $endDate = request('endDate');
        $category = request('category');

        $tickets = Ticket::with(['category', 'ticketSolutions', 'responsible:id,name'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->when($category !== 'Todas', function ($query) use ($category) {
                return $query->whereHas('category', function ($query) use ($category) {
                    $query->where('name', $category);
                });
            })
            ->get();

        return inertia('Report/Tickets', compact('tickets', 'startDate', 'endDate'));
    }
}
