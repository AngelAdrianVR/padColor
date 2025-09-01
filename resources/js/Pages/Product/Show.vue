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
                <!-- Alerta de Solicitud Pendiente -->
                <div v-if="pendingChangeRequest"
                    class="mb-6 p-4 bg-blue-50 border-l-4 border-blue-400 flex items-center justify-between">
                    <div>
                        <h4 class="font-bold text-blue-800">Hay una solicitud de cambio pendiente</h4>
                        <p class="text-sm text-blue-700 mt-1">
                            El usuario <strong>{{ pendingChangeRequest.requester_name }}</strong> solicitó cambios el
                            <strong>{{ formatDate(pendingChangeRequest.created_at) }}</strong>.
                        </p>
                    </div>
                    <el-button type="primary" plain @click="isModalVisible = true">
                        <EyeIcon class="w-4 h-4 mr-2" />
                        Revisar Solicitud
                    </el-button>
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



        <!-- MODAL PARA VER SOLICITUD DE CAMBIO -->
        <el-dialog v-model="isModalVisible" :title="'Solicitud de Cambio para ' + product.name" width="70%" top="5vh">
            <div v-if="pendingChangeRequest" class="space-y-6">
                <!-- Información General -->
                <div class="grid grid-cols-3 gap-4 text-sm">
                    <div><span class="font-semibold text-gray-600">Solicitante:</span> {{
                        pendingChangeRequest.requester_name }}</div>
                    <div><span class="font-semibold text-gray-600">Fecha:</span> {{
                        formatDate(pendingChangeRequest.created_at, true) }}</div>
                    <div>
                        <span class="font-semibold text-gray-600">Revisores:</span>
                        <span v-if="!pendingChangeRequest.reviewers.length" class="text-gray-500"> Ninguno
                            asignado</span>
                        <div v-else>
                            <el-tag v-for="reviewer in pendingChangeRequest.reviewers" :key="reviewer.name" size="small"
                                class="mr-1" :type="getReviewerStatusTag(reviewer.status)">
                                {{ reviewer.name }} ({{ reviewer.status }})
                            </el-tag>
                        </div>
                    </div>
                </div>

                <!-- Pestañas del Modal -->
                <el-tabs v-model="modalActiveTab">
                    <el-tab-pane label="Cambios Propuestos" name="changes">
                        <div class="max-h-[50vh] overflow-y-auto pr-2">
                            <!-- TABLA DE CAMBIOS ACTUALIZADA -->
                            <el-table :data="pendingChangeRequest.changes" stripe size="small" border>
                                <el-table-column prop="tab" label="Pestaña" width="140" />
                                <el-table-column prop="section" label="Sección" width="180" />
                                <el-table-column prop="label" label="Campo" width="180" />
                                <el-table-column prop="old" label="Valor Anterior">
                                    <template #default="scope">
                                        <span class="text-gray-500 italic">{{ scope.row.old }}</span>
                                    </template>
                                </el-table-column>
                                <el-table-column prop="new" label="Nuevo Valor">
                                    <template #default="scope">
                                        <span class="font-semibold text-blue-600">{{ scope.row.new }}</span>
                                    </template>
                                </el-table-column>
                            </el-table>
                        </div>
                    </el-tab-pane>

                    <el-tab-pane name="files">
                        <template #label>
                            <span>Nuevos Archivos</span>
                            <el-badge :value="pendingChangeRequest.pending_media.length" class="ml-2" type="primary" />
                        </template>
                        <div v-if="pendingChangeRequest.pending_media.length > 0"
                            class="max-h-[50vh] overflow-y-auto pr-2 space-y-2">
                            <div v-for="doc in pendingChangeRequest.pending_media" :key="doc.name"
                                class="flex items-center p-2 border rounded-md">
                                <PaperClipIcon class="w-5 h-5 mr-3 text-gray-400 flex-shrink-0" />
                                <div class="min-w-0">
                                    <a :href="doc.url" target="_blank"
                                        class="text-sm font-medium text-blue-600 hover:underline truncate">{{
                                            doc.name }}</a>
                                    <p class="text-xs text-gray-500">{{ (doc.size / 1024).toFixed(2) }} KB</p>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-center text-gray-500 py-8">No se adjuntaron nuevos archivos en esta
                            solicitud.</p>
                    </el-tab-pane>
                </el-tabs>
            </div>

            <template #footer>
                <div class="flex justify-between items-center w-full">
                    <el-button @click="isModalVisible = false" :disabled="!!processingRequest">Cerrar</el-button>

                    <!-- BOTONES DE ACCIÓN CONDICIONALES -->
                    <div v-if="pendingChangeRequest && pendingChangeRequest.is_reviewer" class="space-x-2">
                        <el-button type="danger" @click="openRejectDialog(pendingChangeRequest)"
                            :loading="processingRequest === pendingChangeRequest.id && isRejecting"
                            :disabled="!!processingRequest">Rechazar</el-button>
                        <el-button type="success" @click="approveRequest(pendingChangeRequest)"
                            :loading="processingRequest === pendingChangeRequest.id && !isRejecting"
                            :disabled="!!processingRequest">Aprobar Cambios</el-button>
                    </div>
                </div>
            </template>
        </el-dialog>
    </AppLayout>
</template>

<script>
import { useForm, Link } from '@inertiajs/vue3';
import { shallowRef } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Design from './Tabs/Design.vue';
import Finishes from './Tabs/Finishes.vue';
import CostsAndPrices from './Tabs/CostsAndPrices.vue';
import { PhotoIcon, PencilIcon, PencilSquareIcon, EyeIcon, PaperClipIcon } from '@heroicons/vue/24/outline';
import { ElMessage, ElMessageBox, ElDialog, ElBadge, ElTooltip } from 'element-plus';

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
        EyeIcon,
        PaperClipIcon,
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
            isModalVisible: false,
            modalActiveTab: 'changes',
            processingRequest: null,
            isRejecting: false,
            rejectDialogVisible: false,
            rejectionComments: '',
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
        },
        // ---- MÉTODOS PARA EL MODAL ----
        getReviewerStatusTag(status) {
            if (status === 'approved') return 'success';
            if (status === 'rejected') return 'danger';
            return 'info';
        },
        // --- LÓGICA DE APROBACIÓN ---
        approveRequest(request) {
            ElMessageBox.confirm(
                `¿Estás seguro de que quieres aprobar los cambios para el producto "${request.product.name}"? Esta acción aplicará los cambios y no se puede deshacer.`,
                'Confirmar Aprobación',
                {
                    confirmButtonText: 'Sí, aprobar',
                    cancelButtonText: 'Cancelar',
                    type: 'success',
                }
            ).then(() => {
                router.post(route('change-requests.approve', request.id), {}, {
                    preserveScroll: true,
                    onStart: () => {
                        this.processingRequest = request.id;
                        this.isRejecting = false;
                    },
                    onSuccess: () => {
                        ElMessage.success('Solicitud aprobada con éxito.');
                        this.isModalVisible = false;
                    },
                    onFinish: () => { this.processingRequest = null; },
                });
            }).catch(() => {
                ElMessage.info('Aprobación cancelada.');
            });
        },
        // --- LÓGICA DE RECHAZO ---
        openRejectDialog(request) {
            this.rejectionComments = '';
            this.rejectDialogVisible = true;
        },
        confirmReject() {
            if (!this.pendingChangeRequest) return;
            const requestId = this.pendingChangeRequest.id;

            router.post(route('change-requests.reject', requestId), {
                comments: this.rejectionComments
            }, {
                preserveScroll: true,
                onStart: () => {
                    this.processingRequest = requestId;
                    this.isRejecting = true;
                },
                onSuccess: () => {
                    ElMessage.info('La solicitud ha sido rechazada.');
                    this.rejectDialogVisible = false;
                    this.isModalVisible = false;
                },
                onError: () => {
                    ElMessage.error('Hubo un error al procesar el rechazo.');
                },
                onFinish: () => { this.processingRequest = null; },
            });
        },
    }
};
</script>