<template>
    <AppLayout :title="ticket.data.title">
        <div class="lg:p-9 p-1 text-sm">
            <div class="flex items-center justify-between">
                <Back />
                <PrimaryButton
                    v-if="$page.props.auth.user.permissions.includes('Editar cualquier ticket') || $page.props.auth.user.id == ticket.data.user.id"
                    @click="$inertia.get(route('tickets.edit', ticket.data.id))">Editar
                </PrimaryButton>
            </div>
            <h1 class="text-gray66 lg:mx-[72px] mt-2">Información sobre el ticket</h1>
            <div class="lg:mx-16 mt-2">
                <!-- Info general -->
                <h1 class="font-bold ml-2">{{ ticket.data.title }}</h1>
                <div class="border rounded-md  p-2 my-2">
                    <p class="text-gray66">Descripción</p>
                    <span class="text-black">{{ ticket.data.description }}</span>
                </div>
                <div class="lg:flex flex-wrap items-center space-x-3 ml-2 text-gray66 mt-3">
                    <p>Folio: <span class="text-black ml-1">#{{ getFolio(ticket.data) }}</span></p>
                    <span class="hidden lg:block">•</span>
                    <p>Creado por: <span class="text-black ml-1">
                            {{ ticket.data.user?.name }}</span></p>
                    <span class="hidden lg:block">•</span>
                    <p>Creado el: <span class="text-black ml-1">{{ ticket.data.created_at_formatted
                            }}</span></p>
                    <span class="hidden lg:block">•</span>
                    <p>Fecha límite: <span class="text-black ml-1">{{ ticket.data.expired_date
                            }}</span></p>
                    <p>Sucursal: <span class="text-black ml-1">{{ ticket.data.branch
                            }}</span></p>
                    <p>Tel: <span class="text-black ml-1">{{ ticket.data.user?.phone
                            }}</span></p>
                </div>
                <div class="lg:flex flex-wrap items-center space-x-3 ml-2 text-gray66 mt-3">
                    <p>Tipo de ticket: <span class="text-black ml-1">{{ ticket.data.ticket_type
                            }}</span></p>
                    <p>Categoría: <span class="text-black ml-1">{{ ticket.data.category.name
                            }}</span></p>
                    <span class="hidden lg:block">•</span>
                    <div class="flex items-center space-x-3">
                        <p>Prioridad: <span class="text-black ml-1">{{ ticket.data.priority
                                }}</span></p>
                        <i :class="getPriorityColor()" class="fa-solid fa-circle text-[7px]"></i>
                    </div>
                </div>
                <div class="flex justify-between my-3 ml-2">
                    <!-- Estatus -->
                    <div class="lg:flex items-center space-x-3 w-full">
                        <p class="text-gray66">Estatus:</p>
                        <div class="lg:w-[14%]">
                            <el-select @change="updateStatus" v-model="status" placeholder="Seleccione" size="small"
                                :disabled="!$page.props.auth.user.permissions.includes('Editar status de tickets')"
                                no-data-text="No hay opciones registradas"
                                no-match-text="No se encontraron coincidencias">
                                <el-option v-for="item in statuses.filter(status => status.show)" :key="item" :label="item.label" :value="item.label">
                                    <p class="flex items-center justify-center size-5 bg-white rounded-full text-xs mt-2"
                                        style="float: left">
                                        <span v-html="item.icon"></span>
                                    </p>
                                    <span class="ml-2">{{ item.label }}</span>
                                </el-option>
                            </el-select>
                        </div>
                        <span class="text-gray66 hidden lg:block">•</span>
                        <p class="text-gray66 "><span v-html="getIcon()"></span>{{ this.ticket.data.status + ' el'
                            }}: <span class="text-black ml-1">{{ ticket.data.updated_at }}</span></p>
                        <span class="text-gray66 hidden lg:block">•</span>
                        <div class="flex items-center space-x-3" v-if="ticket.data.responsible">
                            <p class="text-gray66 ">Responsable: </p>
                            <div class="flex rounded-full w-10">
                                <img class="size-9 rounded-full object-cover"
                                    :src="ticket.data.responsible.profile_photo_url"
                                    :alt="ticket.data.responsible.name" />
                            </div>
                            <p>{{ ticket.data.responsible.name }}</p>
                        </div>
                        <div v-else>
                            <p class="text-gray66 ">Sin responsable</p>
                        </div>
                        <span class="text-gray66 hidden lg:block">•</span>
                        <p class="text-gray66">
                            Tiempo de solución:
                            <span v-if="ticket.data.status == 'Completado' || ticket.data.status == 'En espera de 3ro' || ticket.data.status == 'En espera'"
                                class="text-black ml-1">
                                {{ convertMinutesToHours(ticket.data.solution_minutes ?? 0) }}</span>
                            <span v-else class="text-black ml-1">{{ tiempoTranscurrido(ticket.data.opened_at) }}</span>
                        </p>
                    </div>
                </div>
                <p class="text-gray66 px-2 py-2">Archivos adjuntos</p>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-2" v-if="ticket.data.media?.length > 0">
                    <FileView v-for="file in ticket.data.media" :key="file" :file="file" />
                </div>
                <p v-else class=" text-gray-400 mx-6 text-xs">No hay archivos adjuntos</p>
            </div>

            <el-tabs v-model="activeTab" class="lg:mx-20 mt-10">
                <el-tab-pane name="1">
                    <template #label>
                        <div class="flex items-center">
                            <i class="fa-solid fa-check-double mr-1"></i>
                            <span>Resoluciones ({{ ticket.data.solutions_count }})</span>
                        </div>
                    </template>
                    <Solutions :ticketId="this.ticket.data.id" :ticketStatus="this.ticket.data.status"
                        @updateCountSolutions="ticket.data.solutions_count++"
                        @decrementCountSolutions="ticket.data.solutions_count--" />
                </el-tab-pane>
                <el-tab-pane name="2">
                    <template #label>
                        <div class="flex items-center">
                            <i class="fa-regular fa-comment-dots mr-1"></i>
                            <span>Comentarios</span>
                        </div>
                    </template>
                    <Comments :ticketId="this.ticket.data.id" />
                </el-tab-pane>
                <el-tab-pane v-if="$page.props.auth.user.permissions.includes('Ver registro de actividad en ticket')"
                    name="3">
                    <template #label>
                        <div class="flex items-center">
                            <i class="fa-solid fa-clock-rotate-left mr-1"></i>
                            <span>Registro de actividad</span>
                        </div>
                    </template>
                    <Historical :ticketId="this.ticket.data.id" />
                </el-tab-pane>
            </el-tabs>
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SolutionGlove from "@/Components/MyComponents/TicketSolution/SolutionGlove.vue";
import FileView from "@/Components/MyComponents/Ticket/FileView.vue";
import RichText from "@/Components/MyComponents/RichText.vue";
import Comment from "@/Components/MyComponents/Ticket/Comment.vue";
import Back from "@/Components/MyComponents/Back.vue";
import Solutions from "./Tabs/Solutions.vue";
import Comments from "./Tabs/Comments.vue";
import Historical from "./Tabs/Historical.vue";
import axios from 'axios';
import { differenceInMinutes } from 'date-fns';

export default {
    data() {
        return {
            activeTab: '1',
            status: this.ticket.data.status,
            statuses: [
                {
                    label: 'Abierto',
                    color: 'text-[#0355B5]',
                    icon: '<i class="fa-solid fa-arrow-up text-[#0355B5]"></i>',
                    show: ['Abierto'].includes(this.ticket.data.status)
                },
                {
                    label: 'En proceso',
                    color: 'text-[#3D3D3D]',
                    icon: '<i class="fa-solid fa-arrow-right-to-bracket text-[#3D3D3D]"></i>',
                    show: ['En proceso', 'En espera', 'En espera de 3ro', 'Abierto', 'Re-abierto'].includes(this.ticket.data.status)
                },
                {
                    label: 'En espera',
                    color: 'text-[#FD8827]',
                    icon: '<i class="fa-regular fa-clock text-[#FD8827]"></i>',
                    show: ['En espera', 'Abierto', 'Re-abierto', 'En proceso'].includes(this.ticket.data.status)
                },
                {
                    label: 'En espera de 3ro',
                    color: 'text-[#B927FD]',
                    icon: '<i class="fa-regular fa-hourglass-half text-[#B927FD]"></i>',
                    show: ['En espera de 3ro', 'Abierto', 'Re-abierto', 'En proceso'].includes(this.ticket.data.status)
                },
                {
                    label: 'Completado',
                    color: 'text-[#3F9C30]',
                    icon: '<i class="fa-solid fa-check text-[#3F9C30]"></i>',
                    show: ['Completado', 'En espera', 'En espera de 3ro', 'En proceso'].includes(this.ticket.data.status)
                },
                {
                    label: 'Re-abierto',
                    color: 'text-[#FD4646]',
                    icon: '<i class="fa-solid fa-rotate-right text-[#FD4646]"></i>',
                    show: ['Re-abierto', 'Completado'].includes(this.ticket.data.status)
                },
            ],
        }
    },
    components: {
        AppLayout,
        PrimaryButton,
        SolutionGlove,
        FileView,
        RichText,
        Comment,
        Back,
        Solutions,
        Comments,
        Historical,
    },
    props: {
        ticket: Object,
        solutions_count: Number,
    },
    methods: {
        setShowPropStatuses() {
            this.statuses[0].show = ['Abierto'].includes(this.ticket.data.status);
            this.statuses[1].show = ['En proceso', 'En espera', 'En espera de 3ro', 'Abierto', 'Re-abierto'].includes(this.ticket.data.status);
            this.statuses[2].show = ['En espera', 'Abierto', 'Re-abierto', 'En proceso'].includes(this.ticket.data.status);
            this.statuses[3].show = ['En espera de 3ro', 'Abierto', 'Re-abierto', 'En proceso'].includes(this.ticket.data.status);
            this.statuses[4].show = ['Completado', 'En espera', 'En espera de 3ro', 'En proceso'].includes(this.ticket.data.status);
            this.statuses[5].show = ['Re-abierto', 'Completado'].includes(this.ticket.data.status);
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
        tiempoTranscurrido(fecha) {
            const fechaInicio = new Date(fecha);
            const fechaActual = new Date();

            let minutosTranscurridos = differenceInMinutes(fechaActual, fechaInicio);
            minutosTranscurridos += this.ticket.data.solution_minutes; // Sumar los minutos adicionales

            let dias = Math.floor(minutosTranscurridos / (60 * 24)); // Calcular días completos
            let horas = Math.floor((minutosTranscurridos % (60 * 24)) / 60);
            let minutos = minutosTranscurridos % 60;

            if (dias > 0) {
                horas = String(horas).padStart(2, '0');
                minutos = String(minutos).padStart(2, '0');
                return `${dias} día${dias > 1 ? 's' : ''} ${horas}:${minutos}`;
            } else {
                horas = String(horas).padStart(2, '0');
                minutos = String(minutos).padStart(2, '0');
                return `${horas}:${minutos}`;
            }
        },
        getFolio(ticket) {
            if (ticket.ticket_type == 'Soporte o incidencia') {
                return 'GPCI' + String(ticket.id).padStart(6, '0');
            } else {
                return 'GPCS' + String(ticket.id).padStart(6, '0');
            }
        },
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
        async fetchHistorical() {
            this.loading = true;
            try {
                const response = await axios.get(route("tickets.fetch-history", this.ticketId));
                if (response.status === 200) {
                    this.historical = response.data.items;
                }
            } catch (error) {
                console.log(error);

            } finally {
                this.loading = false;
            }
        },
        async updateStatus() {
            try {
                const response = await axios.put(route('tickets.update-status', this.ticket.data.id), { status: this.status });

                if (response.status === 200) {
                    this.ticket.data.status = response.data.item.status;
                    this.ticket.data.updated_at = response.data.item.updated_at;
                    this.ticket.data.closed_at = response.data.item.closed_at;
                    this.ticket.data.puased_at = response.data.item.paused_at;
                    this.ticket.data.opened_at = response.data.item.opened_at;
                    this.ticket.data.solution_minutes = response.data.item.solution_minutes;
                    this.setShowPropStatuses();

                    this.$notify({
                        title: "Correcto",
                        message: "Se ha actualizado el estatus",
                        type: "success",
                    });
                    this.ticketId = this.ticket.data.id;
                    this.fetchHistorical();
                }
            } catch (error) {
                console.log('error', error);
                this.$notify({
                   title: "Servidor",
                   message: "No se pudo enviar notificación al correo " + this.ticket.data.user?.email + ", perteneciente a " + this.ticket.data.user?.name + ". Revisar si es correo válido",
                   type: "error",
                });
            }
        },

    },
}
</script>
