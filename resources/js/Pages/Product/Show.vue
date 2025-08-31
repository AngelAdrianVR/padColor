<template>
    <AppLayout title="Ficha Técnica del Producto">
        <div class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto">
            <!-- 1. Encabezado General -->
            <div class="bg-white shadow-sm rounded-xl p-6 mb-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div
                            class="w-24 h-24 bg-gray-100 border border-gray-200 rounded-lg flex items-center justify-center flex-shrink-0">
                            <PhotoIcon class="w-10 h-10 text-gray-400" />
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-800">{{ product.name }}</h1>
                            <p class="text-sm text-gray-500 mt-1">Código: {{ product.code }}</p>
                            <p class="text-sm text-gray-500">Cliente: PadColor Insumos Gráficos</p>
                        </div>
                    </div>
                    <div class="flex space-x-2 mt-4 sm:mt-0">
                        <el-button @click="handleEditRequest">
                            <PencilIcon class="w-4 h-4 mr-2" /> Editar
                        </el-button>
                        <el-button type="primary" plain @click="downloadProof">
                            <ArrowDownTrayIcon class="w-4 h-4 mr-2" /> Proof
                        </el-button>
                    </div>
                </div>
            </div>

            <!-- Botón de Edición General -->
            <div class="flex justify-end mb-4">
                <el-button v-if="!isEditing" @click="toggleEditMode">
                    <PencilIcon class="w-4 h-4 mr-2" /> Editar Ficha
                </el-button>
                <div v-else class="flex space-x-2">
                    <el-button @click="cancelEdit">Cancelar</el-button>
                    <el-button type="primary" @click="submit" :loading="form.processing">
                        Guardar Cambios
                    </el-button>
                </div>
            </div>

            <!-- 2. Pestañas de la Ficha Técnica -->
            <el-tabs v-model="activeTab" class="product-sheet-tabs">
                <el-tab-pane v-for="tab in sheetStructure" :key="tab.slug" :label="tab.name" :name="tab.slug">
                    <component v-if="activeTab === tab.slug" :is="resolveTabComponent(tab.slug)" :product="product"
                        :fields-by-section="tab.fields_by_section" :description="getTabDescription(tab.slug)"
                        :is-editing="isEditing" :form-data="form.sheet_data"
                        @update:form-data="form.sheet_data = $event" />
                </el-tab-pane>
            </el-tabs>
        </div>
    </AppLayout>
</template>

<script>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { PencilIcon, ArrowDownTrayIcon, PhotoIcon } from '@heroicons/vue/24/outline';
import Design from './Tabs/Design.vue';
import Finishes from './Tabs/Finishes.vue';
import CostsAndPrices from './Tabs/CostsAndPrices.vue';

export default {
    name: 'ProductsShow',
    components: {
        AppLayout, PencilIcon, ArrowDownTrayIcon, PhotoIcon,
        Design, Finishes, CostsAndPrices,
    },
    props: {
        product: { type: Object, required: true },
        sheetStructure: { type: Array, required: true },
    },
    setup(props) {
        const form = useForm({
            sheet_data: JSON.parse(JSON.stringify(props.product.sheet_data || {})),
        });

        return { form };
    },
    data() {
        return {
            activeTab: this.sheetStructure.length > 0 ? this.sheetStructure[0].slug : '',
            isEditing: false,
        };
    },
    methods: {
        toggleEditMode() {
            this.isEditing = !this.isEditing;
            if (this.isEditing) {
                // Reiniciar el formulario con los datos actuales por si se canceló antes
                this.form.reset();
            }
        },
        cancelEdit() {
            this.form.reset();
            this.isEditing = false;
        },
        submit() {
            // Aquí iría la lógica para enviar una "change_request"
            // Por ahora, actualizaremos directamente para probar el formulario
            this.form.put(route('products.sheet.update', this.product.id), {
                onSuccess: () => {
                    this.isEditing = false;
                    // Aquí podrías mostrar una notificación de éxito
                },
            });
        },
        resolveTabComponent(tabSlug) {
            const components = {
                diseno: 'Design',
                acabados: 'Finishes',
                costos_y_precios: 'CostsAndPrices',
            };
            return components[tabSlug] || null;
        },
        getTabDescription(tabSlug) {
            return 'Descripción de la pestaña...';
        },
    }
};
</script>
