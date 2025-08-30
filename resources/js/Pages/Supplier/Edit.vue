<template>
    <AppLayout title="Editar proveedor">
        <main class="px-2 lg:px-14 py-8">
            <form @submit.prevent="update" class="rounded-lg border border-grayD9 lg:p-5 p-3 lg:w-1/2 mx-auto">
                <div class="flex items-center space-x-2 mb-6">
                    <Back />
                    <h1 class="font-semibold text-xl text-gray3F">Editar proveedor</h1>
                </div>

                <div class="space-y-3">
                    <div>
                        <InputLabel value="Nombre / Razón Social*" />
                        <el-input v-model="form.name" placeholder="Ej. Proveedor Industrial S.A. de C.V." />
                        <InputError :message="form.errors.name" />
                    </div>
                    <div>
                        <InputLabel value="Nombre del contacto" />
                        <el-input v-model="form.contact_person" placeholder="Ej. Juan Pérez" />
                        <InputError :message="form.errors.contact_person" />
                    </div>
                    <div>
                        <InputLabel value="Email" />
                        <el-input v-model="form.email" placeholder="Ej. contacto@proveedor.com" />
                        <InputError :message="form.errors.email" />
                    </div>
                    <div>
                        <InputLabel value="Teléfono" />
                        <el-input v-model="form.phone" placeholder="Ej. 33 1234 5678" />
                        <InputError :message="form.errors.phone" />
                    </div>
                    <div>
                         <InputLabel value="Dirección" />
                         <el-input v-model="form.address" :autosize="{ minRows: 2, maxRows: 4 }" type="textarea"
                             placeholder="Dirección completa del proveedor" />
                         <InputError :message="form.errors.address" />
                    </div>
                    <div>
                         <InputLabel value="Notas" />
                         <el-input v-model="form.notes" :autosize="{ minRows: 2, maxRows: 4 }" type="textarea"
                             placeholder="Escribe comentarios relacionados con el proveedor" />
                         <InputError :message="form.errors.notes" />
                    </div>
                </div>

                <div class="text-right mt-6">
                    <CancelButton @click="$inertia.visit(route('suppliers.index'))" type="button">
                        Cancelar
                    </CancelButton>
                    <PrimaryButton :disabled="form.processing" class="ml-2">
                        <i v-if="form.processing" class="fa-solid fa-circle-notch fa-spin mr-2"></i>
                        Guardar cambios
                    </PrimaryButton>
                </div>
            </form>
        </main>
    </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import Back from '@/Components/MyComponents/Back.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import CancelButton from '@/Components/MyComponents/CancelButton.vue';
import { useForm } from '@inertiajs/vue3';

export default {
    components: {
        AppLayout, Back, InputError, InputLabel, PrimaryButton, CancelButton,
    },
    props: {
        supplier: Object,
    },
    data() {
        return {
            form: useForm({
                name: this.supplier.name,
                contact_person: this.supplier.contact_person,
                email: this.supplier.email,
                phone: this.supplier.phone,
                notes: this.supplier.notes,
                address: this.supplier.address,
            }),
        };
    },
    methods: {
        update() {
            this.form.put(route('suppliers.update', this.supplier.id), {
                onSuccess: () => this.$notify({ title: 'Éxito', message: 'Proveedor actualizado', type: 'success' }),
            });
        },
    },
};
</script>
