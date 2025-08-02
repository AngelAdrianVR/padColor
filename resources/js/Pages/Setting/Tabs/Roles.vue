<template>
    <div>
        <div class="flex justify-end mt-5 mx-14">
            <PrimaryButton v-if="$page.props.auth.user.permissions.includes('Crear roles')" @click="createRole()"
                class="rounded-full">
                Agregar rol
            </PrimaryButton>
        </div>
        <div class="text-sm mt-3">
            <div v-if="roles.data.length" class="flex items-center border-b border-grayD9 pb-2">
                <label class="flex items-center ml-3">
                    <Checkbox @change="handleAllItemsChecked" v-model:checked="allItems" name="all"
                        :disabled="!roles.data.length" />
                    <span class="ms-2 text-sm font-bold">Todos los roles</span>
                </label>
                <div v-if="selectedItems.length && $page.props.auth.user.permissions.includes('Eliminar roles')"
                    class="lg:ml-36">
                    <el-popconfirm confirm-button-text="Si" cancel-button-text="No" icon-color="#D72C8A"
                        :title="'¿Desea eliminar los elementos seleccionados (' + selectedItems.length + ')?'"
                        @confirm="deleteItems()">
                        <template #reference>
                            <button class="bg-redpad text-white rounded-full px-2 py-px text-sm">Eliminar</button>
                        </template>
                    </el-popconfirm>
                </div>
            </div>
            <RoleRow :ref="'role' + role.id" v-for="(role, index) in roles.data" :key="role.id"
                @open="editRole(role, index)" @checked="handleCheckedItem" :item="role" />
            <el-empty v-if="!roles.data.length" description="No hay roles para mostrar" />
        </div>
        <DialogModal :show="showRoleModal" @close="showRoleModal = false">
            <template #title>
                <p v-if="editFlag">Rol {{ currentRole.name }}</p>
                <p v-else>Crear nuevo rol</p>
            </template>
            <template #content>
                <div>
                    <form @submit.prevent="editFlag ? updateRole() : storeRole()" ref="myform" class="grid grid-cols-2 lg:grid-cols-3">
                        <div class="col-span-full mb-4">
                            <InputLabel value="Nombre de rol *" class="ml-2" />
                            <input v-model="form.name" class="input" type="text">
                            <InputError :message="form.errors.name" />
                        </div>
                        <p class="font-bold mb-2 col-span-full">Asignar permisos</p>
                        <div v-for="(guard, index) in Object.keys(permissions.data)" :key="index" class="border p-3">
                            <h1 class="font-bold">{{ guard.replace(/_/g, " ") }}</h1>
                            <label v-for="permission in permissions.data[guard]" :key="permission.id"
                                class="flex items-center">
                                <input type="checkbox" v-model="form.permissions" :value="permission.id"
                                    class="rounded border-gray-400 text-primary shadow-sm focus:ring-primary bg-transparent" />
                                <span class="ml-2 text-sm">{{ permission.name }}</span>
                            </label>
                        </div>
                    </form>
                </div>
            </template>
            <template #footer>
                <CancelButton class="mr-1" @click="showRoleModal = false; form.reset(); editFlag = false;"
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
import RoleRow from "@/Components/MyComponents/Role/RoleRow.vue";
import { useForm } from "@inertiajs/vue3";
import axios from "axios";

export default {
    data() {
        const form = useForm({
            name: null,
            permissions: [],
        });

        return {
            form,
            selectedItems: [],
            allItems: false,
            currentRole: null,
            showRoleModal: false,
            indexRoleEdit: null,
            editFlag: false,
        };
    },
    components: {
        AppLayout,
        PrimaryButton,
        CancelButton,
        DialogModal,
        InputLabel,
        InputError,
        RoleRow,
        Checkbox,
    },
    props: {
        roles: Object,
        permissions: Object,
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

            if (this.selectedItems.length === this.roles.data.length) {
                this.allItems = true;
            } else if (this.selectedItems.length < this.roles.data.length && this.allItems) {
                this.allItems = false;
            }
        },
        handleAllItemsChecked() {
            if (this.allItems) {
                this.selectedItems = this.roles.data.map(role => role.id);
            } else {
                this.selectedItems = [];
            }
            this.setSelectedPropFromUserRow(this.allItems);
        },
        setSelectedPropFromUserRow(value) {
            this.roles.data.forEach(element => {
                const ref = 'role' + element.id;
                this.$refs[ref][0].selected = value;
            });
        },
        editRole(role, index) {
            if (this.$page.props.auth.user.permissions.includes('Editar roles')) {
                this.currentRole = role;
                this.editFlag = true;
                this.indexRoleEdit = index;
                this.showRoleModal = true;

                this.form.name = role.name;
                this.form.permissions = role.permissions.ids;
            }
        },
        createRole() {
            this.currentRole = null;
            this.showRoleModal = true;
            this.editFlag = false;
            this.form.reset();
        },
        async deleteRole(role, index) {
            try {
                const response = await axios.delete(route('settings.role-permission.delete-role', role));

                if (response.status === 200) {
                    this.roles.data.splice(index, 1);
                    this.$notify({
                        title: 'Éxito',
                        message: response.data.message,
                        type: 'success'
                    });
                }
            } catch (error) {
                this.$notify({
                    title: 'Error',
                    message: error.message,
                    type: 'error'
                });
            }
        },
        async updateRole() {
            try {
                const response = await axios.put(route('settings.role-permission.update-role', this.currentRole), {
                    name: this.form.name,
                    permissions: this.form.permissions,
                });

                if (response.status === 200) {
                    this.$notify({
                        title: 'Éxito',
                        message: 'Rol actualizado',
                        type: 'success'
                    });
                    this.roles.data[this.indexRoleEdit] = response.data.item;
                    this.form.reset();
                    this.showRoleModal = false;
                }
            } catch (error) {
                this.$notify({
                    title: 'Error',
                    message: error.message,
                    type: 'error'
                });
            }
        },
        async storeRole() {
            try {
                const response = await axios.post(route('settings.role-permission.store-role'), {
                    name: this.form.name,
                    permissions: this.form.permissions,
                });

                if (response.status === 200) {
                    this.$notify({
                        title: 'Éxito',
                        message: 'Rol creado',
                        type: 'success'
                    });
                    this.roles.data.push(response.data.item);
                    this.form.reset();
                    this.showRoleModal = false;
                }
            } catch (error) {
                this.$notify({
                    title: 'Error',
                    message: error.message,
                    type: 'error'
                });
            }
        },
        async deleteItems() {
            try {
                const response = await axios.post(route('settings.role-permission.roles-massive-delete', {
                    items_ids: this.selectedItems
                }));

                if (response.status == 200) {
                    this.$notify({
                        title: 'Éxito',
                        message: response.data.message,
                        type: 'success'
                    });

                    // Filtrar el arreglo 'roles' excluyendo los elementos con IDs en 'selectedItems'
                    this.roles.data = this.roles.data.filter(role => !this.selectedItems.includes(role.id));
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
};
</script>