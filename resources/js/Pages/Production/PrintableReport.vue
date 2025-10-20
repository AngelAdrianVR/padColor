<template>
    <PrintableLayout>
        <Head title="Reporte de producción" />
        <div class="p-1 sm:p-2">
            <!-- Botón de Imprimir (se oculta al imprimir) -->
            <div class="mb-3 text-center print:hidden">
                <button @click="printPage"
                    class="bg-indigo-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-300 ease-in-out shadow-lg">
                    <i class="fa-solid fa-print mr-2"></i>
                    Imprimir o Guardar como PDF
                </button>
            </div>

            <!-- Contenido del Reporte -->
            <main class="bg-white p-3 sm:p-10 rounded-xl shadow-2xl printable-area">
                <!-- Encabezado -->
                <header class="flex justify-between items-start pb-3 border-b">
                    <div>
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Reporte de Productividad</h1>
                        <p class="text-sm text-gray-500 mt-1">Del {{ startDate }} al {{ endDate }}</p>
                    </div>
                    <div class="text-right">
                        <figure class="h-10">
                            <img class="h-10" src="/images/logo_name.png" alt="logo">
                        </figure>
                    </div>
                </header>

                <!-- Resumen -->
                <section class="mt-1 avoid-break">
                    <h2 class="text-xl font-semibold text-gray-700 mb-2">Resumen</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2">
                        <div class="bg-gray-50 p-2 rounded-lg">
                            <p class="text-xs text-gray-600">Eficiencia general</p>
                            <p class="text-sm font-bold text-gray-800">{{ reportData.general_efficiency.toFixed(1) }}%
                            </p>
                        </div>
                        <div class="bg-gray-50 p-2 rounded-lg">
                            <p class="text-xs text-gray-600">Órdenes terminadas</p>
                            <p class="text-sm font-bold text-gray-800">{{ reportData.finished_orders }}</p>
                        </div>
                        <div class="bg-gray-50 p-2 rounded-lg">
                            <p class="text-xs text-gray-600">Tiempo total en pausa</p>
                            <p class="text-sm font-bold text-gray-800">{{ formatHours(reportData.time_breakdown.paused)
                                }} hrs</p>
                        </div>
                        <div class="bg-gray-50 p-2 rounded-lg">
                            <p class="text-xs text-gray-600">Principal motivo e pausa</p>
                            <p class="text-sm font-bold text-gray-800 truncate">{{ topPauseReason }}</p>
                        </div>
                    </div>
                </section>

                <!-- Análisis de Rendimiento -->
                <section class="mt-5 avoid-break">
                    <h2 class="text-lg font-semibold text-gray-700 mb-2">Análisis de rendimiento</h2>
                    <div class="border rounded-t-lg p-4">
                        <h3 class="font-semibold text-gray-800 mb-4 text-center">Rendimiento promedio por estación</h3>
                        <div class="relative h-52 w-[75%] mx-auto print:mx-0">
                            <canvas ref="stationsChartCanvas"></canvas>
                        </div>
                    </div>
                    <div v-if="analysis.station_highlight"
                        class="bg-[#F2F2F2] text-[#373737] text-xs p-1 rounded-b-lg text-center"
                        v-html="formatAnalysisText(analysis.station_highlight)">
                    </div>
                </section>

                <!-- Análisis de Causas de Pausa -->
                <section class="mt-5 avoid-break">
                    <h2 class="text-lg font-semibold text-gray-700 mb-2">Análisis de causas de pausa</h2>
                    <div class="border rounded-t-lg p-4">
                        <div class="relative h-52 w-[75%] mx-auto print:mx-0">
                            <canvas ref="barChartCanvas"></canvas>
                        </div>
                    </div>
                    <div v-if="analysis.pause_highlight"
                        class="bg-[#F2F2F2] text-[#373737] text-xs p-1 rounded-b-lg text-center"
                        v-html="formatAnalysisText(analysis.pause_highlight)">
                    </div>
                </section>
                
                <!-- Productions Detail Table -->
                <section class="mt-8 break-inside-avoid-page">
                    <h2 class="text-lg font-semibold text-gray-700 mb-2">Detalle de órdenes de producción</h2>
                    <div class="border rounded-lg overflow-x-auto">
                        <table class="w-full text-xs text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-3 py-2">Folio</th>
                                    <th scope="col" class="px-3 py-2">Producto</th>
                                    <th scope="col" class="px-3 py-2">Cant. Solicitada</th>
                                    <th scope="col" class="px-3 py-2">T. Efectivo</th>
                                    <th scope="col" class="px-3 py-2">T. Pausa</th>
                                    <th scope="col" class="px-3 py-2">T. Espera</th>
                                    <th scope="col" class="px-3 py-2">Creación</th>
                                    <th scope="col" class="px-3 py-2">Término</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="prod in reportData.productions_list" :key="prod.folio" class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-3 py-1 font-medium text-gray-900">{{ prod.folio }}</td>
                                    <td class="px-3 py-1">{{ prod.product_name }}</td>
                                    <td class="px-3 py-1">{{ prod.quantity.toLocaleString('es-MX') }}</td>
                                    <td class="px-3 py-1">{{ formatDuration(prod.total_effective_time) }}</td>
                                    <td class="px-3 py-1">{{ formatDuration(prod.total_paused_time) }}</td>
                                    <td class="px-3 py-1">{{ formatDuration(prod.total_waiting_time) }}</td>
                                    <td class="px-3 py-1">{{ formatSimpleDateTime(prod.created_at) }}</td>
                                    <td class="px-3 py-1">{{ prod.finish_date ? formatSimpleDateTime(prod.finish_date) : '-' }}</td>
                                </tr>
                                <tr v-if="!reportData.productions_list.length">
                                    <td colspan="8" class="text-center py-4">No hay producciones en este rango de fechas.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </main>
        </div>
    </PrintableLayout>
</template>

<script setup>
import PrintableLayout from '@/Layouts/PrintableLayout.vue';
import { ref, onMounted, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import { stations } from '@/Data/stations.js';
import {
    Chart, BarController, BarElement, CategoryScale, LinearScale, Tooltip, Legend
} from 'chart.js';
import { format as formatDate, parseISO } from 'date-fns';

// Register Chart.js components
Chart.register(BarController, BarElement, CategoryScale, LinearScale, Tooltip, Legend);

const props = defineProps({
    reportData: Object,
    analysis: Object,
    startDate: String,
    endDate: String,
});

const barChartCanvas = ref(null);
const stationsChartCanvas = ref(null);
let barChart = null;
let stationsChart = null;

const topPauseReason = computed(() => {
    if (!props.reportData?.pause_reasons || Object.keys(props.reportData.pause_reasons).length === 0) {
        return 'N/A';
    }
    return Object.keys(props.reportData.pause_reasons).reduce((a, b) => props.reportData.pause_reasons[a] > props.reportData.pause_reasons[b] ? a : b);
});

const initCharts = () => {
    // Station Performance Chart
    if (stationsChartCanvas.value && props.reportData) {
        const stationData = props.reportData.performance_by_station;
        const excludedStations = ["Terminadas", "Empaques terminado", "Canceladas"];
        const filteredStationLabels = stations.map(s => s.name).filter(name => !excludedStations.includes(name));

        const stationsCtx = stationsChartCanvas.value.getContext('2d');
        stationsChart = new Chart(stationsCtx, {
            type: 'bar',
            data: {
                labels: filteredStationLabels,
                datasets: [
                    { label: 'Tiempo efectivo promedio (min)', data: filteredStationLabels.map(name => (stationData[name]?.effective ?? 0) / 60), backgroundColor: '#1FAE07' },
                    { label: 'Tiempo en pausa promedio (min)', data: filteredStationLabels.map(name => (stationData[name]?.paused ?? 0) / 60), backgroundColor: '#FBBF24' },
                    { label: 'Tiempo en espera promedio (min)', data: filteredStationLabels.map(name => (stationData[name]?.waiting ?? 0) / 60), backgroundColor: '#B3B3B3' }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } },
                scales: {
                    x: { stacked: true, grid: { display: false } },
                    y: { stacked: true, beginAtZero: true, title: { display: true, text: 'Minutos' } }
                }
            }
        });
    }

    // Pause Reasons Chart
    if (barChartCanvas.value && props.reportData) {
        const reasons = props.reportData.pause_reasons;
        const sortedReasons = Object.entries(reasons).sort(([, a], [, b]) => a - b);
        const barCtx = barChartCanvas.value.getContext('2d');
        barChart = new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: sortedReasons.map(item => item[0]),
                datasets: [{
                    data: sortedReasons.map(item => item[1]),
                    backgroundColor: '#FDBA74',
                    borderColor: '#FB923C',
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });
    }
};

const formatHours = (seconds) => {
    if (seconds === null || isNaN(seconds)) return '0.0';
    return (seconds / 3600).toFixed(1);
};

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

const formatSimpleDateTime = (dateString) => {
    if (!dateString) return '-';
    return formatDate(parseISO(dateString), 'dd/MM/yyyy hh:mm a');
};

const formatAnalysisText = (text) => {
    if (!text) return '';
    return text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
};

const printPage = () => {
    window.print();
};

onMounted(initCharts);
</script>

<style>
@media print {
    .printable-area {
        box-shadow: none !important;
        border: none !important;
    }
    body,
    html {
        background-color: #fff !important;
    }
    .avoid-break {
        page-break-inside: avoid;
    }
}
</style>