<template>
    <AppLayout title="Reportes de Producción">
        <div class="px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <header class="flex justify-between items-start mb-8">
                <div>
                    <h1 class="text-xl lg:text-2xl font-bold text-gray-800">Reportes</h1>
                    <p class="mt-1 text-sm text-gray-500">Análisis de rendimiento y eficiencia en tiempo real.</p>
                </div>
                <div class="flex items-center space-x-2">
                    <el-date-picker
                        v-model="dateRange"
                        type="daterange"
                        range-separator="A"
                        start-placeholder="Fecha de inicio"
                        end-placeholder="Fecha de fin"
                        format="DD/MM/YYYY"
                        value-format="YYYY-MM-DD"
                        @change="fetchReportData"
                    />
                    <PrimaryButton @click="generatePrintableView">
                        <i class="fa-solid fa-print mr-2"></i>
                        Generar reporte
                    </PrimaryButton>
                </div>
            </header>

            <!-- Stat Panels -->
            <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5">
                 <div v-for="i in 5" :key="i" class="bg-white p-5 rounded-xl border border-grayD9 animate-pulse">
                    <div class="h-8 w-8 bg-gray-200 rounded-full mb-4"></div>
                    <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
                    <div class="h-8 bg-gray-200 rounded w-1/2 mb-4"></div>
                    <div class="h-3 bg-gray-200 rounded w-full"></div>
                </div>
            </div>
            <div v-else-if="reportData" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5">
                <!-- Stat Cards -->
                <StatCard 
                    :icon="PlayCircleIcon" 
                    title="Promedio de tiempo efectivo" 
                    :current-value="formatDuration(reportData.current_period.avg_effective_time)" 
                    :prev-value="formatDuration(reportData.previous_period.avg_effective_time)" 
                    :percentage="reportData.comparison.effective_time" 
                />
                <StatCard 
                    :icon="PauseCircleIcon" 
                    title="Promedio de tiempo en pausa" 
                    :current-value="formatDuration(reportData.current_period.avg_paused_time)" 
                    :prev-value="formatDuration(reportData.previous_period.avg_paused_time)" 
                    :percentage="reportData.comparison.paused_time" 
                    invert-colors 
                />
                <StatCard 
                    :icon="ClockIcon" 
                    title="Promedio de tiempo en espera" 
                    :current-value="formatDuration(reportData.current_period.avg_waiting_time)" 
                    :prev-value="formatDuration(reportData.previous_period.avg_waiting_time)" 
                    :percentage="reportData.comparison.waiting_time" 
                    invert-colors 
                />
                <StatCard 
                    :icon="CheckBadgeIcon" 
                    title="Órdenes terminadas" 
                    :current-value="reportData.current_period.finished_orders" 
                    :prev-value="reportData.previous_period.finished_orders" 
                    :percentage="reportData.comparison.finished_orders" 
                />
                <StatCard 
                    :icon="ChartBarIcon" 
                    title="Eficiencia general" 
                    :current-value="`${reportData.current_period.general_efficiency.toFixed(1)}%`" 
                    :prev-value="`${reportData.previous_period.general_efficiency.toFixed(1)}%`" 
                    :percentage="reportData.comparison.general_efficiency" 
                />
            </div>

            <!-- Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mt-8">
                <div class="lg:col-span-2 bg-white p-5 rounded-xl border border-grayD9">
                    <h3 class="font-semibold text-gray-800 mb-4">Principales motivos de pausa</h3>
                    <div class="relative h-80">
                        <canvas ref="barChartCanvas"></canvas>
                    </div>
                </div>
                <div class="bg-white p-5 rounded-xl border border-grayD9">
                     <h3 class="font-semibold text-gray-800 mb-4">Desglose de tiempo de producción</h3>
                    <div class="relative h-80">
                        <canvas ref="doughnutChartCanvas"></canvas>
                    </div>
                </div>
            </div>

            <!-- New Station Performance Chart -->
            <div class="bg-white p-5 rounded-xl border border-grayD9 mt-8">
                <h3 class="font-semibold text-gray-800 mb-4">Rendimiento promedio por estación</h3>
                <div class="relative h-96">
                    <canvas ref="stationsChartCanvas"></canvas>
                </div>
            </div>

        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import StatCard from './Partials/StatCard.vue';
import { stations } from '@/Data/stations.js';
import { ref, onMounted, watch } from 'vue';
import { subDays, format as formatDate } from 'date-fns';
import {
  Chart,
  BarController,
  BarElement,
  CategoryScale,
  LinearScale,
  DoughnutController,
  ArcElement,
  Tooltip,
  Legend
} from 'chart.js';
import { 
    PlayCircleIcon, 
    PauseCircleIcon, 
    ClockIcon, 
    CheckBadgeIcon, 
    ChartBarIcon 
} from '@heroicons/vue/24/outline';


// Register Chart.js components
Chart.register(
  BarController,
  BarElement,
  CategoryScale,
  LinearScale,
  DoughnutController,
  ArcElement,
  Tooltip,
  Legend
);

// --- STATE ---
const dateRange = ref([
    formatDate(subDays(new Date(), 29), 'yyyy-MM-dd'),
    formatDate(new Date(), 'yyyy-MM-dd')
]);
const reportData = ref(null);
const loading = ref(true);
const barChartCanvas = ref(null);
const doughnutChartCanvas = ref(null);
const stationsChartCanvas = ref(null);
let barChart = null;
let doughnutChart = null;
let stationsChart = null;

// --- API & DATA ---
const fetchReportData = async () => {
    if (!dateRange.value) return;
    loading.value = true;
    try {
        const response = await axios.get(route('productions.get-report-data', {
            startDate: dateRange.value[0],
            endDate: dateRange.value[1],
        }));
        reportData.value = response.data;
    } catch (error) {
        console.error("Error fetching report data:", error);
    } finally {
        loading.value = false;
    }
};

// --- CHARTS ---
const initCharts = () => {
    // Pause Reasons Bar Chart
    if (barChartCanvas.value) {
        const barCtx = barChartCanvas.value.getContext('2d');
        barChart = new Chart(barCtx, {
            type: 'bar',
            data: { labels: [], datasets: [] },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true } }
            }
        });
    }

    // Time Breakdown Doughnut Chart
    if (doughnutChartCanvas.value) {
        const doughnutCtx = doughnutChartCanvas.value.getContext('2d');
        doughnutChart = new Chart(doughnutCtx, {
            type: 'doughnut',
            data: { labels: [], datasets: [] },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, boxWidth: 8 }
                    }
                }
            }
        });
    }

    // New Station Performance Chart
    if (stationsChartCanvas.value) {
        const stationsCtx = stationsChartCanvas.value.getContext('2d');
        stationsChart = new Chart(stationsCtx, {
            type: 'bar',
            data: { labels: [], datasets: [] },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    x: { stacked: true },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Minutos'
                        }
                     }
                }
            }
        });
    }
};

const updateCharts = () => {
    if (!reportData.value) return;

    // Update Bar Chart
    if (barChart) {
        const reasons = reportData.value.current_period.pause_reasons;
        const sortedReasons = Object.entries(reasons).sort(([, a], [, b]) => b - a);
        barChart.data.labels = sortedReasons.map(item => item[0]);
        barChart.data.datasets = [{
            label: 'Número de Pausas',
            data: sortedReasons.map(item => item[1]),
            backgroundColor: '#FBBF24',
            borderColor: '#FB923C',
            borderWidth: 1
        }];
        barChart.update();
    }

    // Update Doughnut Chart
    if (doughnutChart) {
        const times = reportData.value.current_period.time_breakdown;
        doughnutChart.data.labels = ['Tiempo efectivo', 'Tiempo en pausa', 'Tiempo en espera'];
        doughnutChart.data.datasets = [{
            data: [times.effective, times.paused, times.waiting],
            backgroundColor: ['#1FAE07', '#FBBF24', '#B3B3B3'],
        }];
        doughnutChart.update();
    }

    // Update Station Performance Chart
    if (stationsChart) {
        const stationData = reportData.value.current_period.performance_by_station;
        const excludedStations = ["Terminadas", "Empaques terminado", "Canceladas"];
        
        const filteredStationLabels = stations
            .map(s => s.name)
            .filter(name => !excludedStations.includes(name));

        stationsChart.data.labels = filteredStationLabels;
        stationsChart.data.datasets = [
            {
                label: 'Tiempo efectivo promedio (min)',
                data: filteredStationLabels.map(name => (stationData[name]?.effective ?? 0) / 60),
                backgroundColor: '#1FAE07',
            },
            {
                label: 'Tiempo en pausa promedio (min)',
                data: filteredStationLabels.map(name => (stationData[name]?.paused ?? 0) / 60),
                backgroundColor: '#FBBF24',
            },
            {
                label: 'Tiempo en espera promedio (min)',
                data: filteredStationLabels.map(name => (stationData[name]?.waiting ?? 0) / 60),
                backgroundColor: '#B3B3B3',
            }
        ];
        stationsChart.update();
    }
};

// --- HELPERS ---
const formatDuration = (totalSeconds) => {
    if (totalSeconds === null || isNaN(totalSeconds) || totalSeconds < 0) return '0d 00:00:00';
    const days = Math.floor(totalSeconds / 86400); totalSeconds %= 86400;
    const hours = Math.floor(totalSeconds / 3600); totalSeconds %= 3600;
    const minutes = Math.floor(totalSeconds / 60);
    const seconds = Math.floor(totalSeconds % 60);
    let result = '';
    if (days > 0) result += `${days}d `;
    result += `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    return result;
};

const generatePrintableView = () => {
    if (!dateRange.value) return;
    
    const url = route('productions.report.printable', { 
        startDate: dateRange.value[0], 
        endDate: dateRange.value[1] 
    });
    window.open(url, '_blank');
};

// --- LIFECYCLE ---
onMounted(() => {
    fetchReportData();
    initCharts();
});

watch(reportData, updateCharts);
</script>