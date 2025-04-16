<template>
  <AppLayout title="Máquinas">
    <main class="px-2 lg:px-14">
        <div>
            <h1 class="font-bold">Máquinas</h1>
            <!-- Buscador -->
            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center mt-4">
                <div class="lg:w-1/4 relative lg:mr-12">
                    <input v-model="searchTemp" @keyup.enter="handleSearch" class="input w-full pl-9"
                        placeholder="Buscar producto" type="search">
                    <i class="fa-solid fa-magnifying-glass text-xs text-gray99 absolute top-[10px] left-4"></i>
                </div>
                <PrimaryButton v-if="this.$page.props.auth.user.permissions.includes('Crear máquinas')" @click="showCreateModal = true">Agregar máquina</PrimaryButton>
            </div>
            <div class="mt-2 text-center">
                <el-tag v-if="search" size="large" closable @close="handleTagClose">
                    Estas buscando: <b>{{ search }}</b>
                </el-tag>
            </div>

            <!-- pagination -->
            <div class="overflow-auto mb-2 mt-8">
                <PaginationWithNoMeta :pagination="machines" class="py-2" />
            </div>

            <Loading v-if="loading" />

            <el-table v-else :data="filteredMachines.data" @row-click="handleRowClick" max-height="670" style="width: 100%" class="mt-4"
                :row-class-name="tableRowClassName">
                <!-- <el-table-column type="selection" width="45" /> -->
                <el-table-column prop="id" label="ID" />
                <el-table-column prop="name" label="Nombre de la máquina" />
                <el-table-column prop="created_at" label="Creado el">
                    <template #default="scope">
                        <span>{{ formatDate(scope.row.created_at) }}</span>
                    </template>
                </el-table-column>
                <el-table-column prop="created_by" label="Creado por" />
                <el-table-column prop="description" label="Descripción" />
                <el-table-column align="right">
                    <template #default="scope">
                        <el-dropdown trigger="click" @command="handleCommand">
                            <button @click.stop
                                class="el-dropdown-link mr-3 justify-center items-center size-8 rounded-full text-primary hover:bg-gray2 transition-all duration-200 ease-in-out">
                                <i class="fa-solid fa-ellipsis-vertical"></i>
                            </button>
                            <template #dropdown>
                                <el-dropdown-menu>
                                    <el-dropdown-item class="!text-xs"
                                        v-if="$page.props.auth.user.permissions.includes('Editar productos')"
                                        :command="'edit-' + scope.row.id">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-4 mr-2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                        </svg>
                                        Editar</el-dropdown-item>
                                    <el-dropdown-item class="!text-xs"
                                        v-if="$page.props.auth.user.permissions.includes('Crear productos')"
                                        :command="'clone-' + scope.row.id">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-4 mr-2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                        </svg>
                                        Clonar</el-dropdown-item>
                                    <el-dropdown-item class="!text-xs"
                                        v-if="$page.props.auth.user.permissions.includes('Eliminar productos')"
                                        :command="'delete-' + scope.row.id">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-4 mr-2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                        Eliminar
                                    </el-dropdown-item>
                                </el-dropdown-menu>
                            </template>
                        </el-dropdown>
                    </template>
                </el-table-column>
            </el-table>
        </div>
    </main>

    <DialogModal :show="showCreateModal" @close="showCreateModal = false">
        <template #title>
            <h1 class="font-bold text-sm">Crear máquina</h1>
        </template>
        <template #content>
            <div>
                <div class="mb-2">
                    <InputLabel value="Nombre de la máquina*" />
                    <el-input v-model="form.name" placeholder="Ej. Suajadora" type="text" />                    
                    <InputError :message="form.errors.name" />
                </div>

                <div class="mb-2">
                    <InputLabel value="Descripción" />
                    <el-input v-model="form.description" :autosize="{ minRows: 3, maxRows: 5 }" type="textarea"
                        :maxlength="500" placeholder="Agrega una descripción a la máquina"
                        show-word-limit clearable />
                    <InputError :message="form.errors.description" />
                </div>

                <div class="mb-2">
                    <InputLabel value="Imagen de la máquina" class="ml-3 mb-1" />
                    <InputFilePreview @imagen="saveImage" @cleared="clearImage" />
                </div>
            </div>
        </template>
        <template #footer>
            <PrimaryButton @click="store()" :disabled="form.processing">
                <i v-if="form.processing" class="fa-solid fa-circle-notch fa-spin mr-2"></i>
                Continuar
            </PrimaryButton>
        </template>
    </DialogModal>
  </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import PaginationWithNoMeta from "@/Components/MyComponents/PaginationWithNoMeta.vue";
import InputFilePreview from "@/Components/MyComponents/InputFilePreview.vue";
import Loading from "@/Components/MyComponents/Loading.vue";
import DialogModal from "@/Components/DialogModal.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';

export default {
data() {

    const form = useForm({
        name: null,
        description: null,
        image: null,
    });

    return {
        form,
        loading: false,
        search: null,
        searchTemp: null,
        filteredMachines: this.machines,
        showCreateModal: false
    }
},
components: {
    Loading,
    AppLayout,
    InputLabel,
    InputError,
    DialogModal,
    PrimaryButton,
    InputFilePreview,
    PaginationWithNoMeta,
},
props: {
    machines: Object,
},
methods: {
    store() {
        this.form.post(route('machines.store'), {
            onSuccess: () => {
                this.form.reset();
                this.$notify({
                    title: 'Máquina agregada correctamente',
                    message: '',
                    type: 'success',
                });
                this.showCreateModal = false;
                window.location.reload();
            },
            onError: () => {
                console.log(this.form.errors);
            },
        });
    },
    formatDate(date) {
        const months = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
        const d = new Date(date);
        const day = String(d.getDate()).padStart(2, '0');
        const month = months[d.getMonth()];
        const year = d.getFullYear();

        return `${day}-${month}-${year}`;
    },
    async handleSearch() {
        this.loading = true;
        this.search = this.searchTemp;
        this.searchTemp = null;
        try {
            if (!this.search) {
                this.filteredMachines = this.machines;
            } else {
                const response = await axios.post(route('machines.get-matches', { query: this.search }));
                if (response.status === 200) {
                    this.filteredMachines = response.data.items;
                }
            }
        } catch (error) {
            console.log(error);
            this.$message({
                type: 'error',
                message: error
            });

        } finally {
            this.loading = false;
        }
    },
    handleTagClose() {
        this.search = null;
        this.filteredMachines = this.machines;
    },
    handleRowClick(row) {
        this.showDetails = true;
        this.selectedProduction = row;
    },
    tableRowClassName({ row, rowIndex }) {
        return 'cursor-pointer text-xs';
    },
    handleCommand(command) {
        const commandName = command.split('-')[0];
        const rowId = command.split('-')[1];

        if (commandName == 'clone') {
            this.clone(rowId);
        } else if (commandName == 'edit') {
            this.$inertia.get(route('machines.edit', rowId));
        } else if (commandName == 'delete') {
            this.delete(rowId);
        }
    },
    delete(id) {
        this.$confirm('¿Estás seguro de que deseas eliminar esta máquina?', 'Eliminar máquina', {
            type: 'warning',
            showCancelButton: true,
            cancelButtonText: 'Cancelar',
            confirmButtonText: 'Eliminar',
        }).then(() => {
            axios.delete(route('machines.destroy', id))
                .then(response => {
                    this.$message({
                        type: 'success',
                        message: 'Producto eliminado correctamente',
                    });
                    this.$inertia.reload();
                })
                .catch(error => {
                    this.$message.error(error.response.data.message);
                });
        }).catch(() => {});
    },
    clone(id) {
        this.$confirm('¿Estás seguro de que deseas clonar esta máquina?', 'Clonar máquina', {
            type: 'warning',
            showCancelButton: true,
            cancelButtonText: 'Cancelar',
            confirmButtonText: 'Clonar',
        }).then(() => {
            this.$inertia.get(route('machines.clone', id));
            
        }).catch(() => {});
    },
    saveImage(image) {
        this.form.image = image;
    },
    clearImage() {
        this.form.image = null;
    },
}
}
</script>
