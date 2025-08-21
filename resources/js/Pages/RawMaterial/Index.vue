<template>
    <AppLayout title="Materia Prima">
        <main class="px-2 lg:px-14 py-2">
            <div class="flex justify-between items-center">
                <h1 class="font-bold text-xl">Materia prima</h1>
                <PrimaryButton v-if="$page.props.auth.user.permissions.includes('Crear materias primas')" @click="$inertia.get(route('raw-materials.create'))">
                    <PlusIcon class="size-5 inline mr-1" />
                    Crear materia prima
                </PrimaryButton>
            </div>

            <!-- Buscador -->
            <div class="lg:w-1/3 relative mt-4">
                <el-input v-model="localFilters.search" placeholder="Buscar por código o nombre" clearable />
            </div>

            <!-- Tabla de Materia Prima -->
            <div class="mt-6">
                <el-table :data="raw_materials.data" @row-click="handleRowClick" :row-class-name="tableRowClassName"
                    style="width: 100%" max-height="670">
                    <el-table-column prop="sku" label="Código" width="180" />
                    <el-table-column prop="name" label="Nombre" />
                    <el-table-column prop="measure_unit" label="Unidad de medida" />
                    <el-table-column prop="description" label="Descripción" />
                    <el-table-column align="right" width="100">
                        <template #default="scope">
                            <el-dropdown trigger="click" @command="handleCommand">
                                <button @click.stop
                                    class="el-dropdown-link justify-center items-center size-8 rounded-full text-gray-600 hover:bg-gray-200 transition-all duration-200 ease-in-out">
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </button>
                                <template #dropdown>
                                    <el-dropdown-menu>
                                        <el-dropdown-item :command="'show-' + scope.row.id" class="!text-xs">
                                            <EyeIcon class="size-4 mr-2" /> Ver
                                        </el-dropdown-item>
                                        <el-dropdown-item v-if="$page.props.auth.user.permissions.includes('Editar materias primas')" :command="'edit-' + scope.row.id" class="!text-xs">
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

            <!-- Paginación -->
            <div class="mt-4 text-center">
                <el-pagination layout="prev, pager, next" :total="raw_materials.total"
                    :current-page="raw_materials.current_page" @current-change="handlePageChange" />
            </div>
        </main>

        <!-- Modal de Detalles -->
        <RawMaterialDetails v-if="selectedRawMaterial" :show="showDetailsModal" :raw-material-data="selectedRawMaterial"
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
import RawMaterialDetails from './Details.vue';

export default {
    components: {
        AppLayout,
        PrimaryButton,
        PlusIcon,
        EyeIcon,
        TrashIcon,
        PencilIcon,
        RawMaterialDetails,
    },
    props: {
        raw_materials: Object,
        filters: Object,
    },
    data() {
        return {
            localFilters: {
                search: this.filters.search || '',
            },
            showDetailsModal: false,
            selectedRawMaterial: null,
        };
    },
    methods: {
        handleRowClick(row) {
            this.showDetails(parseInt(row.id));
        },
        tableRowClassName({ row, rowIndex }) {
            return 'cursor-pointer text-xs';
        },
        showDetails(rawMaterialId) {
            const rawMaterial = this.raw_materials.data.find(item => item.id === rawMaterialId);
            if (rawMaterial) {
                this.selectedRawMaterial = rawMaterial;
                this.showDetailsModal = true;
            }
        },
        handleCommand(command) {
            const [action, id] = command.split('-');
            if (action === 'edit') {
                router.get(route('raw-materials.edit', id));
            } else if (action === 'delete') {
                this.confirmDelete(id);
            } else if (action === 'show') {
                this.showDetails(parseInt(id));
            }
        },
        confirmDelete(rawMaterialId) {
            ElMessageBox.confirm(
                '¿Estás seguro de que deseas eliminar esta materia prima? Esta acción no se puede deshacer.',
                'Confirmar eliminación',
                {
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    type: 'warning',
                }
            ).then(() => {
                router.delete(route('raw-materials.destroy', rawMaterialId), {
                    preserveScroll: true,
                    onSuccess: () => {
                        ElNotification({
                            title: 'Éxito',
                            message: 'Materia prima eliminada correctamente',
                            type: 'success',
                        });
                    },
                });
            }).catch(() => {
                // Cancelado
            });
        },
        handlePageChange(newPage) {
            router.get(route('raw-materials.index', { page: newPage, ...this.localFilters }));
        }
    },
    watch: {
        localFilters: {
            handler: throttle(function () {
                router.get(route('raw-materials.index'), this.localFilters, {
                    preserveState: true,
                    replace: true,
                });
            }, 300),
            deep: true,
        },
    },
};
</script>
