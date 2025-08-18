<template>
    <div>
        <AppLayout title="Importaciones">
            <!-- Encabezado con filtros y acciones -->
            <div class="flex justify-between items-center mx-2 md:mx-6 lg:mx-10 mt-4">
                <div class="flex flex-col">
                    <h2 class="font-semibold text-black">Importaciones</h2>
                    <span class="text-gray-500 text-xs">Gestiona y monitorea todas tus operaciones de importación</span>
                </div>
                <div class="flex items-center space-x-4">
                    <el-input v-model="localFilters.search" placeholder="Buscar por folio"
                        clearable />
                    <el-select v-model="localFilters.supplier" placeholder="Proveedor" clearable>
                        <el-option label="Todos" value="" />
                        <el-option v-for="supplier in suppliers" :key="supplier.id" :label="supplier.name"
                            :value="supplier.id" />
                    </el-select>
                    <el-date-picker v-model="localFilters.dates" type="daterange" range-separator="A"
                        start-placeholder="Fecha inicio" end-placeholder="Fecha fin" value-format="YYYY-MM-DD" />
                    <button @click="$inertia.get(route('imports.create'))"
                        class="bg-blue-500 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-600 transition">
                        Nueva Importación
                    </button>
                </div>
            </div>

            <!-- Contenedor principal del Kanban -->
            <div class="py-12">
                <div class="max-w-full mx-auto sm:px-6 lg:px-8">
                    <div class="grid grid-cols-5 gap-2">
                        <!-- Iteramos sobre las columnas definidas en 'data' -->
                        <div v-for="column in columns" :key="column.id" 
                             class="bg-gray-100 rounded-lg p-4 transition-colors duration-300"
                             :class="{ 'bg-blue-100': isDragging }">
                            <!-- Encabezado de la columna -->
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center space-x-2">
                                    <component :is="column.icon" class="h-6 w-6" :class="column.iconColor" />
                                    <h3 class="font-bold text-gray-700">{{ column.title }}</h3>
                                </div>
                                <span class="bg-gray-200 text-gray-800 text-sm font-semibold px-2 py-1 rounded-full">
                                    {{ (localImports[column.id] || []).length }}
                                </span>
                            </div>

                            <!-- Zona de Arrastre (Draggable) -->
                            <draggable :list="localImports[column.id] || []" group="imports" @add="handleDrop"
                                @start="isDragging = true" @end="isDragging = false" :animation="300" :id="column.id"
                                item-key="id" class="space-y-4 min-h-[60vh]">
                                <template #item="{ element }">
                                    <div @click="$inertia.get(route('imports.show', element.id))"
                                        class="bg-white p-4 rounded-lg shadow border-l-4 cursor-pointer hover:shadow-md transition"
                                        :class="column.borderColor">
                                        <!-- Tarjeta de Importación -->
                                        <div class="flex justify-between items-start mb-2">
                                            <span class="text-sm font-bold text-gray-600">ID. {{ element.folio }}</span>
                                            <span class="text-xs text-gray-500">{{ formatDate(element.created_at)
                                            }}</span>
                                        </div>
                                        <p class="font-bold text-gray-800">{{ element.supplier?.name }}</p>
                                        <p class="text-xl font-extrabold text-blue-600 my-2">{{
                                            formatCurrency(element.total_cost) }}</p>
                                        <p class="text-sm text-gray-600">Agente: {{ element.customs_agent?.name }}</p>
                                        <div class="flex justify-between items-center mt-4 text-sm text-gray-500">
                                            <div class="flex items-center space-x-1">
                                                <CalendarIcon class="h-4 w-4" />
                                                <span>{{ formatDate(element.estimated_arrival_date) }}</span>
                                            </div>
                                            <div class="flex items-center space-x-1">
                                                <ChatBubbleBottomCenterTextIcon class="h-4 w-4" />
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
    ChatBubbleBottomCenterTextIcon
} from '@heroicons/vue/24/outline';
import { router } from '@inertiajs/vue3';
import AnclaIcon from '@/Components/MyComponents/Icons/AnclaIcon.vue';
import PalomitaIcon from '@/Components/MyComponents/Icons/PalomitaIcon.vue';
import MarkerIcon from '@/Components/MyComponents/Icons/MarkerIcon.vue';
import BarcoIcon from '@/Components/MyComponents/Icons/BarcoIcon.vue';
import throttle from 'lodash/throttle';

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
    },
    props: {
        imports: Object,
        suppliers: Array,
        filters: Object, // Recibimos los filtros actuales desde el controlador
    },
    data() {
        return {
            isDragging: false,
            // Usamos un objeto local para los filtros para no mutar las props directamente
            localFilters: {
                search: this.filters.search || '',
                supplier: this.filters.supplier || '',
                dates: this.filters.dates || [],
            },
            columns: [
                { id: 'proveedor', title: 'Con proveedor', icon: markRaw(ArchiveBoxIcon), iconColor: 'text-gray-600', borderColor: 'border-red-500' },
                { id: 'puerto_origen', title: 'Puerto Origen', icon: markRaw(AnclaIcon), iconColor: 'text-yellow-500', borderColor: 'border-yellow-500' },
                { id: 'mar', title: 'En tránsito Marítimo', icon: markRaw(BarcoIcon), iconColor: 'text-orange-500', borderColor: 'border-orange-500' },
                { id: 'puerto_destino', title: 'Puerto destino', icon: markRaw(MarkerIcon), iconColor: 'text-cyan-500', borderColor: 'border-cyan-500' },
                { id: 'entregado', title: 'Entregado', icon: markRaw(PalomitaIcon), iconColor: 'text-green-500', borderColor: 'border-green-500' },
            ],
            localImports: {}, // Se inicializa vacío y se llena en 'created'
        };
    },
    methods: {
        handleDrop(event) {
            const newStatus = event.to.id;
            const importId = this.localImports[newStatus][event.newIndex].id;

            if (importId && newStatus) {
                 router.patch(route('imports.status.update', importId), {
                    status: newStatus
                }, {
                    preserveState: true,
                    preserveScroll: true,
                    onSuccess: () => {},
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
                if (importsData[column.id]) {
                    localData[column.id] = JSON.parse(JSON.stringify(importsData[column.id]));
                } else {
                    localData[column.id] = [];
                }
            });
            this.localImports = localData;
        },
        formatDate(dateString) {
            if (!dateString) return 'N/A';
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
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
