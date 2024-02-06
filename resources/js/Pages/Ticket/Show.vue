<template>
    <AppLayout :title="ticket.data.title">
        <div class="lg:p-9 p-1">
            <div class="flex items-center justify-between">
                <Back />
                <ThirthButton @click="$inertia.get(route('tickets.edit', ticket.data.id))">Editar</ThirthButton>
            </div>
            <div class="lg:mx-16 mt-5">
                <!-- Info general -->
                <p class="text-gray66 text-sm">Folio: <span class="text-black ml-1">#{{ ticket.data.id }}</span></p>
                <div class="lg:flex items-center space-x-3">
                    <p class="text-gray66 text-sm">Creado por: <span class="text-black ml-1">{{ ticket.data.responsible?.name }}</span></p>
                    <i class="fa-solid fa-circle text-[3px]"></i>
                    <p class="text-gray66 text-sm">Creado el: <span class="text-black ml-1">{{ ticket.data.created_at }}</span></p>
                    <i class="fa-solid fa-circle text-[3px]"></i>
                    <p class="text-gray66 text-sm">Fecha límite: <span class="text-black ml-1">{{ ticket.data.expired_date }}</span></p>
                </div>
                <div class="flex justify-between">
                    <div class="flex items-center space-x-3">
                        <p class="text-gray66 text-sm">Prioridad: <span class="text-black ml-1">{{ ticket.data.priority }}</span></p>
                        <i :class="getPriorityColor()" class="fa-solid fa-circle text-[7px]"></i>
                    </div>
                    <!-- Estatus -->
                    <div class="flex items-center space-x-3 w-1/2">
                        <p class="text-gray66 text-sm">Estatus:</p>
                        <div class="w-1/2">
                            <el-select @change="updateStatus" v-model="status"
                                placeholder="Seleccione" no-data-text="No hay opciones registradas"
                                no-match-text="No se encontraron coincidencias">
                                <el-option v-for="item in statuses" :key="item" :label="item.label" :value="item.label">
                                    <p class="w-4" style="float: left">
                                        <span v-html="item.icon"></span>
                                    </p>
                                    <span class="ml-2">{{ item.label }}</span>
                                </el-option>
                            </el-select>
                        </div>
                        <p class="text-gray66 text-sm"><span v-html="getIcon()"></span>{{ this.ticket.data.status + ' el' }}: <span class="text-black ml-1">{{ ticket.data.updated_at }}</span></p>
                    </div>
                </div>

                <!-- Título del ticket -->
                <h1 class="font-bold text-lg my-2">{{ ticket.data.title }}</h1>
                <p class="text-gray66 text-sm">Descripción: <span class="text-black ml-1">{{ ticket.data.description }}</span></p>
                <p class="text-gray66 text-sm py-2">Archivos adjuntos</p>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-2" v-if="ticket.data.media?.length > 0 ">
                    <FileView v-for="file in ticket.data.media" :key="file" :file="file" />
                </div>
                <p v-else class="text-sm text-gray-400">No hay archivos adjuntos</p>
            </div>

            <!-- Pestañas -->
            <div class="lg:w-3/4 w-full flex items-center space-x-7 text-sm border-b border-gray4 lg:mx-16 mx-2 mt-16 mb-5">
              <div @click="currentTab = 1" :class="currentTab == 1 ? 'text-primary border-b-2 border-primary pb-1 px-3 font-bold' : 'px-3 pb-1 text-gray66 font-semibold' " class="flex items-center space-x-2 cursor-pointer text-base">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"  class="size-4">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                </svg>
                <p>Resoluciones(2)</p> 
              </div>
              <div @click="currentTab = 2" :class="currentTab == 2 ? 'text-primary border-b-2 border-primary pb-1 px-3 font-bold' : 'px-3 pb-1 text-gray66 font-semibold'" class="flex items-center space-x-2 cursor-pointer text-base">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
                <p>Conversaciones</p> 
              </div>
              <div @click="currentTab = 3" :class="currentTab == 3 ? 'text-primary border-b-2 border-primary pb-1 px-3 font-bold' : 'px-3 pb-1 text-gray66 font-semibold'" class="flex items-center space-x-2 cursor-pointer text-base">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
                <p>Registro de actividad</p> 
              </div>
            </div>

            <!-- Tab 1 all events ------------------------ -->
            <div class="lg:grid grid-cols-2 gap-5 lg:mx-7" v-if="currentTab == 1">
              <!-- <div class="space-y-5" v-if="community_events.data.length > 0">
                <CommunityEventCard 
                @delete-community-event="deleteCommunityEvent" 
                v-for="communityEvent in community_events.data" :key="communityEvent" :communityEvent="communityEvent"
                :registered_events="my_community_events" />
              </div>
              <p class="text-xs text-gray-400 text-center" v-else>No hay eventos próximos</p> -->
            </div>
            <!-- ----------------------------------------- -->

        </div>
    </AppLayout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";
import ThirthButton from "@/Components/MyComponents/ThirthButton.vue";
import FileView from "@/Components/MyComponents/Ticket/FileView.vue";
import Back from "@/Components/MyComponents/Back.vue";
import axios from 'axios';

export default {
data() {
    return {
        currentTab: 1,
        status: this.ticket.data.status,
        statuses: [
            {
                label: 'Abierto',
                color: 'text-[#0355B5]',
                icon: '<i class="fa-solid fa-arrow-up text-[#0355B5]"></i>',

            },
            {
                label:'En espera',
                color:'text-[#FD8827]',
                icon:'<i class="fa-regular fa-clock text-[#FD8827]"></i>',
            },
            {
                label:'En espera de 3ro',
                color:'text-[#B927FD]',
                icon:'<i class="fa-regular fa-hourglass-half text-[#B927FD]"></i>',
            },
            {
                label:'Completado',
                color:'text-[#3F9C30]',
                icon:'<i class="fa-solid fa-check text-[#3F9C30]"></i>',
            },
            {
                label:'Re-abierto',
                color:'text-[#FD4646]',
                icon:'<i class="fa-solid fa-rotate-right text-[#FD4646]"></i>',
            },
            {
                label:'En proceso',
                color:'text-[#3D3D3D]',
                icon:'<i class="fa-solid fa-arrow-right-to-bracket text-[#3D3D3D]"></i>',
            },
        ],
    }
},
components:{
AppLayout,
ThirthButton,
FileView,
Back
},
props:{
ticket: Object
},
methods:{
    getPriorityColor() {
        if (this.ticket.data.priority === 'Baja') {
            return 'text-[#A49C9D]';
        } else if (this.ticket.data.priority === 'Media') {
            return 'text-[#D68D1F]';
        } else if (this.ticket.data.priority === 'Alta') {
            return 'text-[#C1202A]';
        }
    },
    getIcon() {
        if (this.ticket.data.status === 'Abierto') {
            return '<i class="fa-solid fa-arrow-up text-[#0355B5] mr-2"></i>';
        } else if (this.ticket.data.status === 'En espera') {
            return '<i class="fa-regular fa-clock text-[#FD8827] mr-2"></i>';
        } else if (this.ticket.data.status === 'En espera de 3ro') {
            return '<i class="fa-regular fa-hourglass-half text-[#B927FD] mr-2"></i>';
        } else if (this.ticket.data.status === 'Completado') {
            return '<i class="fa-solid fa-check text-[#3F9C30] mr-2"></i>';
        } else if (this.ticket.data.status === 'Re-abierto') {
            return '<i class="fa-solid fa-rotate-right text-[#FD4646] mr-2"></i>';
        } else if (this.ticket.data.status === 'En proceso') {
            return '<i class="fa-solid fa-arrow-right-to-bracket text-[#3D3D3D] mr-2"></i>';
        }
    },
    async updateStatus() {
        try {
            const response = await axios.put(route('tickets.update-status', this.ticket.data.id), { status: this.status });

            if (response.status === 200) {

                this.ticket.data.status = response.data.item.status;
                this.ticket.data.updated_at = response.data.item.updated_at;

                this.$notify({
                title: "Correcto",
                message: "Se ha actualizado el estatus",
                type: "success",
            });
            }
        } catch (error) {
            console.log(error);
            this.$notify({
                title: "Error de servidor",
                message: "No se pudo completar tu petición. Inténtalo más tarde",
                type: "error",
            });
        }
    },
}
}
</script>
