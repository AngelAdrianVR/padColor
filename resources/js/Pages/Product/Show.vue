<template>
    <AppLayout title="Ficha Técnica del Producto">
        <div class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto">
            <!-- 1. Encabezado General -->
            <div class="bg-white shadow-sm rounded-xl p-6 mb-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div
                            class="w-24 h-24 bg-gray-100 border border-gray-200 rounded-lg flex items-center justify-center flex-shrink-0">
                            <!-- Puedes reemplazar esto con la imagen real del producto -->
                            <el-icon :size="40" class="text-gray-400">
                                <Picture />
                            </el-icon>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-800">{{ product.name }}</h1>
                            <p class="text-sm text-gray-500 mt-1">Código: {{ product.code }}</p>
                            <p class="text-sm text-gray-500">Cliente: PadColor Insumos Gráficos</p>
                        </div>
                    </div>
                    <div class="flex space-x-2 mt-4 sm:mt-0">
                        <el-button @click="handleEditRequest">
                            <el-icon class="mr-2">
                                <Edit />
                            </el-icon> Editar
                        </el-button>
                        <el-button type="primary" plain @click="downloadProof">
                            <el-icon class="mr-2">
                                <Download />
                            </el-icon> Proof
                        </el-button>
                    </div>
                </div>
            </div>

            <!-- 2. Pestañas de la Ficha Técnica -->
            <el-tabs v-model="activeTab" class="product-sheet-tabs">
                <el-tab-pane v-for="tab in sheetStructure" :key="tab.slug" :label="tab.name" :name="tab.slug">
                    <div class="pt-1">
                        <p class="text-gray-600 text-sm mb-6">{{ getTabDescription(tab.slug) }}</p>

                        <!-- Grid para las tarjetas de información -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                            <!-- Iteramos sobre cada sección (tarjeta) -->
                            <template v-for="(fields, sectionName) in tab.fields_by_section" :key="sectionName">
                                <div class="bg-white rounded-xl border border-gray-200 p-5">
                                    <h3 class="font-semibold text-gray-700 mb-4 capitalize">
                                        {{ formatSectionName(sectionName) }}
                                    </h3>

                                    <!-- Iteramos sobre los campos de la sección -->
                                    <div class="space-y-3">
                                        <div v-for="field in fields" :key="field.slug">

                                            <!-- Renderizado para tipo 'multiselect' (tags) -->
                                            <div v-if="field.type === 'multiselect'">
                                                <div
                                                    v-if="parsedSheetData[field.slug] && parsedSheetData[field.slug].length > 0">
                                                    <el-tag v-for="item in parsedSheetData[field.slug]" :key="item"
                                                        type="primary" effect="light" class="mr-2 mb-1">
                                                        {{ item }}
                                                    </el-tag>
                                                </div>
                                                <p v-else class="text-gray-400 italic text-sm">N/A</p>
                                            </div>

                                            <!-- Renderizado para tipo 'checklist' -->
                                            <div v-else-if="field.type === 'checklist'">
                                                <h4 class="text-sm font-medium text-gray-500 mb-1">{{ field.label }}
                                                </h4>
                                                <div
                                                    v-if="parsedSheetData[field.slug] && parsedSheetData[field.slug].length > 0">
                                                    <p v-for="item in parsedSheetData[field.slug]" :key="item"
                                                        class="flex items-center text-sm text-gray-800">
                                                        <el-icon class="text-blue-500 mr-2">
                                                            <SuccessFilled />
                                                        </el-icon> {{ item }}
                                                    </p>
                                                </div>
                                                <p v-else class="text-gray-400 italic text-sm">N/A</p>
                                            </div>

                                            <!-- Renderizado por defecto (texto key-value) -->
                                            <div v-else class="flex justify-between items-start text-sm">
                                                <span class="text-gray-500 mr-2">{{ field.label }}</span>
                                                <span class="text-gray-800 font-medium text-right">{{
                                                    parsedSheetData[field.slug] || '-' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </el-tab-pane>
            </el-tabs>

        </div>
    </AppLayout>
</template>

<script>
// Asegúrate de que la ruta a tu layout y los componentes sea correcta
import AppLayout from '@/Layouts/AppLayout.vue';
import { ElButton, ElTabs, ElTabPane, ElTag, ElIcon } from 'element-plus';
import { Edit, Download, Picture, SuccessFilled } from '@element-plus/icons-vue';

export default {
    name: 'ProductsShow',
    components: {
        AppLayout,
        ElButton,
        ElTabs,
        ElTabPane,
        ElTag,
        ElIcon,
        Edit,
        Download,
        Picture,
        SuccessFilled,
    },
    props: {
        product: {
            type: Object,
            required: true,
        },
        sheetStructure: {
            type: Array,
            required: true,
        },
    },
    data() {
        return {
            // La pestaña activa será la primera de la estructura por defecto
            activeTab: this.sheetStructure.length > 0 ? this.sheetStructure[0].slug : '',
        };
    },
    computed: {
        // Es una buena práctica parsear el JSON en una propiedad computada
        // para evitar errores y hacerlo solo una vez.
        parsedSheetData() {
            try {
                return JSON.parse(this.product.sheet_data);
            } catch (e) {
                console.error("Error al parsear sheet_data del producto:", e);
                return {}; // Devolver objeto vacío en caso de error
            }
        }
    },
    methods: {
        formatSectionName(slug) {
            // Convierte 'dimensiones_y_formatos_cm' a 'Dimensiones y formatos (cm)'
            if (!slug) return '';
            return slug.replace(/_/g, ' ');
        },
        getTabDescription(tabSlug) {
            const descriptions = {
                diseno: 'En esta pestaña te permite tener una visión completa del diseño y su estructura para asegurar la correcta producción.',
                acabados: 'Detalles sobre los acabados finales del producto.',
                costos_y_precios: 'Información sobre la estructura de costos y precios de venta.',
                historial: 'Historial de cambios y versiones de la ficha técnica.'
            };
            return descriptions[tabSlug] || '';
        },
        handleEditRequest() {
            // Lógica para redirigir a la página de solicitud de cambio
            // this.$inertia.get(route('products.change-request.create', this.product.id));
            alert('Redirigiendo para crear una solicitud de cambio...');
        },
        downloadProof() {
            alert('Iniciando descarga del archivo Proof...');
        }
    }
};
</script>

<style>
/* Pequeños ajustes para que los tabs de Element Plus se vean mejor */
.product-sheet-tabs .el-tabs__header {
    margin-bottom: 0;
}

.product-sheet-tabs .el-tabs__nav-wrap {
    padding: 0 1.5rem;
    /* 24px */
    background-color: white;
    box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
    border-radius: 12px;
}

.product-sheet-tabs .el-tabs__item {
    padding: 0 20px;
    height: 50px;
}
</style>
