<template>
    <AppLayout :title="ticket.data.title">
        <div class="lg:p-9 p-1">
            <div class="flex items-center justify-between">
                <Back />
                <p class="text-gray66">Información sobre el ticket</p>
                <ThirthButton @click="$inertia.get(route('tickets.edit', ticket.data.id))">Editar</ThirthButton>
            </div>
            <div class="lg:mx-16 mt-5">
                <!-- Info general -->
                <h1 class="font-bold text-lg my-2 ml-2">{{ ticket.data.title }}</h1>
                <div class="border rounded-md text-sm p-2 my-2">
                    <p class="text-gray66">Descripción</p>
                    <span class="text-black">{{ ticket.data.description }}</span>
                </div>
                <div class="lg:flex items-center space-x-3">
                    <p class="text-gray66 text-sm">Folio: <span class="text-black ml-1">#{{ ticket.data.id }}</span></p>
                    <i class="fa-solid fa-circle text-[3px]"></i>
                    <p class="text-gray66 text-sm">Creado por: <span class="text-black ml-1">{{
                        ticket.data.responsible?.name }}</span></p>
                    <i class="fa-solid fa-circle text-[3px]"></i>
                    <p class="text-gray66 text-sm">Creado el: <span class="text-black ml-1">{{ ticket.data.created_at
                    }}</span></p>
                    <i class="fa-solid fa-circle text-[3px]"></i>
                    <p class="text-gray66 text-sm">Fecha límite: <span class="text-black ml-1">{{ ticket.data.expired_date
                    }}</span></p>
                    <i class="fa-solid fa-circle text-[3px]"></i>
                    <div class="flex items-center space-x-3">
                        <p class="text-gray66 text-sm">Prioridad: <span class="text-black ml-1">{{ ticket.data.priority
                        }}</span></p>
                        <i :class="getPriorityColor()" class="fa-solid fa-circle text-[7px]"></i>
                    </div>
                </div>
                <div class="flex justify-between my-2">
                    <!-- Estatus -->
                    <div class="flex items-center space-x-3 w-full">
                        <p class="text-gray66 text-sm">Estatus:</p>
                        <div class="lg:w-1/4">
                            <el-select @change="updateStatus" v-model="status" placeholder="Seleccione"
                                no-data-text="No hay opciones registradas" no-match-text="No se encontraron coincidencias">
                                <el-option v-for="item in statuses" :key="item" :label="item.label" :value="item.label">
                                    <p class="w-4" style="float: left">
                                        <span v-html="item.icon"></span>
                                    </p>
                                    <span class="ml-2">{{ item.label }}</span>
                                </el-option>
                            </el-select>
                        </div>
                        <i class="fa-solid fa-circle text-[3px]"></i>
                        <p class="text-gray66 text-sm"><span v-html="getIcon()"></span>{{ this.ticket.data.status + ' el'
                        }}: <span class="text-black ml-1">{{ ticket.data.updated_at }}</span></p>
                        <i class="fa-solid fa-circle text-[3px]"></i>
                        <div class="flex items-center space-x-3">
                            <p class="text-gray66 text-sm">Responsable: </p>
                            <div class="flex text-sm rounded-full w-10">
                                <img class="size-9 rounded-full object-cover"
                                    :src="ticket.data.responsible.profile_photo_url" :alt="ticket.data.responsible.name" />
                            </div>
                            <p class="text-sm">{{ ticket.data.responsible.name }}</p>
                        </div>
                    </div>
                </div>

                <p class="text-gray66 text-sm py-2">Archivos adjuntos</p>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-2" v-if="ticket.data.media?.length > 0">
                    <FileView v-for="file in ticket.data.media" :key="file" :file="file" />
                </div>
                <p v-else class="text-sm text-gray-400">No hay archivos adjuntos</p>
            </div>

            <el-tabs v-model="activeTab" @tab-click="handleClick">
                <el-tab-pane name="1">
                    <template #label>
                        <div class="flex items-center">
                            <i class="fa-solid fa-check-double mr-1"></i>
                            <span>Resoluciones ({{ ticketSolutions?.length ?? '0' }})</span>
                        </div>
                    </template>
                    <Solutions />
                </el-tab-pane>
                <el-tab-pane name="2">
                    <template #label>
                        <div class="flex items-center">
                            <i class="fa-regular fa-comment-dots mr-1"></i>
                            <span>Conversaciones</span>
                        </div>
                    </template>
                    <Comments />
                </el-tab-pane>
                <el-tab-pane name="3">
                    <template #label>
                        <div class="flex items-center">
                            <i class="fa-solid fa-clock-rotate-left mr-1"></i>
                            <span>Registro de actividad</span>
                        </div>
                    </template>
                    <Historical />
                </el-tab-pane>
            </el-tabs>
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";
import ThirthButton from "@/Components/MyComponents/ThirthButton.vue";
import SolutionGlove from "@/Components/MyComponents/TicketSolution/SolutionGlove.vue";
import FileView from "@/Components/MyComponents/Ticket/FileView.vue";
import RichText from "@/Components/MyComponents/RichText.vue";
import Comment from "@/Components/MyComponents/Ticket/Comment.vue";
import Back from "@/Components/MyComponents/Back.vue";
import Solutions from "./Tabs/Solutions.vue";
import Comments from "./Tabs/Comments.vue";
import Historical from "./Tabs/Historical.vue";
import axios from 'axios';

export default {
    data() {
        return {
            currentTab: 1,
            loading: false,
            // solutionDescription: null, //texto para la solución 
            solutionMedia: null, //Archivos adjuntos para la solución
            conversationComment: null,
            conversation: null, //todos los comentarios del ticket
            ticketSolutions: null, //Todas las soluciones
            ticketHistory: null, //Toda la actividad del ticket
            status: this.ticket.data.status,
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
        AppLayout,
        ThirthButton,
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
        users: Array,
    },
    methods: {
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
                    this.fetchHistory();
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
        // updateSolutionDescription(content) {
        //     this.solutionDescription = content;
        // },
        updateConversationComment(content) {
            this.conversationComment = content;
        },
        async storeComment() {
            const editor = this.$refs.commentEditor;
            this.loading = true;
            try {
                const response = await axios.post(route("tickets.comment", this.ticket.data.id), {
                    comment: this.conversationComment,
                    mentions: editor.mentions,
                });
                if (response.status === 200) {
                    // this.taskComponentLocal?.comments.push(response.data.item);
                }
            } catch (error) {
                console.log(error);
            } finally {
                this.loading = false;
                this.conversationComment = null;
                // editor.clearContent();
                this.fetchConversation(); //recupera los comentarios de nuevo
            }
        },
        // async storeSolution(solutionMedia) {
        //     this.loading = true;
        //     try {
        //         const response = await axios.post(route("ticket-solutions.store"), {
        //             ticketId: this.ticket.data.id,
        //             solutionDescription: this.solutionDescription,
        //             solution_media: solutionMedia,
        //         });
        //         if (response.status === 200) {
        //             console.log(response);
        //             this.$notify({
        //                 title: "Correcto",
        //                 message: "Se ha publicado tu solución",
        //                 type: "success",
        //             });
        //             this.solutionDescription = null;
        //         }
        //     } catch (error) {
        //         console.log(error);
        //         this.$notify({
        //             title: "Error de servidor",
        //             message: "Hubo un problema al publicar tu solución. Inténta más tarde",
        //             type: "error",
        //         });

        //     } finally {
        //         this.loading = false;
        //         this.fetchSolutions(); //recupera las soluciones de nuevo
        //     }
        // },
        // async fetchSolutions() {
        //     this.loading = true;
        //     try {
        //         const response = await axios.get(route("ticket-solutions.fetch-solutions", this.ticket.data.id));
        //         if (response.status === 200) {
        //             this.ticketSolutions = response.data.items;
        //         }
        //     } catch (error) {
        //         console.log(error);
        //     } finally {
        //         this.loading = false;
        //     }
        // },
        // async fetchConversation() {
        //     this.loading = true;
        //     try {
        //         const response = await axios.get(route("tickets.fetch-conversation", this.ticket.data.id));
        //         if (response.status === 200) {
        //             this.conversation = response.data.items;
        //         }
        //     } catch (error) {
        //         console.log(error);
        //         this.$notify({
        //             title: "Error de servidor",
        //             message: "Hubo un problema al publicar tu solución. Inténta más tarde",
        //             type: "error",
        //         });

        //     } finally {
        //         this.loading = false;
        //     }
        // },
        async fetchHistory() {
            this.loading = true;
            try {
                const response = await axios.get(route("tickets.fetch-history", this.ticket.data.id));
                if (response.status === 200) {
                    this.ticketHistory = response.data.items;
                    this.loading = false;
                    this.fetchSolutions(); //recupera las soluciones de nuevo
                }
            } catch (error) {
                console.log(error);

            } finally {
                this.loading = false;
            }
        },

        // solutionDeleted(solutionId) {
        //     const indexToDelete = this.ticketSolutions.findIndex(item => item.id === solutionId);

        //     if (indexToDelete !== -1) {
        //         this.ticketSolutions.splice(indexToDelete, 1);
        //     }
        // },
        commentDeleted(commentId) {
            const indexToDelete = this.conversation.findIndex(item => item.id === commentId);

            if (indexToDelete !== -1) {
                this.conversation.splice(indexToDelete, 1);
            }
        },
    },
    mounted() {
        // this.fetchSolutions(); //carga todas las soluciones
        // this.fetchConversation(); //carga todos los comentarios
        // this.fetchHistory(); //carga todo el historial de actividad
    }
}
</script>
