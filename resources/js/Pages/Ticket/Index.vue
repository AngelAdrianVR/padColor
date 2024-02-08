<template>
    <AppLayout title="Tickets">
        <div class="flex justify-between items-center mt-4 mx-10">
            <h1 class="text-lg font-bold">Todos los tickets</h1>
            <PrimaryButton @click="$inertia.get(route('tickets.create'))">Crear ticket</PrimaryButton>
        </div>

        <!-- Buscador y filtros -->
        <div class="flex justify-between items-center mt-4 mx-2 lg:mx-10">

            <div class="lg:w-1/4 relative">
                <input class="input w-full pl-9" placeholder="Buscar tickets" type="text">
                <i class="fa-solid fa-magnifying-glass text-xs text-gray99 absolute top-[10px] left-4"></i>
            </div>

            <div class="flex items-center space-x-3 lg:w-1/4">
                <el-cascader class="w-full" v-model="filter" :options="options" @change="handleChangeFilter" clearable
                    placeholder="Filtrar" />
            </div>
        </div>

        <!-- Tickets -->
        <div v-if="loading" class="mt-32">
            <Loading />
        </div>
        <div v-else class="mt-7">
            <div class="flex items-center space-x-9 border-b border-grayD9 pb-2">
                <label class="flex items-center ml-2 lg:ml-24">
                    <Checkbox v-model:checked="selectAllTickets" name="remember" />
                    <span class="ms-2 text-sm font-bold">Todos los tickets</span>
                </label>
                <el-popconfirm v-if="selectAllTickets || selectedTicketsId.length > 0" confirm-button-text="Si"
                    cancel-button-text="No" icon-color="#D72C8A" title="¿Continuar?" @confirm="massiveDelete">
                    <template #reference>
                        <button class="bg-redpad text-white rounded-full px-2 py-px text-sm">Eliminar</button>
                    </template>
                </el-popconfirm>
            </div>
            <TicketRow v-for="ticket in tickets.data" :key="ticket" :ticket="ticket" :selectTicket="selectAllTickets"
                @selected="selectedTicket" @unselected="unselectedTicket" />
            <el-empty v-if="!tickets.data.length" description="No hay tickets para mostrar" />
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import TicketRow from "@/Components/MyComponents/Ticket/TicketRow.vue";
import Loading from "@/Components/MyComponents/Loading.vue";
import Checkbox from '@/Components/Checkbox.vue';
import axios from 'axios';

export default {
    data() {
        return {
            selectAllTickets: false,
            loading: false,
            filter: null,
            selectedTicketsId: [],
            ticketsBuffer: [],
            options: [
                {
                    label: "Por estado",
                    value: "status",
                    children: [
                        {
                            label: 'Abierto',
                            value: 'Abierto',
                        },
                        {
                            label: 'En espera',
                            value: 'En espera',
                        },
                        {
                            label: 'En espera de 3ro',
                            value: 'En espera de 3ro',
                        },
                        {
                            label: 'Completado',
                            value: 'Completado',
                        },
                        {
                            label: 'Re-abierto',
                            value: 'Re-abierto',
                        },
                        {
                            label: 'En proceso',
                            value: 'En proceso',
                        },
                    ],
                },
                {
                    label: "Por prioridad",
                    value: "priority",
                    children: [
                        {
                            label: 'Baja',
                            value: 'Baja',
                        },
                        {
                            label: 'Media',
                            value: 'Media',
                        },
                        {
                            label: 'Alta',
                            value: 'Alta',
                        },
                    ],
                },
                {
                    label: "Por fecha de creación",
                    value: "created_at",
                    children: [
                        {
                            label: 'Hoy',
                            value: 'Hoy',
                        },
                        {
                            label: 'Esta semana ',
                            value: 'Esta semana ',
                        },
                        {
                            label: 'Este mes',
                            value: 'Este mes',
                        },
                        {
                            label: 'Mes pasado',
                            value: 'Mes pasado',
                        },
                        {
                            label: 'Este año',
                            value: 'Este año',
                        },
                        {
                            label: 'Año pasado',
                            value: 'Año pasado',
                        },
                    ],
                },
                {
                    label: "Por fecha de expiración",
                    value: "expired_date",
                    children: [
                        {
                            label: 'Hoy',
                            value: 'Hoy',
                        },
                        {
                            label: 'Esta semana ',
                            value: 'Esta semana ',
                        },
                        {
                            label: 'Este mes',
                            value: 'Este mes',
                        },
                        {
                            label: 'Mes pasado',
                            value: 'Mes pasado',
                        },
                        {
                            label: 'Este año',
                            value: 'Este año',
                        },
                        {
                            label: 'Año pasado',
                            value: 'Año pasado',
                        },
                    ],
                },

            ],
        }
    },
    components: {
        AppLayout,
        PrimaryButton,
        DangerButton,
        TicketRow,
        Loading,
        Checkbox
    },
    props: {
        tickets: Object
    },
    methods: {
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
            if (this.filter) {
                this.fetchFiltered();
            } else {
                this.showAllTickets();
            }
        },
        showAllTickets() {
            // solo si hay items en el buffer, para no dejar vacio el arreglo principal
            if (this.ticketsBuffer.length) {
                this.tickets.data = this.ticketsBuffer;
                this.ticketsBuffer = [];
            }
        },
        async fetchFiltered() {
            try {
                this.loading = true;
                const response = await axios.get(route('tickets.get-filters', {prop: this.filter[0], value: this.filter[1]}));

                if (response.status === 200) {
                    // si el bufer no tiene nada aun, guardar los tickets
                    if (!this.ticketsBuffer.length) {
                        this.ticketsBuffer = this.tickets.data;
                    }
                    this.tickets.data = response.data.items;
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
    }
}
</script>

