<template>
    <AppLayout title="Inicio">
        <header class="mx-2 lg:mx-14 mt-4">
            <h1 class="mx-2 lg:mx-16 font-bold">Inicio</h1>
            <section class="flex items-center justify-end">
                <ThirthButton @click="showReportModal = true">Generar reporte</ThirthButton>
            </section>
        </header>

        <!-- SECCIÓN: Rendimiento (General / Usuario / Departamento) -->
        <section class="mx-2 lg:mx-14 mt-6 bg-white border border-grayD9 rounded-lg p-5">
            <h2 class="font-bold text-gray-700 mb-4">Métricas de rendimiento</h2>
            <div class="flex flex-col lg:flex-row gap-5 items-start">
                
                <!-- Buscadores -->
                <div class="lg:w-1/4 w-full flex flex-col space-y-4">
                    
                    <el-radio-group v-model="performanceMode" size="large" class="w-full flex-wrap">
                        <el-radio-button label="general">Generales</el-radio-button>
                        <el-radio-button label="user">Por usuario</el-radio-button>
                        <el-radio-button label="department">Por departamento</el-radio-button>
                    </el-radio-group>

                    <div v-if="performanceMode === 'user'">
                        <InputLabel value="Seleccione un usuario" class="mb-1" />
                        <el-select 
                            v-model="selectedUser" 
                            filterable 
                            clearable 
                            placeholder="Buscar usuario..." 
                            class="w-full"
                            no-data-text="No hay usuarios registrados" 
                            no-match-text="No se encontraron coincidencias">
                            <el-option v-for="user in users" :key="user.id" :label="user.name" :value="user.id">
                                <div class="flex items-center space-x-2">
                                    <img :src="user.profile_photo_url" class="size-6 rounded-full object-cover" />
                                    <span>{{ user.name }}</span>
                                </div>
                            </el-option>
                        </el-select>
                    </div>

                    <div v-if="performanceMode === 'department'">
                        <InputLabel value="Seleccione un departamento" class="mb-1" />
                        <el-select 
                            v-model="selectedDepartment" 
                            filterable 
                            clearable 
                            placeholder="Buscar departamento..." 
                            class="w-full"
                            no-data-text="No hay departamentos registrados" 
                            no-match-text="No se encontraron coincidencias">
                            <el-option v-for="dept in departments" :key="dept" :label="dept" :value="dept" />
                        </el-select>
                    </div>
                </div>

                <!-- KPIs dinámicos -->
                <div class="lg:w-3/4 w-full grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-1 lg:gap-5" 
                     v-if="performanceMode === 'general' || (performanceMode === 'user' && selectedUser) || (performanceMode === 'department' && selectedDepartment)">
                    <SimpleKPI 
                        v-for="(stat, index) in currentPerformanceStats" 
                        :key="index" 
                        :title="stat.title" 
                        :icon="stat.icon" 
                        :value="stat.value" 
                    />
                </div>
                
                <!-- Placeholder cuando no hay selección -->
                <div v-else class="lg:w-3/4 w-full flex items-center justify-center text-gray-400 h-[120px] border border-dashed border-grayD9 rounded-lg bg-gray-50">
                    <i class="fa-solid fa-hand-pointer mr-2 text-lg"></i> Selecciona un objetivo para visualizar sus métricas
                </div>
            </div>
        </section>

        <!-- Gráficas -->
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

        <!-- Modal Reporte -->
        <DialogModal :show="showReportModal" @close="showReportModal = false">
            <template #title>
                <h1 class="font-bold text-sm">Reporte de tickets</h1>
            </template>
            <template #content>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <InputLabel value="Rango de fechas *" class="ml-3 mb-1" />
                        <el-date-picker v-model="dateRange" class="!w-full" type="daterange" range-separator="A"
                            start-placeholder="Fecha de inicio" end-placeholder="Fecha de fin" format="DD/MMM/YYYY"
                            value-format="YYYY-MM-DD" />
                    </div>
                    <div>
                        <InputLabel value="Categoría *" class="ml-3 mb-1" />
                        <el-select class="w-full" v-model="category" placeholder="Seleccione la categoría"
                            no-data-text="No hay opciones registradas" no-match-text="No se encontraron coincidencias">
                            <el-option v-for="item in reportCategories" :key="item" :label="item" :value="item" />
                        </el-select>
                    </div>
                </div>
            </template>
            <template #footer>
                <PrimaryButton @click="generateReport" :disabled="!dateRange">Continuar</PrimaryButton>
            </template>
        </DialogModal>
    </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import InputLabel from "@/Components/InputLabel.vue";
import PolarAreaChart from '@/Components/MyComponents/Charts/PolarAreaChart.vue';
import DialogModal from '@/Components/DialogModal.vue';
import SimpleKPI from '@/Components/MyComponents/Dashboard/SimpleKPI.vue';
import ThirthButton from '@/Components/MyComponents/ThirthButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import PieChart from '@/Components/MyComponents/Charts/PieChart.vue';

export default {
    data() {
        return {
            showReportModal: false,
            dateRange: null,
            category: 'Todas',
            reportCategories: ['Todas'],
            performanceMode: 'general', // Controla el "switch", arranca en modo General
            selectedUser: null, 
            selectedDepartment: null,
            departments: [
                'Administración', 'Almacén', 'Comercial', 'Compras', 'Contabilidad',
                'Contraloría', 'Crédito y cobranza', 'Dirección', 'Empaques',
                'Inspección', 'Mantenimiento', 'Producción', 'Recursos Humanos',
                'Sistemas', 'Tesorería',
            ],
            branches: [
                'Alfajayucan', 'Morelia', 'San Luis Potosí', 'Acapulco', 'Av. del Tigre',
                'Calle C', 'Calle 2', 'Veracruz', 'León', 'Juárez', 'Puebla', 'Monterrey', 'Federalismo',
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
                    'Alfajayucan', 'Morelia', 'San Luis Potosí', 'Acapulco', 'Av. del Tigre',
                    'Calle C', 'Calle 2', 'Veracruz', 'León', 'Juárez', 'Puebla', 'Monterrey',
                    'Federalismo', 'General',
                ],
                series: this.ticketsByBranch(),
            },
            ticketsByCategoriesOptions: {
                colors: ['#0355B5', '#FD8827', '#B927FD', '#3F9C30', '#FD4646', '#3D3D3D'],
                labels: this.ticketsByCategory().map(item => item[0]),
                series: this.ticketsByCategory().map(item => item[1]),
            },
        }
    },
    props: {
        tickets: Array,
        categories: Array,
        users: Array,
        userStats: Object,
        departmentStats: Object,
    },
    components: {
        AppLayout, PolarAreaChart, PieChart, SimpleKPI, 
        ThirthButton, PrimaryButton, DialogModal, InputLabel,
    },
    computed: {
        // Sirve la información de los 7 KPIs basado en el modo seleccionado (General/Usuario/Departamento)
        currentPerformanceStats() {
            if (this.performanceMode === 'user' && !this.selectedUser) return [];
            if (this.performanceMode === 'department' && !this.selectedDepartment) return [];

            let created = 0;
            let solutions = 0;
            let pending = 0;
            let lowTickets = [];
            let medTickets = [];
            let highTickets = [];
            let targetTickets = [];

            if (this.performanceMode === 'general') {
                // Métricas generales de todo el ERP
                created = this.tickets.length;
                solutions = Object.values(this.userStats.solutions || {}).reduce((a, b) => a + b, 0);
                pending = this.tickets.filter(t => t.status !== 'Completado').length;
                
                targetTickets = this.tickets;
                
            } else if (this.performanceMode === 'user') {
                // Métricas globales de usuario
                created = this.userStats.created[this.selectedUser] || 0;
                solutions = this.userStats.solutions[this.selectedUser] || 0;
                pending = this.userStats.pending_assigned[this.selectedUser] || 0;

                targetTickets = this.tickets.filter(t => 
                    t.responsible_id == this.selectedUser || 
                    (t.ticket_solutions && t.ticket_solutions.some(ts => ts.user_id == this.selectedUser))
                );
            } else {
                // Métricas de departamento
                const deptUsersIds = this.users
                    .filter(u => u.employee_properties?.department == this.selectedDepartment)
                    .map(u => u.id);
                
                deptUsersIds.forEach(uid => {
                    created += this.userStats.created[uid] || 0;
                    solutions += this.userStats.solutions[uid] || 0;
                });

                pending = this.departmentStats?.pending_assigned[this.selectedDepartment] || 0;

                targetTickets = this.tickets.filter(t => t.department == this.selectedDepartment);
            }

            lowTickets = targetTickets.filter(t => t.priority === 'Baja');
            medTickets = targetTickets.filter(t => t.priority === 'Media');
            highTickets = targetTickets.filter(t => t.priority === 'Alta');

            return [
                {
                    title: this.performanceMode === 'general' ? "Tickets creados (Totales)" : "Tickets creados",
                    icon: "fa-solid fa-ticket text-primary",
                    value: created
                },
                {
                    title: this.performanceMode === 'general' ? "Soluciones publicadas (Totales)" : "Soluciones publicadas",
                    icon: "fa-solid fa-check-double text-green-600",
                    value: solutions
                },
                {
                    title: this.performanceMode === 'general' ? "Tickets pendientes (Totales)" : "Tickets pendientes (Asignados)",
                    icon: "fa-solid fa-clock-rotate-left text-orange-600",
                    value: pending
                },
                {
                    title: "T. Prom. Respuesta (Baja)",
                    icon: "fa-solid fa-snowflake text-gray-400",
                    value: this.calculateAverageResponseTime(lowTickets)
                },
                {
                    title: "T. Prom. Respuesta (Media)",
                    icon: "fa-solid fa-droplet text-[#D68D1F]",
                    value: this.calculateAverageResponseTime(medTickets)
                },
                {
                    title: "T. Prom. Respuesta (Alta)",
                    icon: "fa-solid fa-fire-flame-curved text-[#C1202A]",
                    value: this.calculateAverageResponseTime(highTickets)
                },
                {
                    title: "Índice de cumplimiento de fechas de expiración",
                    icon: "fa-regular fa-clock text-indigo-500",
                    value: this.calculatePercentageResolvedBeforeExpiration(targetTickets)
                }
            ];
        }
    },
    methods: {
        generateReport() {
            const url = route('dashboard.tickets-report', {
                startDate: this.dateRange[0],
                endDate: this.dateRange[1],
                category: this.category,
            });
            window.open(url, '_blank');
        },
        // Función Genérica para calcular tiempos promedios
        calculateAverageResponseTime(ticketsArray) {
            const responseTimes = ticketsArray
                .map(ticket => {
                    if (ticket.solution_minutes !== null && ticket.solution_minutes !== undefined && ticket.solution_minutes !== '') {
                        return Number(ticket.solution_minutes) / 60;
                    }
                    return null;
                })
                .filter(time => time !== null);

            if (responseTimes.length > 0) {
                const averageResponseTime = responseTimes.reduce((total, time) => total + time, 0) / responseTimes.length;

                const days = Math.floor(averageResponseTime / 24);
                const hours = Math.floor(averageResponseTime % 24);
                const minutes = Math.floor((averageResponseTime % 1) * 60);

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
        // Función para calcular dinámicamente el índice de cumplimiento según el arreglo de tickets proporcionado
        calculatePercentageResolvedBeforeExpiration(ticketsArray) {
            if (!ticketsArray || ticketsArray.length === 0) return 'Sin información';

            const resolvedBeforeExpiration = ticketsArray.filter(ticket => {
                if (ticket.ticket_solutions && ticket.ticket_solutions.length > 0 && ticket.expired_date) {
                    const solutionDate = new Date(ticket.ticket_solutions[0].created_at);
                    const expirationDate = new Date(ticket.expired_date);
                    return solutionDate < expirationDate;
                }
                return false;
            });

            const totalWithSolutions = ticketsArray.filter(item => item.ticket_solutions?.length).length;
            if(totalWithSolutions === 0) return 'Sin información';

            const percentage = (resolvedBeforeExpiration.length / totalWithSolutions) * 100;
            return percentage ? Math.round(percentage, 2) + '%' : 'Sin información';
        },
        ticketsByStatus() {
            const statuses = ["Abierto", "En espera", "En espera de 3ro", "Completado", "Re-abierto", "En proceso"];
            const byStatus = {};
            statuses.forEach(status => byStatus[status] = 0);

            this.tickets.forEach(ticket => {
                if (byStatus.hasOwnProperty(ticket.status)) byStatus[ticket.status]++;
            });

            return statuses.map(status => byStatus[status]);
        },
        ticketsByCategory() {
            const categories = {};
            this.categories.forEach(category => categories[category.name] = 0);

            this.tickets.forEach(ticket => {
                if (categories.hasOwnProperty(ticket.category.name)) {
                    categories[ticket.category.name]++;
                }
            });

            return Object.entries(categories).map(([item, quantity]) => [item, quantity]);
        },
        ticketsByBranch() {
            const branches = [
                'Alfajayucan', 'Morelia', 'San Luis Potosí', 'Acapulco', 'Av. del Tigre',
                'Calle C', 'Calle 2', 'Veracruz', 'León', 'Juárez', 'Puebla', 'Monterrey',
                'Federalismo', 'General',
            ];
            const byBranch = {};
            branches.forEach(branch => byBranch[branch] = 0);

            this.tickets.forEach(ticket => {
                if (byBranch.hasOwnProperty(ticket.branch)) byBranch[ticket.branch]++;
            });

            return branches.map(branch => byBranch[branch]);
        },
        ticketsByPriority() {
            const priorities = ["Baja", "Media", "Alta"];
            const byStatus = {};
            priorities.forEach(priority => byStatus[priority] = 0);

            this.tickets.forEach(ticket => {
                if (byStatus.hasOwnProperty(ticket.priority)) byStatus[ticket.priority]++;
            });

            return priorities.map(priority => byStatus[priority]);
        },
    },
    mounted() {
        const allCategories = this.categories.map(item => item.name);
        this.reportCategories.push(...allCategories);
    }
}
</script>