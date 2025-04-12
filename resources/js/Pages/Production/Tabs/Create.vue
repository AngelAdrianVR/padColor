<template>
    <div class="mb-4">
        <form @submit.prevent="store"
            class="rounded-lg border border-grayD9 lg:p-5 p-3 lg:w-3/4 mx-auto mt-7 grid grid-cols-2 gap-x-3 gap-y-2">
            <h1 class="font-semibold ml-2 col-span-full">Crear orden de producción</h1>
            <h2 class="text-gray-500 font-semibold ml-2 col-span-full my-4">Información de la orden</h2>
            <div>
                <InputLabel value="N° de orden*" />
                <el-input v-model="form.folio" placeholder="">
                    <template #prepend>
                        <el-select v-model="form.type" placeholder="Tipo" class="!w-28">
                            <el-option label="Nuevo" value="N" />
                            <el-option label="Repetido" value="R" />
                        </el-select>
                    </template>
                </el-input>
                <InputError :message="form.errors.folio" />
            </div>
            <div>
                <InputLabel value="Fecha de emisión*" />
                <el-date-picker class="!w-full" v-model="form.created_at" type="date" placeholder="dd/mm/aa"
                    value-format="YYYY-MM-DD" format="DD/MM/YYYY" />
                <InputError :message="form.errors.created_at" />
            </div>
            <div>
                <InputLabel value="Fecha estimada de entrega*" />
                <el-date-picker class="!w-full" v-model="form.estimated_date" type="date" placeholder="dd/mm/aa"
                    value-format="YYYY-MM-DD" format="DD/MM/YYYY" />
                <InputError :message="form.errors.estimated_date" />
            </div>
            <div>
                <InputLabel value="Cliente*" />
                <el-select v-model="form.client" placeholder="" class="!w-full">
                    <el-option label="PadColor" value="PadColor" />
                    <el-option label="Papel diseño y color" value="Papel diseño y color" />
                </el-select>
                <InputError :message="form.errors.client" />
            </div>
            <h2 class="text-gray-500 font-semibold ml-2 col-span-full my-3">Información del producto</h2>
            <div>
                <InputLabel value="Producto*" />
                <div class="flex items-center">
                    <i v-if="fetchingProducts" class="fa-solid fa-circle-notch fa-spin mr-2"></i>
                    <span v-if="fetchingProducts" class="text-[10px]">Cargando productos</span>
                    <el-select v-model="form.product_id" placeholder="Selecciona el producto" class="!w-full"
                        :disabled="fetchingProducts">
                        <el-option v-for="product in products" :key="product.id" :label="product.name"
                            :value="product.id" />
                    </el-select>
                </div>
                <InputError :message="form.errors.product_id" />
            </div>
            <div>
                <InputLabel value="Cantidad solicitada*" />
                <el-input-number v-model="form.quantity" placeholder="Ingresa la cantidad" :min="1" class="!w-60" />
                <InputError :message="form.errors.quantity" />
            </div>
            <div>
                <InputLabel value="Lista de material" />
                <el-select v-model="form.materials" placeholder="Selecciona el progreso actual" class="!w-full">
                    <el-option v-for="material in materials" :key="material" :label="material" :value="material" />
                </el-select>
                <InputError :message="form.errors.materials" />
            </div>
            <h2 class="text-gray-500 font-semibold ml-2 col-span-full my-3">Procesos de producción</h2>
            <div>
                <InputLabel value="Progreso*" />
                <el-select v-model="form.station" placeholder="Selecciona el progreso actual" class="!w-full">
                    <el-option v-for="station in stations" :key="station" :label="station" :value="station" />
                </el-select>
                <InputError :message="form.errors.station" />
            </div>
            <div>
                <InputLabel value="Máquina" />
                <div class="flex items-center">
                    <i v-if="fetchingMachines" class="fa-solid fa-circle-notch fa-spin mr-2"></i>
                    <span v-if="fetchingMachines" class="text-[10px]">Cargando máquinas</span>
                    <el-select v-model="form.machine_id" placeholder="Selecciona la máquina" class="!w-full"
                        :disabled="fetchingMachines">
                        <el-option v-for="machine in machines" :key="machine.id" :label="machine.name"
                            :value="machine.id" />
                    </el-select>
                </div>
                <InputError :message="form.errors.machine_id" />
            </div>
            <div class="col-span-full">
                <InputLabel value="Notas" />
                <el-input v-model="form.notes" :autosize="{ minRows: 3, maxRows: 5 }" type="textarea"
                    :maxlength="500" placeholder="Agrega las notas que consideres relevantes para producción"
                    show-word-limit clearable />
                <InputError :message="form.errors.notes" />
            </div>
            <div class="col-span-2 text-right mt-4">
                <PrimaryButton :disabled="form.processing">
                    <i v-if="form.processing" class="fa-solid fa-circle-notch fa-spin mr-2"></i>
                    <span>Crear</span>
                </PrimaryButton>
            </div>
        </form>
    </div>
</template>

<script>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { format } from "date-fns";

export default {
    name: 'CreateProduction',
    data() {
        const form = useForm({
            folio: null,
            type: 'N',
            client: 'PadColor',
            season: null,
            station: null,
            quantity: null,
            materials: null,
            notes: null,
            product_id: null,
            machine_id: null,
            // por defecto la fecha de hoy
            created_at: format(new Date(), "yyyy-MM-dd"), // Establece la fecha de hoy por defecto
            estimated_date: null,
        });

        return {
            form,
            products: [],
            machines: [],
            fetchingProducts: false,
            fetchingMachines: false,
            seasons: [
                'Verano',
                'Invierno',
                'Primavera',
                'Otoño',
            ],
            stations: [
                'Estación 1',
                'Estación 2',
                'Estación 3',
                'Estación 4',
            ],
            materials: [
                'Material 1',
                'Material 2',
                'Material 3',
                'Material 4',
            ],
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
        store() {
            this.form.post(route('productions.store'), {
                onSuccess: () => {
                    this.form.reset();
                    this.$notify({
                        title: 'Orden de producción creada',
                        message: '',
                        type: 'success',
                    });
                },
                onError: () => {
                    console.log(this.form.errors);
                },
            });
        },
        async fetchProducts() {
            this.fetchingProducts = true;
            try {
                const response = await axios.get(route('products.get-all'));

                if (response.status === 200) {
                    this.products = response.data.items;
                }
            } catch (error) {
                console.error('Error fetching products:', error);
            } finally {
                this.fetchingProducts = false;
            }
        },
        async fetchMachines() {
            this.fetchingMachines = true;
            try {
                const response = await axios.get(route('machines.get-all'));

                if (response.status === 200) {
                    this.machines = response.data.items;
                }
            } catch (error) {
                console.error('Error fetching machines:', error);
            } finally {
                this.fetchingMachines = false;
            }
        },
    },
    mounted() {
        this.fetchProducts();
        this.fetchMachines();
    },
}
</script>