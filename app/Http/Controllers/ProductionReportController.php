<?php

namespace App\Http\Controllers;

use App\Models\Production;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProductionReportController extends Controller
{
    public function getReportData(Request $request)
    {
        $request->validate([
            'startDate' => 'required|date',
            'endDate' => 'required|date',
        ]);

        // Ensure the date range covers the entire day(s) selected.
        $currentPeriodStart = Carbon::parse($request->startDate)->startOfDay();
        $currentPeriodEnd = Carbon::parse($request->endDate)->endOfDay();

        $durationInDays = $currentPeriodStart->diffInDays($currentPeriodEnd);
        $previousPeriodStart = $currentPeriodStart->copy()->subDays($durationInDays + 1);
        $previousPeriodEnd = $currentPeriodEnd->copy()->subDays($durationInDays + 1);

        $currentData = $this->calculateMetricsForPeriod($currentPeriodStart, $currentPeriodEnd);
        $previousData = $this->calculateMetricsForPeriod($previousPeriodStart, $previousPeriodEnd);

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

    private function calculateMetricsForPeriod(Carbon $start, Carbon $end)
    {
        // Eager load the product relationship
        $productions = Production::with('product')->whereBetween('created_at', [$start, $end])->get();
        $totalProductions = $productions->count();

        $totalEffectiveTime = 0;
        $totalPausedTime = 0;
        $totalWaitingTime = 0;
        $pauseReasons = [];
        $performanceByStation = [];
        
        // NEW: Process productions for the detailed list
        $productionsList = $productions->map(function ($production) use (&$totalEffectiveTime, &$totalPausedTime, &$totalWaitingTime, &$pauseReasons, &$performanceByStation) {
            $prodTotalEffective = 0;
            $prodTotalPaused = 0;
            $prodTotalWaiting = 0;

            if (is_array($production->station_times)) {
                foreach ($production->station_times as $record) {
                    $effective = $record['times']['effective_seconds'] ?? 0;
                    $paused = $record['times']['paused_seconds'] ?? 0;
                    $waiting = $record['times']['waiting_seconds'] ?? 0;

                    // Sum for individual production
                    $prodTotalEffective += $effective;
                    $prodTotalPaused += $paused;
                    $prodTotalWaiting += $waiting;
                    
                    // Sum for overall report metrics
                    $totalEffectiveTime += $effective;
                    $totalPausedTime += $paused;
                    $totalWaitingTime += $waiting;

                    // Aggregate Pause Reasons for the entire report
                    if (is_array($record['pauses'])) {
                        foreach ($record['pauses'] as $pause) {
                            $reason = $pause['reason'] ?? 'Otro';
                            $pauseReasons[$reason] = ($pauseReasons[$reason] ?? 0) + 1;
                        }
                    }

                    // Aggregate Performance by Station for the entire report
                    $stationName = $record['station_name'];
                    if (!isset($performanceByStation[$stationName])) {
                        $performanceByStation[$stationName] = ['effective' => 0, 'paused' => 0, 'waiting' => 0];
                    }
                    $performanceByStation[$stationName]['effective'] += $effective;
                    $performanceByStation[$stationName]['paused'] += $paused;
                    $performanceByStation[$stationName]['waiting'] += $waiting;
                }
            }

            return [
                'folio' => $production->folio,
                'product_name' => $production->product?->name ?? 'N/A',
                'quantity' => $production->quantity,
                'total_effective_time' => $prodTotalEffective,
                'total_paused_time' => $prodTotalPaused,
                'total_waiting_time' => $prodTotalWaiting,
                'created_at' => $production->created_at->toDateTimeString(),
                'finish_date' => $production->finish_date?->toDateTimeString(),
            ];
        });
        
        if ($totalProductions === 0) {
            return $this->getEmptyMetrics();
        }

        $totalTime = $totalEffectiveTime + $totalPausedTime + $totalWaitingTime;

        return [
            'avg_effective_time' => $totalProductions > 0 ? $totalEffectiveTime / $totalProductions : 0,
            'avg_paused_time' => $totalProductions > 0 ? $totalPausedTime / $totalProductions : 0,
            'avg_waiting_time' => $totalProductions > 0 ? $totalWaitingTime / $totalProductions : 0,
            'finished_orders' => $productions->whereIn('station', ['Terminadas', 'Empaques terminado'])->count(),
            'general_efficiency' => $totalTime > 0 ? ($totalEffectiveTime / $totalTime) * 100 : 0,
            'pause_reasons' => $pauseReasons,
            'time_breakdown' => [
                'effective' => $totalEffectiveTime,
                'paused' => $totalPausedTime,
                'waiting' => $totalWaitingTime,
            ],
            'performance_by_station' => $performanceByStation,
            'productions_list' => $productionsList, // Add the list to the response
        ];
    }
    
    public function printableReport(Request $request)
    {
        $request->validate([
            'startDate' => 'required|date',
            'endDate' => 'required|date',
        ]);

        $start = Carbon::parse($request->startDate)->startOfDay();
        $end = Carbon::parse($request->endDate)->endOfDay();

        $reportData = $this->calculateMetricsForPeriod($start, $end);

        $analysis = [
            'station_highlight' => null,
            'pause_highlight' => null,
        ];

        if (!empty($reportData['performance_by_station'])) {
            $maxTime = 0;
            $worstStation = null;
            foreach ($reportData['performance_by_station'] as $station => $times) {
                $totalTime = ($times['effective'] ?? 0) + ($times['paused'] ?? 0) + ($times['waiting'] ?? 0);
                if ($totalTime > $maxTime) {
                    $maxTime = $totalTime;
                    $worstStation = $station;
                }
            }
            if ($worstStation) {
                $analysis['station_highlight'] = "La estación de **{$worstStation}** acumuló el mayor tiempo total. Se recomienda analizar los procesos.";
            }
        }
        
        if (!empty($reportData['pause_reasons'])) {
            $topReason = array_keys($reportData['pause_reasons'], max($reportData['pause_reasons']))[0];
            $totalPauses = array_sum($reportData['pause_reasons']);
            $topReasonPercentage = $totalPauses > 0 ? ($reportData['pause_reasons'][$topReason] / $totalPauses) * 100 : 0;
            $analysis['pause_highlight'] = "La **{$topReason}** representó el **" . round($topReasonPercentage) . "%** del tiempo en pausa, siendo el área de mayor oportunidad.";
        }

        return inertia('Production/PrintableReport', [
            'reportData' => $reportData,
            'analysis' => $analysis,
            'startDate' => $start->isoFormat('D [de] MMMM [de] YYYY'),
            'endDate' => $end->isoFormat('D [de] MMMM [de] YYYY'),
        ]);
    }

    private function getEmptyMetrics()
    {
         return [
            'avg_effective_time' => 0,
            'avg_paused_time' => 0,
            'avg_waiting_time' => 0,
            'finished_orders' => 0,
            'general_efficiency' => 0,
            'pause_reasons' => [],
            'time_breakdown' => ['effective' => 0, 'paused' => 0, 'waiting' => 0],
            'performance_by_station' => [],
            'productions_list' => [], // Ensure empty list is returned
        ];
    }

    private function calculatePercentageChange($previous, $current)
    {
        if ($previous == 0) {
            return $current > 0 ? 100.0 : 0.0;
        }
        return (($current - $previous) / $previous) * 100;
    }
}