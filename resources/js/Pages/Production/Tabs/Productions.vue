<template>
    <div class="min-h-[80vh]">
        <Loading v-if="fetching" />
        <div v-else>
            <h1 class="font-bold">Ordenes de producción</h1>
            <!-- Buscador -->
            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center mt-4">
                <div class="lg:w-1/4 relative lg:mr-12">
                    <input v-model="searchTemp" @keyup.enter="handleSearch" class="input w-full pl-9"
                        placeholder="Buscar producción" type="search">
                    <i class="fa-solid fa-magnifying-glass text-xs text-gray99 absolute top-[10px] left-4"></i>
                </div>
                <el-dropdown split-button type="primary" @click="" trigger="click" @command="handleCommand">
                    Importar
                    <template #dropdown>
                        <el-dropdown-menu>
                            <el-dropdown-item command="export">Exportar</el-dropdown-item>
                        </el-dropdown-menu>
                    </template>
                </el-dropdown>
            </div>
            <div class="mt-2 text-center">
                <el-tag v-if="search" size="large" closable @close="handleTagClose">
                    Estas buscando: <b>{{ search }}</b>
                </el-tag>
            </div>
            <el-table :data="productions" @row-click="handleRowClick" max-height="670" style="width: 100%" class="mt-4"
                :row-class-name="tableRowClassName">
                <!-- <el-table-column type="selection" width="45" /> -->
                <el-table-column prop="folio" label="N° Orden" />
                <el-table-column prop="product.name" label="Producto" />
                <el-table-column prop="product.season" label="Temporada" />
                <el-table-column prop="quantity" label="Cantidad solicitada" />
                <el-table-column prop="client" label="Cliente" />
                <el-table-column prop="station" label="Progreso">
                    <template #default="scope">
                        <b>{{ scope.row.station }}</b>
                    </template>
                </el-table-column>
                <el-table-column prop="notes" label="Notas" width="160">
                    <template #default="scope">
                        <p class="truncate">{{ scope.row.notes }} asldkj fdaslkñj faslñkjf asñlkfsjd asdrfaoiu</p>
                    </template>
                </el-table-column>
                <el-table-column prop="materials" label="Lista de material">
                    <template #default="scope">
                        <p class="truncate">{{ scope.row.materials?.join(', ') ?? 'Ninguna' }}</p>
                    </template>
                </el-table-column>
                <el-table-column prop="machine.name" label="Máquina" />
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
                                        v-if="$page.props.auth.user.permissions.includes('Editar producciones')"
                                        :command="'edit-' + scope.row.id">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-4 mr-2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                        </svg>
                                        Editar</el-dropdown-item>
                                    <el-dropdown-item class="!text-xs"
                                        v-if="$page.props.auth.user.permissions.includes('Crear producciones')"
                                        :command="'clone-' + scope.row.id">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-4 mr-2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                        </svg>
                                        Repetir</el-dropdown-item>
                                    <el-dropdown-item class="!text-xs"
                                        v-if="$page.props.auth.user.permissions.includes('Eliminar producciones')"
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
    <DialogModal :show="showDetails" @close="showDetails = false">
        <template #title>
            <h1 class="font-semibold">Orden de producción</h1>
        </template>
        <template #content>
            <h2 class="text-[#666666] font-bold">Información de la orden</h2>
            <div class="text-sm grid grid-cols-3 gap-2 mt-3">
                <p class="text-[#464646]">N° de Orden:</p>
                <p class="col-span-2">{{ selectedProduction.folio }}</p>
                <p class="text-[#464646]">Fecha de emisión:</p>
                <p class="col-span-2">{{ selectedProduction.created_at }}</p>
                <p class="text-[#464646]">Fecha estimada de entrega:</p>
                <p class="col-span-2">{{ selectedProduction.estimated_date }}</p>
                <p class="text-[#464646]">Cliente:</p>
                <p class="col-span-2">{{ selectedProduction.client }}</p>
            </div>
            <h2 class="text-[#666666] font-bold mt-5">Información del producto</h2>
            <div class="text-sm grid grid-cols-3 gap-2 mt-3">
                <p class="text-[#464646]">Código del producto</p>
                <p class="col-span-2">{{ selectedProduction.product.code }}</p>
                <p class="text-[#464646]">Nombre del producto:</p>
                <p class="col-span-2">{{ selectedProduction.product.name }}</p>
                <p class="text-[#464646]">Temporada:</p>
                <p class="col-span-2">{{ selectedProduction.product.season }}</p>
                <p class="text-[#464646]">Descripción:</p>
                <p class="col-span-2">{{ selectedProduction.product.description }}</p>
                <p class="text-[#464646]">Cantidad solicitada:</p>
                <p class="col-span-2 font-bold">
                    {{ selectedProduction.quantity.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}
                </p>
                <p class="text-[#464646]">Lista de material:</p>
                <p class="col-span-2">{{ selectedProduction.materials?.join(', ') }}</p>
            </div>
            <h2 class="text-[#666666] font-bold mt-5">Proceso de producción</h2>
            <div class="text-sm grid grid-cols-3 gap-2 mt-3">
                <p class="text-[#464646]">Progreso:</p>
                <p class="col-span-2">{{ selectedProduction.station }}</p>
                <p class="text-[#464646]">Máquina:</p>
                <p class="col-span-2">{{ selectedProduction.machine.name }}</p>
                <p class="text-[#464646]">Notas:</p>
                <p class="col-span-2" style="white-space: pre-line;">{{ selectedProduction.notes }}</p>
            </div>
        </template>
    </DialogModal>
</template>

<script>
import DialogModal from '@/Components/DialogModal.vue';
import Loading from '@/Components/MyComponents/Loading.vue';

export default {
    name: 'ProductionList',
    data() {
        return {
            productions: [],
            fetching: false,
            showDetails: false,
            selectedProduction: null,
            searchTemp: null,
            search: null,
        }
    },
    components: {
        DialogModal,
        Loading,
    },
    props: {
    },
    methods: {
        handleSearch() {
            this.search = this.searchTemp;
            this.searchTemp = null;
            // if (this.search) {
            //     this.fetchMatches();
            // } else {
            //     this.showAllUsers();
            // }
        },
        handleTagClose() {
            this.search = null;
            // this.showAllUsers();
        },
        handleRowClick(row) {
            this.showDetails = true;
            this.selectedProduction = row;
        },
        tableRowClassName({ row, rowIndex }) {
            return 'cursor-pointer text-xs';
        },
        handleCommand(command) {
            // const commandName = command.split('-')[0];
            // const rowId = command.split('-')[1];

            // if (commandName == 'clone') {
            //     this.clone(rowId);
            // } else if (commandName == 'make_so') {
            //     this.selectedQuoteId = rowId;
            //     this.showConversionConfirm = true;
            // } else if (commandName == 'authorize') {
            //     this.authorize(rowId);
            // } else {
            //     this.$inertia.get(route('quotes.' + commandName, rowId));
            // }
        },
        async fetchProductions() {
            this.fetching = true;
            try {
                const response = await axios.get(route('productions.get-all'));

                if (response.status === 200) {
                    this.productions = response.data.items;
                }
            } catch (error) {
                console.error('Error fetching productions:', error);
            } finally {
                this.fetching = false;
            }
        },
    },
    mounted() {
        this.fetchProductions();
    }
}
</script>