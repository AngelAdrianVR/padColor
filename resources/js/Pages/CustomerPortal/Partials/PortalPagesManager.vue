<template>
    <div>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold">Gestionar rutas del portal</h2>
            <el-tooltip
                v-if="limitReached"
                content="Has alcanzado el límite de 15 rutas. Elimina alguna página antes de crear una nueva."
                placement="top"
                :show-after="300"
            >
                <el-button type="primary" size="default" disabled>
                    <i class="fa-solid fa-plus mr-1"></i> Nueva ruta
                </el-button>
            </el-tooltip>
            <el-button v-else type="primary" size="default" @click="openCreateDialog">
                <i class="fa-solid fa-plus mr-1"></i> Nueva ruta
            </el-button>
        </div>

        <el-alert
            v-if="limitReached"
            type="warning"
            show-icon
            :closable="false"
            class="mb-5"
        >
            <template #title>
                Has alcanzado el límite de <b>15 rutas</b>. Si deseas agregar más, elimina alguna de las rutas registradas.
            </template>
        </el-alert>

        <el-alert type="info" show-icon :closable="false" class="mb-5">
            <template #title>
                Aquí puedes crear, editar o eliminar las rutas públicas del portal de clientes.
                Al crear una nueva ruta, se genera automáticamente un archivo <b>.blade.php</b> vacío en el servidor
                y la ruta queda disponible inmediatamente para que subas contenido.
            </template>
        </el-alert>

        <!-- Tabla de rutas -->
        <el-table :data="portalPages" stripe style="width: 100%" v-loading="loading">
            <el-table-column prop="route_key" label="Clave" min-width="140" />
            <el-table-column prop="label" label="Nombre" min-width="180" />
            <el-table-column label="URL pública" min-width="220">
                <template #default="{ row }">
                    <code class="bg-gray-100 px-2 py-0.5 rounded text-sm">/{{ row.url_path }}</code>
                </template>
            </el-table-column>
            <el-table-column prop="filename" label="Archivo" min-width="160" />
            <el-table-column label="Activo" width="80" align="center">
                <template #default="{ row }">
                    <el-tag :type="row.is_active ? 'success' : 'danger'" size="small">
                        {{ row.is_active ? 'Sí' : 'No' }}
                    </el-tag>
                </template>
            </el-table-column>
            <el-table-column label="Acciones" width="140" align="right">
                <template #default="{ row }">
                    <el-button size="small" @click="openEditDialog(row)">
                        <i class="fa-solid fa-pen"></i>
                    </el-button>
                    <el-button size="small" type="danger" @click="confirmDelete(row)">
                        <i class="fa-solid fa-trash-can"></i>
                    </el-button>
                </template>
            </el-table-column>
        </el-table>

        <!-- Diálogo Crear / Editar -->
        <el-dialog
            v-model="dialog.visible"
            :title="dialog.isEdit ? 'Editar ruta del portal' : 'Nueva ruta del portal'"
            width="500px"
            :close-on-click-modal="false"
        >
            <el-form :model="form" label-position="top" @submit.prevent="save">
                <el-form-item
                    label="Clave de ruta"
                    prop="route_key"
                    :rules="[{ required: true, message: 'La clave es obligatoria' }]"
                    :error="form.errors?.route_key"
                >
                    <el-input v-model="form.route_key" placeholder="ej: catalogo-verano" :disabled="dialog.isEdit">
                        <template #prefix>
                            <i class="fa-solid fa-key"></i>
                        </template>
                    </el-input>
                    <p class="text-xs text-gray-400 mt-1">
                        Identificador único. Se usará para nombrar el archivo en el servidor (ej: <code>catalogo-verano.blade.php</code>).
                    </p>
                </el-form-item>

                <el-form-item
                    label="Nombre descriptivo"
                    prop="label"
                    :rules="[{ required: true, message: 'El nombre es obligatorio' }]"
                    :error="form.errors?.label"
                >
                    <el-input v-model="form.label" placeholder="ej: Catálogo de Verano 2026" />
                </el-form-item>

                <el-form-item
                    label="URL pública"
                    prop="url_path"
                    :rules="[{ required: true, message: 'La URL es obligatoria' }]"
                    :error="form.errors?.url_path"
                >
                    <el-input v-model="form.url_path" placeholder="ej: catalogo-verano-2026">
                        <template #prepend>
                            <span class="text-gray-400 text-sm">/</span>
                        </template>
                    </el-input>
                    <p class="text-xs text-gray-400 mt-1">
                        Ruta pública sin slash inicial. Ej: <code>/catalogo-verano-2026</code>
                    </p>
                </el-form-item>

                <el-form-item v-if="dialog.isEdit" label="Activo" prop="is_active">
                    <el-switch v-model="form.is_active" active-text="Ruta activa" inactive-text="Ruta inactiva" />
                </el-form-item>
            </el-form>

            <template #footer>
                <el-button @click="dialog.visible = false">Cancelar</el-button>
                <el-button type="primary" :loading="saving" @click="save">
                    {{ dialog.isEdit ? 'Guardar cambios' : 'Crear ruta' }}
                </el-button>
            </template>
        </el-dialog>
    </div>
</template>

<script>
import axios from "axios";
import { ElMessage, ElMessageBox } from "element-plus";

const MAX_PAGES = 15;

export default {
    props: {
        portalPages: {
            type: Array,
            required: true,
        },
    },
    emits: ['refresh'],
    data() {
        return {
            loading: false,
            saving: false,
            dialog: {
                visible: false,
                isEdit: false,
                editingId: null,
            },
            form: {
                route_key: '',
                label: '',
                url_path: '',
                is_active: true,
                errors: {},
            },
        }
    },
    computed: {
        limitReached() {
            return this.portalPages.length >= MAX_PAGES;
        },
    },
    methods: {
        resetForm() {
            this.form = {
                route_key: '',
                label: '',
                url_path: '',
                is_active: true,
                errors: {},
            };
            this.dialog = { visible: false, isEdit: false, editingId: null };
        },
        openCreateDialog() {
            this.resetForm();
            this.dialog.visible = true;
            this.dialog.isEdit = false;
        },
        openEditDialog(page) {
            this.resetForm();
            this.dialog.visible = true;
            this.dialog.isEdit = true;
            this.dialog.editingId = page.id;
            this.form.route_key = page.route_key;
            this.form.label = page.label;
            this.form.url_path = page.url_path;
            this.form.is_active = page.is_active;
        },
        async save() {
            if (this.saving) return;

            // Validación manual simple
            if (!this.form.route_key || !this.form.label || !this.form.url_path) {
                ElMessage.warning('Completa todos los campos obligatorios.');
                return;
            }

            this.saving = true;
            this.form.errors = {};

            try {
                if (this.dialog.isEdit) {
                    const response = await axios.put(
                        route('settings.portal-pages.update', this.dialog.editingId),
                        this.form
                    );
                    ElMessage.success(response.data.message);
                } else {
                    const response = await axios.post(
                        route('settings.portal-pages.store'),
                        this.form
                    );
                    ElMessage.success(response.data.message);
                }

                this.dialog.visible = false;
                this.$emit('refresh');
            } catch (error) {
                if (error.response?.status === 422) {
                    this.form.errors = error.response.data.errors || {};
                    ElMessage.error('Corrige los errores del formulario.');
                } else {
                    ElMessage.error(error.response?.data?.message || 'Error al guardar la ruta.');
                }
            } finally {
                this.saving = false;
            }
        },
        async confirmDelete(page) {
            try {
                await ElMessageBox.confirm(
                    `¿Estás seguro de eliminar la ruta "${page.label}"?`,
                    'Confirmar eliminación',
                    { confirmButtonText: 'Eliminar', cancelButtonText: 'Cancelar', type: 'warning' }
                );

                await axios.delete(route('settings.portal-pages.destroy', page.id));
                ElMessage.success(`Ruta "${page.label}" eliminada.`);
                this.$emit('refresh');
            } catch {
                // User cancelled
            }
        },
    },
}
</script>
