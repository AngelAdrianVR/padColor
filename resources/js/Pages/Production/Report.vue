<template>
    <AppLayout title="Reportes de Producción">
        <div class="px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <header class="flex justify-between items-start mb-8">
                <div>
                    <h1 class="text-2xl lg:text-3xl font-bold text-gray-800">Reportes</h1>
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
                 <div v-for="i in 5" :key="i" class="bg-white p-5 rounded-xl shadow animate-pulse">
                    <div class="h-8 w-8 bg-gray-200 rounded-full mb-4"></div>
                    <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
                    <div class="h-8 bg-gray-200 rounded w-1/2 mb-4"></div>
                    <div class="h-3 bg-gray-200 rounded w-full"></div>
                </div>
            </div>
            <div v-else-if="reportData" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5">
                <!-- Stat Cards -->
                <StatCard icon-path="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" title="Promedio de tiempo efectivo" :current-value="formatDuration(reportData.current_period.avg_effective_time)" :prev-value="formatDuration(reportData.previous_period.avg_effective_time)" :percentage="reportData.comparison.effective_time" :invert-colors="true" />
                <StatCard icon-path="M14.25 9v6m-4.5 0V9M21 12a9 9 0 11-18 0 9 9 0 0118 0z" title="Promedio de tiempo en pausa" :current-value="formatDuration(reportData.current_period.avg_paused_time)" :prev-value="formatDuration(reportData.previous_period.avg_paused_time)" :percentage="reportData.comparison.paused_time" />
                <StatCard icon-path="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.852l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0z" title="Promedio de tiempo en espera" :current-value="formatDuration(reportData.current_period.avg_waiting_time)" :prev-value="formatDuration(reportData.previous_period.avg_waiting_time)" :percentage="reportData.comparison.waiting_time" />
                <StatCard icon-path="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" title="Órdenes terminadas" :current-value="reportData.current_period.finished_orders" :prev-value="reportData.previous_period.finished_orders" :percentage="reportData.comparison.finished_orders" :invert-colors="true" />
                <StatCard icon-path="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 1.5m1-1.5l1 1.5m0 0l.5 1.5m-2-3l2 3m4.5-3l2 3m-2-3l.5 1.5m-5-3l.5 1.5" title="Eficiencia general" :current-value="`${reportData.current_period.general_efficiency.toFixed(1)}%`" :prev-value="`${reportData.previous_period.general_efficiency.toFixed(1)}%`" :percentage="reportData.comparison.general_efficiency" :invert-colors="true" />
            </div>

            <!-- Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mt-8">
                <div class="lg:col-span-2 bg-white p-5 rounded-xl shadow">
                    <h3 class="font-semibold text-gray-800 mb-4">Principales motivos de pausa</h3>
                    <div class="relative h-80">
                        <canvas ref="barChartCanvas"></canvas>
                    </div>
                </div>
                <div class="bg-white p-5 rounded-xl shadow">
                     <h3 class="font-semibold text-gray-800 mb-4">Desglose de tiempo de producción</h3>
                    <div class="relative h-80">
                        <canvas ref="doughnutChartCanvas"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import StatCard from './Partials/StatCard.vue';
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
let barChart = null;
let doughnutChart = null;

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
            backgroundColor: '#FDBA74',
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
            backgroundColor: ['#22C55E', '#F97316', '#A1A1AA'],
        }];
        doughnutChart.update();
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
    // Logic to open a new window/tab for printing will be added later
    alert("La funcionalidad para generar el reporte impreso se implementará en el siguiente paso.");
};

// --- LIFECYCLE ---
onMounted(() => {
    fetchReportData();
    initCharts();
});

watch(reportData, updateCharts);
</script>