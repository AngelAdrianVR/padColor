<template>
    <div>

        <Head title="Reporte de tickets" />
        <header class="mx-8 mt-5">
            <figure class="h-20">
                <img class="h-20" src="@/../../public/images/logo_name.png" alt="logo">
            </figure>
        </header>
        <main class="text-xs">
            <section class="text-center">
                <h1 class="font-bold">Reporte</h1>
                <p class="mt-5"><b>Fecha: </b> Del {{ formatDate(startDate) }} al {{ formatDate(endDate) }}</p>
            </section>
            <section class="mx-20 mt-8 flex justify-between">
                <article class="w-[42%]">
                    <h2 class="text-primary font-bold text-xs mb-1">Sucursales con más tickets</h2>
                    <div>
                        <p class="font-bold">{{ 'Alfayucan' }}</p>
                        <div class="flex items-center space-x-2 space-y-0">
                            <div class="h-px w-5/6 bg-gray99"></div>
                            <span class="text-[9px] text-[#747474] leading-none">{{ '50' }}%</span>
                        </div>
                        <p class="text-[9px] text-[#747474] leading-none">{{ 'Papel, Diseño y Color' }}</p>
                    </div>
                    <div>
                        <p class="font-bold">{{ 'Alfayucan' }}</p>
                        <div class="flex items-center space-x-2">
                            <div class="h-px w-5/6 bg-gray99"></div>
                            <span class="text-[9px] text-[#747474] leading-none">{{ '50' }}%</span>
                        </div>
                        <p class="text-[9px] text-[#747474] leading-none">{{ 'Papel, Diseño y Color' }}</p>
                    </div>
                    <div>
                        <p class="font-bold">{{ 'Alfayucan' }}</p>
                        <div class="flex items-center space-x-2">
                            <div class="h-px w-5/6 bg-gray99"></div>
                            <span class="text-[9px] text-[#747474] leading-none">{{ '50' }}%</span>
                        </div>
                        <p class="text-[9px] text-[#747474] leading-none">{{ 'Papel, Diseño y Color' }}</p>
                    </div>
                </article>
                <article class="w-[42%]">
                    <h2 class="text-primary font-bold text-xs mb-1">Categoría con más recurrencia</h2>
                    <div>
                        <p class="font-bold">{{ 'Alfayucan' }}</p>
                        <div class="flex items-center space-x-2">
                            <div class="h-px w-5/6 bg-gray99"></div>
                            <span class="text-[9px] text-[#747474] leading-none">{{ '50' }}%</span>
                        </div>
                        <p class="text-[9px] text-[#747474] leading-none">{{ 'Papel, Diseño y Color' }}</p>
                    </div>
                    <div>
                        <p class="font-bold">{{ 'Alfayucan' }}</p>
                        <div class="flex items-center space-x-2">
                            <div class="h-px w-5/6 bg-gray99"></div>
                            <span class="text-[9px] text-[#747474] leading-none">{{ '50' }}%</span>
                        </div>
                        <p class="text-[9px] text-[#747474] leading-none">{{ 'Papel, Diseño y Color' }}</p>
                    </div>
                    <div>
                        <p class="font-bold">{{ 'Alfayucan' }}</p>
                        <div class="flex items-center space-x-2">
                            <div class="h-px w-5/6 bg-gray99"></div>
                            <span class="text-[9px] text-[#747474] leading-none">{{ '50' }}%</span>
                        </div>
                        <p class="text-[9px] text-[#747474] leading-none">{{ 'Papel, Diseño y Color' }}</p>
                    </div>
                </article>
            </section>
            <section class="mx-20 mt-14 grid grid-cols-6 gap-x-2">
                <h2 class="text-primary font-bold text-sm text-center mb-3 col-span-full">Estado de los tickets</h2>
                <article v-for="item in statuses" :key="item.label" class="rounded-[5px] px-2 py-[5px] text-[10px]"
                    :style="{ color: item.color, backgroundColor: item.bg }">
                    <header class="flex items-center justify-between">
                        <p class="flex items-center space-x-1">
                            <span>{{ '19' }}</span>
                            <i :class="item.icon"></i>
                        </p>
                        <span>{{ '15' }}%</span>
                    </header>
                    <p class="mt-1 text-[9px]">{{ item.label }}</p>
                </article>
            </section>
            <section class="mx-20 mt-5 grid grid-cols-4 gap-x-3">
                <article v-for="(item, index) in simpleKpis" :key="index" class="border border-grayD9 rounded-[10px] px-2 py-1 text-[10px]">
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
            <section class="mx-5 mt-6">
                <table class="w-full">
                    <thead>
                        <tr class="*:bg-primary *:text-white *:border *:border-grayD9 *:text-start *:px-4 *:text-[10px]">
                            <th>ID</th>
                            <th>Tipo de servicio</th>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Fecha de creación</th>
                            <th>Estado</th>
                            <th>Prioridad</th>
                            <th>Responsable</th>
                            <th>Resoluciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in tickets" :key="item.id" class="*:border *:border-grayD9 *:text-start *:px-4 *:text-[10px]">
                            <td>{{ getFolio(item) }}</td>
                            <td>{{ item.ticket_type }}</td>
                            <td>{{ item.title }}</td>
                            <td>{{ item.category.name }}</td>
                            <td>{{ formatDateTime(item.created_at) }}</td>
                            <td>{{ item.status }}</td>
                            <td>{{ item.priority }}</td>
                            <td>{{ item.responsible?.name ?? '-' }}</td>
                            <td>{{ item.ticket_solutions.length }}</td>
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
    methods: {
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
            return format(parseISO(dateTime), 'dd MMMM yyyy, hh:mm a', { locale: es });
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