<template>
    <div class="md:flex justify-between border-b border-grayD9 hover:bg-primarylight pb-2 pt-3 lg:pl-[95px] pl-2">
        <section class="flex items-center w-2/3">
            <label class="flex items-center py-1">
                <Checkbox @change="ticketSelection" v-model:checked="selected" />
            </label>
            <div @click="$inertia.get(route('tickets.show', ticket.id))" class="flex flex-col cursor-pointer w-full">
                <div class="flex items-center space-x-4">
                    <p class="text-xs text-gray66 font-bold ml-6">{{ ticket.category?.name }}</p>
                    <el-tooltip :content="'Prioridad: ' + ticket.priority" placement="top">
                        <i :class="getPriorityColor(ticket)" class="fa-solid fa-circle text-[7px]"></i>
                    </el-tooltip>
                </div>
                <span class="text-sm font-bold ml-6">{{ ticket.title }}</span>
                <div class="flex items-center space-x-2 ml-6 text-gray66 text-xs">
                    <el-tooltip :content="ticket.ticket_type" placement="top">
                        <span
                            :class="ticket.ticket_type == 'Soporte o incidencia' ? 'text-[#095992]' : 'text-[#A10F3B]'">
                            #{{ getFolio(ticket) }}
                        </span>
                    </el-tooltip>
                    <span>•</span>
                    <span>{{ ticket.user?.name }}</span>
                    <span>•</span>
                    <el-tooltip :content="ticket.status" placement="top">
                        <span v-html="getIcon(ticket)"></span>
                    </el-tooltip>
                    <span>{{ ticket.updated_at }}</span>
                </div>
            </div>
        </section>
        <section @click="$inertia.get(route('tickets.show', ticket.id))"
            class="flex justify-end items-center space-x-3 w-1/3 lg:pr-7 cursor-pointer">
            <div v-if="$page.props.auth.user.permissions.includes('Editar status de tickets')" class="w-1/2">
                <el-select @change="updateStatus" v-model="status" placeholder="Seleccione"
                    no-data-text="No hay opciones registradas" no-match-text="No se encontraron coincidencias"
                    size="small" class="!w-full">
                    <el-option v-for="item in statuses" :key="item" :label="item.label" :value="item.label">
                        <p class="flex items-center justify-center size-5 bg-white rounded-full text-xs mt-2"
                            style="float: left">
                            <span v-html="item.icon"></span>
                        </p>
                        <span class="ml-2">{{ item.label }}</span>
                    </el-option>
                </el-select>
            </div>
            <el-tooltip
                :content="ticket.responsible?.name ? 'Responsable: ' + ticket.responsible?.name : 'Responsable: Sin asignar'"
                placement="top">
                <figure v-if="ticket.responsible"
                    class="flex justify-center items-center space-x-2 text-sm rounded-full">
                    <img class="size-10 lg:size-14 rounded-full object-cover"
                        :src="ticket.responsible?.profile_photo_url" :alt="ticket.responsible?.name">
                </figure>
                <div @click.stop="$inertia.get(route('tickets.edit', ticket.id))" v-else
                    class="rounded-full border size-14 flex items-center justify-center px-4 cursor-pointer hover:border-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-7">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                    </svg>
                </div>
            </el-tooltip>
        </section>
    </div>
</template>

<script>
import Checkbox from '@/Components/Checkbox.vue';
import axios from 'axios';

export default {
    data() {
        return {
            selected: null,
            showResponsibleModal: false,
            status: this.ticket.status,
            statuses: [
                {
                    label: 'Abierto',
                    color: 'text-[#0355B5]',
                    icon: '<i class="fa-solid fa-arrow-up text-[#0355B5]"></i>',

                },
                {
                    label: 'En espera',
                    color: 'text-[#FD8827]',
                    icon: '<i class="fa-regular fa-clock text-[#FD8827]"></i>',
                },
                {
                    label: 'En espera de 3ro',
                    color: 'text-[#B927FD]',
                    icon: '<i class="fa-regular fa-hourglass-half text-[#B927FD]"></i>',
                },
                {
                    label: 'Completado',
                    color: 'text-[#3F9C30]',
                    icon: '<i class="fa-solid fa-check text-[#3F9C30]"></i>',
                },
                {
                    label: 'Re-abierto',
                    color: 'text-[#FD4646]',
                    icon: '<i class="fa-solid fa-rotate-right text-[#FD4646]"></i>',
                },
                {
                    label: 'En proceso',
                    color: 'text-[#3D3D3D]',
                    icon: '<i class="fa-solid fa-arrow-right-to-bracket text-[#3D3D3D]"></i>',
                },
            ],
        }
    },
    components: {
        Checkbox
    },
    props: {
        ticket: Object,
        selectTicket: Boolean
    },
    emits: ['selected', 'unselected'],
    methods: {
        getFolio(ticket) {
            if (ticket.ticket_type == 'Soporte o incidencia') {
                return 'GPCI' + String(ticket.id).padStart(6, '0');
            } else {
                return 'GPCS' + String(ticket.id).padStart(6, '0');
            }
        },
        getIcon(ticket) {
            if (ticket.status === 'Abierto') {
                return '<i class="fa-solid fa-arrow-up text-[#0355B5] mr-2"></i>';
            } else if (ticket.status === 'En espera') {
                return '<i class="fa-regular fa-clock text-[#FD8827] mr-2"></i>';
            } else if (ticket.status === 'En espera de 3ro') {
                return '<i class="fa-regular fa-hourglass-half text-[#B927FD] mr-2"></i>';
            } else if (ticket.status === 'Completado') {
                return '<i class="fa-solid fa-check text-[#3F9C30] mr-2"></i>';
            } else if (ticket.status === 'Re-abierto') {
                return '<i class="fa-solid fa-rotate-right text-[#FD4646] mr-2"></i>';
            } else if (ticket.status === 'En proceso') {
                return '<i class="fa-solid fa-arrow-right-to-bracket text-[#3D3D3D] mr-2"></i>';
            }
        },
        getPriorityColor(ticket) {
            if (ticket.priority === 'Baja') {
                return 'text-[#A49C9D]';
            } else if (ticket.priority === 'Media') {
                return 'text-[#D68D1F]';
            } else if (ticket.priority === 'Alta') {
                return 'text-[#C1202A]';
            }
        },
        async updateStatus() {
            try {
                const response = await axios.put(route('tickets.update-status', this.ticket.id), { status: this.status });

                if (response.status === 200) {

                    this.ticket.status = response.data.item.status;
                    this.ticket.updated_at = response.data.item.updated_at;

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
        ticketSelection() {
            if (this.selected) {
                this.$emit('selected', this.ticket.id);
            } else {
                this.$emit('unselected', this.ticket.id);
            }
        }
    },
    watch: {
        selectTicket(newVal) {
            this.selected = newVal;
            this.ticketSelection();
        }
    }
}
</script>