<template>
    <div>
        <form @submit.prevent="store"
            class="rounded-lg border border-grayD9 lg:p-5 p-3 lg:w-3/4 mx-auto mt-7 grid grid-cols-2 gap-x-3 gap-y-1">
            <h1 class="font-bold ml-2 col-span-full">Crear orden de producción</h1>
            <h2 class="text-gray-500 font-bold ml-2 col-span-full mt-4">Información de la orden</h2>
            <div>
                <InputLabel value="N° de orden" />
                <el-input v-model="form.folio" placeholder="">
                    <template #prepend>
                        <el-select v-model="form.type" placeholder="Tipo" class="!w-20">
                            <el-option label="Nuevo" value="N" />
                            <el-option label="Repetido" value="R" />
                        </el-select>
                    </template>
                </el-input>
                <InputError :message="form.errors.folio" />
            </div>
            <div class="col-span-2 text-right mt-4">
                <PrimaryButton :disabled="form.processing">Guardar</PrimaryButton>
            </div>
        </form>
    </div>
</template>

<script>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useForm } from '@inertiajs/vue3';

export default {
    name: 'CreateProduction',
    data() {
        const form = useForm({
            folio: null,
            type: 'N',
            client: null,
            season: null,
            station: null,
            quantity: null,
            materials: null,
            notes: null,
            product_id: null,
            machine_id: null,
        });

        return {
            form,
        }
    },
    components: {
        InputLabel,
        InputError,
        PrimaryButton,
    },
    props: {
    },
    methods: {

    },
}
</script>