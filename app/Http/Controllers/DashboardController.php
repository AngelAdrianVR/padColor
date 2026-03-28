<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Ticket;
use App\Models\TicketSolution;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // CORRECCIÓN: Se agregaron 'ticket_id' y 'user_id' a ticketSolutions para poder vincular quién resolvió el ticket
        $tickets = Ticket::with(['category:id,name', 'ticketSolutions:id,ticket_id,user_id,created_at'])
            ->get(['id', 'status', 'priority', 'category_id', 'created_at', 'solution_minutes', 'expired_date', 'branch', 'user_id', 'responsible_id', 'department']);
            
        $categories = Category::all(['id', 'name']);
        
        // Se agregó 'employee_properties' para poder agrupar usuarios por departamento en el Frontend
        $users = User::where('is_active', true)->get(['id', 'name', 'profile_photo_path', 'employee_properties']);

        // Sumatorias pre-calculadas por usuario
        $userStats = [
            'created' => Ticket::selectRaw('user_id, count(*) as total')
                ->groupBy('user_id')
                ->pluck('total', 'user_id'),
            
            'solutions' => TicketSolution::selectRaw('user_id, count(*) as total')
                ->groupBy('user_id')
                ->pluck('total', 'user_id'),
            
            'pending_assigned' => Ticket::where('status', '!=', 'Completado')
                ->selectRaw('responsible_id, count(*) as total')
                ->whereNotNull('responsible_id')
                ->groupBy('responsible_id')
                ->pluck('total', 'responsible_id'),
        ];

        // Sumatorias pre-calculadas por departamento
        $departmentStats = [
            'pending_assigned' => Ticket::where('status', '!=', 'Completado')
                ->selectRaw('department, count(*) as total')
                ->whereNotNull('department')
                ->groupBy('department')
                ->pluck('total', 'department'),
        ];

        return inertia('Dashboard', compact('tickets', 'categories', 'users', 'userStats', 'departmentStats'));
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