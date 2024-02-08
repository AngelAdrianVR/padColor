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
                <select value="Filtrar" class="input">
                    <option disabled value="Filtrar"> <i class="fa-solid fa-arrow-down-short-wide"></i> Filtro </option>
                </select>
                <select value="Ordenar" class="input">
                    <option disabled value="Ordenar"> <i class="fa-solid fa-arrow-down-short-wide"></i> Ordenar </option>
                </select>
            </div>
        </div>

        <!-- Tickets -->
        <div class="mt-7">
            <div class="flex items-center space-x-9 border-b border-grayD9 pb-2">
                <label class="flex items-center ml-2 lg:ml-24">
                    <Checkbox v-model:checked="selectAllTickets" name="remember" />
                    <span class="ms-2 text-sm font-bold">Todos los tickets</span>
                </label>
                <el-popconfirm v-if="selectAllTickets || selectedTicketsId.length > 0" confirm-button-text="Si" cancel-button-text="No" icon-color="#ff4d4d"
                    title="¿Continuar?" @confirm="massiveDelete">
                    <template #reference>
                        <DangerButton class="!py-1">Eliminar</DangerButton>
                    </template>
                </el-popconfirm>
                <p class="text-[#ff4d4d]"></p>
            </div>
            <TicketRow v-for="ticket in tickets.data" :key="ticket" :ticket="ticket" 
                :selectTicket="selectAllTickets"
                @selected="selectedTicket"
                @unselected="unselectedTicket" />
            </div>
    </AppLayout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import TicketRow from "@/Components/MyComponents/Ticket/TicketRow.vue";
import Checkbox from '@/Components/Checkbox.vue';
import axios from 'axios';

export default {
data() {
    return {
        selectAllTickets: false,
        selectedTicketsId: []
    }
},
components:{
AppLayout,
PrimaryButton,
DangerButton,
TicketRow,
Checkbox
},
props:{
tickets: Object
},
methods:{
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