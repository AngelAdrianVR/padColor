<template>
    <div>

        <Head :title="'Reporte de tickets ' + formatDate(startDate) + ' al ' + formatDate(endDate)" />
        <header>
            <figure class="h-12">
                <img class="h-12" src="@/../../public/images/logo_name.png" alt="logo">
            </figure>
        </header>
        <main class="text-xs">
            <section class="text-center">
                <h1 class="font-bold">Reporte</h1>
                <p class="mt-5"><b>Fecha: </b> Del {{ formatDate(startDate) }} al {{ formatDate(endDate) }}</p>
            </section>
            <section class="mx-12 mt-5 flex justify-between">
                <article class="w-[45%]">
                    <h2 class="text-primary font-bold text-xs mb-2">Sucursales con más tickets</h2>
                    <div v-for="item in getTop3Branches" :key="item.branch" class="mb-1">
                        <p class="font-bold">{{ item.branch }}</p>
                        <div class="flex items-center space-x-1 space-y-0">
                            <div class="h-px w-[85%] bg-gray99 relative">
                                <div class="h-[3px] bg-primary absolute left-0 bottom-0"
                                    :style="{ width: item.percentage + '%' }"></div>
                            </div>
                            <span class="text-[8px] text-[#747474] leading-none">{{ item.percentage }}% ({{ item.tickets
                                }}/{{ tickets.length }})</span>
                        </div>
                        <p class="text-[9px] text-[#747474] leading-none">{{ companies[item.branch] }}</p>
                    </div>
                </article>
                <article class="w-[45%]">
                    <h2 class="text-primary font-bold text-xs mb-1">Categoría con más recurrencia</h2>
                    <div v-for="item in getTop3Categories" :key="item.category" class="mb-2">
                        <p class="font-bold">{{ item.category }}</p>
                        <div class="flex items-center space-x-1">
                            <div class="h-px w-[85%] bg-gray99 relative">
                                <div class="h-[3px] bg-primary absolute left-0 bottom-0"
                                    :style="{ width: item.percentage + '%' }"></div>
                            </div>
                            <span class="text-[8px] text-[#747474] leading-none">{{ item.percentage }}% ({{ item.tickets
                                }}/{{ tickets.length }})</span>
                        </div>
                    </div>
                </article>
            </section>
            <section class="mx-2">
                <div id="chart" class="flex items-center justify-around">
                    <article>
                        <h1 class="text-primary font-bold text-xs text-center">PDC</h1>
                        <apexchart type="pie" width="340" :options="chartOptions"
                            :series="ticketCountsArray['Padcolor']">
                        </apexchart>
                    </article>
                    <article>
                        <h1 class="text-primary font-bold text-xs text-center">PIG</h1>
                        <apexchart type="pie" width="340" :options="chartOptions" :series="ticketCountsArray['Papel']">
                        </apexchart>
                    </article>
                    <article>
                        <h1 class="text-primary font-bold text-xs text-center">General</h1>
                        <apexchart type="pie" width="340" :options="chartOptions" :series="ticketCountsArray['General']">
                        </apexchart>
                    </article>
                </div>
            </section>
            <section class="mx-12 grid grid-cols-6 gap-x-2">
                <h2 class="text-primary font-bold text-sm text-center mb-3 col-span-full">Estado de los tickets</h2>
                <article v-for="item in statuses" :key="item.label" class="rounded-[5px] px-2 py-[5px] text-[10px]"
                    :style="{ color: item.color, backgroundColor: item.bg }">
                    <header class="flex items-center justify-between">
                        <p class="flex items-center space-x-1">
                            <span>{{ getTicketsCountByStatus(item.label) }}</span>
                            <i :class="item.icon"></i>
                        </p>
                        <span>{{ getTicketsPercentageByStatus(item.label) }}%</span>
                    </header>
                    <p class="mt-1 text-[9px]">{{ item.label }}</p>
                </article>
            </section>
            <section class="mx-12 mt-5 grid grid-cols-4 gap-x-3">
                <article v-for="(item, index) in simpleKpis" :key="index"
                    class="border border-grayD9 rounded-[10px] px-2 py-1 text-[10px]">
                    <section class="flex space-x-1">
                        <span class="flex items-center justify-center text-gray66 bg-grayED size-6 rounded-lg">
                            <i :class="item.icon"></i>
                        </span>
                        <div class="flex flex-col w-4/5">
                            <h2 class="text-gray66 leading-tight">{{ item.title }}</h2>
                            <span class="text-xs font-semibold">{{ item.value }}</span>
                        </div>
                    </section>
                </article>
            </section>
            <section class="mx-5 mt-5">
                <table class="w-full">
                    <thead>
                        <tr class="*:bg-primary *:text-white *:border *:border-grayD9 *:text-start *:px-1 *:text-[9px]">
                            <th>ID</th>
                            <th>Tipo de servicio</th>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Fecha de creación</th>
                            <th>Fecha de cierre</th>
                            <th>Estado</th>
                            <th>T. de solución</th>
                            <th>Prioridad</th>
                            <th>Responsable</th>
                            <th>Resoluciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in tickets" :key="item.id"
                            class="*:border *:border-grayD9 *:text-start *:px-1 *:text-[9px]">
                            <td>{{ getFolio(item) }}</td>
                            <td>{{ item.ticket_type }}</td>
                            <td>{{ item.title }}</td>
                            <td class="w-[12%]">{{ item.category.name }}</td>
                            <td class="w-[12%]">{{ formatDateTime(item.created_at) }}</td>
                            <td class="w-[12%]">{{ item.closed_at ? formatDateTime(item.closed_at) : '-' }}</td>
                            <td>{{ item.status }}</td>
                            <td class="w-[12%]">{{ item.solution_minutes ? convertMinutesToHours(item.solution_minutes)
                                : '-' }}</td>
                            <td>{{ item.priority }}</td>
                            <td>{{ item.responsible?.name ?? '-' }}</td>
                            <td class="w-[1%]">{{ item.ticket_solutions.length }}</td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</template>
<script>
import SimpleKPI from '@/Components/MyComponents/Dashboard/SimpleKPI.vue';
import { Head } from "@inertiajs/vue3";
import { format, parseISO } from 'date-fns';
import es from 'date-fns/locale/es';

export default {
    data() {
        return {
            chartOptions: {
                chart: {
                    width: 340,
                    type: 'pie',
                },
                colors: ['#25346D', '#9a9a9a'],
                labels: ['Incidencias', 'Solicitudes'],
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: 320
                        },
                        legend: {
                            position: 'right'
                        }
                    }
                }]
            },
            statuses: [
                {
                    label: 'Abierto',
                    color: '#0355B5',
                    bg: '#E6F1FF',
                    icon: 'fa-solid fa-arrow-up',
                },
                {
                    label: 'En espera',
                    color: '#FD8827',
                    bg: '#FFF1E6',
                    icon: 'fa-regular fa-clock',
                },
                {
                    label: 'En espera de 3ro',
                    color: '#B927FD',
                    bg: '#F5E1FF',
                    icon: 'fa-regular fa-hourglass-half',
                },
                {
                    label: 'Completado',
                    color: '#3F9C30',
                    bg: '#EBFCE8',
                    icon: 'fa-solid fa-check',
                },
                {
                    label: 'Re-abierto',
                    color: '#FD4646',
                    bg: '#FFE6E6',
                    icon: 'fa-solid fa-rotate-right',
                },
                {
                    label: 'En proceso',
                    color: '#3D3D3D',
                    bg: '#F0F0F0',
                    icon: 'fa-solid fa-arrow-right-to-bracket',
                },
            ],
            companies: {
                'Alfajayucan': 'Papel, diseño y color',
                'Morelia': 'Papel, diseño y color',
                'San Luis Potosí': 'Papel, diseño y color',
                'Acapulco': 'Papel, diseño y color',
                'Av. del Tigre': 'Papel, diseño y color',
                'Calle C': 'Papel, diseño y color',
                'Calle 2': 'Papel, diseño y color',
                'Veracruz': 'Padcolor insumos gráficos',
                'León': 'Padcolor insumos gráficos',
                'Juárez': 'Padcolor insumos gráficos',
                'Puebla': 'Padcolor insumos gráficos',
                'Monterrey': 'Padcolor insumos gráficos',
                'Federalismo': 'Padcolor insumos gráficos',
                'General': 'General',
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
    components: {
        Head,
        SimpleKPI,
    },
    props: {
        tickets: Array,
        startDate: String,
        endDate: String,
    },
    computed: {
        ticketsByCompany() {
            const ticketsPadcolor = this.tickets.filter(ticket => this.companies[ticket.branch] === 'Padcolor insumos gráficos');
            const ticketsPapel = this.tickets.filter(ticket => this.companies[ticket.branch] === 'Papel, diseño y color');
            const ticketsGeneral = this.tickets.filter(ticket => this.companies[ticket.branch] === 'General');

            const countByTypePadcolor = this.countTicketsByType(ticketsPadcolor);
            const countByTypePapel = this.countTicketsByType(ticketsPapel);
            const countByTypeGeneral = this.countTicketsByType(ticketsGeneral);

            return { Padcolor: countByTypePadcolor, Papel: countByTypePapel, General: countByTypeGeneral };
        },
        ticketCountsArray() {
            return {
                Padcolor: Object.values(this.ticketsByCompany.Padcolor),
                Papel: Object.values(this.ticketsByCompany.Papel),
                General: Object.values(this.ticketsByCompany.General)
            };
        },
        getTop3Categories() {
            const categoriesTicketsMap = {};

            // Contar el número de tickets por categoría
            this.tickets.forEach(ticket => {
                const category = ticket.category.name;

                if (!categoriesTicketsMap[category]) {
                    categoriesTicketsMap[category] = 0;
                }
                categoriesTicketsMap[category]++;
            });

            // Convertir el mapa en un array de objetos { category, tickets }
            const categoriesWithTickets = Object.keys(categoriesTicketsMap).map(category => ({
                category,
                tickets: categoriesTicketsMap[category]
            }));

            // Ordenar el array por número de tickets de mayor a menor
            categoriesWithTickets.sort((a, b) => b.tickets - a.tickets);

            // Calcular el total de tickets
            const totalTickets = this.tickets.length;

            // Obtener las tres categorías con más tickets y calcular el porcentaje
            const top3categories = categoriesWithTickets.slice(0, 3).map(category => ({
                ...category,
                percentage: Math.round((category.tickets / totalTickets) * 100)
            }));

            return top3categories;
        },
        getTop3Branches() {
            const branchTicketsMap = {};

            // Contar el número de tickets por sucursal
            this.tickets.forEach(ticket => {
                if (!branchTicketsMap[ticket.branch]) {
                    branchTicketsMap[ticket.branch] = 0;
                }
                branchTicketsMap[ticket.branch]++;
            });

            // Convertir el mapa en un array de objetos { branch, tickets }
            const branchesWithTickets = Object.keys(branchTicketsMap).map(branch => ({
                branch,
                tickets: branchTicketsMap[branch]
            }));

            // Ordenar el array por número de tickets de mayor a menor
            branchesWithTickets.sort((a, b) => b.tickets - a.tickets);

            // Calcular el total de tickets
            const totalTickets = this.tickets.length;

            // Obtener las tres sucursales con más tickets y calcular el porcentaje
            const top3Branches = branchesWithTickets.slice(0, 3).map(branch => ({
                ...branch,
                percentage: Math.round((branch.tickets / totalTickets) * 100)
            }));

            return top3Branches;
        },
    },
    methods: {
        countTicketsByType(tickets) {
            const ticketCounts = tickets.reduce((acc, ticket) => {
                acc[ticket.ticket_type] = (acc[ticket.ticket_type] || 0) + 1;
                return acc;
            }, {});

            // Mover el tipo "Soporte o incidencia" al principio del objeto
            const sortedTicketCounts = {};
            sortedTicketCounts['Soporte o incidencia'] = ticketCounts['Soporte o incidencia'] || 0;
            sortedTicketCounts['Solicitud o servicio'] = ticketCounts['Solicitud o servicio'] || 0;

            return sortedTicketCounts;
        },
        getTicketsCountByStatus(status) {
            return this.tickets.filter(item => item.status == status).length;
        },
        getTicketsPercentageByStatus(status) {
            return Math.round((this.getTicketsCountByStatus(status) / this.tickets.length) * 100);
        },
        getFolio(ticket) {
            if (ticket.ticket_type == 'Soporte o incidencia') {
                return 'GPCI' + String(ticket.id).padStart(6, '0');
            } else {
                return 'GPCS' + String(ticket.id).padStart(6, '0');
            }
        },
        formatDate(dateString) {
            return format(parseISO(dateString), 'dd MMMM yyyy', { locale: es });
        },
        formatDateTime(dateTime) {
            return format(parseISO(dateTime), 'dd MMM yy, hh:mm a', { locale: es });
        },
        calculateAverageResponseTimeByPriority(priority) {
            const responseTimes = this.tickets
                .filter(ticket => ticket.priority === priority)
                .map(ticket => {
                    // Check if there are solutions for the ticket
                    if (ticket.solution_minutes) {
                        const responseTimeMinutes = ticket.solution_minutes;

                        // Convertir a horas
                        const responseTimeHours = responseTimeMinutes / 60;

                        return responseTimeHours;
                    } else {
                        // Si no hay solución, devolver null o un valor que indique que no hay tiempo de respuesta
                        return null;
                    }
                });

            // Filtrar los tiempos de respuesta que no son nulos
            const validResponseTimes = responseTimes.filter(time => time !== null);

            // Calcular el tiempo promedio de respuesta
            if (validResponseTimes.length > 0) {
                // Calcular el total de horas
                const averageResponseTime = validResponseTimes.reduce((total, time) => total + time, 0) / validResponseTimes.length;

                // Calcular días y horas
                const days = Math.floor(averageResponseTime / 24);
                const hours = Math.floor(averageResponseTime % 24);
                const minutes = Math.floor((averageResponseTime % 1) * 60);

                // Formatear el resultado
                let result = '';
                if (days > 0) {
                    result += `${days} día${days > 1 ? 's' : ''} `;
                }
                result += `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;

                return result;
            } else {
                return 'Sin información';
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
        convertMinutesToHours(minutes) {
            // Calcular días, horas y minutos
            const days = Math.floor(minutes / (60 * 24));
            const horas = Math.floor((minutes % (60 * 24)) / 60);
            const remainingMinutes = minutes % 60;

            // Construir la cadena de resultado
            let result = '';
            if (days > 0) {
                result += `${days} día${days > 1 ? 's' : ''} `;
            }
            result += `${horas.toString().padStart(2, '0')}:${remainingMinutes.toString().padStart(2, '0')}`;

            return result;
        },
    },
}
</script>