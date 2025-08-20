<template>
    <div>
        <AppLayout title="Importaciones">
            <!-- Encabezado con filtros y acciones -->
            <div class="flex justify-between items-center mx-2 md:mx-6 lg:mx-10 mt-4">
                <div class="flex flex-col">
                    <h2 class="font-semibold text-black">Importaciones</h2>
                    <span class="text-gray-500 text-sm">Gestiona y monitorea todas tus operaciones de importación</span>
                </div>
                <el-dropdown split-button type="primary" trigger="click" @click="$inertia.get(route('imports.create'))">
                    <PlusIcon class="size-4 mr-1" />
                    Crear importación
                    <template #dropdown>
                        <el-dropdown-menu>
                            <el-dropdown-item>Action 1</el-dropdown-item>
                            <!-- <el-dropdown-item>Action 2</el-dropdown-item>
                            <el-dropdown-item>Action 3</el-dropdown-item>
                            <el-dropdown-item divided>Action 4</el-dropdown-item>
                            <el-dropdown-item>Action 5</el-dropdown-item> -->
                        </el-dropdown-menu>
                    </template>
                </el-dropdown>
            </div>
            <div class="flex items-center justify-end space-x-4 mx-2 md:mx-6 lg:mx-10 mt-4">
                <div>
                    <InputLabel value="Folio" />
                    <el-input v-model="localFilters.search" placeholder="Buscar por folio" class="lg:!w-60" clearable />
                </div>
                <div>
                    <InputLabel value="Proveedor" />
                    <el-select v-model="localFilters.supplier" placeholder="Proveedor" class="lg:!w-60" clearable>
                        <el-option label="Todos" value="" />
                        <el-option v-for="supplier in suppliers" :key="supplier.id" :label="supplier.name"
                            :value="supplier.id" />
                    </el-select>
                </div>
                <div>
                    <InputLabel value="Rango de fechas" />
                    <el-date-picker v-model="localFilters.dates" type="daterange" range-separator="A" class="lg:!w-60"
                        start-placeholder="Fecha inicio" end-placeholder="Fecha fin" value-format="YYYY-MM-DD" />
                </div>
            </div>

            <!-- Contenedor principal del Kanban -->
            <div class="py-6">
                <div class="max-w-full mx-auto sm:px-6 lg:px-8">
                    <div class="grid grid-cols-5 gap-2">
                        <!-- Iteramos sobre las columnas definidas en 'data' -->
                        <div v-for="column in columns" :key="column.title"
                            class="rounded-[10px] p-2 transition-colors duration-300 border"
                            :style="{ backgroundColor: isDragging ? '#dbeafe' : column.bgColor, borderColor: isDragging ? '#dbeafe' : column.borderColor, }">
                            <!-- Encabezado de la columna -->
                            <div class="flex items-center justify-between text-sm mb-2 pb-1 border-b border-grayD9"
                                :class="column.iconColor">
                                <div class="flex items-center space-x-2">
                                    <component :is="column.icon" class="size-4" />
                                    <h3 class="font-bold">{{ column.title }}</h3>
                                </div>
                                <span class="font-semibold px-2 py-1">
                                    {{ (localImports[column.title] || []).length }}
                                </span>
                            </div>

                            <!-- Zona de Arrastre (Draggable) -->
                            <draggable :list="localImports[column.title] || []" group="imports" @add="handleDrop"
                                @start="isDragging = true" @end="isDragging = false" :animation="300" :id="column.title"
                                item-key="id" class="space-y-2 min-h-[60vh]">
                                <template #item="{ element }">
                                    <div @click="showDetails(element)"
                                        class="relative text-xs bg-white border border-grayD9 rounded-[14px] px-4 py-2 cursor-pointer hover:shadow-md transition">
                                        <div class="w-1 h-12 rounded-full absolute -left-[2px] top-3"
                                            :class="true ? 'bg-[#27A416]' : 'bg-[#C1202A]'"></div>
                                        <div class="flex justify-between items-start text-sm mb-2">
                                            <span class="font-light text-gray66">ID. {{ element.id }}</span>
                                            <span class="text-gray3F font-semibold">
                                                {{ element.incoterm.substring(0, 3) }}
                                            </span>
                                        </div>
                                        <p class="font-semibold text-gray3F">{{ element.supplier?.name }}</p>
                                        <p class="font-bold">
                                            {{ formatCurrency(element.total_cost) }}
                                        </p>
                                        <p class="text-gray3F font-semibold">
                                            Agente: {{ element.customs_agent?.name }}
                                        </p>
                                        <div
                                            class="flex justify-between items-center pt-2 mt-2 border-t border-grayD9 text-xs text-gray66">
                                            <div class="flex items-center space-x-1">
                                                <CalendarIcon class="size-4" />
                                                <span>{{ formatDate(element.estimated_arrival_date) }}</span>
                                            </div>
                                            <div class="flex items-center space-x-1">
                                                <ChatBubbleBottomCenterTextIcon class="size-4" />
                                                <span>{{ element.comments_count || 0 }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </draggable>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
        <!-- Integración del Modal de Detalles -->
        <ImportDetails v-if="selectedImport" :show="showDetailsModal" :import-data="selectedImport"
            @close="showDetailsModal = false" @reload="reloadData" />
    </div>
</template>

<script>
import { markRaw } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import draggable from 'vuedraggable';
import { Search as SearchIcon } from '@element-plus/icons-vue';
import {
    ArchiveBoxIcon,
    CalendarIcon,
    ChatBubbleBottomCenterTextIcon,
    PlusIcon
} from '@heroicons/vue/24/outline';
import { router } from '@inertiajs/vue3';
import AnclaIcon from '@/Components/MyComponents/Icons/AnclaIcon.vue';
import PalomitaIcon from '@/Components/MyComponents/Icons/PalomitaIcon.vue';
import MarkerIcon from '@/Components/MyComponents/Icons/MarkerIcon.vue';
import BarcoIcon from '@/Components/MyComponents/Icons/BarcoIcon.vue';
import throttle from 'lodash/throttle';
import InputLabel from '@/Components/InputLabel.vue';
import ImportDetails from './Details.vue'; // Importar el nuevo componente

export default {
    components: {
        AppLayout,
        draggable,
        SearchIcon,
        ArchiveBoxIcon,
        CalendarIcon,
        ChatBubbleBottomCenterTextIcon,
        AnclaIcon,
        PalomitaIcon,
        MarkerIcon,
        BarcoIcon,
        PlusIcon,
        InputLabel,
        ImportDetails,
    },
    props: {
        imports: Object,
        suppliers: Array,
        filters: Object, // Recibimos los filtros actuales desde el controlador
    },
    data() {
        return {
            isDragging: false,
            showDetailsModal: false,   // Estado para controlar la visibilidad del modal
            selectedImport: null,      // Estado para guardar la importación seleccionada
            // Usamos un objeto local para los filtros para no mutar las props directamente
            localFilters: {
                search: this.filters.search || '',
                supplier: this.filters.supplier || '',
                dates: this.filters.dates || [],
            },
            columns: [
                { title: 'Con proveedor', icon: markRaw(ArchiveBoxIcon), iconColor: 'text-gray3F', bgColor: '#EDEDED', borderColor: '#D9D9D9' },
                { title: 'Puerto origen', icon: markRaw(AnclaIcon), iconColor: 'text-[#645E20]', bgColor: '#FCFFD8', borderColor: '#FFFB7B' },
                { title: 'En tránsito marítimo', icon: markRaw(BarcoIcon), iconColor: 'text-[#C06102]', bgColor: '#FFEFE2', borderColor: '#FDD192' },
                { title: 'Puerto destino', icon: markRaw(MarkerIcon), iconColor: 'text-[#004C7B]', bgColor: '#E9F6FF', borderColor: '#A5CCFE' },
                { title: 'Entregado', icon: markRaw(PalomitaIcon), iconColor: 'text-[#448734]', bgColor: '#E9FFDD', borderColor: '#84FC59' },
            ],
            localImports: {}, // Se inicializa vacío y se llena en 'created'
        };
    },
    methods: {
        reloadData() {
            const selectedId = this.selectedImport.id;

            router.reload({
                only: ['imports'], // Recarga solo la prop 'imports'
                preserveState: true,
                onSuccess: (page) => {
                    // Una vez que Inertia trae los datos frescos...
                    const updatedImports = page.props.imports;
                    let foundImport = null;

                    // Buscamos la importación actualizada dentro de los grupos de estado
                    for (const status in updatedImports) {
                        const found = updatedImports[status].find(imp => imp.id === selectedId);
                        if (found) {
                            foundImport = found;
                            break;
                        }
                    }

                    // Si la encontramos, actualizamos el estado.
                    // Vue se encargará de pasar la nueva información al modal.
                    if (foundImport) {
                        this.selectedImport = foundImport;
                    }
                }
            });
        },
        showDetails(importData) {
            this.selectedImport = importData;
            this.showDetailsModal = true;
        },
        handleDrop(event) {
            const newStatus = event.to.id;
            const importId = this.localImports[newStatus][event.newIndex].id;

            if (importId && newStatus) {
                router.patch(route('imports.status.update', importId), {
                    status: newStatus
                }, {
                    preserveState: true,
                    preserveScroll: true,
                    onSuccess: () => { },
                    onError: () => {
                        router.reload();
                    }
                });
            } else {
                console.error("No se pudo obtener el ID de la importación o el nuevo estado.");
            }
        },
        initializeLocalImports() {
            const importsData = this.imports || {};
            const localData = {};
            // Nos aseguramos de que todas las columnas existan en localImports
            this.columns.forEach(column => {
                if (importsData[column.title]) {
                    localData[column.title] = JSON.parse(JSON.stringify(importsData[column.title]));
                } else {
                    localData[column.title] = [];
                }
            });
            this.localImports = localData;
        },
        formatDate(dateString) {
            if (!dateString) return 'N/A';
            const options = { year: 'numeric', month: 'short', day: 'numeric' };
            return new Date(dateString).toLocaleDateString('es-ES', options);
        },
        formatCurrency(value) {
            if (typeof value !== 'number') {
                value = 0;
            }
            return value.toLocaleString('en-US', {
                style: 'currency',
                currency: 'USD',
            });
        },
    },
    created() {
        this.initializeLocalImports();
    },
    watch: {
        // Este watcher es crucial: actualiza los datos locales cuando las props cambian
        // (es decir, después de que Inertia trae los nuevos datos filtrados del backend).
        imports: {
            handler() {
                this.initializeLocalImports();
            },
            deep: true,
        },
        // Este watcher observa los cambios en los filtros y lanza la petición al backend.
        localFilters: {
            handler: throttle(function () {
                router.get(route('imports.index'), this.localFilters, {
                    preserveState: true,
                    replace: true,
                });
            }, 300), // El throttle espera 300ms para optimizar las peticiones.
            deep: true,
        },
    },
}
</script>

<style>
/* Estilo para el elemento fantasma que aparece al arrastrar */
.sortable-ghost {
    opacity: 0.4;
    background-color: #c8ebfb;
    border: 2px dashed #3490dc;
}

.sortable-drag {
    opacity: 1 !important;
    transform: rotate(3deg);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}
</style>
