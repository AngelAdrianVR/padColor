<template>
    <div class="flex space-x-3 mb-4 group">
        <!-- Foto de perfil -->
        <div class="flex-shrink-0">
            <img class="h-10 w-10 rounded-full object-cover" :src="comment.user.profile_photo_url"
                :alt="comment.user.name" />
        </div>

        <!-- Contenido del comentario -->
        <div class="flex-1 bg-gray-50 rounded-lg px-4 py-3 relative border border-gray-100">
            <div class="flex justify-between items-start">
                <div class="flex items-center space-x-2">
                    <span class="font-bold text-sm text-gray-800">{{ comment.user.name }}</span>
                    <!-- Fecha: Usamos el string directo del backend para evitar errores de JS -->
                    <span v-if="!in_edition" class="text-xs text-gray-500">{{ comment.created_at }}</span>
                </div>

                <!-- Botones de acción (Editar / Eliminar) -->
                <div v-if="$page.props.auth.user.id === comment.user.id || $page.props.auth.user.permissions.includes('Eliminar comentarios')" 
                     class="flex items-center space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                    
                    <!-- Botón Editar (Solo dueño) -->
                    <button v-if="$page.props.auth.user.id === comment.user.id && !in_edition" 
                            @click="in_edition = true"
                            class="text-gray-400 hover:text-blue-500 transition-colors" 
                            title="Editar">
                        <i class="fa-solid fa-pencil text-xs"></i>
                    </button>

                    <!-- Botones Guardar/Cancelar Edición -->
                    <div v-if="in_edition" class="flex items-center space-x-1">
                        <PrimaryButton @click="update" class="!py-1 !px-2 !text-xs">Guardar</PrimaryButton>
                        <button @click="cancelEdit" class="text-xs text-gray-500 hover:text-gray-700 px-2">Cancelar</button>
                    </div>

                    <!-- Botón Eliminar -->
                    <el-popconfirm 
                        v-if="!in_edition"
                        confirm-button-text="Sí" 
                        cancel-button-text="No" 
                        icon-color="#ff4d4d" 
                        title="¿Eliminar comentario?"
                        @confirm="deleteItem">
                        <template #reference>
                            <button class="text-gray-400 hover:text-red-500 transition-colors" title="Eliminar">
                                <i class="fa-regular fa-trash-can text-xs"></i>
                            </button>
                        </template>
                    </el-popconfirm>
                </div>
            </div>

            <!-- Cuerpo del mensaje o Input de Edición -->
            <div class="mt-1">
                <div v-if="in_edition">
                    <!-- Usamos input nativo o el-input dependiendo de tu preferencia, aquí mantengo el estilo simple -->
                    <textarea
                        v-model="form.body"
                        class="w-full text-sm border-gray-300 rounded-md focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50"
                        rows="3"
                        placeholder="Edita tu comentario"
                    ></textarea>
                </div>
                <div v-else class="text-sm text-gray-700 prose prose-sm max-w-none break-words" v-html="comment.body"></div>
            </div>

            <!-- SECCIÓN DE ARCHIVOS ADJUNTOS (NUEVO) -->
            <!-- Solo se muestra si NO se está editando y SI hay archivos -->
            <div v-if="!in_edition && comment.media && comment.media.length > 0" class="mt-3 pt-3 border-t border-gray-200">
                <p class="text-[10px] text-gray-400 mb-2 uppercase font-bold tracking-wider">Adjuntos:</p>
                
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                    <div v-for="file in comment.media" :key="file.id" class="relative group/file">

                        <!-- OPCIÓN A: Si es imagen, mostramos preview -->
                        <div v-if="isImage(file)"
                            class="relative h-24 w-full rounded-lg overflow-hidden border border-gray-200 cursor-pointer bg-white shadow-sm hover:shadow-md transition-shadow"
                            @click="openFile(file.original_url)">
                            <img :src="file.original_url"
                                class="h-full w-full object-cover transition-transform duration-300 group-hover/file:scale-105" />
                            <!-- Overlay oscuro al pasar el mouse -->
                            <div class="absolute inset-0 bg-black/0 group-hover/file:bg-black/20 transition-colors flex items-center justify-center">
                                <i class="fa-solid fa-magnifying-glass-plus text-white opacity-0 group-hover/file:opacity-100 drop-shadow-md transform scale-75 group-hover/file:scale-100 transition-all"></i>
                            </div>
                        </div>

                        <!-- OPCIÓN B: Si es otro archivo (PDF, Word, etc), mostramos icono -->
                        <div v-else
                            class="h-24 w-full rounded-lg border border-gray-200 bg-white flex flex-col items-center justify-center p-2 cursor-pointer hover:bg-gray-50 hover:border-primary/50 transition-all shadow-sm group-hover/file:shadow-md"
                            @click="openFile(file.original_url)">
                            
                            <!-- Icono según tipo -->
                            <i v-if="file.mime_type.includes('pdf')" class="fa-solid fa-file-pdf text-2xl text-red-500 mb-1"></i>
                            <i v-else-if="file.mime_type.includes('word') || file.mime_type.includes('document')" class="fa-solid fa-file-word text-2xl text-blue-500 mb-1"></i>
                            <i v-else-if="file.mime_type.includes('sheet') || file.mime_type.includes('excel')" class="fa-solid fa-file-excel text-2xl text-green-500 mb-1"></i>
                            <i v-else class="fa-solid fa-file-lines text-2xl text-gray-400 mb-1"></i>
                            
                            <span class="text-[10px] text-gray-600 text-center line-clamp-2 w-full break-words leading-tight px-1 font-medium">
                                {{ file.file_name }}
                            </span>
                            <span class="text-[9px] text-gray-400 mt-1 bg-gray-100 px-1 rounded">{{ formatBytes(file.size) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { useForm } from "@inertiajs/vue3";
import axios from 'axios';

export default {
    components: {
        PrimaryButton
    },
    props: {
        comment: Object
    },
    emits: ['comment-deleted'],
    data() {
        const form = useForm({
            body: this.comment.body,
        });
        return {
            form,
            in_edition: false
        }
    },
    methods: {
        update() {
            this.form.put(route("comments.update", this.comment.id), {
                onSuccess: () => {
                    this.$notify({
                        title: "Correcto",
                        message: "Se ha editado tu comentario",
                        type: "success",
                    });
                    this.in_edition = false;
                    this.comment.body = this.form.body; // Actualizar vista localmente
                },
                onError: (error) => {
                    console.log(error);
                    let msg = "No se pudo actualizar";
                    if (error.body) msg = error.body;
                    
                    this.$notify({
                        title: "Error",
                        message: msg,
                        type: "error",
                    });
                }
            });
        },
        cancelEdit() {
            this.in_edition = false;
            this.form.body = this.comment.body; // Revertir cambios
            this.form.clearErrors();
        },
        async deleteItem() {
            try {
                const response = await axios.delete(route('comments.destroy', this.comment.id));
                if (response.status === 200) {
                    this.$emit('comment-deleted', this.comment.id);
                    this.$notify({
                        title: "Correcto",
                        message: "Se ha eliminado tu comentario",
                        type: "success",
                    });
                }
            } catch (error) {
                console.log(error);
                this.$notify({
                    title: "Error de servidor",
                    message: "No se pudo eliminar tu comentario. Inténtalo de nuevo más tarde",
                    type: "error",
                });
            }
        },
        // --- LÓGICA PARA ARCHIVOS ---
        isImage(file) {
            return file.mime_type?.startsWith('image/');
        },
        openFile(url) {
            window.open(url, '_blank');
        },
        formatBytes(bytes, decimals = 1) {
            if (!+bytes) return '0 Bytes';
            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`;
        },
    }
}
</script>