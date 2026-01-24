<template>
    <div class="mt-8 mb-20">
        <!-- Envolvemos Loading en un div para evitar el warning de atributos -->
        <div v-if="loading" class="mt-10">
            <Loading />
        </div>
        <div v-else>
            <div v-if="$page.props.auth.user.permissions.includes('Crear comentarios en ticket')" class="mt-5">
                <!-- Mensaje de advertencia para imágenes -->
                <p class="text-xs text-amber-600 mb-1 ml-1"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Por favor, <b>no pegues imágenes</b> directamente. Usa el clip adjunto.</p>
                
                <div class="flex space-x-3">
                    <RichText 
                        @submitComment="storeComment()" 
                        @content="updateComment($event)" 
                        ref="commentEditor"
                        class="flex-1" 
                        hasMedia 
                        :userList="users" 
                        :disabled="loading || !comment" 
                    />
                </div>
            </div>

            <!-- Lista de comentarios -->
            <Comment class="mt-5 mx-9" v-for="comment in conversation" :key="comment.id" :comment="comment"
                @comment-deleted="commentDeleted" />
        </div>
    </div>
</template>

<script>
import Comment from "@/Components/MyComponents/Ticket/Comment.vue";
import RichText from "@/Components/MyComponents/RichText.vue";
import Loading from "@/Components/MyComponents/Loading.vue";
import axios from 'axios';

export default {
    data() {
        return {
            conversation: [],
            loading: true,
            comment: null,
            users: [],
        }
    },
    components: {
        Comment,
        RichText,
        Loading,
    },
    props: {
        ticketId: [String, Number],
    },
    methods: {
        updateComment(content) {
            this.comment = content;
        },
        commentDeleted(commentId) {
            const indexToDelete = this.conversation.findIndex(item => item.id === commentId);

            if (indexToDelete !== -1) {
                this.conversation.splice(indexToDelete, 1);
            }
        },
        async storeComment() {
            // 1. Capturamos los datos del editor ANTES de activar el loading
            // (porque al activar loading, el componente se destruye y perdemos el acceso)
            const editor = this.$refs.commentEditor;
            let mentions = [];
            let media = [];

            if (editor) {
                mentions = editor.mentions || [];
                media = editor.media || [];
            }

            this.loading = true; // Ahora sí activamos la carga

            // Preparar FormData para enviar archivos
            let formData = new FormData();
            formData.append('comment', this.comment);
            
            if (mentions.length > 0) {
                formData.append('mentions', JSON.stringify(mentions));
            }

            if (media.length > 0) {
                media.forEach(file => {
                    formData.append('media[]', file);
                });
            }

            let requestSuccess = false;

            try {
                const response = await axios.post(route("tickets.comment", this.ticketId), formData, {
                    headers: { 'Content-Type': 'multipart/form-data' },
                });
                
                if (response.status === 200) {
                    requestSuccess = true;
                }
            } catch (error) {
                console.log(error);
                // Manejo de errores de validación (422)
                if (error.response && error.response.status === 422) {
                    const errors = error.response.data.errors;
                    let msg = "Error de validación";
                    if(errors.comment) msg = errors.comment[0];
                    
                    this.$notify({
                        title: "No se pudo guardar",
                        message: msg,
                        type: "error",
                    });
                } else {
                    this.$notify({
                        title: "Error de servidor",
                        message: "Hubo un problema al publicar tu comentario. Inténta más tarde",
                        type: "error",
                    });
                }
            } finally {
                if (requestSuccess) {
                    this.$notify({
                        title: "Comentario agregado",
                        message: "Se ha publicado tu comentario correctamente.",
                        type: "success",
                    });
                    this.comment = null;
                    // NOTA: Eliminamos 'editor.clearContent()' porque causaba error. 
                    // Al reactivar el componente (loading = false), este nace limpio automáticamente.
                    await this.fetchConversation(); 
                }
                this.loading = false;
            }
        },
        async fetchUsers() {
            try {
                const response = await axios.get(route("users.get-all"));
                if (response.status === 200) {
                    this.users = response.data.items;
                }
            } catch (error) {
                console.log(error);
            }
        },
        async fetchConversation() {
            try {
                const response = await axios.get(route("tickets.fetch-conversation", this.ticketId));
                if (response.status === 200) {
                    this.conversation = response.data.items;
                }
            } catch (error) {
                console.log(error);
            } finally {
                this.loading = false; 
            }
        },
    },
    mounted() {
        this.fetchConversation();
        this.fetchUsers();
    }
}
</script>