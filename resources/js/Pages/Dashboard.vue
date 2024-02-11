<template>
    <AppLayout title="Inicio">
        <h1 class="font-bold lg:mx-32 mt-4">Inicio</h1>
        <section class="lg:mx-14 mt-6 lg:grid grid-cols-2 gap-8">
            <PolarAreaChart :options="prioritiesChartOptions" title="Estado de Prioridades"
                icon='<i class="fa-solid fa-stopwatch ml-2"></i>' />
            <PieChart :options="ticketsStatusChartOptions" title="Estado de los tickets"
                icon='<i class="fa-solid fa-circle-nodes ml-2"></i>' />
        </section>
    </AppLayout>
</template>
<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import PolarAreaChart from '@/Components/MyComponents/Charts/PolarAreaChart.vue';
import PieChart from '@/Components/MyComponents/Charts/PieChart.vue';

export default {
    data() {
        return {
            ticketsStatusChartOptions: {
                colors: ['#0355B5', '#FD8827', '#B927FD', '#3F9C30', '#FD4646', '#3D3D3D'],
                labels: ['Abierto', 'En espera', 'En espera de 3ro', 'Completado', 'Re-abierto', 'En proceso'],
                series: [3, 5, 7, 2, 1, 3]//this.projectGroupCounts(),
            },
            prioritiesChartOptions: {
                colors: ['#A49C9D', '#D68D1F', '#C1202A'],
                labels: ['Baja', 'Media', 'Alta'],
                series: [3, 4, 5]//this.totalTasksByPriority(),
            },
        }
    },
    components: {
        AppLayout,
        PolarAreaChart,
        PieChart,
    }
}
</script>
