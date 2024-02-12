<template>
    <div>
        <div class="flex justify-end mt-5 mx-14">
            <PrimaryButton @click="createPermission()" class="rounded-full">
                Agregar permiso
            </PrimaryButton>
        </div>
        <div class="text-sm overflow-scroll mt-3">
            <div class="lg:grid grid-cols-4">
                <div v-for="(guard, index) in Object.keys(permissions.data)" :key="index" class="border p-3">
                    <h1 class="font-bold">{{ guard.replace(/_/g, " ") }}</h1>
                    <div v-for="(permission, index2) in permissions.data[guard]" :key="index"
                        class="flex justify-between items-center mt-1">
                        <p @click="editPermission(permission, index2)" class="hover:underline cursor-pointer">{{
                            permission.name
                        }}</p>
                        <el-popconfirm confirm-button-text="Si" cancel-button-text="No" icon-color="#FFFFFF"
                            title="¿Continuar?" @confirm="deletePermission(permission, index2)">
                            <template #reference>
                                <i class="fa-solid fa-trash-can text-primary cursor-pointer"></i>
                            </template>
                        </el-popconfirm>
                    </div>
                </div>
            </div>
        </div>

        <DialogModal :show="showPermissionModal" @close="showPermissionModal = false">
            <template #title>
                <p v-if="editFlag">Editar permiso</p>
                <p v-else>Crear nuevo permiso</p>
            </template>
            <template #content>
                <div>
                    <form @submit.prevent="editFlag ? updatePermission() : storePermission()" ref="myform">
                        <div>
                            <InputLabel value="Nombre del permiso *" class="ml-2" />
                            <input v-model="form.name" class="input" type="text">
                            <InputError :message="form.errors.name" />
                        </div>
                        <div class="mt-3">
                            <InputLabel value="Categoria del permiso *" class="ml-2" />
                            <input v-model="form.category" class="input" type="text">
                            <InputError :message="form.errors.category" />
                        </div>
                    </form>
                </div>
            </template>
            <template #footer>
                <CancelButton class="mr-1" @click="showPermissionModal = false; form.reset(); editFlag = false;"
                    :disabled="form.processing">Cancelar</CancelButton>
                <PrimaryButton @click="submitForm" :disabled="form.processing">{{ editFlag ?
                    'Actualizar' :
                    'Crear' }}
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
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import { useForm } from "@inertiajs/vue3";
import axios from "axios";

export default {
    data() {
        const form = useForm({
            name: null,
            category: null,
        });

        return {
            form,
            currentPermission: null,
            showPermissionModal: false,
            indexPermissionEdit: null,
            editFlag: false,
        };
    },
    components: {
        AppLayout,
        PrimaryButton,
        CancelButton,
        DialogModal,
        InputLabel,
        InputError
    },
    props: {
        permissions: Object,
    },
    methods: {
        async deletePermission(permission, index) {
            try {
                const response = await axios.delete(route('settings.role-permission.delete-permission', permission));

                if (response.status === 200) {
                    this.permissions.data[permission.category].splice(index, 1);
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
        editPermission(permission, index) {
            // if (this.$page.props.auth.user.permissions.includes('Editar roles y permisos')) {
            this.currentPermission = permission;
            this.editFlag = true;
            this.indexPermissionEdit = index;
            this.showPermissionModal = true;

            this.form.name = permission.name;
            this.form.category = permission.category;
            // }
        },
        createPermission() {
            this.currentPermission = null;
            this.showPermissionModal = true;
            this.editFlag = false;
        },
        async updatePermission() {
            try {
                const response = await axios.put(route('settings.role-permission.update-permission', this.currentPermission), {
                    name: this.form.name,
                    category: this.form.category,
                });

                if (response.status === 200) {
                    this.$notify({
                        title: 'Éxito',
                        message: 'Permiso actualizado',
                        type: 'success'
                    });
                    this.permissions.data[response.data.item.category][this.indexPermissionEdit] = response.data.item;
                    this.form.reset();
                    this.showPermissionModal = false;
                }
            } catch (error) {
                this.$notify({
                    title: 'Error',
                    message: error.message,
                    type: 'error'
                });
            }
        },
        async storePermission() {
            try {
                const response = await axios.post(route('settings.role-permission.store-permission'), {
                    name: this.form.name,
                    category: this.form.category,
                });

                if (response.status === 200) {
                    this.$notify({
                        title: 'Éxito',
                        message: 'Permiso creado',
                        type: 'success'
                    });
                    if (!(response.data.item.category in this.permissions.data)) {
                        this.permissions.data[response.data.item.category] = [];
                        console.log(this.permissions.data);
                    }
                    this.permissions.data[response.data.item.category].push(response.data.item);
                    this.form.reset();
                    this.showPermissionModal = false;
                }
            } catch (error) {
                this.$notify({
                    title: 'Error',
                    message: error.message,
                    type: 'error'
                });
            }
        },
        submitForm() {
            this.$refs.myform.dispatchEvent(new Event('submit', { cancelable: true }));
        },
    }
};
</script>