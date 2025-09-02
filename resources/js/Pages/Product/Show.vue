<template>
    <AppLayout title="Detalles del Producto">
        <div class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto">

            <!-- 1. Detalles del Producto -->
            <div class="flex items-center space-x-6">
                <Back :to="route('products.index')" />
                <h2 class="text-base font-semibold text-black">Detalles del producto</h2>
            </div>
            <div class="bg-[#f2f2f2] border border-grayD9 rounded-3xl shadow-sm p-3 mt-4">
                <div class="flex flex-col sm:flex-row items-start justify-between">
                    <div class="flex items-center space-x-6 w-full">
                        <!-- Imagen del producto -->
                        <div
                            class="size-40 bg-white border border-grayD9 rounded-2xl flex items-center justify-center flex-shrink-0">
                            <img v-if="product.media.length" :src="product.media[0].original_url"
                                class="w-full h-full object-contain rounded-2xl" />
                            <PhotoIcon v-else class="w-12 h-12 text-gray-400" />
                        </div>

                        <!-- Información en columnas -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-x-4 gap-y-4 w-full">
                            <div class="col-span-full">
                                <h1 class="text-base font-bold text-gray-800">{{ product.name }}</h1>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500 block">Código</span>
                                <p class="font-medium text-gray-800">{{ product.code || '-' }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500 block">Fecha de creación</span>
                                <p class="font-medium text-gray-800">{{ formatDate(product.created_at) }}</p>
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
                    <div v-if="hasPermition('Editar productos')" class="mt-4 sm:mt-0 sm:ml-6 flex-shrink-0">
                        <el-button @click="editGeneralData">
                            <PencilIcon class="w-4 h-4 mr-2" />
                            Editar datos generales
                        </el-button>
                    </div>
                </div>
            </div>

            <!-- 2. Ficha Técnica del Producto -->
            <section v-if="hasPermition('Ver información de diseño en fichas técnicas') ||
                hasPermition('Ver información de acabados en fichas técnicas') ||
                hasPermition('Ver información de costos y precios en fichas técnicas') ||
                hasPermition('Ver historial en fichas técnicas')">
                <h2 class="text-base font-bold text-black mt-8">Ficha técnica del producto</h2>
                <div class="bg-white rounded-xl">
                    <!-- Banner de Solicitud Pendiente -->
                    <div v-if="pendingChangeRequest"
                        class="mb-6 p-4 bg-blue-50 border-l-4 border-blue-400 flex items-center justify-between">
                        <div>
                            <h4 class="font-bold text-blue-800">Hay una solicitud de cambio pendiente</h4>
                            <p class="text-sm text-blue-700 mt-1">
                                El usuario <strong>{{ pendingChangeRequest.requester_name }}</strong> solicitó cambios
                                el
                                <strong>{{ formatDate(pendingChangeRequest.created_at) }}</strong>.
                            </p>
                        </div>
                        <el-button @click="isModalVisible = true">
                            <EyeIcon class="size-4 mr-2" />
                            Revisar Solicitud
                        </el-button>
                    </div>

                    <!-- Botones de Acción de Ficha Técnica -->
                    <div class="flex justify-end">
                        <div v-if="isEditing">
                            <el-button @click="cancelEdit">Cancelar</el-button>
                            <el-button type="primary" @click="openRequesterCommentsModal"
                                :loading="form.processing">Enviar
                                Solicitud</el-button>
                        </div>
                        <div v-else-if="hasPermition('Editar fichas técnicas')">
                            <el-tooltip content="Hay una solicitud pendiente de aprobación"
                                :disabled="!pendingChangeRequest" placement="top">
                                <span class="inline-block">
                                    <el-button @click="isEditing = true" :disabled="!!pendingChangeRequest">
                                        <PencilSquareIcon class="size-4 mr-2" />
                                        Editar ficha técnica
                                    </el-button>
                                </span>
                            </el-tooltip>
                        </div>
                    </div>

                    <!-- Pestañas -->
                    <el-tabs v-model="activeTab" class="product-sheet-tabs">
                        <el-tab-pane v-for="tab in sheetStructure" :key="tab.slug" :label="tab.name" :name="tab.slug">
                            <Component :is="tabs[tab.slug]"
                                v-if="activeTab === tab.slug && hasPermition('Ver información de ' + tab.name.toLowerCase() + ' en fichas técnicas')"
                                :product="product" :fields-by-section="tab.fields_by_section"
                                :description="getTabDescription(tab.slug)" :is-editing="isEditing" :form="form" />
                        </el-tab-pane>
                        <!-- PESTAÑA DE HISTORIAL AHORA USA EL NUEVO COMPONENTE -->
                        <el-tab-pane label="Historial" name="history">
                            <HistoryTab v-if="hasPermition('Ver historial en fichas técnicas')"
                                :history="changeRequestHistory" />
                        </el-tab-pane>
                    </el-tabs>
                </div>
            </section>
        </div>

        <!-- MODAL PARA VER SOLICITUD DE CAMBIO -->
        <el-dialog v-model="isModalVisible" :title="'Solicitud de Cambio para ' + product.name" width="70%" top="5vh">
            <div v-if="pendingChangeRequest" class="space-y-6">
                <!-- Información General -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6  text-[13px] p-4 bg-gray-50 rounded-lg">
                    <div>
                        <span class="font-semibold text-gray3F block">Solicitante:</span>
                        <span class="mt-1 inline-block">{{ pendingChangeRequest.requester_name }}</span>
                    </div>
                    <div>
                        <span class="font-semibold text-gray3F block">Fecha de Solicitud:</span>
                        <span class="mt-1 inline-block">{{ formatDate(pendingChangeRequest.created_at, true) }}</span>
                    </div>
                    <div v-if="pendingChangeRequest.requester_comments" class="md:col-span-3 lg:col-span-1">
                        <span class="font-semibold text-gray3F block">Comentarios del Solicitante:</span>
                        <p
                            class="text-gray-700 text-xs mt-1 italic whitespace-pre-wrap">
                            "{{ pendingChangeRequest.requester_comments }}"</p>
                    </div>
                </div>

                <!-- Revisores -->
                <div>
                    <h4 class="font-semibold text-gray-700 mb-2">Revisores y Votos</h4>
                    <div class="space-y-3">
                        <div v-for="reviewer in pendingChangeRequest.reviewers" :key="reviewer.name">
                            <el-tag size="small" class="mr-1" :type="getReviewerStatusTag(reviewer.status)">
                                {{ reviewer.name }} ({{ translateStatus(reviewer.status) }})
                            </el-tag>
                            <p v-if="reviewer.comments" class="text-xs text-gray-500 italic mt-1 pl-2 border-l-2 ml-2">
                                "{{ reviewer.comments }}"</p>
                        </div>
                    </div>
                </div>

                <!-- Pestañas del Modal -->
                <el-tabs v-model="modalActiveTab">
                    <el-tab-pane label="Cambios Propuestos" name="changes">
                        <div class="max-h-[50vh] overflow-y-auto pr-2">
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
                    <el-button @click="isModalVisible = false" :disabled="!!processingRequest" plain type="info">
                        Cerrar
                    </el-button>

                    <div v-if="pendingChangeRequest && pendingChangeRequest.is_reviewer">
                        <div v-if="userHasVoted" class="text-sm text-gray-500 italic">
                            Ya has votado en esta solicitud (tu voto fue: <span class="font-semibold">{{
                                translateStatus(pendingChangeRequest.current_user_vote_status) }}</span>).
                        </div>
                        <div v-else class="space-x-2">
                            <el-button type="danger" @click="openDecisionDialog(false)"
                                :loading="processingRequest === pendingChangeRequest.id && isRejecting"
                                :disabled="!!processingRequest">Rechazar</el-button>
                            <el-button type="success" @click="openDecisionDialog(true)"
                                :loading="processingRequest === pendingChangeRequest.id && !isRejecting"
                                :disabled="!!processingRequest">Aprobar</el-button>
                        </div>
                    </div>
                </div>
            </template>
        </el-dialog>

        <!-- MODAL PARA COMENTARIOS DEL SOLICITANTE -->
        <el-dialog v-model="requesterCommentsModalVisible" title="Enviar Solicitud de Cambio" width="40%">
            <span>Opcionalmente, añade un comentario para describir los cambios que propones.</span>
            <el-input v-model="requesterComments" type="textarea" :rows="4"
                placeholder="Ej: Se actualizó el costo de producción y se añadieron los planos finales." class="mt-4" />
            <template #footer>
                <el-button @click="requesterCommentsModalVisible = false">Cancelar</el-button>
                <el-button type="primary" @click="confirmAndSendRequest" :loading="form.processing">Confirmar y
                    Enviar</el-button>
            </template>
        </el-dialog>


        <!-- MODAL GENÉRICO PARA DECISIÓN DEL REVISOR (APROBAR/RECHAZAR) -->
        <el-dialog v-model="decisionModalVisible" :title="isApproving ? 'Aprobar Solicitud' : 'Rechazar Solicitud'"
            width="30%">
            <span v-if="isApproving">Puedes añadir un comentario opcional con tu aprobación.</span>
            <span v-else>Por favor, escribe el motivo del rechazo. Este comentario es obligatorio.</span>
            <el-input v-model="decisionComments" type="textarea" :rows="3"
                :placeholder="isApproving ? 'Comentario opcional...' : 'Motivo del rechazo...'" class="mt-4" />
            <template #footer>
                <span class="dialog-footer">
                    <el-button @click="decisionModalVisible = false">Cancelar</el-button>
                    <el-button :type="isApproving ? 'success' : 'primary'" @click="confirmDecision()"
                        :loading="processingRequest === (pendingChangeRequest && pendingChangeRequest.id)">
                        {{ isApproving ? 'Confirmar Aprobación' : 'Confirmar Rechazo' }}
                    </el-button>
                </span>
            </template>
        </el-dialog>
    </AppLayout>
</template>

<script>
import { useForm, Link, router } from '@inertiajs/vue3';
import { shallowRef } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Design from './Tabs/Design.vue';
import Finishes from './Tabs/Finishes.vue';
import CostsAndPrices from './Tabs/CostsAndPrices.vue';
import HistoryTab from './Tabs/History.vue';
import { PhotoIcon, PencilIcon, PencilSquareIcon, EyeIcon, PaperClipIcon } from '@heroicons/vue/24/outline';
import { ElMessage, ElDialog, ElBadge, ElTooltip } from 'element-plus';
import Back from '@/Components/MyComponents/Back.vue';

export default {
    name: 'ProductsShow',
    components: {
        AppLayout,
        Link,
        Back,
        Design,
        Finishes,
        CostsAndPrices,
        HistoryTab,
        PhotoIcon,
        PencilIcon,
        PencilSquareIcon,
        EyeIcon,
        PaperClipIcon,
        ElDialog,
        ElBadge,
        ElTooltip
    },
    props: {
        product: { type: Object, required: true },
        sheetStructure: { type: Array, required: true },
        pendingChangeRequest: { type: Object, default: null },
        changeRequestHistory: { type: Array, default: () => [] },
    },
    setup(props) {
        const form = useForm({
            sheet_data: props.product.sheet_data || {},
            new_documents: [],
            comments: '',
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
            activeTab: this.sheetStructure.length > 0 ? this.sheetStructure[0].slug : 'history',
            isEditing: false,
            // Modal principal
            isModalVisible: false,
            modalActiveTab: 'changes',
            // Lógica de aprobación/rechazo
            processingRequest: null,
            isRejecting: false,
            // Modal de comentarios del solicitante
            requesterCommentsModalVisible: false,
            requesterComments: '',
            // Modal de decisión del revisor
            decisionModalVisible: false,
            isApproving: false,
            decisionComments: '',
        };
    },
    computed: {
        userHasVoted() {
            if (!this.pendingChangeRequest || !this.pendingChangeRequest.is_reviewer) {
                return true;
            }
            return this.pendingChangeRequest.current_user_vote_status !== 'pending';
        }
    },
    methods: {
        hasPermition(permission) {
            return this.$page.props.auth.user.permissions.includes(permission);
        },
        formatDate(dateString, withTime = false) {
            if (!dateString) return '-';
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            if (withTime) {
                options.hour = '2-digit';
                options.minute = '2-digit';
            }
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

        cancelEdit() {
            this.form.reset();
            this.form.clearErrors();
            this.isEditing = false;
        },
        translateStatus(status) {
            const map = {
                pending: 'Pendiente',
                approved: 'Aprobado',
                rejected: 'Rechazado',
            };
            return map[status] || status;
        },
        getReviewerStatusTag(status) {
            const map = {
                pending: 'info',
                approved: 'success',
                rejected: 'danger',
            };
            return map[status] || 'info';
        },
        // --- LÓGICA DEL SOLICITANTE ---
        openRequesterCommentsModal() {
            this.requesterComments = '';
            this.requesterCommentsModalVisible = true;
        },
        confirmAndSendRequest() {
            this.form.comments = this.requesterComments;
            this.form.post(route('products.sheet.update', this.product.id), {
                preserveScroll: true,
                onSuccess: () => {
                    this.isEditing = false;
                    this.requesterCommentsModalVisible = false;
                    this.form.reset();
                },
                onError: (errors) => {
                    console.error("Errores al enviar solicitud: ", errors);
                    ElMessage({ type: 'error', message: 'Hubo un error al enviar la solicitud.' });
                }
            });
        },
        // --- LÓGICA DEL REVISOR ---
        openDecisionDialog(isApproving) {
            this.isApproving = isApproving;
            this.decisionComments = '';
            this.decisionModalVisible = true;
        },
        confirmDecision() {
            const decision = this.isApproving ? 'approved' : 'rejected';
            if (decision === 'rejected' && !this.decisionComments) {
                ElMessage.warning('Debes proporcionar un motivo para el rechazo.');
                return;
            }
            this.submitDecision(decision, this.decisionComments);
        },
        submitDecision(decision, comments = '') {
            if (!this.pendingChangeRequest) return;
            const requestId = this.pendingChangeRequest.id;

            router.post(route('change-requests.decide', requestId), {
                decision: decision,
                comments: comments,
            }, {
                preserveScroll: true,
                onStart: () => {
                    this.processingRequest = requestId;
                    this.isRejecting = (decision === 'rejected');
                },
                onSuccess: () => {
                    this.decisionModalVisible = false;
                    this.isModalVisible = false;
                },
                onError: (errors) => {
                    console.error("Error al registrar voto:", errors);
                    ElMessage.error('Hubo un error al registrar tu voto.');
                },
                onFinish: () => { this.processingRequest = null; },
            });
        },
    }
};
</script>
