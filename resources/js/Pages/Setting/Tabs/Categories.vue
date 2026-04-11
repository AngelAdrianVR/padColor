<template>
    <div>
        <div class="flex justify-end mt-5 mx-14">
            <PrimaryButton v-if="$page.props.auth.user.permissions.includes('Crear categorias')"
                @click="createCategory()" class="rounded-full">
                Agregar categoría
            </PrimaryButton>
            <div v-if="selectedItems.length && $page.props.auth.user.permissions.includes('Eliminar categorias')"
                class="ml-3">
                <el-popconfirm confirm-button-text="Si" cancel-button-text="No" icon-color="#D72C8A"
                    :title="'¿Desea eliminar los elementos seleccionados (' + selectedItems.length + ')?'"
                    @confirm="deleteItems()">
                    <template #reference>
                        <button class="bg-redpad text-white rounded-full px-3 py-[9px] text-xs tracking-widest">Eliminar</button>
                    </template>
                </el-popconfirm>
            </div>
        </div>
        <div class="text-sm mt-3">
            <p v-if="selectedItems.length" class="text-sm text-redpad flex items-center space-x-2 pb-5">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-4">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                <span>Al eliminar alguna categoría, también se eliminarán los tickets asociados</span>
            </p>
            <div v-if="categories.length" class="flex items-center border-b border-grayD9 pb-2">
                <label class="flex items-center ml-3">
                    <Checkbox @change="handleAllItemsChecked" v-model:checked="allItems" name="all"
                        :disabled="!categories.length" />
                    <span class="ms-2 text-sm font-bold">Todas las categorías</span>
                </label>
            </div>
            <CategoryRow :ref="'category' + category.id" v-for="(category, index) in categories" :key="category.id"
                @open="editcategory(category, index)" @checked="handleCheckedItem" :item="category" />
            <el-empty v-if="!categories.length" description="No hay categorías para mostrar" />
        </div>

        <DialogModal :show="showCategoryModal" @close="showCategoryModal = false">
            <template #title>
                <p v-if="editFlag">Categoría {{ currentCategory.name }}</p>
                <p v-else>Crear nueva categoría</p>
            </template>
            <template #content>
                <div>
                    <form @submit.prevent="editFlag ? updateCategory() : storeCategory()" ref="myform"
                        class="flex flex-col space-y-4">
                        
                        <div>
                            <InputLabel value="Nombre de categoría *" class="ml-2" />
                            <el-input v-model="form.name" placeholder="Escribe el nombre de la categoría" clearable />
                            <InputError :message="form.errors.name" />
                        </div>

                        <!-- NUEVA SECCIÓN DE REGLAS DE CATEGORÍA -->
                        <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                            <InputLabel value="Visibilidad por departamento" class="font-bold mb-2 text-primary" />
                            
                            <label class="flex items-center text-sm cursor-pointer mb-3">
                                <el-switch v-model="form.is_all_departments" active-color="#13ce66" />
                                <span class="ml-2 font-medium text-gray-700">Categoría visible para todos los departamentos</span>
                            </label>

                            <div v-if="!form.is_all_departments" class="mt-2">
                                <InputLabel value="Selecciona los departamentos que podrán ver esta categoría al crear un ticket:" class="text-xs text-gray-500 mb-1" />
                                <el-select
                                    v-model="form.allowed_departments"
                                    multiple
                                    clearable
                                    collapse-tags
                                    collapse-tags-tooltip
                                    placeholder="Selecciona departamentos..."
                                    class="w-full"
                                    no-data-text="No hay departamentos registrados">
                                    <el-option v-for="opt in departmentsList" :key="opt" :label="opt" :value="opt" />
                                </el-select>
                            </div>
                        </div>

                    </form>
                </div>
            </template>
            <template #footer>
                <CancelButton class="mr-1" @click="showCategoryModal = false; form.reset(); editFlag = false;"
                    :disabled="form.processing">
                    Cancelar</CancelButton>
                <PrimaryButton @click="submitForm" :disabled="form.processing">{{ editFlag ? 'Actualizar' : 'Crear'
                    }}
                </PrimaryButton>
            </template>
        </DialogModal>
    </div>
</template>
<script>
import AppLayout from "@/Layouts/AppLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import CancelButton from "@/Components/MyComponents/CancelButton.vue";
import DialogModal from "@/Components/DialogModal.vue";
import Checkbox from "@/Components/Checkbox.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import CategoryRow from "@/Components/MyComponents/Category/CategoryRow.vue";
import { useForm } from "@inertiajs/vue3";
import axios from "axios";

export default {
    data() {
        const form = useForm({
            name: null,
            allowed_departments: [],
            is_all_departments: true, // Propiedad local del front para el switch
        });

        return {
            form,
            selectedItems: [],
            categories: [],
            allItems: false,
            currentCategory: null,
            showCategoryModal: false,
            indexCategoryEdit: null,
            editFlag: false,
            departmentsList: [
                'Administración', 'Almacén', 'Comercial', 'Compras', 'Contabilidad', 
                'Contraloría', 'Crédito y cobranza', 'Dirección', 'Empaques', 
                'Inspección', 'Mantenimiento', 'Producción', 'Recursos Humanos', 
                'Sistemas', 'Tesorería',
            ],
        };
    },
    components: {
        AppLayout,
        PrimaryButton,
        CancelButton,
        DialogModal,
        InputLabel,
        InputError,
        CategoryRow,
        Checkbox,
    },
    props: {
    },
    methods: {
        submitForm() {
            this.$refs.myform.dispatchEvent(new Event('submit', { cancelable: true }));
        },
        handleCheckedItem(evt) {
            if (evt.isActive) {
                this.selectedItems.push(evt.id);
            } else {
                const index = this.selectedItems.findIndex(item => item === evt.id);
                this.selectedItems.splice(index, 1);
            }

            if (this.selectedItems.length === this.categories.length) {
                this.allItems = true;
            } else if (this.selectedItems.length < this.categories.length && this.allItems) {
                this.allItems = false;
            }
        },
        handleAllItemsChecked() {
            if (this.allItems) {
                this.selectedItems = this.categories.map(category => category.id);
            } else {
                this.selectedItems = [];
            }
            this.setSelectedPropFromUserRow(this.allItems);
        },
        setSelectedPropFromUserRow(value) {
            this.categories.forEach(element => {
                const ref = 'category' + element.id;
                this.$refs[ref][0].selected = value;
            });
        },
        editcategory(category, index) {
            this.currentCategory = category;
            this.editFlag = true;
            this.indexCategoryEdit = index;
            
            // Cargar datos al formulario
            this.form.name = category.name;
            if (category.allowed_departments && category.allowed_departments.length > 0) {
                this.form.is_all_departments = false;
                this.form.allowed_departments = category.allowed_departments;
            } else {
                this.form.is_all_departments = true;
                this.form.allowed_departments = [];
            }

            this.showCategoryModal = true;
        },
        createCategory() {
            this.currentCategory = null;
            this.editFlag = false;
            this.form.reset();
            this.form.is_all_departments = true;
            this.form.allowed_departments = [];
            this.showCategoryModal = true;
        },
        async updateCategory() {
            try {
                const payload = {
                    name: this.form.name,
                    allowed_departments: this.form.is_all_departments ? null : this.form.allowed_departments,
                };

                const response = await axios.put(route('settings.categories.update', this.currentCategory), payload);

                if (response.status === 200) {
                    this.$notify({
                        title: 'Éxito',
                        message: 'Categoría actualizada',
                        type: 'success'
                    });
                    this.categories[this.indexCategoryEdit] = response.data.item;
                    this.form.reset();
                    this.showCategoryModal = false;
                }
            } catch (error) {
                this.$notify({
                    title: 'Error',
                    message: error.response?.data?.message || error.message,
                    type: 'error'
                });
            }
        },
        async storeCategory() {
            try {
                const payload = {
                    name: this.form.name,
                    allowed_departments: this.form.is_all_departments ? null : this.form.allowed_departments,
                };

                const response = await axios.post(route('settings.categories.store'), payload);

                if (response.status === 200) {
                    this.$notify({
                        title: 'Éxito',
                        message: 'Categoría creada',
                        type: 'success'
                    });
                    this.categories.push(response.data.item);
                    this.form.reset();
                    this.showCategoryModal = false;
                }
            } catch (error) {
                this.$notify({
                    title: 'Error',
                    message: error.response?.data?.message || error.message,
                    type: 'error'
                });
            }
        },
        async deleteItems() {
            try {
                const response = await axios.post(route('settings.categories.massive-delete', {
                    items_ids: this.selectedItems
                }));

                if (response.status == 200) {
                    this.$notify({
                        title: 'Éxito',
                        message: response.data.message,
                        type: 'success'
                    });

                    this.categories = this.categories.filter(category => !this.selectedItems.includes(category.id));
                }
            } catch (err) {
                this.$notify({
                    title: 'Algo salió mal',
                    message: err.message,
                    type: 'error'
                });
                console.log(err);
            }
        },
        async fetchCategories() {
            try {
                const response = await axios.get(route('settings.categories.get-all'));

                if (response.status == 200) {
                    this.categories = response.data.items;
                }
            } catch (err) {
                this.$notify({
                    title: 'Algo salió mal',
                    message: err.message,
                    type: 'error'
                });
                console.log(err);
            }
        },
    },
    mounted() {
        this.fetchCategories();
    }
};
</script>