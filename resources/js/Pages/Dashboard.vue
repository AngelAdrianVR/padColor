<template>
    <AppLayout title="Inicio">
        <h1 class="font-bold mx-4 lg:mx-32 mt-4">Inicio</h1>
        <section class="mx-2 lg:mx-14 mt-6 grid grid-cols-1 lg:grid-cols-4 md:grid-cols-3 gap-1 lg:gap-5">
            <SimpleKPI v-for="(item, index) in simpleKpis" :key="index" :title="item.title" :icon="item.icon"
                :value="item.value" />
        </section>
        <section class="mx-2 lg:mx-14 mt-6 grid-cols-1 grid lg:grid-cols-2 gap-1 lg:gap-8 mb-6">
            <PieChart :options="ticketsByBranchOptions" title="Tickets por sucursal"
                icon='<i class="fa-solid fa-stopwatch ml-2"></i>' />
            <PieChart :options="ticketsStatusChartOptions" title="Estado de los tickets"
                icon='<i class="fa-solid fa-circle-nodes ml-2"></i>' />
            <PieChart :options="ticketsByCategoriesOptions" title="Tickets por categorías"
                icon='<i class="fa-solid fa-circle-nodes ml-2"></i>' />
            <PolarAreaChart :options="prioritiesChartOptions" title="Estado de Prioridades"
                icon='<i class="fa-solid fa-stopwatch ml-2"></i>' />
        </section>
    </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import PolarAreaChart from '@/Components/MyComponents/Charts/PolarAreaChart.vue';
import SimpleKPI from '@/Components/MyComponents/Dashboard/SimpleKPI.vue';
import PieChart from '@/Components/MyComponents/Charts/PieChart.vue';

export default {
    data() {
        return {
            branches: [
                'Alfajayucan',
                'Morelia',
                'San Luis Potosí',
                'Acapulco',
                'Av. del Tigre',
                'Calle C',
                'Calle 2',
                'Veracruz',
                'León',
                'Juárez',
                'Puebla',
                'Monterrey',
                'Federalismo',
            ],
            ticketsStatusChartOptions: {
                colors: ['#0355B5', '#FD8827', '#B927FD', '#3F9C30', '#FD4646', '#3D3D3D'],
                labels: ["Abierto", "En espera", "En espera de 3ro", "Completado", "Re-abierto", "En proceso"],
                series: this.ticketsByStatus(),
            },
            prioritiesChartOptions: {
                colors: ['#0355B5', '#FD8827', '#B927FD', '#3F9C30', '#FD4646', '#3D3D3D'],
                labels: ['Baja', 'Media', 'Alta'],
                series: this.ticketsByPriority(),
            },
            ticketsByBranchOptions: {
                colors: ['#0355B5', '#FD8827', '#B927FD', '#3F9C30', '#FD4646', '#3D3D3D'],
                labels: [
                    'Alfajayucan',
                    'Morelia',
                    'San Luis Potosí',
                    'Acapulco',
                    'Av. del Tigre',
                    'Calle C',
                    'Calle 2',
                    'Veracruz',
                    'León',
                    'Juárez',
                    'Puebla',
                    'Monterrey',
                    'Federalismo',
                ],
                series: this.ticketsByBranch(),
            },
            ticketsByCategoriesOptions: {
                colors: ['#0355B5', '#FD8827', '#B927FD', '#3F9C30', '#FD4646', '#3D3D3D'],
                labels: this.ticketsByCategory().map(item => item[0]),
                series: this.ticketsByCategory().map(item => item[1]),
            },

            // kpis simples
            simpleKpis: [
                {
                    title: "Tiempo promedio de respuesta prioridad baja",
                    icon: "fa-solid fa-snowflake",
                    value: this.calculateAverageResponseTimeByPriority('Baja'),
                },
                {
                    title: "Tiempo promedio de respuesta prioridad media",
                    icon: "fa-solid fa-droplet",
                    value: this.calculateAverageResponseTimeByPriority('Media'),
                },
                {
                    title: "Tiempo promedio de respuesta prioridad alta",
                    icon: "fa-solid fa-fire-flame-curved",
                    value: this.calculateAverageResponseTimeByPriority('Alta'),
                },
                {
                    title: "Índice de cumplimiento de fechas de expiración",
                    icon: "fa-regular fa-clock",
                    value: this.calculatePercentageResolvedBeforeExpiration(),
                },
            ]
        }
    },
    props: {
        tickets: Array,
        categories: Array,
    },
    components: {
        AppLayout,
        PolarAreaChart,
        PieChart,
        SimpleKPI,
    },
    methods: {
        ticketsByStatus() {
            // Inicializar un objeto para almacenar el recuento de tickets por estado
            const statuses = ["Abierto", "En espera", "En espera de 3ro", "Completado", "Re-abierto", "En proceso"];
            const byStatus = {};
            statuses.forEach(status => {
                byStatus[status] = 0;
            });

            // Contar los tickets por estado
            this.tickets.forEach(ticket => {
                if (byStatus.hasOwnProperty(ticket.status)) {
                    byStatus[ticket.status]++;
                }
            });

            // Convertir el objeto a un arreglo de recuento
            const result = statuses.map(status => byStatus[status]);

            return result;
        },
        ticketsByCategory() {
            const categories = {};

            this.categories.forEach(category => {
                categories[category.name] = 0;
            });

            this.tickets.forEach(ticket => {
                const categoryName = ticket.category.name;

                if (categories.hasOwnProperty(categoryName)) {
                    categories[categoryName]++;
                }
            });

            const res = Object.entries(categories).map(([item, quantity]) => [item, quantity]);
            return res;
        },
        ticketsByBranch() {
            const branches = [
                'Alfajayucan',
                'Morelia',
                'San Luis Potosí',
                'Acapulco',
                'Av. del Tigre',
                'Calle C',
                'Calle 2',
                'Veracruz',
                'León',
                'Juárez',
                'Puebla',
                'Monterrey',
                'Federalismo',
            ];
            // Inicializar un objeto para almacenar el recuento de tickets por estado
            const byBranch = {};
            branches.forEach(branch => {
                byBranch[branch] = 0;
            });

            // Contar los tickets por estado
            this.tickets.forEach(ticket => {
                if (byBranch.hasOwnProperty(ticket.branch)) {
                    byBranch[ticket.branch]++;
                }
            });

            // Convertir el objeto a un arreglo de recuento
            const result = branches.map(branch => byBranch[branch]);

            return result;
        },
        ticketsByPriority() {
            // Inicializar un objeto para almacenar el recuento de tickets por estado
            const priorities = ["Baja", "Media", "Alta"];
            const byStatus = {};
            priorities.forEach(priority => {
                byStatus[priority] = 0;
            });

            // Contar los tickets por estado
            this.tickets.forEach(ticket => {
                if (byStatus.hasOwnProperty(ticket.priority)) {
                    byStatus[ticket.priority]++;
                }
            });

            // Convertir el objeto a un arreglo de recuento
            const result = priorities.map(priority => byStatus[priority]);

            return result;
        },
        calculateAverageResponseTimeByPriority(priority) {
            const responseTimes = this.tickets
                .filter(ticket => ticket.priority === priority)
                .map(ticket => {
                    // Check if there are solutions for the ticket
                    if (ticket.ticket_solutions && ticket.ticket_solutions.length > 0) {
                        // Calculate response time in milliseconds
                        const responseTimeMillis = new Date(ticket.ticket_solutions[0].created_at) - new Date(ticket.created_at);

                        // Convert to hours
                        const responseTimeHours = responseTimeMillis / (1000 * 60 * 60);

                        return responseTimeHours;
                    } else {
                        // If there is no solution, return null or a value indicating no response time
                        return null;
                    }
                });

            // Filter out non-null response times
            const validResponseTimes = responseTimes.filter(time => time !== null);

            // Calculate the average response time
            if (validResponseTimes.length > 0) {
                const averageResponseTime = validResponseTimes.reduce((total, time) => total + time, 0) / validResponseTimes.length;
                return averageResponseTime.toFixed(2) + ' horas';
            } else {
                return 'Sin información'; // Another way to handle the case when there are no valid response times
            }
        },
        calculatePercentageResolvedBeforeExpiration() {
            const resolvedBeforeExpiration = this.tickets.filter(ticket => {
                // Check if there are solutions for the ticket and an expiration date
                if (ticket.ticket_solutions && ticket.ticket_solutions.length > 0 && ticket.expired_date) {
                    // Compare the solution date with the expiration date
                    const solutionDate = new Date(ticket.ticket_solutions[0].created_at);
                    const expirationDate = new Date(ticket.expired_date);

                    return solutionDate < expirationDate;
                } else {
                    return false;
                }
            });

            // Calculate the percentage
            const percentage = (resolvedBeforeExpiration.length / this.tickets.filter(item => item.ticket_solutions.length).length) * 100;

            if (percentage)
                return percentage + '%'; // Return the percentage or 0 if there are no tickets
            else
                return 'Sin información';
        },
    }
}
</script>
