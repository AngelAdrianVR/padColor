<template>
    <AppLayout title="Crear materia prima">
        <main class="px-2 lg:px-14 py-8">
            <form @submit.prevent="store" class="rounded-lg border border-grayD9 lg:p-5 p-3 lg:w-1/2 mx-auto">

                <!-- Encabezado -->
                <div class="flex items-center space-x-2 mb-6">
                    <Back />
                    <h1 class="font-semibold text-gray-800">Crear materia prima</h1>
                </div>

                <!-- Formulario -->
                <div class="grid grid-cols-2 gap-3 gap-y-4">
                    <div>
                        <InputLabel value="Código interno (SKU)" />
                        <el-input v-model="form.sku" placeholder="Ej. 3847" />
                        <InputError :message="form.errors.sku" />
                    </div>
                    <div>
                        <InputLabel value="Nombre*" />
                        <el-input v-model="form.name" placeholder="Ej. Rollo de papel Kraft" />
                        <InputError :message="form.errors.name" />
                    </div>
                    <div class="col-span-full">
                        <InputLabel value="Unidad de medida" />
                        <el-select v-model="form.measure_unit" placeholder="Selecciona una unidad" class="!w-full">
                            <el-option v-for="unit in measureUnits" :key="unit" :label="unit" :value="unit" />
                        </el-select>
                        <InputError :message="form.errors.measure_unit" />
                    </div>
                    <div class="col-span-full">
                        <InputLabel value="Descripción" />
                        <el-input v-model="form.description" :autosize="{ minRows: 3, maxRows: 5 }" type="textarea"
                            placeholder="Describe la materia prima" show-word-limit clearable />
                        <InputError :message="form.errors.description" />
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="text-right mt-6">
                    <CancelButton @click="$inertia.visit(route('raw-materials.index'))" type="button">
                        Cancelar
                    </CancelButton>
                    <PrimaryButton :disabled="form.processing" class="ml-2">
                        <i v-if="form.processing" class="fa-solid fa-circle-notch fa-spin mr-2"></i>
                        Crear materia prima
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
        AppLayout,
        Back,
        InputError,
        InputLabel,
        PrimaryButton,
        CancelButton,
    },
    data() {
        const form = useForm({
            name: null,
            sku: null,
            measure_unit: 'Pieza',
            description: null,
        });

        return {
            form,
            measureUnits: [
                'Pieza', 'Rollo', 'Metro', 'Litro', 'Kilogramo', 'Paquete'
            ],
        };
    },
    methods: {
        store() {
            this.form.post(route('raw-materials.store'), {
                onSuccess: () => {
                    this.$notify({
                        title: 'Éxito',
                        message: 'Materia prima creada correctamente',
                        type: 'success',
                    });
                },
            });
        },
    },
};
</script>
