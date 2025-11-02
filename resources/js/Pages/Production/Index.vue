<template>
    <AppLayout title="Gestión de producción">
        <div class="min-h-[80vh] mx-2 lg:mx-8">
            <ActionHeader v-model="searchTemp" @search="handleSearch" @show-import="showImportModal = true"
                @show-export="showExportFilters = true" />

            <div class="mt-2 text-center">
                <el-tag v-if="search" size="large" closable @close="handleTagClose">
                    Estas buscando: <b>{{ search }}</b>
                </el-tag>
            </div>
            <div class="mt-6">
                <el-pagination layout="total, prev, pager, next" size="small" v-model:page-size="pageSize"
                    v-model:current-page="currentPage" :total="total" @current-change="handleCurrentChange"
                    class="ml-2" />
                <Loading v-if="fetching" />
                <ProductionTable v-else :productions="productions" :stations="stations" @row-click="handleRowClick"
                    @command="handleCommand" />
            </div>
        </div>
        <!-- Modals -->
        <ProductionDetailsModal 
            :show="showDetails" 
            :selectedProduction="selectedProduction"
            :updatingDetails="updatingDetails" 
            :stations="stations" 
            :users="users" 
            :machines="machines"
            @close="showDetails = false" 
            @start-process="startProcess"
            @pause-process="pauseProcess" 
            @resume-process="resumeProcess" 
            @finish-move-process="finishAndMoveProcess"
            @skip-move-process="skipAndMoveProcess" 
            @register-delivery="handleRegisterDelivery"
         />

        <DialogModal :show="showExportFilters" @close="showExportFilters = false">
            <template #title>
                <h1 class="font-semibold">Exportar órdenes de producción</h1>
            </template>
            <template #content>
                <div class="lg:grid grid-cols-2 gap-3">
                    <div>
                        <InputLabel value="Fecha de emisión*" />
                        <el-date-picker v-model="dateRange" class="!w-full" type="daterange" range-separator="A"
                            start-placeholder="Fecha de inicio" end-placeholder="Fecha de fin" format="DD/MMM/YYYY"
                            value-format="YYYY-MM-DD" />
                    </div>
                    <div>
                        <InputLabel value="Temporada" />
                        <el-select class="w-full" v-model="season" placeholder="Seleccione la temporada"
                            no-data-text="No hay opciones registradas" no-match-text="No se encontraron coincidencias">
                            <el-option v-for="item in seasons" :key="item" :label="item" :value="item" />
                        </el-select>
                    </div>
                    <div>
                        <InputLabel value="Progreso" />
                        <el-select class="w-full" v-model="station" placeholder="Seleccione el pregreso"
                            no-data-text="No hay opciones registradas" no-match-text="No se encontraron coincidencias">
                            <el-option label="Todos" value="Todos" />
                            <el-option v-for="item in stations" :key="item.name" :label="item.name"
                                :value="item.name" />
                        </el-select>
                    </div>
                    <div class="col-span-full text-end text-xs">
                        <button @click="showImportModal = true; showExportFilters = false"
                            class="text-secondary font-semibold">Ir a importar</button>
                    </div>
                </div>
            </template>
            <template #footer>
                <div class="flex justify-end space-x-2">
                    <CancelButton @click="showExportFilters = false" :disabled="form.processing">
                        Cancelar
                    </CancelButton>
                    <PrimaryButton @click="exportExcel" :disabled="!dateRange">
                        Continuar
                    </PrimaryButton>
                </div>
            </template>
        </DialogModal>

        <DialogModal :show="showImportModal" @close="showImportModal = false">
            <template #title>
                <h1 class="font-semibold">Importar órdenes de producción</h1>
            </template>
            <template #content>
                <h2 class="font-bold">Prepara tu archivo:</h2>
                <ul class="ml-5 text-xs">
                    <li class="list-disc">
                        Utiliza el mismo layout que puedes exportar desde esta sección (SwAssistant)
                        <span @click="showImportModal = false; showExportFilters = true"
                            class="cursor-pointer text-secondary font-semibold"> Ir a exportar</span>
                    </li>
                    <p>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-4 text-amber-600 inline-block mr-1">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                        </svg>
                        <span>Es importante cumplir con los siguientes formatos de fecha y en caso de cambiar alguno,
                            avisar
                            a desarrolladores para hacer los cambios necesarios:</span>
                    </p>
                    <ul class="ml-5 list-disc">
                        <li>Fecha de inicio: aaaa/mm/dd</li>
                        <li>Fecha esperada producción y fecha esperada empaque: dd/mm/aaaa</li>
                        <li>Fecha fin producción y producto terminado: aaaa-mm-dd hh:mm:ss</li>
                    </ul>
                    <p>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-4 text-amber-600 inline-block mr-1">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                        </svg>
                        <span>
                            Atención con los siguientes casos:
                        </span>
                    </p>
                    <ul class="ml-5 list-disc">
                        <li>Si una producción contiene un producto y/o máquina que no existen en el sistema, se
                            registrarán
                            como "POR DEFINIR"</li>
                        <li>Si una producción no contiene producto y/o máquina, se registrarán como "POR DEFINIR"</li>
                        <li>Si una producción no contiene progreso se registrará como "NO ESPECIFICADO"</li>
                        <li>El progreso "Producto Terminado" se cambiará automáticamente a "Terminadas" por estándares
                            del
                            sistema</li>
                        <li>El progreso "X Material" se cambiará automáticamente a "Material pendiente" por estándares
                            del
                            sistema</li>
                    </ul>
                </ul>
                <h2 class="font-bold mt-3">Proceso de importación:</h2>
                <ul class="list-disc ml-5 text-xs">
                    <li>Las órdenes que ya existan en el sistema se actualizarán automáticamente (progreso, máquina y
                        cantidad final)</li>
                    <li>Las órdenes nuevas se crearán con la información proporcionada en el archivo</li>
                </ul>
                <h2 class="font-bold mt-3">Sube tu archivo:</h2>
                <ul class="list-disc ml-5 text-xs">
                    <li>Haz clic en "Adjuntar archivo" para seleccionar tu archivo Excel</li>
                    <li>Al terminar la importación, se cargará nuevamente la lista de órdenes de la tabla con los nuevos
                        registros o las actualizaciones correspondientes.</li>
                </ul>

                <div class="ml-2 mt-8">
                    <FileUploader @files-selected="importForm.excel = $event" :multiple="false" acceptedFormat="excel" />
                    <InputError :message="importForm.errors.excel" />
                </div>
            </template>
            <template #footer>
                <div class="flex justify-end space-x-2">
                    <CancelButton @click="showImportModal = false; importForm.excel = []" :disabled="importForm.processing">
                        Cancelar
                    </CancelButton>
                    <PrimaryButton @click="importExcel" :disabled="importForm.processing || !importForm.excel.length">
                        <i v-if="importForm.processing" class="fa-solid fa-circle-notch fa-spin mr-2"></i>
                        Continuar
                    </PrimaryButton>
                </div>
            </template>
        </DialogModal>

        <ConfirmationModal :show="showConfirmation" @close="showConfirmation = false">
            <template #title>
                <h1 class="font-semibold">Eliminar orden de producción</h1>
            </template>
            <template #content>
                <p class="text-sm">¿Estás seguro de que deseas eliminar esta orden de producción?</p>
                <p class="text-sm text-red-500 mt-2">Esta acción no se puede deshacer.</p>
            </template>
            <template #footer>
                <div class="flex justify-end space-x-2">
                    <PrimaryButton @click="deleteProduction" :disabled="form.processing">
                        <i v-if="form.processing" class="fa-solid fa-circle-notch fa-spin mr-2"></i>
                        Eliminar
                    </PrimaryButton>
                    <CancelButton @click="showConfirmation = false" :disabled="form.processing">
                        Cancelar
                    </CancelButton>
                </div>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import DialogModal from '@/Components/DialogModal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import CancelButton from '@/Components/MyComponents/CancelButton.vue';
import FileUploader from '@/Components/MyComponents/FileUploader.vue';
import Loading from '@/Components/MyComponents/Loading.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useForm } from '@inertiajs/vue3';
import { stations } from '@/Data/stations';
import ActionHeader from './Partials/ActionHeader.vue';
import ProductionTable from './Partials/ProductionTable.vue';
import ProductionDetailsModal from './Partials/ProductionDetailsModal.vue';
import { PlusIcon } from '@heroicons/vue/24/outline';

export default {
    name: 'ProductionList',
    components: {
        AppLayout,
        DialogModal,
        ConfirmationModal,
        PrimaryButton,
        CancelButton,
        Loading,
        InputLabel,
        InputError,
        FileUploader,
        PlusIcon,
        ActionHeader,
        ProductionTable,
        ProductionDetailsModal,
    },
    data() {
        const form = useForm({}); // Form for simple actions like delete
        const importForm = useForm({ excel: [] });

        return {
            form,
            importForm,
            productions: [],
            fetching: false,
            // modal visibility
            showDetails: false,
            showConfirmation: false,
            showExportFilters: false,
            showImportModal: false,
            // component state
            selectedProduction: null,
            searchTemp: null,
            search: null,
            updatingDetails: false,
            dateRange: null,
            season: 'Todas',
            station: 'Todos',
            currentPage: 1,
            prevTotal: 0,
            total: 0,
            pageSize: 30,
            stations: stations,
            machines: [],
            users: {},
            seasons: [
                'Todas', 'Amistad', 'Escolar', 'Fiestas patrias', 'Monarca',
                'Muestos', 'Navidad', 'Servicios', 'Toda ocasión', 'UUPZ',
            ]
        }
    },
    methods: {
        // --- Event Handlers from Children ---
        handleRowClick(row) {
            this.selectedProduction = row;
            this.showDetails = true;
        },
        handleCommand(command) {
            const commandName = command.split('-')[0];
            const rowId = command.split('-')[1];

            if (commandName === 'clone') {
                this.clone(rowId);
            } else if (commandName === 'delete') {
                this.selectedProduction = this.productions.find(p => p.id == rowId);
                this.showConfirmation = true;
            } else if (commandName === 'viajera') {
                const url = this.route('productions.hoja-viajera', rowId);
                window.open(url, '_blank');
            } else if (commandName === 'report') {
                this.selectedProduction = this.productions.find(p => p.id == rowId);
                this.exportReportExcel(this.selectedProduction.folio);
            } else {
                this.$inertia.get(route('productions.' + commandName, rowId));
            }
        },
        // --- Data Fetching & Actions ---
        handleSearch() {
            if (this.searchTemp) {
                this.search = this.searchTemp;
                this.searchTemp = null;
                if (this.search) {
                    this.currentPage = 1;
                    this.fetchProductions();
                }
            }
        },
        handleTagClose() {
            this.search = null;
            this.currentPage = 1;
            this.fetchProductions();
        },
        handleCurrentChange() {
            this.fetchProductions();
        },
        deleteProduction() {
            this.form.delete(route('productions.destroy', this.selectedProduction.id), {
                onSuccess: () => {
                    this.showConfirmation = false;
                    this.$notify({ title: "Orden de producción eliminada", type: "success" });
                    this.fetchProductions();
                    this.selectedProduction = null;
                },
                onError: () => this.$notify({ title: "Error al eliminar la orden", type: "error" }),
            });
        },
        clone(rowId) {
            this.form.post(route('productions.clone', rowId), {
                onSuccess: () => this.$notify({ title: "Orden clonada", type: "success" }),
                onError: () => this.$notify({ title: "Error al clonar", type: "error" }),
            });
        },
        async fetchProductions() {
            this.fetching = true;
            try {
                const response = await axios.get(route('productions.get-by-page', { page: this.currentPage, search: this.search }));
                if (response.status === 200) {
                    this.productions = response.data.items;
                    if ((this.total != this.prevTotal) || this.currentPage == 1) {
                        this.total = response.data.total;
                    }
                    this.prevTotal = this.total;
                }
            } catch (error) {
                console.error(error);
            } finally {
                this.fetching = false;
            }
        },
        async fetchMachines() {
            try {
                const response = await axios.get(route('machines.get-all'));
                if (response.status === 200) {
                    this.machines = response.data.items;
                }
            } catch (error) {
                console.error(error);
            }
        },
        exportExcel() {
            const url = route('productions.export-excel', {
                startDate: this.dateRange[0],
                endDate: this.dateRange[1],
                station: this.station,
                season: this.season,
            });
            window.open(url, '_blank');
        },
        exportReportExcel(folio) {
            const url = route('productions.export-report-excel', { folio });
            window.open(url, '_blank');
        },
        importExcel() {
            this.importForm.post(route('productions.import-excel'), {
                onSuccess: () => {
                    this.$notify({ title: "Importación exitosa", type: "success" });
                    this.importForm.reset();
                    this.showImportModal = false;
                    this.fetchProductions();
                },
                onError: (err) => this.$notify({ title: "Error en importación", type: "error" }),
            });
        },
         startProcess(payload) { // Modificado
            const id = payload ? payload.productionId : this.selectedProduction.id; // Modificado
            this.$inertia.post(route('productions.station-process.start', id), {}, { // Modificado
                onSuccess: () => this.updateDetails(id), // Modificado
                onError: () => this.$notify({ title: "Error", message: "No se pudo iniciar el proceso", type: "error" }),
                preserveScroll: true,
            });
        },
        pauseProcess(payload) { // Modificado
            const id = payload.productionId || this.selectedProduction.id; // Modificado
            this.$inertia.post(route('productions.station-process.pause', id), { reason: payload.reason, notes: payload.notes }, { // Modificado
                onSuccess: () => this.updateDetails(id), // Modificado
                onError: (errors) => this.$notify({ title: "Error", message: errors.reason || "No se pudo pausar el proceso", type: "error" }),
                preserveScroll: true,
            });
        },
        resumeProcess(payload) { // Modificado
            const id = payload ? payload.productionId : this.selectedProduction.id; // Modificado
            this.$inertia.post(route('productions.station-process.resume', id), {}, { // Modificado
                onSuccess: () => this.updateDetails(id), // Modificado
                onError: () => this.$notify({ title: "Error", message: "No se pudo reanudar el proceso", type: "error" }),
                preserveScroll: true,
            });
        },
        finishAndMoveProcess(payload) { // Modificado
            const id = payload.productionId || this.selectedProduction.id; // Modificado
            const production = payload.productionObject || this.selectedProduction; // Modificado

            const isReturn = (payload.next_station === 'X Reproceso' && production.station === 'Calidad') ||
                             (payload.next_station === 'Calidad' && production.station === 'Inspección');

            if (isReturn) {
                 this.$inertia.post(route('productions.return-station', id), payload, { // Modificado
                    onSuccess: () => this.updateDetails(id, true), // Modificado
                    onError: () => this.$notify({ title: "Error", message: "No se pudo regresar la orden", type: "error" }),
                 });
            } else {
                 this.$inertia.post(route('productions.station-process.finishAndMove', id), payload, { // Modificado
                    onSuccess: () => this.updateDetails(id, true), // Modificado
                    onError: (e) => this.$notify({ title: "Error", message: "No se pudo finalizar y mover la orden", type: "error" }),
                 });
            }
        },
        skipAndMoveProcess(payload) { // Modificado
            const id = payload.productionId || this.selectedProduction.id; // Modificado
            this.$inertia.post(route('productions.station-process.skipAndMove', id), payload, { // Modificado
                 onSuccess: () => this.updateDetails(id, true), // Modificado
                 onError: () => this.$notify({ title: "Error", message: "No se pudo mover la estación", type: "error" }),
            });
        },
        handleRegisterDelivery(payload) { // Modificado
            const id = payload.productionId || this.selectedProduction.id; // Modificado
            const data = { ...payload.form, context: payload.context };
            this.$inertia.post(route('productions.station-process.register-delivery', id), data, { // Modificado
                onSuccess: () => {
                    this.$notify({ title: "Entrega registrada", type: "success" });
                    this.updateDetails(id); // Modificado
                },
                onError: (e) => {
                    this.$notify({ title: "Error", message: "No se pudo registrar la entrega. Revisa los datos.", type: "error" });
                },
                preserveScroll: true,
            });
        },
        async updateDetails(updatedId = null, closeOnSuccess = false) { // Modificado
            this.updatingDetails = true;
            await this.fetchProductions(); // Refresca la lista principal de padres
            
            // ID a buscar (puede ser el padre o un hijo que se actualizó)
            const idToFind = updatedId || this.selectedProduction?.id;

            // Si la orden es dividida, necesitamos recargar los hijos
            if (this.selectedProduction?.station === 'Producción dividida') {
                try {
                    const response = await axios.get(route('productions.get-children', this.selectedProduction.id));
                    // selectedProduction se actualiza desde la lista de padres
                    const updatedParent = this.productions.find(p => p.id == this.selectedProduction.id);
                    if (updatedParent) {
                        this.selectedProduction = { ...updatedParent, children: response.data.items }; // Sobrescribimos/añadimos los hijos actualizados
                    } else {
                        this.showDetails = false; // El padre ya no está (raro)
                    }
                } catch (e) {
                    console.error("Error recargando componentes", e);
                    this.showDetails = false; // Error cargando hijos
                }
            } else {
                // Lógica de orden simple (como antes)
                const updatedProduction = this.productions.find(p => p.id == idToFind);
                if (updatedProduction) {
                    this.selectedProduction = updatedProduction;
                } else {
                    this.showDetails = false; // Production might have been moved out of view
                }
            }
            
            if (closeOnSuccess) {
                this.showDetails = false;
            }
            this.updatingDetails = false;
        },
        async fetchUsers() {
            try {
                const response = await axios.get(route('users.get-all'));
                if (response.status === 200) {
                    this.users = response.data.items.reduce((acc, user) => {
                        acc[user.id] = user.name;
                        return acc;
                    }, {});
                }
            } catch (error) {
                console.error('Error fetching users:', error);
            }
        },
        getFilterFromUrl() {
            const urlParams = new URLSearchParams(window.location.search);
            const filter = urlParams.get('filter');
            if (filter) {
                this.search = filter;
                this.searchTemp = filter;
            }
        }
    },
    mounted() {
        this.fetchMachines();
        this.fetchUsers();
        this.getFilterFromUrl();
        this.fetchProductions();
    }
}
</script>