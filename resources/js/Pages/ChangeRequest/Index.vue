<template>
    <AppLayout title="Solicitudes de Cambio">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Administración de Solicitudes de Cambio
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div v-if="changeRequests.data.length > 0">
                        <el-table :data="changeRequests.data" stripe style="width: 100%">
                            <el-table-column prop="product.name" label="Producto">
                                <template #default="scope">
                                    <Link :href="route('products.show', scope.row.product.id)"
                                        class="text-blue-600 hover:underline font-semibold">
                                    {{ scope.row.product.name }}
                                    </Link>
                                </template>
                            </el-table-column>

                            <el-table-column prop="requester.name" label="Solicitante" />

                            <el-table-column prop="created_at" label="Fecha de Solicitud">
                                <template #default="scope">
                                    {{ formatDate(scope.row.created_at) }}
                                </template>
                            </el-table-column>

                            <el-table-column prop="status" label="Estado">
                                <template #default="scope">
                                    <el-tag :type="getStatusTagType(scope.row.status)" effect="light">
                                        {{ scope.row.status }}
                                    </el-tag>
                                </template>
                            </el-table-column>

                            <el-table-column label="Acciones" width="200">
                                <template #default="scope">
                                    <div v-if="scope.row.status === 'pending'">
                                        <el-button-group>
                                            <el-button type="success" :icon="CheckIcon"
                                                @click="approveRequest(scope.row)" />
                                            <el-button type="danger" :icon="XMarkIcon"
                                                @click="openRejectDialog(scope.row)" />
                                        </el-button-group>
                                    </div>
                                    <span v-else class="text-xs text-gray-500">Decisión tomada</span>
                                </template>
                            </el-table-column>
                        </el-table>

                        <!-- Paginación -->
                        <div v-if="changeRequests.links.length > 3" class="mt-4 flex justify-center">
                            <div class="flex flex-wrap -mb-1">
                                <template v-for="(link, key) in changeRequests.links" :key="key">
                                    <div v-if="link.url === null"
                                        class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-gray-400 border rounded"
                                        v-html="link.label" />
                                    <Link v-else
                                        class="mr-1 mb-1 px-4 py-3 text-sm leading-4 border rounded hover:bg-white focus:border-indigo-500 focus:text-indigo-500"
                                        :class="{ 'bg-blue-700 text-white': link.active }" :href="link.url"
                                        v-html="link.label" />
                                </template>
                            </div>
                        </div>

                    </div>
                    <div v-else class="text-center text-gray-500">
                        <p>No hay solicitudes de cambio pendientes o procesadas.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dialogo para confirmar rechazo -->
        <el-dialog v-model="rejectDialogVisible" title="Rechazar Solicitud de Cambio" width="30%">
            <span>Por favor, escribe el motivo del rechazo. Este comentario será visible para el solicitante.</span>
            <el-input v-model="rejectionComments" type="textarea" :rows="3" placeholder="Motivo del rechazo..."
                class="mt-4" />
            <template #footer>
                <span class="dialog-footer">
                    <el-button @click="rejectDialogVisible = false">Cancelar</el-button>
                    <el-button type="primary" @click="confirmReject">Confirmar Rechazo</el-button>
                </span>
            </template>
        </el-dialog>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ElTable, ElTableColumn, ElTag, ElButton, ElButtonGroup, ElDialog, ElInput } from 'element-plus';
import { CheckIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import { ElMessageBox, ElMessage } from 'element-plus';

const props = defineProps({
    changeRequests: Object,
});

// Estado para el diálogo de rechazo
const rejectDialogVisible = ref(false);
const currentRequest = ref(null);
const rejectionComments = ref('');

// --- Métodos ---

const formatDate = (dateString) => {
    const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return new Date(dateString).toLocaleDateString('es-MX', options);
};

const getStatusTagType = (status) => {
    switch (status) {
        case 'pending': return 'warning';
        case 'approved': return 'success';
        case 'rejected': return 'danger';
        default: return 'info';
    }
};

const approveRequest = (request) => {
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
            onSuccess: () => ElMessage.success('Solicitud aprobada con éxito.'),
        });
    }).catch(() => {
        ElMessage.info('Aprobación cancelada.');
    });
};

const openRejectDialog = (request) => {
    currentRequest.value = request;
    rejectionComments.value = '';
    rejectDialogVisible.value = true;
};

const confirmReject = () => {
    if (!currentRequest.value) return;

    router.post(route('change-requests.reject', currentRequest.value.id), {
        comments: rejectionComments.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            ElMessage.info('La solicitud ha sido rechazada.');
            rejectDialogVisible.value = false;
        },
        onError: () => {
            ElMessage.error('Hubo un error al procesar el rechazo.');
        }
    });
};
</script>
