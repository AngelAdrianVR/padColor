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
                <i class="fa-solid fa-check-double"></i>
                <p>Resoluciones (2)</p> 
              </div>
              <div @click="currentTab = 2" :class="currentTab == 2 ? 'text-primary border-b-2 border-primary pb-1 px-3 font-bold' : 'px-3 pb-1 text-gray66 font-semibold'" class="flex items-center space-x-2 cursor-pointer text-base">
                <i class="fa-regular fa-comment-dots"></i>
                <p>Conversaciones</p> 
              </div>
              <div @click="currentTab = 3" :class="currentTab == 3 ? 'text-primary border-b-2 border-primary pb-1 px-3 font-bold' : 'px-3 pb-1 text-gray66 font-semibold'" class="flex items-center space-x-2 cursor-pointer text-base">
                <i class="fa-solid fa-clock-rotate-left"></i>
                <p>Registro de actividad</p> 
              </div>
            </div>

            <!-- Tab 1 resoluciones ------------------------ -->
            <div class="lg:mx-7" v-if="currentTab == 1">
                <div class="flex space-x-3 mt-5">
                    <div v-if="$page.props.jetstream.managesProfilePhotos" class="flex text-sm rounded-full w-10">
                        <img class="size-9 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url"
                        :alt="$page.props.auth.user.name" />
                    </div>
                    <RichText @submitComment="storeComment(taskComponentLocal)" @content="updateSolutionComment($event)" ref="commentEditor"
                        class="flex-1" hasMedia :userList="users" :disabled="sendingComments || !solutionComment" />
                </div>
            </div>
            <!-- ----------------------------------------- -->

            <!-- Tab 2 conversaciones ------------------------ -->
            <div class="lg:mx-7" v-if="currentTab == 2">
                <div class="flex space-x-3 mt-5">
                    <div v-if="$page.props.jetstream.managesProfilePhotos" class="flex text-sm rounded-full w-10">
                        <img class="size-9 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url"
                        :alt="$page.props.auth.user.name" />
                    </div>
                    <RichText @submitComment="storeComment(taskComponentLocal)" @content="updateConversationComment($event)" ref="commentEditor"
                        class="flex-1" withFooter :userList="users" :disabled="sendingComments || !conversationComment" />
                </div>
            </div>
            <!-- ----------------------------------------- -->

        </div>
    </AppLayout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";
import ThirthButton from "@/Components/MyComponents/ThirthButton.vue";
import FileView from "@/Components/MyComponents/Ticket/FileView.vue";
import RichText from "@/Components/MyComponents/RichText.vue";
import Back from "@/Components/MyComponents/Back.vue";
import axios from 'axios';

export default {
data() {
    return {
        currentTab: 1,
        sendingComments: false,
        solutionComment: null,
        conversationComment: null,
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
RichText,
Back
},
props:{
ticket: Object,
users: Array,
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
    updateSolutionComment(content) {
      this.solutionComment = content;
    },
    updateConversationComment(content) {
      this.conversationComment = content;
    },
    async storeComment() {
      const editor = this.$refs.commentEditor;
      if (this.form.comment) {
        this.sendingComments = true;
        try {
          const response = await axios.post(route("oportunity-tasks.comment", this.taskComponentLocal.id), {
            comment: this.form.comment,
            mentions: editor.mentions,
          });
          if (response.status === 200) {
            this.taskComponentLocal?.comments.push(response.data.item);
            this.form.comment = null;
            editor.clearContent();
          }
        } catch (error) {
          console.log(error);
        } finally {
          this.sendingComments = false;
        }
      }
    },
}
}
</script>
