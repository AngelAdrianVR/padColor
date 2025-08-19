<template>
    <DialogModal :show="show" @close="closeModal" max-width="3xl">
        <template #title>
            <h2 class="text-xl font-semibold">Detalles de importación</h2>
        </template>
        <template #content>
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <span class="text-sm font-bold text-gray-700">Nº {{ importData.id }}</span>
                    <p class="px-2 py-1 text-xs font-semibold rounded-full">
                        <!-- <component :is="column.icon" class="size-4" /> -->
                        <span class="" :class="statusClass(importData.status)">
                            {{ formatStatus(importData.status) }}
                        </span>
                    </p>
                </div>
                <PrimaryButton @click="editImport"
                    class="!text-primary !bg-white border !border-gray-300 !rounded-md text-sm">
                    <PencilIcon class="size-4 inline mr-1" />
                    Editar
                </PrimaryButton>
            </div>
            <el-tabs v-model="activeTab" class="mt-4">
                <!-- Pestaña: Información General -->
                <el-tab-pane label="Información general" name="general">
                    <div class="divide-y divide-gray-200">
                        <div class="py-3 flex justify-between text-sm">
                            <span class="text-gray-500">Creado por</span>
                            <span class="font-semibold text-gray-800">{{ importData.user?.name }}</span>
                        </div>
                        <div class="py-3 flex justify-between text-sm">
                            <span class="text-gray-500">Creado el</span>
                            <span class="font-semibold text-gray-800">{{ formatDate(importData.created_at) }}</span>
                        </div>
                        <div class="py-3 flex justify-between text-sm">
                            <span class="text-gray-500">Proveedor</span>
                            <span class="font-semibold text-gray-800">{{ importData.supplier?.name }}</span>
                        </div>
                        <div class="py-3 flex justify-between text-sm">
                            <span class="text-gray-500">Agente aduanal</span>
                            <span class="font-semibold text-gray-800">{{ importData.customs_agent?.name }}</span>
                        </div>
                        <div class="py-3 flex justify-between text-sm">
                            <span class="text-gray-500">Incoterm</span>
                            <span class="font-semibold text-gray-800">{{ importData.incoterm }}</span>
                        </div>
                        <div class="py-3 flex justify-between text-sm">
                            <span class="text-gray-500">ETA (Fecha estimada de llegada)</span>
                            <span class="font-semibold text-blue-600">{{ formatDate(importData.estimated_arrival_date)
                            }}</span>
                        </div>
                    </div>
                </el-tab-pane>

                <!-- Pestaña: Documentos -->
                <el-tab-pane label="Documentos" name="documents">
                    <p class="text-gray-500 p-4">Sección de documentos en construcción.</p>
                </el-tab-pane>

                <!-- Pestaña: Costos y Pagos -->
                <el-tab-pane label="Costos y pagos" name="costs">
                    <p class="text-gray-500 p-4">Sección de costos y pagos en construcción.</p>
                </el-tab-pane>

                <!-- Pestaña: Mercancías -->
                <el-tab-pane label="Mercancías" name="merchandise">
                    <p class="text-gray-500 p-4">Sección de mercancías en construcción.</p>
                </el-tab-pane>

                <!-- Pestaña: Historial -->
                <el-tab-pane label="Historial" name="history">
                    <p class="text-gray-500 p-4">Sección de historial en construcción.</p>
                </el-tab-pane>
            </el-tabs>
        </template>
    </DialogModal>
</template>

<script>
import DialogModal from '@/Components/DialogModal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { PencilIcon } from '@heroicons/vue/24/outline';
import { router } from '@inertiajs/vue3';

export default {
    components: {
        DialogModal,
        PrimaryButton,
        PencilIcon,
    },
    props: {
        show: {
            type: Boolean,
            default: false,
        },
        importData: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            activeTab: 'general',
        };
    },
    methods: {
        closeModal() {
            this.$emit('close');
        },
        editImport() {
            router.get(route('imports.edit', this.importData.id));
        },
        formatDate(dateString) {
            if (!dateString) return 'N/A';
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            return new Date(dateString).toLocaleDateString('es-ES', options);
        },
        formatStatus(status) {
            if (!status) return '';
            return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
        },
        statusClass(status) {
            const classes = {
                proveedor: 'bg-red-100 text-red-700',
                puerto_origen: 'bg-yellow-100 text-yellow-700',
                mar: 'bg-orange-100 text-orange-700',
                puerto_destino: 'bg-cyan-100 text-cyan-700',
                entregado: 'bg-green-100 text-green-700',
            };
            return classes[status] || 'bg-gray-100 text-gray-700';
        }
    },
};
</script>
