<template>
    <div class="mt-8 min-h-28">
        <Loading v-if="loading" class="mt-10" />
        <div v-else>
            <!-- Alerta de Ticket Cerrado -->
            <el-alert 
                v-if="ticketStatus == 'Completado'" 
                title="Ticket Completado"
                type="error" 
                show-icon 
                :closable="false"
                class="mb-4">
                Para poder agregar más soluciones es necesario marcar como "Re-abierto" este ticket
            </el-alert>

            <!-- Área de escritura de solución -->
            <div v-if="$page.props.auth.user.permissions.includes('Crear resoluciones') && ticketStatus != 'Completado'" class="mt-5">
                <!-- Alerta de advertencia de imágenes -->
                <el-alert
                    type="warning"
                    show-icon
                    :closable="false"
                    class="mb-2"
                >
                    <template #title>
                        Por favor, <b>no pegues imágenes</b> directamente en el cuadro de texto. Usa el botón de clip adjunto para subir imágenes.
                    </template>
                </el-alert>

                <div class="flex space-x-3">
                    <RichText 
                        @submitComment="storeSolution" 
                        @content="updateDescription($event)" 
                        ref="mySolution"
                        class="flex-1" 
                        hasMedia 
                        :userList="users" 
                        :disabled="loading || !description" 
                    />
                </div>
            </div>

            <SolutionGlove 
                v-for="(solution, index) in solutions" 
                :key="solution.id" 
                :solution="solution" 
                :index="index"
                @solution-deleted="solutionDeleted" 
            />
        </div>
    </div>
</template>

<script>
import SolutionGlove from "@/Components/MyComponents/TicketSolution/SolutionGlove.vue";
import RichText from "@/Components/MyComponents/RichText.vue";
import Loading from "@/Components/MyComponents/Loading.vue";
import axios from 'axios';

export default {
    data() {
        return {
            solutions: [],
            loading: true,
            description: null,
            users: [],
        }
    },
    emits: ['updateCountSolutions', 'decrementCountSolutions'],
    components: {
        SolutionGlove,
        RichText,
        Loading,
    },
    props: {
        ticketId: [String, Number], // Ajusté a String o Number por seguridad
        ticketStatus: String,
    },
    methods: {
        solutionDeleted(solutionId) {
            const indexToDelete = this.solutions.findIndex(item => item.id === solutionId);

            if (indexToDelete !== -1) {
                this.solutions.splice(indexToDelete, 1);
                this.$emit('decrementCountSolutions');
            }
        },
        updateDescription(content) {
            this.description = content;
        },
        async storeSolution() {
            this.loading = true;
            try {
                const response = await axios.post(route("ticket-solutions.store"), {
                    ticketId: this.ticketId,
                    description: this.description,
                    media: this.$refs.mySolution.media,
                }, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                });
                if (response.status === 200) {
                    this.$notify({
                        title: "Correcto",
                        message: "Se ha publicado tu solución",
                        type: "success",
                    });
                    this.description = null;
                    // Limpiar el editor si tiene un método para ello, o resetear la key
                    this.$emit('updateCountSolutions');
                }
            } catch (error) {
                console.log(error);
                // Manejo de errores de validación (422) específico para la imagen pegada
                if (error.response && error.response.status === 422) {
                    const errors = error.response.data.errors;
                    let msg = "Error de validación";
                    if(errors.description) msg = errors.description[0];
                    
                    this.$notify({
                        title: "No se pudo guardar",
                        message: msg,
                        type: "error",
                    });
                } else {
                    this.$notify({
                        title: "Error de servidor",
                        message: "Hubo un problema al publicar tu solución. Inténta más tarde",
                        type: "error",
                    });
                }

            } finally {
                this.loading = false;
                this.fetchSolutions(); //recupera las soluciones de nuevo
            }
        },
        async fetchSolutions() {
            this.loading = true;
            try {
                const response = await axios.get(route("ticket-solutions.fetch-solutions", this.ticketId));
                if (response.status === 200) {
                    this.solutions = response.data.items;
                }
            } catch (error) {
                console.log(error);
            } finally {
                this.loading = false;
            }
        },
        async fetchUsers() {
            this.loading = true;
            try {
                const response = await axios.get(route("users.get-all"));
                if (response.status === 200) {
                    this.users = response.data.items;
                }
            } catch (error) {
                console.log(error);
            } finally {
                this.loading = false;
            }
        },
    },
    mounted() {
        this.fetchSolutions();
        this.fetchUsers();
    }
}
</script>