<template>
    <AppLayout title="Gestor de Ficha Técnica">
        <div class="flex justify-between items-center max-w-7xl mx-auto sm:px-6 lg:px-8 mt-7">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Gestor de Estructura de Ficha Técnica
            </h2>
            <el-button type="primary" @click="openTabModal()">
                <PlusIcon class="w-4 h-4 mr-2" />
                Nueva Pestaña
            </el-button>
        </div>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <p class="text-gray-600 mb-6">
                        Desde aquí puedes administrar las pestañas, secciones, campos y opciones que componen la ficha
                        técnica de todos los productos.
                    </p>

                    <el-collapse v-model="activeTabName" accordion>
                        <el-collapse-item v-for="tab in tabs" :key="tab.id" :name="tab.id">
                            <template #title>
                                <div class="flex justify-between items-center w-full pr-8">
                                    <span class="font-semibold text-lg">{{ tab.name }}</span>
                                    <div>
                                        <el-button size="small"
                                            @click.stop="openTabModal(tab)">Editar</el-button>
                                        <el-button size="small"
                                            @click.stop="deleteTab(tab)">Eliminar</el-button>
                                    </div>
                                </div>
                            </template>

                            <div class="p-4 space-y-4">
                                <div v-for="(fields, sectionName) in groupFieldsBySection(tab.fields)"
                                    :key="sectionName">
                                    <h3 class="font-semibold text-gray-600 mb-2 capitalize border-b pb-1">{{
                                        sectionName.replace(/_/g, ' ') }}</h3>
                                    <div class="space-y-2">
                                        <div v-for="field in fields" :key="field.id"
                                            class="flex items-center justify-between p-2 rounded hover:bg-gray-50">
                                            <div>
                                                <p class="font-medium">{{ field.label }} <span
                                                        class="text-xs text-gray-400">({{ field.type
                                                        }})</span></p>
                                                <p class="text-xs text-gray-500">slug: {{ field.slug }}</p>
                                            </div>
                                            <div>
                                                <el-button
                                                    v-if="['select', 'radio', 'multicheckbox', 'checklist'].includes(field.type)"
                                                    size="small" @click="openOptionsModal(field)">Opciones</el-button>
                                                <el-button size="small"
                                                    @click="openFieldModal(field, tab.id)">Editar</el-button>
                                                <el-button size="small" type="danger" plain
                                                    @click="deleteField(field)">Eliminar</el-button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <el-button class="mt-4" @click="openFieldModal(null, tab.id)">
                                    <PlusIcon class="w-4 h-4 mr-2" />
                                    Añadir Campo a esta Pestaña
                                </el-button>
                            </div>
                        </el-collapse-item>
                    </el-collapse>

                </div>
            </div>
        </div>

        <!-- Modales -->
        <TabModal :is-open="isTabModalOpen" :tab="editableTab" @close="isTabModalOpen = false" />
        <FieldModal :is-open="isFieldModalOpen" :field="editableField" :tab-id="currentTabId"
            @close="isFieldModalOpen = false" />
        <OptionsModal :is-open="isOptionsModalOpen" :field="fieldForOptions" @close="isOptionsModalOpen = false" />

    </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { ElCollapse, ElCollapseItem, ElButton, ElMessageBox } from 'element-plus';
import { PlusIcon } from '@heroicons/vue/24/outline';
import TabModal from './Partials/TabModal.vue';
import FieldModal from './Partials/FieldModal.vue';
import OptionsModal from './Partials/OptionsModal.vue';

export default {
    name: 'ProductSheetStructure',
    components: {
        AppLayout,
        ElCollapse,
        ElCollapseItem,
        ElButton,
        PlusIcon,
        TabModal,
        FieldModal,
        OptionsModal,
    },
    props: {
        tabs: Array,
    },
    data() {
        return {
            activeTabName: this.tabs.length > 0 ? this.tabs[0].id : null,
            isTabModalOpen: false,
            editableTab: null,
            isFieldModalOpen: false,
            editableField: null,
            currentTabId: null,
            isOptionsModalOpen: false,
            fieldForOptions: null,
        };
    },
    methods: {
        groupFieldsBySection(fields) {
            return fields.reduce((acc, field) => {
                (acc[field.section] = acc[field.section] || []).push(field);
                return acc;
            }, {});
        },
        openTabModal(tab = null) {
            this.editableTab = tab;
            this.isTabModalOpen = true;
        },
        deleteTab(tab) {
            ElMessageBox.confirm(`¿Estás seguro de que quieres eliminar la pestaña "${tab.name}"? Todos sus campos y secciones serán eliminados permanentemente.`, 'Confirmar Eliminación', { type: 'warning' })
                .then(() => {
                    router.delete(route('product-sheet-structure.tabs.destroy', tab.id));
                }).catch(() => { });
        },
        openFieldModal(field = null, tabId) {
            this.editableField = field;
            this.currentTabId = tabId;
            this.isFieldModalOpen = true;
        },
        deleteField(field) {
            ElMessageBox.confirm(`¿Estás seguro de que quieres eliminar el campo "${field.label}"?`, 'Confirmar Eliminación', { type: 'warning' })
                .then(() => {
                    router.delete(route('product-sheet-structure.fields.destroy', field.id));
                }).catch(() => { });
        },
        openOptionsModal(field) {
            this.fieldForOptions = field;
            this.isOptionsModalOpen = true;
        }
    }
}
</script>