<template>
    <AppLayout title="Tickets">
        <div class="flex justify-between items-center mt-4 mx-10">
            <h1 class="font-bold">
                {{ $page.props.auth.user.permissions.includes('Ver todos los tickets') ? 'Todos los tickets' : 'Todos mis tickets' }}
            </h1>
            <div class="flex items-center space-x-2">
                <el-button 
                    v-if="$page.props.auth.user.permissions.includes('Crear tickets')"
                    type="primary" 
                    @click="$inertia.get(route('tickets.create'))">
                    Crear ticket
                </el-button>
                
                <el-popconfirm
                    v-if="(selectAllTickets || selectedTicketsId.length) && $page.props.auth.user.permissions.includes('Eliminar tickets')"
                    confirm-button-text="Si" 
                    cancel-button-text="No" 
                    icon-color="#D72C8A"
                    :title="'¿Desea eliminar los elementos seleccionados (' + selectedTicketsId.length + ')?'"
                    @confirm="massiveDelete">
                    <template #reference>
                        <el-button type="danger" plain round size="small" class="mb-1">Eliminar</el-button>
                    </template>
                </el-popconfirm>
            </div>
        </div>

        <!-- Buscador y filtros -->
        <div class="flex flex-col lg:flex-row justify-between space-y-3 space-x-3 lg:items-center mt-4 mx-2 lg:mx-10">
            <div class="lg:w-1/4 relative lg:mr-12">
                <el-input 
                    v-model="searchTemp" 
                    @keyup.enter="handleSearch" 
                    placeholder="Buscar tickets" 
                    clearable
                    @clear="handleTagClose">
                    <template #prefix>
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </template>
                </el-input>
            </div>
            
            <el-tag v-if="search" size="large" closable @close="handleTagClose">
                Estas buscando: <b>{{ search }}</b>
            </el-tag>

            <div v-if="$page.props.auth.user.permissions.includes('Ver todos los tickets')" class="flex items-center space-x-3 lg:w-1/5">
                <el-cascader 
                    class="w-full" 
                    v-model="filter" 
                    :options="options" 
                    @change="handleChangeFilter" 
                    clearable
                    placeholder="Filtrar" 
                />
            </div>
        </div>

        <!-- Tickets -->
        <div v-if="loading" class="mt-32">
            <Loading />
        </div>
        <div v-else class="mt-7">
            <div v-if="localTickets.length" class="flex items-center space-x-9 border-b border-grayD9 pb-2">
                <div class="ml-2 lg:ml-24">
                    <el-checkbox 
                        v-model="selectAllTickets" 
                        label="Todos los tickets" 
                        class="!font-bold"
                    />
                </div>
                <p class="text-gray66 text-right text-[11px]">{{ localTickets.length }} de {{ total_tickets }} elementos</p>
            </div>
            
            <TicketRow 
                v-for="ticket in localTickets" 
                :key="ticket.id" 
                :ticket="ticket" 
                :selectTicket="selectAllTickets"
                @selected="selectedTicket" 
                @unselected="unselectedTicket" 
            />
            
            <p class="text-gray66 text-left ml-8 mt-2 text-[11px]">{{ localTickets.length }} de {{ total_tickets }} elementos</p>
            
            <div class="flex justify-center my-4">
                <p v-if="loadingItems" class="text-xs text-center">
                    Cargando <i class="fa-sharp fa-solid fa-circle-notch fa-spin ml-2 text-secondary"></i>
                </p>
                <el-button 
                    v-else-if="localTickets.length && !search && !filtered && (total_tickets > 15 && localTickets.length < total_tickets)" 
                    @click="fetchItemsByPage"
                    link 
                    type="primary">
                    Cargar más elementos
                </el-button>
            </div>
            
            <el-empty v-if="!localTickets.length" description="No hay tickets para mostrar" />
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";
import TicketRow from "@/Components/MyComponents/Ticket/TicketRow.vue";
import Loading from "@/Components/MyComponents/Loading.vue";
import axios from 'axios';

export default {
    data() {
        return {
            selectAllTickets: false,
            loading: false,
            filter: null,
            searchTemp: null,
            search: null,
            selectedTicketsId: [],
            ticketsBuffer: [],
            localTickets: this.tickets.data,
            options: [
                {
                    label: "Por estado",
                    value: "status",
                    children: [
                        { label: 'Abierto', value: 'Abierto' },
                        { label: 'En espera', value: 'En espera' },
                        { label: 'En espera de 3ro', value: 'En espera de 3ro' },
                        { label: 'Completado', value: 'Completado' },
                        { label: 'Re-abierto', value: 'Re-abierto' },
                        { label: 'En proceso', value: 'En proceso' },
                    ],
                },
                {
                    label: "Por prioridad",
                    value: "priority",
                    children: [
                        { label: 'Baja', value: 'Baja' },
                        { label: 'Media', value: 'Media' },
                        { label: 'Alta', value: 'Alta' },
                    ],
                },
                {
                    label: "Por fecha de creación",
                    value: "created_at",
                    children: [
                        { label: 'Hoy', value: 'Hoy' },
                        { label: 'Esta semana', value: 'Esta semana' }, // Nota: 'Esta semana ' tenía un espacio en tu código original, verifica si el backend lo espera así
                        { label: 'Este mes', value: 'Este mes' },
                        { label: 'Mes pasado', value: 'Mes pasado' },
                        { label: 'Este año', value: 'Este año' },
                        { label: 'Año pasado', value: 'Año pasado' },
                    ],
                },
                {
                    label: "Por fecha de expiración",
                    value: "expired_date",
                    children: [
                        { label: 'Hoy', value: 'Hoy' },
                        { label: 'Esta semana', value: 'Esta semana' },
                        { label: 'Este mes', value: 'Este mes' },
                        { label: 'Mes pasado', value: 'Mes pasado' },
                        { label: 'Este año', value: 'Este año' },
                        { label: 'Año pasado', value: 'Año pasado' },
                    ],
                },
                {
                    label: "Por categoría",
                    value: "category_id",
                    children: []
                },
                {
                    label: "Por sucursal",
                    value: "branch",
                    children: [
                        { label: 'Alfajayucan', value: 'Alfajayucan' },
                        { label: 'Morelia', value: 'Morelia' },
                        { label: 'San Luis Potosí', value: 'San Luis Potosí' },
                        { label: 'Acapulco', value: 'Acapulco' },
                        { label: 'Av. del Tigre', value: 'Av. del Tigre' },
                        { label: 'Calle C', value: 'Calle C' },
                        { label: 'Calle 2', value: 'Calle 2' },
                        { label: 'Veracruz', value: 'Veracruz' },
                        { label: 'León', value: 'León' },
                        { label: 'Juárez', value: 'Juárez' },
                        { label: 'Puebla', value: 'Puebla' },
                        { label: 'Monterrey', value: 'Monterrey' },
                        { label: 'Federalismo', value: 'Federalismo' },
                    ],
                },
            ],
            // paginacion dinamica
            currentPage: 1,
            loadingItems: false,
            filtered: false,
        }
    },
    components: {
        AppLayout,
        TicketRow,
        Loading,
    },
    props: {
        tickets: Object,
        categories: Array,
        total_tickets: Number,
    },
    methods: {
        handleSearch() {
            this.filter = null;
            this.search = this.searchTemp;
            this.searchTemp = null;
            if (this.search) {
                this.fetchMatches();
            } else {
                this.showAllTickets();
            }
        },
        handleTagClose() {
            this.search = null;
            this.searchTemp = null; // Limpiar también el temporal al cerrar
            this.showAllTickets();
        },
        selectedTicket(ticket_id) {
            this.selectedTicketsId.push(ticket_id);
        },
        unselectedTicket(ticket_id) {
            const index = this.selectedTicketsId.findIndex(id => id === ticket_id);

            //Elimina del arreglo el elemento si encuentra el id
            if (index != -1) {
                this.selectedTicketsId.splice(index, 1);
            }
        },
        handleChangeFilter() {
            this.handleTagClose();
            if (this.filter) {
                this.fetchFiltered();
            }
        },
        showAllTickets() {
            // solo si hay items en el buffer, para no dejar vacio el arreglo principal
            if (this.ticketsBuffer.length) {
                this.localTickets = this.ticketsBuffer;
                this.ticketsBuffer = [];
            }
        },
        async fetchItemsByPage() {
            try {
                this.loadingItems = true;
                const response = await axios.get(route('tickets.get-by-page', this.currentPage));

                if (response.status === 200) {
                    this.localTickets = [...this.localTickets, ...response.data.items];
                    this.currentPage++;
                }
            } catch (error) {
                console.log(error)
            } finally {
                this.loadingItems = false;
            }
        },
        async fetchMatches() {
            try {
                this.filtered = false;
                this.loading = true;
                const response = await axios.get(route('tickets.get-matches', { query: this.search }));

                if (response.status === 200) {
                    if (!this.ticketsBuffer.length) {
                        this.ticketsBuffer = this.localTickets;
                    }
                    this.localTickets = response.data.items;
                }
            } catch (error) {
                console.log(error);
            } finally {
                this.loading = false;
            }
        },
        async fetchFiltered() {
            try {
                this.loading = true;
                const response = await axios.get(route('tickets.get-filters', { prop: this.filter[0], value: this.filter[1] }));

                if (response.status === 200) {
                    // si el bufer no tiene nada aun, guardar los tickets
                    if (!this.ticketsBuffer.length) {
                        this.ticketsBuffer = this.localTickets;
                    }
                    this.localTickets = response.data.items;
                    this.filtered = true;
                }
            } catch (error) {
                console.log(error);
            } finally {
                this.loading = false;
            }
        },
        async massiveDelete() {
            try {
                const response = await axios.post(route('tickets.massive-delete', { tickets: this.selectedTicketsId }));

                if (response.status == 200) {
                    this.$notify({
                        title: "Correcto",
                        message: "ticket(s) eliminado(s)",
                        type: "success",
                    });
                    location.reload();
                }
            } catch (error) {
                console.log(error);
                this.$notify({
                    title: "Error de servidor",
                    message: "No se pudo completar la acción. Inténta más tarde",
                    type: "error",
                });
            }
        }
    },
    mounted() {
        // agregar las categorias en las opciones de filtro
        this.categories.forEach(element => {
            const ops = { label: element.name, value: element.id }
            this.options[4].children.push(ops);
        });
    },
}
</script>