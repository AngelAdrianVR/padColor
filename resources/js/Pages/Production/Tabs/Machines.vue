<template>
    <div class="min-h-[80vh]">
        <div>
            <h1 class="font-bold">Máquinas</h1>
            <!-- Buscador -->
            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center mt-4">
                <div class="lg:w-1/4 relative lg:mr-12">
                    <input v-model="searchTemp" @keyup.enter="handleSearch" class="input w-full pl-9"
                        placeholder="Buscar producto" type="search">
                    <i class="fa-solid fa-magnifying-glass text-xs text-gray99 absolute top-[10px] left-4"></i>
                </div>
                <PrimaryButton class="mt-3 lg:mt-0"
                    v-if="this.$page.props.auth.user.permissions.includes('Crear máquinas')"
                    @click="showCreateModal = true">Agregar máquina</PrimaryButton>
            </div>
            <div class="mt-2 text-center">
                <el-tag v-if="search" size="large" closable @close="handleTagClose">
                    Estas buscando: <b>{{ search }}</b>
                </el-tag>
            </div>

            <!-- pagination -->
            <!-- <div class="overflow-auto mb-2 mt-8">
                <PaginationWithNoMeta v-if="!search" :pagination="machines" class="py-2" />
            </div> -->

            <Loading v-if="loading" />
            <el-table v-else :data="filteredMachines" @row-click="handleRowClick" max-height="670" style="width: 100%"
                class="mt-4" :row-class-name="tableRowClassName">
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
    </div>

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
                        :maxlength="500" placeholder="Agrega una descripción a la máquina" show-word-limit clearable />
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
                Guardar máquina
            </PrimaryButton>
        </template>
    </DialogModal>

    <!-- Modal para ver detalles de la máquina -->
    <DialogModal :show="showDetailsModal" @close="showDetailsModal = false; editmode = false">
        <template #title>
            <div class="flex justify-between items-center w-full mr-8">
                <h1 class="font-bold text-sm">Detalles de la máquina</h1>
                <ThirthButton @click="startEditMode()" v-if="!editmode" class="!rounded-md !px-3 !py-1">Editar
                </ThirthButton>
            </div>
        </template>
        <template #content>
            <!-- Formularion de edición -->
            <div v-if="editmode">
                <div class="mb-4">
                    <InputLabel value="Nombre de la máquina" />
                    <el-input v-if="editmode" v-model="form.name" placeholder="Ej. Suajadora" type="text" />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="mb-4">
                    <InputLabel value="Descripción" />
                    <el-input v-if="editmode" v-model="form.description" :autosize="{ minRows: 3, maxRows: 5 }"
                        type="textarea" :maxlength="500" placeholder="Agrega una descripción a la máquina"
                        show-word-limit clearable />
                    <InputError :message="form.errors.description" />
                </div>

                <div class="mb-2 w-auto">
                    <InputLabel value="Imagen de la máquina" class="ml-3 mb-1" />
                    <InputFilePreview v-if="editmode" @imagen="saveImage" @cleared="clearImage"
                        :imageUrl="selectedMachine.media[0]?.original_url" />
                </div>
            </div>

            <!-- Detalles de máquina -->
            <div class="md:grid grid-cols-3 gap-5 py-2" v-else>
                <figure class="h-40 w-auto mt-5 border border-[#D9D9D9] rounded-lg flex items-center justify-center">
                    <img v-if="selectedMachine.media[0]?.original_url" :src="selectedMachine.media[0]?.original_url"
                        alt="" class="h-full object-contain">
                    <div v-else>
                        <img class="h-full object-contain" src="/images/no-image.png" alt="No hay imagen disponible">
                    </div>
                </figure>

                <div class="col-span-2 mt-5">
                    <h2 class="text-[#464646]">Nombre de la máquina:</h2>
                    <h2 class="text-black font-bold text-lg">{{ selectedMachine.name }}</h2>

                    <section class="grid grid-cols-2 gap-2 mt-5">
                        <div>
                            <p class="text-[#464646]">ID:</p>
                            <p class="text-black">{{ selectedMachine.id }}</p>
                        </div>
                        <div>
                            <p class="text-[#464646]">Creado el:</p>
                            <p class="text-black">{{ formatDate2(selectedMachine.created_at) }}</p>
                        </div>
                        <div>
                            <p class="text-[#464646]">Creado por:</p>
                            <p class="text-black">{{ selectedMachine.created_by }}</p>
                        </div>
                        <div class="col-span-full mt-2">
                            <p class="text-[#464646]">Descripción:</p>
                            <p class="text-black">{{ selectedMachine.description }}</p>
                        </div>
                    </section>
                </div>
            </div>
        </template>
        <template #footer>
            <div class="flex items-center space-x-2">
                <ThirthButton @click="editmode = false" v-if="editmode">Cancelar edición</ThirthButton>
                <PrimaryButton v-if="editmode" @click="update()" :disabled="form.processing">
                    <i v-if="form.processing" class="fa-solid fa-circle-notch fa-spin mr-2"></i>
                    Guardar cambios
                </PrimaryButton>
            </div>
        </template>
    </DialogModal>
</template>

<script>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ThirthButton from '@/Components/MyComponents/ThirthButton.vue';
// import PaginationWithNoMeta from "@/Components/MyComponents/PaginationWithNoMeta.vue";
import InputFilePreview from "@/Components/MyComponents/InputFilePreview.vue";
import Loading from "@/Components/MyComponents/Loading.vue";
import DialogModal from "@/Components/DialogModal.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import { format, parseISO } from 'date-fns';
import es from 'date-fns/locale/es';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';

export default {
    data() {

        const form = useForm({
            name: null,
            description: null,
            image: null,
            imageCleared: false,
        });

        return {
            form,
            machines: [],
            loading: false,
            search: null,
            searchTemp: null,
            filteredMachines: this.machines,
            showCreateModal: false, // Para crear máquina
            showDetailsModal: false, // Para mostrar detalles de la máquina.
            selectedMachine: null, // Para mostrar detalles de la máquina.
            editmode: false, // Para editar máquina
        }
    },
    components: {
        Loading,
        InputLabel,
        InputError,
        DialogModal,
        ThirthButton,
        PrimaryButton,
        InputFilePreview,
        // PaginationWithNoMeta,
    },
    props: {
    },
    methods: {
        update() {
            if (this.form.image) {
                this.form.post(route("machines.update-with-media", this.selectedMachine.id), {
                    method: '_put',
                    onSuccess: () => {
                        this.$notify({
                            title: "Máquina actualizada",
                            message: "",
                            type: "success",
                        });
                        // window.location.reload();
                        this.selectedMachine.name = this.form.name;
                        this.selectedMachine.description = this.form.description;
                        this.editmode = false;
                    },
                });
            } else {
                this.form.put(route('machines.update', this.selectedMachine.id), {
                    onSuccess: () => {
                        this.$notify({
                            title: 'Máquina actualizada',
                            message: '',
                            type: 'success',
                        });
                        // window.location.reload();
                        this.selectedMachine.name = this.form.name;
                        this.selectedMachine.description = this.form.description;
                        this.editmode = false;
                    },
                    onError: () => {
                        console.log(this.form.errors);
                    },
                });
            }

        },
        startEditMode() {
            this.form.name = this.selectedMachine.name;
            this.form.description = this.selectedMachine.description;
            this.editmode = true;
        },
        formatDate(date) {
            const months = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
            const d = new Date(date);
            const day = String(d.getDate()).padStart(2, '0');
            const month = months[d.getMonth()];
            const year = d.getFullYear();

            return `${day}-${month}-${year}`;
        },
        formatDate2(dateString) {
            return format(parseISO(dateString), 'dd MMMM, yyyy', { locale: es });
        },
        handleTagClose() {
            this.search = null;
            this.filteredMachines = this.machines;
        },
        handleRowClick(row) {
            this.showDetailsModal = true;
            this.selectedMachine = row;
        },
        tableRowClassName({ row, rowIndex }) {
            return 'cursor-pointer text-xs';
        },
        handleCommand(command) {
            const [commandName, rowId] = command.split('-');
            const machineId = parseInt(rowId);

            if (commandName == 'edit') {
                const machine = this.filteredMachines.find(m => m.id === machineId);
                if (machine) {
                    this.selectedMachine = machine;
                    this.showDetailsModal = true;
                    this.startEditMode();
                }
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
                        const index = this.filteredMachines.findIndex(machine => machine.id == id);
                        if (index !== -1) {
                            this.filteredMachines.splice(index, 1);
                        }

                        this.$message({
                            type: 'success',
                            message: 'Máquina eliminado correctamente',
                        });
                    })
                    .catch(error => {
                        this.$message.error(error.response.data.message);
                    });
            }).catch(() => { });
        },
        saveImage(image) {
            this.form.image = image;
            this.form.imageCleared = false;
        },
        clearImage() {
            this.form.image = null;
            this.form.imageCleared = true;
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
        async store() {
            try {
                const response = await axios.post(route('machines.store'), this.form);

                if (response.status === 200) {
                    const newMachine = response.data.machine;
                    this.filteredMachines.unshift(newMachine);

                    this.form.reset();
                    this.$notify({
                        title: 'Máquina agregada correctamente',
                        message: '',
                        type: 'success',
                    });
                    this.showCreateModal = false;
                }
            } catch (error) {
                console.log(error.response?.data?.errors || error);
                this.$notify({
                    title: 'Error al guardar',
                    message: 'Verifica los campos requeridos e inténtalo de nuevo',
                    type: 'error',
                });
            }
        },
        async fetchMachines() {
            try {
                this.loading = true;
                const response = await axios.get(route('machines.get-all', { full: true }));
                
                if (response.status === 200) {
                    this.machines = response.data.items;
                    this.filteredMachines = this.machines;
                }
            } catch (error) {
                console.error('Error fetching machines:', error);
            } finally {
                this.loading = false;
            }
        },
    },
    mounted() {
        this.fetchMachines();
    }
}
</script>
