<template>
    <AppLayout title="Agentes Aduanales">
        <main class="px-2 lg:px-14 py-2">
            <div class="flex justify-between items-center">
                <h1 class="font-bold text-xl">Agentes aduanales</h1>
                <PrimaryButton v-if="$page.props.auth.user.permissions.includes('Crear agentes aduanales')"
                    @click="$inertia.get(route('customs-agents.create'))">
                    <PlusIcon class="size-5 inline mr-1" />
                    Crear agente aduanal
                </PrimaryButton>
            </div>

            <div class="lg:w-1/3 relative mt-4">
                <el-input v-model="localFilters.search" placeholder="Buscar por folio o nombre" clearable />
            </div>

            <div class="mt-6">
                <el-table :data="customs_agents.data" style="width: 100%" max-height="670" @row-click="handleRowClick"
                    :row-class-name="tableRowClassName">
                    <el-table-column prop="id" label="ID" width="100" />
                    <el-table-column prop="name" label="Nombre o razón social" />
                    <el-table-column prop="contact_person" label="Contacto" />
                    <el-table-column prop="email" label="Correo electrónico" />
                    <el-table-column prop="phone" label="Teléfono" />
                    <el-table-column align="right" width="100">
                        <template #default="scope">
                            <el-dropdown trigger="click" @command="handleCommand">
                                <button @click.stop
                                    class="el-dropdown-link justify-center items-center size-8 rounded-full text-gray-600 hover:bg-gray-200 transition-all">
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </button>
                                <template #dropdown>
                                    <el-dropdown-menu>
                                        <el-dropdown-item :command="'show-' + scope.row.id" class="!text-xs">
                                            <EyeIcon class="size-4 mr-2" /> Ver
                                        </el-dropdown-item>
                                        <el-dropdown-item
                                            v-if="$page.props.auth.user.permissions.includes('Editar agentes aduanales')"
                                            :command="'edit-' + scope.row.id" class="!text-xs">
                                            <PencilIcon class="size-4 mr-2" /> Editar
                                        </el-dropdown-item>
                                        <!-- <el-dropdown-item :command="'delete-' + scope.row.id" class="!text-xs">
                                            <TrashIcon class="size-4 mr-2" /> Eliminar
                                        </el-dropdown-item> -->
                                    </el-dropdown-menu>
                                </template>
                            </el-dropdown>
                        </template>
                    </el-table-column>
                </el-table>
            </div>

            <div class="mt-4 text-center">
                <el-pagination layout="prev, pager, next" :total="customs_agents.total"
                    :current-page="customs_agents.current_page" @current-change="handlePageChange" />
            </div>
        </main>

        <CustomsAgentDetails v-if="selectedAgent" :show="showDetailsModal" :agent-data="selectedAgent"
            @close="showDetailsModal = false" />
    </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { router } from '@inertiajs/vue3';
import { ElMessageBox, ElNotification } from 'element-plus';
import { EyeIcon, PencilIcon, PlusIcon, TrashIcon } from '@heroicons/vue/24/outline';
import throttle from 'lodash/throttle';
import CustomsAgentDetails from './Details.vue';

export default {
    components: {
        AppLayout,
        PrimaryButton,
        PlusIcon,
        EyeIcon,
        TrashIcon,
        PencilIcon,
        CustomsAgentDetails,
    },
    props: {
        customs_agents: Object,
        filters: Object,
    },
    data() {
        return {
            localFilters: { search: this.filters.search || '' },
            showDetailsModal: false,
            selectedAgent: null,
        };
    },
    methods: {
        handleRowClick(row) {
            this.showDetails(parseInt(row.id));
        },
        tableRowClassName({ row, rowIndex }) {
            return 'cursor-pointer text-xs';
        },
        showDetails(agentId) {
            const agent = this.customs_agents.data.find(item => item.id === agentId);
            if (agent) {
                this.selectedAgent = agent;
                this.showDetailsModal = true;
            }
        },
        handleCommand(command) {
            const [action, id] = command.split('-');
            if (action === 'edit') router.get(route('customs-agents.edit', id));
            else if (action === 'delete') this.confirmDelete(id);
            else if (action === 'show') this.showDetails(parseInt(id));
        },
        confirmDelete(agentId) {
            ElMessageBox.confirm('¿Estás seguro de que deseas eliminar este agente aduanal?', 'Confirmar eliminación', {
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                type: 'warning',
            }).then(() => {
                router.delete(route('customs-agents.destroy', agentId), {
                    preserveScroll: true,
                    onSuccess: () => ElNotification({ title: 'Éxito', message: 'Agente aduanal eliminado', type: 'success' }),
                });
            }).catch(() => { });
        },
        handlePageChange(newPage) {
            router.get(route('customs-agents.index', { page: newPage, ...this.localFilters }));
        }
    },
    watch: {
        localFilters: {
            handler: throttle(function () {
                router.get(route('customs-agents.index'), this.localFilters, { preserveState: true, replace: true });
            }, 300),
            deep: true,
        },
    },
};
</script>
