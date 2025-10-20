<?php

namespace App\Http\Controllers;

use App\Models\Production;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class ProductionReportController extends Controller
{
    public function getReportData(Request $request)
    {
        $request->validate([
            'startDate' => 'required|date',
            'endDate' => 'required|date',
        ]);

        $currentPeriodStart = Carbon::parse($request->startDate);
        $currentPeriodEnd = Carbon::parse($request->endDate)->endOfDay();
        $durationInDays = $currentPeriodStart->diffInDays($currentPeriodEnd);
        $previousPeriodStart = $currentPeriodStart->copy()->subDays($durationInDays + 1);
        $previousPeriodEnd = $currentPeriodEnd->copy()->subDays($durationInDays + 1)->endOfDay();

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
        $productions = Production::whereBetween('created_at', [$start, $end])->get();
        $totalProductions = $productions->count();
        if ($totalProductions === 0) {
            return $this->getEmptyMetrics();
        }

        $totalEffectiveTime = 0;
        $totalPausedTime = 0;
        $totalWaitingTime = 0;
        $pauseReasons = [];
        $performanceByStation = [];

        foreach ($productions as $production) {
            if (is_array($production->station_times)) {
                foreach ($production->station_times as $record) {
                    $totalEffectiveTime += $record['times']['effective_seconds'] ?? 0;
                    $totalPausedTime += $record['times']['paused_seconds'] ?? 0;
                    $totalWaitingTime += $record['times']['waiting_seconds'] ?? 0;

                    // Pause Reasons
                    if (is_array($record['pauses'])) {
                        foreach ($record['pauses'] as $pause) {
                            $reason = $pause['reason'] ?? 'Otro';
                            $pauseReasons[$reason] = ($pauseReasons[$reason] ?? 0) + 1;
                        }
                    }

                     // Performance by Station
                    $stationName = $record['station_name'];
                    if (!isset($performanceByStation[$stationName])) {
                        $performanceByStation[$stationName] = ['effective' => 0, 'paused' => 0, 'waiting' => 0];
                    }
                    $performanceByStation[$stationName]['effective'] += $record['times']['effective_seconds'] ?? 0;
                    $performanceByStation[$stationName]['paused'] += $record['times']['paused_seconds'] ?? 0;
                    $performanceByStation[$stationName]['waiting'] += $record['times']['waiting_seconds'] ?? 0;
                }
            }
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
        ];
    }
    
    public function printableReport(Request $request)
    {
        $request->validate([
            'startDate' => 'required|date',
            'endDate' => 'required|date',
        ]);

        $start = Carbon::parse($request->startDate);
        $end = Carbon::parse($request->endDate);

        $reportData = $this->calculateMetricsForPeriod($start, $end);

        // --- Analysis Text Calculation ---
        $analysis = [
            'station_highlight' => null,
            'pause_highlight' => null,
        ];

        // Station with the most total time
        $maxTime = 0;
        $worstStation = null;
        if (!empty($reportData['performance_by_station'])) {
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
        

        // Top pause reason analysis
        if (!empty($reportData['pause_reasons'])) {
            $topReason = array_keys($reportData['pause_reasons'], max($reportData['pause_reasons']))[0];
            $totalPauses = array_sum($reportData['pause_reasons']);
            $topReasonPercentage = ($reportData['pause_reasons'][$topReason] / $totalPauses) * 100;
            $analysis['pause_highlight'] = "**{$topReason}** representó el **" . round($topReasonPercentage) . "%** del tiempo en pausa, siendo el área de mayor oportunidad.";
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