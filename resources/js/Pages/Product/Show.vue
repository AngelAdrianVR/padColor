<template>
    <AppLayout title="Detalles del Producto">
        <div class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto">

            <!-- 1. Detalles del Producto (Datos Maestros) -->
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Detalles del producto</h2>
            <div class="bg-white shadow-sm rounded-xl p-6 mb-8">
                <div class="flex flex-col sm:flex-row items-start justify-between">
                    <div class="flex items-center space-x-6 w-full">
                        <!-- Imagen del producto -->
                        <div
                            class="w-28 h-28 bg-gray-100 border border-gray-200 rounded-lg flex items-center justify-center flex-shrink-0">
                            <PhotoIcon class="w-12 h-12 text-gray-400" />
                        </div>

                        <!-- Información en columnas -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-x-8 gap-y-4 w-full">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-800">{{ product.name }}</h1>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500 block">Código</span>
                                <p class="font-medium text-gray-800">{{ product.code || '-' }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500 block">Fecha de creación</span>
                                <p class="font-medium text-gray-800">{{ formattedCreatedAt }}</p>
                            </div>
                            <div></div> <!-- Celda vacía para alinear -->
                            <div>
                                <span class="text-sm text-gray-500 block">Descripción</span>
                                <p class="font-medium text-gray-800">{{ product.description || '-' }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500 block">Temporada</span>
                                <p class="font-medium text-gray-800">{{ product.season || '-' }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500 block">Material</span>
                                <p class="font-medium text-gray-800">{{ product.material || '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Botón de Editar -->
                    <div class="mt-4 sm:mt-0 sm:ml-6 flex-shrink-0">
                        <el-button @click="editGeneralData">
                            <PencilIcon class="w-4 h-4 mr-2" />
                            Editar datos generales
                        </el-button>
                    </div>
                </div>
            </div>

            <!-- 2. Ficha Técnica del Producto -->
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Ficha técnica del producto</h2>
            <div class="bg-white shadow-sm rounded-xl p-6">
                <!-- NUEVO: Alerta de Solicitud Pendiente -->
                <div v-if="pendingChangeRequest"
                    class="mb-6 p-4 bg-yellow-50 border border-yellow-300 rounded-lg text-yellow-800">
                    <h4 class="font-bold">Hay una solicitud de cambio pendiente</h4>
                    <p class="text-sm mt-1">
                        El usuario <strong>{{ pendingChangeRequest.requester.name }}</strong> solicitó cambios el
                        <strong>{{ formatDate(pendingChangeRequest.created_at) }}</strong>.
                        <br>No se pueden realizar nuevas modificaciones hasta que esta solicitud sea aprobada o
                        rechazada.
                    </p>
                </div>

                <!-- Botones de Acción de Ficha Técnica -->
                <div class="flex justify-end mb-4">
                    <div v-if="isEditing">
                        <el-button @click="cancelEdit">Cancelar</el-button>
                        <el-button type="primary" @click="saveSheetData" :loading="form.processing">Enviar
                            Solicitud</el-button>
                    </div>
                    <div v-else>
                        <el-tooltip content="Hay una solicitud pendiente de aprobación"
                            :disabled="!pendingChangeRequest" placement="top">
                            <span class="inline-block"> <!-- Wrapper para tooltip en botón deshabilitado -->
                                <el-button @click="isEditing = true" :disabled="!!pendingChangeRequest">
                                    <PencilSquareIcon class="w-4 h-4 mr-2" />
                                    Editar ficha técnica
                                </el-button>
                            </span>
                        </el-tooltip>
                    </div>
                </div>

                <!-- Pestañas -->
                <el-tabs v-model="activeTab" class="product-sheet-tabs">
                    <el-tab-pane v-for="tab in sheetStructure" :key="tab.slug" :label="tab.name" :name="tab.slug">
                        <Component :is="tabs[tab.slug]" v-if="activeTab === tab.slug" :product="product"
                            :fields-by-section="tab.fields_by_section" :description="getTabDescription(tab.slug)"
                            :is-editing="isEditing" :form="form" />
                    </el-tab-pane>
                </el-tabs>
            </div>
        </div>
    </AppLayout>
</template>

<script>
import { useForm, Link } from '@inertiajs/vue3';
import { shallowRef } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Design from './Tabs/Design.vue';
import Finishes from './Tabs/Finishes.vue';
import CostsAndPrices from './Tabs/CostsAndPrices.vue';
import { PhotoIcon, PencilIcon, PencilSquareIcon } from '@heroicons/vue/24/outline';
import { ElMessage, ElTooltip } from 'element-plus';

export default {
    name: 'ProductsShow',
    components: {
        AppLayout,
        Design,
        Finishes,
        CostsAndPrices,
        PhotoIcon,
        PencilIcon,
        PencilSquareIcon,
        Link,
        ElTooltip
    },
    props: {
        product: { type: Object, required: true },
        sheetStructure: { type: Array, required: true },
        pendingChangeRequest: { type: Object, default: null }, // Prop para recibir la solicitud
    },
    setup(props) {
        const form = useForm({
            sheet_data: props.product.sheet_data || {},
            new_documents: [],
        });

        const tabs = shallowRef({
            diseno: Design,
            acabados: Finishes,
            costos_y_precios: CostsAndPrices,
        });

        return { form, tabs };
    },
    data() {
        return {
            activeTab: this.sheetStructure.length > 0 ? this.sheetStructure[0].slug : '',
            isEditing: false,
        };
    },
    computed: {
        formattedCreatedAt() {
            return this.formatDate(this.product.created_at);
        }
    },
    methods: {
        formatDate(dateString) {
            if (!dateString) return '-';
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            return new Date(dateString).toLocaleDateString('es-MX', options);
        },
        getTabDescription(tabSlug) {
            const descriptions = {
                diseno: 'En esta pestaña se visualiza la estructura completa del diseño del producto.',
                acabados: 'Detalles sobre los acabados especiales y otros procesos adicionales.',
                costos_y_precios: 'Administración de los costos de producción y precios de venta.'
            };
            return descriptions[tabSlug] || '';
        },
        editGeneralData() {
            this.$inertia.get(route('products.edit', this.product.id));
        },
        saveSheetData() {
            this.form.post(route('products.sheet.update', this.product.id), {
                preserveScroll: true,
                onSuccess: () => {
                    this.isEditing = false;
                },
                onError: (errors) => {
                    console.error("Errores al enviar solicitud: ", errors);
                    ElMessage({ type: 'error', message: 'Hubo un error al enviar la solicitud.' });
                }
            });
        },
        cancelEdit() {
            this.form.reset();
            this.form.clearErrors();
            this.isEditing = false;
        }
    }
};
</script>