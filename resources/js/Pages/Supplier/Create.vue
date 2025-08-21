<template>
    <AppLayout title="Crear proveedor">
        <main class="px-2 lg:px-14 py-8">
            <form @submit.prevent="store" class="rounded-lg border border-grayD9 lg:p-5 p-3 lg:w-1/2 mx-auto">
                <div class="flex items-center space-x-2 mb-6">
                    <Back />
                    <h1 class="font-semibold text-xl text-gray3F">Crear proveedor</h1>
                </div>

                <div class="space-y-4">
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
                </div>

                <div class="text-right mt-6">
                    <CancelButton @click="$inertia.visit(route('suppliers.index'))" type="button">
                        Cancelar
                    </CancelButton>
                    <PrimaryButton :disabled="form.processing" class="ml-2">
                        <i v-if="form.processing" class="fa-solid fa-circle-notch fa-spin mr-2"></i>
                        Crear proveedor
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
    data() {
        return {
            form: useForm({
                name: null,
                contact_person: null,
                email: null,
                phone: null,
                address: null,
            }),
        };
    },
    methods: {
        store() {
            this.form.post(route('suppliers.store'), {
                onSuccess: () => this.$notify({ title: 'Éxito', message: 'Proveedor creado', type: 'success' }),
            });
        },
    },
};
</script>
