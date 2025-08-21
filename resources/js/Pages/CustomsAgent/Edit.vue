<template>
    <AppLayout title="Editar agente aduanal">
        <main class="px-2 lg:px-14 py-8">
            <form @submit.prevent="update" class="rounded-lg border border-grayD9 lg:p-5 p-3 lg:w-1/2 mx-auto">
                <div class="flex items-center space-x-2 mb-6">
                    <Back />
                    <h1 class="font-semibold text-xl text-gray-800">Editar agente aduanal</h1>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-full">
                        <InputLabel value="Nombre / Razón Social*" />
                        <el-input v-model="form.name" placeholder="Escribe el nombre o razón social" />
                        <InputError :message="form.errors.name" />
                    </div>
                    <div>
                        <InputLabel value="Nombre del contacto" />
                        <el-input v-model="form.contact_person" placeholder="Ej. Ernesto Moreno Cisneros" />
                        <InputError :message="form.errors.contact_person" />
                    </div>
                     <div>
                        <InputLabel value="Correo electrónico" />
                        <el-input v-model="form.email" placeholder="Ej. ernesto@gmail.com" />
                        <InputError :message="form.errors.email" />
                    </div>
                    <div>
                        <InputLabel value="Teléfono" />
                        <el-input v-model="form.phone" placeholder="Escribe el número con la lada" />
                        <InputError :message="form.errors.phone" />
                    </div>
                </div>

                <div class="text-right mt-6">
                    <CancelButton @click="$inertia.visit(route('customs-agents.index'))" type="button">
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
        customs_agent: Object,
    },
    data() {
        return {
            form: useForm({
                name: this.customs_agent.name,
                contact_person: this.customs_agent.contact_person,
                email: this.customs_agent.email,
                phone: this.customs_agent.phone,
            }),
        };
    },
    methods: {
        update() {
            this.form.put(route('customs-agents.update', this.customs_agent.id), {
                onSuccess: () => this.$notify({ title: 'Éxito', message: 'Agente aduanal actualizado', type: 'success' }),
            });
        },
    },
};
</script>
