<template>
    <DialogModal :show="show" @close="closeModal" max-width="md">
        <template #title>
            Gestión de Categorías
        </template>
        <template #content>
            <div class="flex flex-col space-y-5">
                
                <!-- Formulario Crear/Editar (Solo visible si se tiene permisos) -->
                <div v-if="(editingCategory && canEdit) || (!editingCategory && canCreate)" 
                     class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <h3 class="font-bold text-sm mb-3 text-primary">
                        <i class="fa-solid fa-pen-to-square mr-2" v-if="editingCategory"></i>
                        <i class="fa-solid fa-plus mr-2" v-else></i>
                        {{ editingCategory ? 'Editar categoría' : 'Nueva categoría' }}
                    </h3>
                    <div class="flex items-start space-x-3">
                        <div class="flex-1">
                            <el-input 
                                v-model="form.name" 
                                placeholder="Nombre de la categoría" 
                                @keyup.enter="editingCategory ? updateCategory() : storeCategory()" 
                                clearable
                            />
                            <InputError :message="formErrors.name" class="mt-1" />
                        </div>
                        <PrimaryButton @click="editingCategory ? updateCategory() : storeCategory()" :disabled="processing">
                            {{ editingCategory ? 'Actualizar' : 'Agregar' }}
                        </PrimaryButton>
                        <CancelButton v-if="editingCategory" @click="cancelEdit" :disabled="processing">
                            Cancelar
                        </CancelButton>
                    </div>
                </div>

                <!-- Lista de categorías registradas -->
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div v-if="loading" class="p-6 text-center text-gray-500">
                        <i class="fa-solid fa-circle-notch fa-spin text-xl text-primary mb-2"></i>
                        <p class="text-sm">Cargando categorías...</p>
                    </div>
                    
                    <table v-else class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-500 uppercase bg-gray-100 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3">Nombre de categoría</th>
                                <th class="px-4 py-3 text-right" v-if="canEdit || canDelete">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="category in localCategories" :key="category.id" 
                                class="border-b border-gray-100 last:border-0 hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 font-medium text-gray-800">{{ category.name }}</td>
                                <td class="px-4 py-3 text-right space-x-3" v-if="canEdit || canDelete">
                                    
                                    <!-- Botón Editar -->
                                    <button v-if="canEdit" @click="editCategory(category)" 
                                            class="text-blue-600 hover:text-blue-800 transition-colors" title="Editar">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>

                                    <!-- Botón Eliminar -->
                                    <el-popconfirm v-if="canDelete" 
                                        confirm-button-text="Sí" 
                                        cancel-button-text="No" 
                                        icon-color="#D72C8A" 
                                        title="¿Eliminar esta categoría? Se eliminarán los tickets asociados." 
                                        @confirm="deleteCategory(category.id)">
                                        <template #reference>
                                            <button class="text-red-600 hover:text-red-800 transition-colors" title="Eliminar">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </template>
                                    </el-popconfirm>

                                </td>
                            </tr>
                            <tr v-if="!localCategories.length">
                                <td :colspan="canEdit || canDelete ? 2 : 1" class="px-4 py-6 text-center text-gray-500">
                                    No hay categorías registradas en el sistema.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </template>
        <template #footer>
            <PrimaryButton @click="closeModal">Cerrar</PrimaryButton>
        </template>
    </DialogModal>
</template>

<script>
import DialogModal from "@/Components/DialogModal.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import CancelButton from "@/Components/MyComponents/CancelButton.vue";
import InputError from "@/Components/InputError.vue";
import axios from 'axios';

export default {
    components: {
        DialogModal,
        PrimaryButton,
        CancelButton,
        InputError,
    },
    props: {
        show: {
            type: Boolean,
            default: false
        }
    },
    emits: ['close', 'categories-updated'],
    data() {
        return {
            localCategories: [],
            loading: false,
            processing: false,
            editingCategory: null,
            form: {
                name: ''
            },
            formErrors: {}
        }
    },
    computed: {
        canCreate() {
            return this.$page.props.auth.user.permissions.includes('Crear categorias');
        },
        canEdit() {
            return this.$page.props.auth.user.permissions.includes('Editar categorias');
        },
        canDelete() {
            return this.$page.props.auth.user.permissions.includes('Eliminar categorias');
        }
    },
    watch: {
        // Cargar las categorías frescas cada vez que se abre el modal
        show(newVal) {
            if (newVal) {
                this.fetchCategories();
                this.cancelEdit();
            }
        }
    },
    methods: {
        closeModal() {
            this.$emit('close');
            this.cancelEdit();
        },
        cancelEdit() {
            this.editingCategory = null;
            this.form.name = '';
            this.formErrors = {};
        },
        editCategory(category) {
            this.editingCategory = category;
            this.form.name = category.name;
            this.formErrors = {};
        },
        async fetchCategories() {
            this.loading = true;
            try {
                const response = await axios.get(route('settings.categories.get-all'));
                this.localCategories = response.data.items;
                this.$emit('categories-updated', this.localCategories);
            } catch (error) {
                console.error(error);
            } finally {
                this.loading = false;
            }
        },
        async storeCategory() {
            this.processing = true;
            this.formErrors = {};
            try {
                const response = await axios.post(route('settings.categories.store'), { name: this.form.name });
                this.$notify({ title: 'Éxito', message: 'Categoría creada', type: 'success' });
                this.form.name = '';
                await this.fetchCategories(); // Refrescar lista
            } catch (error) {
                if (error.response?.data?.errors) {
                    this.formErrors = error.response.data.errors;
                } else {
                    this.$notify({ title: 'Error', message: 'No se pudo crear la categoría', type: 'error' });
                }
            } finally {
                this.processing = false;
            }
        },
        async updateCategory() {
            this.processing = true;
            this.formErrors = {};
            try {
                // RUTA ACTUALIZADA AL SETTING CONTROLLER PARA ASEGURARNOS DE QUE SE GUARDE
                const response = await axios.put(route('settings.categories.update', this.editingCategory.id), { name: this.form.name });
                this.$notify({ title: 'Éxito', message: 'Categoría actualizada', type: 'success' });
                this.cancelEdit();
                await this.fetchCategories(); // Refrescar lista
            } catch (error) {
                if (error.response?.data?.errors) {
                    this.formErrors = error.response.data.errors;
                } else {
                    this.$notify({ title: 'Error', message: 'No se pudo actualizar la categoría', type: 'error' });
                }
            } finally {
                this.processing = false;
            }
        },
        async deleteCategory(id) {
            try {
                const response = await axios.post(route('settings.categories.massive-delete'), { items_ids: [id] });
                this.$notify({ title: 'Éxito', message: 'Categoría eliminada', type: 'success' });
                await this.fetchCategories(); // Refrescar lista
            } catch (error) {
                this.$notify({ title: 'Error', message: 'No se pudo eliminar la categoría', type: 'error' });
                console.error(error);
            }
        }
    }
}
</script>