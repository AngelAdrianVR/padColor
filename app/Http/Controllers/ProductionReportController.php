<?php

namespace App\Http\Controllers;

use App\Models\Production;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ProductionReportController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
        ]);

        // --- Current Period ---
        $startDate = Carbon::parse($request->startDate)->startOfDay();
        $endDate = Carbon::parse($request->endDate)->endOfDay();
        $currentData = $this->calculateReportMetrics($startDate, $endDate);

        // --- Previous Period ---
        $daysDifference = $endDate->diffInDays($startDate);
        $prevStartDate = $startDate->copy()->subDays($daysDifference + 1);
        $prevEndDate = $endDate->copy()->subDays($daysDifference + 1);
        $previousData = $this->calculateReportMetrics($prevStartDate, $prevEndDate);

        // --- Calculate Comparisons ---
        $comparison = [
            'effective_time' => $this->calculatePercentageChange($previousData['avg_effective_time'], $currentData['avg_effective_time']),
            'paused_time' => $this->calculatePercentageChange($previousData['avg_paused_time'], $currentData['avg_paused_time']),
            'waiting_time' => $this->calculatePercentageChange($previousData['avg_waiting_time'], $currentData['avg_waiting_time']),
            'finished_orders' => $this->calculatePercentageChange($previousData['finished_orders'], $currentData['finished_orders']),
            'general_efficiency' => $this->calculatePercentageChange($previousData['general_efficiency'], $currentData['general_efficiency']),
        ];

        return response()->json([
            'current_period' => $currentData,
            'previous_period' => $previousData,
            'comparison' => $comparison,
        ]);
    }

    private function calculateReportMetrics($startDate, $endDate)
    {
        $productions = Production::whereBetween('created_at', [$startDate, $endDate])->get();
        $finishedProductions = $productions->where('station', 'Terminadas');

        $totalEffective = 0;
        $totalPaused = 0;
        $totalWaiting = 0;
        $pauseReasons = [];

        foreach ($productions as $production) {
            if (is_array($production->station_times)) {
                foreach ($production->station_times as $record) {
                    $totalEffective += $record['times']['effective_seconds'] ?? 0;
                    $totalPaused += $record['times']['paused_seconds'] ?? 0;
                    $totalWaiting += $record['times']['waiting_seconds'] ?? 0;

                    if (isset($record['pauses']) && is_array($record['pauses'])) {
                        foreach ($record['pauses'] as $pause) {
                            $reason = $pause['reason'] ?? 'Otro';
                            if (!isset($pauseReasons[$reason])) {
                                $pauseReasons[$reason] = 0;
                            }
                            $pauseReasons[$reason]++;
                        }
                    }
                }
            }
        }

        $productionCount = $productions->count() > 0 ? $productions->count() : 1;
        $totalTime = $totalEffective + $totalPaused + $totalWaiting;

        return [
            'avg_effective_time' => $totalEffective / $productionCount,
            'avg_paused_time' => $totalPaused / $productionCount,
            'avg_waiting_time' => $totalWaiting / $productionCount,
            'finished_orders' => $finishedProductions->count(),
            'general_efficiency' => $totalTime > 0 ? ($totalEffective / $totalTime) * 100 : 0,
            'time_breakdown' => [
                'effective' => $totalEffective,
                'paused' => $totalPaused,
                'waiting' => $totalWaiting,
            ],
            'pause_reasons' => $pauseReasons,
        ];
    }

    private function calculatePercentageChange($previous, $current)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        return (($current - $previous) / $previous) * 100;
    }
}