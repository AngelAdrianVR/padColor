<template>
    <div class="p-1">
        <div v-if="history && history.length > 0">
            <el-timeline>
                <el-timeline-item v-for="item in history" :key="item.id" :timestamp="formatDate(item.decided_at, true)"
                    :type="item.status === 'approved' ? 'success' : 'danger'" :hollow="true" size="large">
                    <el-card shadow="hover">
                        <template #header>
                            <div class="flex justify-between items-center">
                                <span class="font-semibold"
                                    :class="item.status === 'approved' ? 'text-green-700' : 'text-red-700'">
                                    Solicitud {{ translateStatus(item.status) }}
                                </span>
                                <span class="text-xs text-gray-500">
                                    Decisión por: {{ item.approver?.name || 'Sistema' }}
                                </span>
                            </div>
                        </template>
                        <div class="text-sm space-y-2">
                            <p class="flex space-x-2">
                                <span class="font-semibold text-gray3F w-[20%]">Solicitado por:</span>
                                {{ item.requester.name }}
                            </p>
                            <p class="flex space-x-2">
                                <span class="font-semibold text-gray3F w-[20%]">Fecha de solicitud:</span>
                                {{ formatDate(item.created_at, true) }}
                            </p>
                            <div v-if="item.requester_comments" class="mt-2 pt-2 border-t flex">
                                <span class="font-semibold text-gray3F w-[20%]">Comentarios de la solicitud:</span>
                                <p class="text-gray-700 italic whitespace-pre-wrap">{{ item.requester_comments }}</p>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t flex justify-end">
                            <button @click="showDetails(item)" class="text-blue-700 text-sm italic">
                                Ver detalles
                            </button>
                        </div>
                    </el-card>
                </el-timeline-item>
            </el-timeline>
        </div>
        <div v-else class="text-center text-gray-400 py-16">
            <p>No hay un historial de cambios para este producto.</p>
        </div>

        <!-- MODAL PARA DETALLES DEL HISTORIAL -->
        <el-dialog v-model="detailsModalVisible" :title="'Detalles de Solicitud Histórica'" width="65%" top="5vh">
            <div v-if="selectedItem" class="space-y-3">
                <!-- Información General -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-[13px] p-4 bg-gray-50 rounded-lg">
                    <div>
                        <span class="font-semibold text-gray3F block">Solicitante:</span>
                        <span class="mt-1 inline-block">{{ selectedItem.requester.name }}</span>
                    </div>
                    <div>
                        <span class="font-semibold text-gray3F block">Fecha de Solicitud:</span>
                        <span class="mt-1 inline-block">{{ formatDate(selectedItem.created_at, true) }}</span>
                    </div>
                    <div v-if="selectedItem.requester_comments" class="md:col-span-3 lg:col-span-1">
                        <span class="font-semibold text-gray3F block">Comentarios del Solicitante:</span>
                        <p
                            class="text-gray-700 text-xs mt-1 italic whitespace-pre-wrap">
                            "{{ selectedItem.requester_comments }}"</p>
                    </div>
                </div>

                <!-- Revisores -->
                <div>
                    <h4 class="font-semibold text-gray-700 mb-2">Decisiones de los Revisores</h4>
                    <div class="space-y-3">
                        <div v-for="reviewer in selectedItem.reviewers" :key="reviewer.name">
                            <el-tag size="small" class="mr-1" :type="getReviewerStatusTag(reviewer.status)">
                                {{ reviewer.name }} ({{ translateStatus(reviewer.status) }})
                            </el-tag>
                            <p v-if="reviewer.comments" class="text-xs text-gray-500 italic mt-1 pl-2 border-l-2 ml-2">
                                "{{ reviewer.comments }}"
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Pestañas del Modal -->
                <el-tabs>
                    <el-tab-pane label="Cambios Propuestos">
                        <div class="max-h-[40vh] overflow-y-auto pr-2">
                            <el-table :data="selectedItem.changes" stripe size="small" border>
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
                    <el-tab-pane>
                        <template #label>
                            <span>Archivos Adjuntos</span>
                            <el-badge :value="selectedItem.attached_media.length" class="ml-2" type="primary" />
                        </template>
                        <div v-if="selectedItem.attached_media.length > 0"
                            class="max-h-[40vh] overflow-y-auto pr-2 space-y-2">
                            <div v-for="doc in selectedItem.attached_media" :key="doc.name"
                                class="flex items-center p-2 border rounded-md">
                                <PaperClipIcon class="w-5 h-5 mr-3 text-gray-400 flex-shrink-0" />
                                <div class="min-w-0">
                                    <a :href="doc.url" target="_blank"
                                        class="text-sm font-medium text-blue-600 hover:underline truncate">{{ doc.name
                                        }}</a>
                                    <p class="text-xs text-gray-500">{{ (doc.size / 1024).toFixed(2) }} KB</p>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-center text-gray-500 py-8">No se adjuntaron archivos en esta solicitud.
                        </p>
                    </el-tab-pane>
                </el-tabs>
            </div>
            <template #footer>
                <el-button @click="detailsModalVisible = false" type="info" plain>Cerrar</el-button>
            </template>
        </el-dialog>
    </div>
</template>

<script>
import { ElTimeline, ElTimelineItem, ElCard, ElButton, ElDialog, ElTabs, ElTabPane, ElTable, ElTableColumn, ElTag, ElBadge } from 'element-plus';
import { PaperClipIcon } from '@heroicons/vue/24/outline';

export default {
    name: 'HistoryTab',
    components: {
        ElTimeline, ElTimelineItem, ElCard, ElButton, ElDialog, ElTabs, ElTabPane, ElTable, ElTableColumn, ElTag, ElBadge, PaperClipIcon
    },
    props: {
        history: {
            type: Array,
            default: () => []
        }
    },
    data() {
        return {
            detailsModalVisible: false,
            selectedItem: null,
        }
    },
    methods: {
        showDetails(item) {
            this.selectedItem = item;
            this.detailsModalVisible = true;
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
        translateStatus(status) {
            const map = {
                pending: 'Pendiente',
                approved: 'Aprobada',
                rejected: 'Rechazada',
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
    }
}
</script>