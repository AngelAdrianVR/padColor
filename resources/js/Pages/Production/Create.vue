<template>
    <AppLayout title="Crear nueva producción">
        <div class="mb-4">
            <Back class="mt-5 mx-2 lg:mx-20" />
            <form @submit.prevent="store" ref="formContainer"
                class="rounded-lg border border-grayD9 lg:p-5 p-3 lg:w-3/4 mx-auto mt-7 md:grid grid-cols-2 gap-x-3 gap-y-2">
                <h1 class="font-semibold ml-2 col-span-full">Crear orden de producción</h1>
                <h2 class="text-gray-500 font-semibold ml-2 col-span-full my-4">Información de la orden</h2>
                <div>
                    <InputLabel value="N° de orden*" />
                    <el-input v-model="form.folio" placeholder="">
                        <template #prepend>
                            <el-select v-model="form.type" placeholder="Tipo" class="!w-28">
                                <el-option label="Nuevo" value="Nuevo" />
                                <el-option label="Repetido" value="Repetido" />
                            </el-select>
                        </template>
                    </el-input>
                    <InputError :message="form.errors.folio" />
                </div>
                <div>
                    <InputLabel value="Fecha de inicio*" />
                    <el-date-picker class="!w-full" v-model="form.start_date" type="date" placeholder="dd/mm/aa"
                        value-format="YYYY-MM-DD" format="DD/MM/YYYY" />
                    <InputError :message="form.errors.start_date" />
                </div>
                <div>
                    <InputLabel value="Fecha estimada de entrega*" />
                    <el-date-picker class="!w-full" v-model="form.estimated_date" type="date" placeholder="dd/mm/aa"
                        value-format="YYYY-MM-DD" format="DD/MM/YYYY" />
                    <InputError :message="form.errors.estimated_date" />
                </div>
                <div>
                    <InputLabel value="Cliente*" />
                    <div class="flex items-center">
                        <i v-if="fetchingClients" class="fa-solid fa-circle-notch fa-spin mr-2"></i>
                        <span v-if="fetchingClients" class="text-[10px]">Cargando clientes</span>
                        <el-select v-model="form.client" placeholder="" class="!w-full" filterable
                            :disabled="fetchingClients">
                            <el-option v-for="item in clients" :key="item.id" :label="item.name" :value="item.name" />
                        </el-select>
                    </div>
                    <InputError :message="form.errors.client" />
                </div>
                <div>
                    <InputLabel value="Número de cambios*" />
                    <el-input v-model="form.changes" placeholder="Ingresa el número de cambios" />
                    <InputError :message="form.errors.changes" />
                </div>
                <h2 class="text-gray-500 font-semibold ml-2 col-span-full my-3">Información del producto</h2>
                <div>
                    <InputLabel value="Producto*" />
                    <el-select v-model="form.product_id" @change="handleChangeProduct" filterable
                        placeholder="Selecciona el producto" remote reserve-keyword :remote-method="fetchProductsMatch"
                        :loading="fetchingProducts" class="!w-full" no-match-text="No hay productos coincidentes">
                        <el-option v-for="product in products" :key="product.id" :label="product.name"
                            :value="product.id" />
                    </el-select>
                    <InputError :message="form.errors.product_id" />
                </div>
                <div>
                    <InputLabel value="Cantidad solicitada*" />
                    <el-input-number v-model="form.quantity" @change="handleSheet" placeholder="Ingresa la cantidad"
                        :min="0.01" :step="0.01" class="!w-full" />
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
                    <InputLabel>
                        <div class="flex items-center space-x-3">
                            <span>Progreso*</span>
                            <div v-if="form.station" class="rounded-full size-6 flex items-center justify-center"
                                :style="{ backgroundColor: stations.find(s => s.name === form.station)?.light, color: stations.find(s => s.name === form.station)?.dark }">
                                <component :is="stations.find(s => s.name === form.station)?.icon" class="size-4" />
                            </div>
                        </div>
                    </InputLabel>
                    <el-select v-model="form.station" filterable placeholder="Selecciona el progreso actual"
                        class="!w-full">
                        <el-option v-for="station in stations" :key="station.name" :label="station.name"
                            :value="station.name" />
                    </el-select>
                    <InputError :message="form.errors.station" />
                </div>
                <div class="mt-1">
                    <InputLabel value="Máquina" />
                    <div class="flex items-center">
                        <i v-if="fetchingMachines" class="fa-solid fa-circle-notch fa-spin mr-2"></i>
                        <span v-if="fetchingMachines" class="text-[10px]">Cargando máquinas</span>
                        <el-select v-model="form.machine_id" filterable placeholder="Selecciona la máquina"
                            class="!w-full" :disabled="fetchingMachines">
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
                <h2 class="text-gray-500 font-semibold ml-2 col-span-full my-3">Materiales y medidas</h2>
                <div>
                    <InputLabel value="Material" />
                    <el-input v-model="form.material" placeholder="Escribe el material" />
                    <InputError :message="form.errors.material" />
                </div>
                <div>
                    <InputLabel value="Ancho" />
                    <el-input v-model="form.width" @change="handleDfh" placeholder="Ej. 25" />
                    <InputError :message="form.errors.width" />
                </div>
                <div>
                    <InputLabel value="Calibre" />
                    <el-input v-model="form.gauge" placeholder="Escribe el calibre" />
                    <InputError :message="form.errors.gauge" />
                </div>
                <div>
                    <InputLabel value="Largo" />
                    <el-input v-model="form.large" @change="handleDfh" placeholder="Ej. 30" />
                    <InputError :message="form.errors.large" />
                </div>
                <div class="mt-6">
                    <InputLabel class="flex items-center">
                        <input type="checkbox" v-model="form.has_varnish" @change="handleVarnish"
                            class="rounded text-primary shadow-sm focus:ring-primary bg-transparent" />
                        <span class="ml-2 text-sm">Con Barniz</span>
                    </InputLabel>
                </div>
                <div v-if="form.has_varnish">
                    <InputLabel value="Tipo de barniz*" />
                    <el-input v-model="form.varnish_type" placeholder="Ej. Barniz UV" />
                    <InputError :message="form.errors.varnish_type" />
                </div>
                <div>
                    <InputLabel value="Acabado" />
                    <el-select v-model="form.look" placeholder="Selecciona el acabado" class="!w-full">
                        <el-option v-for="look in looks" :key="look" :label="look" :value="look" />
                    </el-select>
                    <InputError :message="form.errors.look" />
                </div>
                <div>
                    <InputLabel>
                        <div class="flex items-center space-x-3">
                            <span>Dim. F/H</span>
                            <el-tooltip content="Ancho x Largo" placement="top">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
                                </svg>
                            </el-tooltip>
                        </div>
                    </InputLabel>
                    <el-input v-model="dfh" placeholder="Dimensión del formato de hoja" disabled />
                </div>
                <div>
                    <InputLabel value="Caras" />
                    <el-select v-model="form.faces" placeholder="Selecciona el número de caras" class="!w-full">
                        <el-option v-for="face in [0, 1, 2]" :key="face" :label="face" :value="face" />
                    </el-select>
                    <InputError :message="form.errors.faces" />
                </div>
                <div>
                    <InputLabel value="Dim. F/im" />
                    <el-input v-model="form.dfi" placeholder="Dimensión del formato de impresión" />
                    <InputError :message="form.errors.dfi" />
                </div>
                <div>
                    <InputLabel value="Pz/H" />
                    <el-input-number v-model="form.pps" @change="handleSheet" :min="0.01" placeholder="Piezas por hoja"
                        :step="0.01" class="!w-full" />
                    <InputError :message="form.errors.pps" />
                </div>
                <div>
                    <InputLabel>
                        <div class="flex items-center space-x-3">
                            <span>Hojas</span>
                            <el-tooltip content="Cantidad solicitada / Piezas por hoja" placement="top">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
                                </svg>
                            </el-tooltip>
                        </div>
                    </InputLabel>
                    <el-input v-model="form.sheets" placeholder="Hojas" disabled />
                </div>
                <div>
                    <InputLabel value="Ajuste" />
                    <el-input-number v-model="form.adjust" @change="handleHa" placeholder="Ajuste" :min="0" :step="0.01"
                        class="!w-full" />
                    <InputError :message="form.errors.adjust" />
                </div>
                <div>
                    <InputLabel>
                        <div class="flex items-center space-x-3">
                            <span>H/A</span>
                            <el-tooltip content="Ajuste / P/F" placement="top">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
                                </svg>
                            </el-tooltip>
                        </div>
                    </InputLabel>
                    <el-input v-model="form.ha" placeholder="H/A" disabled />
                </div>
                <div>
                    <InputLabel value="P/F" />
                    <el-input-number v-model="form.pf" @change="handleHa" placeholder="P/F" :min="0" class="!w-full"
                        :step="0.01" />
                    <InputError :message="form.errors.pf" />
                </div>
                <div>
                    <InputLabel>
                        <div class="flex items-center space-x-3">
                            <span>Total de hojas</span>
                            <el-tooltip content="Hojas + Ajuste" placement="top">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
                                </svg>
                            </el-tooltip>
                        </div>
                    </InputLabel>
                    <el-input v-model="form.ts" placeholder="Hojas" disabled />
                </div>
                <div>
                    <InputLabel>
                        <div class="flex items-center space-x-3">
                            <span>Ta/Im</span>
                            <el-tooltip content="Hojas / Piezas por hoja" placement="top">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
                                </svg>
                            </el-tooltip>
                        </div>
                    </InputLabel>
                    <el-input v-model="form.ps" placeholder="Tamaño de impresión" disabled />
                </div>
                <div>
                    <InputLabel>
                        <div class="flex items-center space-x-3">
                            <span>Total Ta/Im</span>
                            <el-tooltip content="Total de Hojas / Piezas por hoja" placement="top">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
                                </svg>
                            </el-tooltip>
                        </div>
                    </InputLabel>
                    <el-input v-model="form.tps" placeholder="Total de tamaños de impresión" disabled />
                </div>
                <div class="col-span-2 text-right mt-4 space-x-2">
                    <PrimaryButton type="button" @click="cleanForm" :disabled="form.processing"
                        class="!bg-[#CFCFCF] !text-[#6E6E6E]">
                        Limpiar formulario
                    </PrimaryButton>
                    <PrimaryButton :disabled="form.processing">
                        <i v-if="form.processing" class="fa-solid fa-circle-notch fa-spin mr-2"></i>
                        <span>Crear</span>
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useForm } from '@inertiajs/vue3';
import { stations } from '@/Data/stations';
import axios from 'axios';
import { format } from "date-fns";
import Back from '@/Components/MyComponents/Back.vue';

export default {
    name: 'CreateProduction',
    data() {
        const form = useForm({
            folio: this.nextProduction,
            type: 'Nuevo',
            client: 'PadColor',
            changes: null,
            season: null,
            station: 'Material pendiente',
            quantity: null,
            materials: null,
            notes: null,
            product_id: null,
            machine_id: null,
            material: null,
            width: null,
            gauge: null,
            large: null,
            pps: null,
            adjust: 0,
            look: null,
            faces: null,
            dfi: null,
            sheets: null,
            ha: null,
            pf: null,
            ts: null,
            ps: null,
            tps: null,
            has_varnish: false,
            varnish_type: null,
            start_date: format(new Date(), "yyyy-MM-dd"), // Establece la fecha de hoy por defecto
            estimated_date: null,
        });

        return {
            form,
            products: [],
            machines: [],
            clients: [],
            fetchingProducts: false,
            fetchingMachines: false,
            fetchingClients: false,
            // calculos
            dfh: null,
            // opciones
            stations: stations,
            materials: [
                'Ninguno',
                'Caja base',
                'Caja tapa',
                'Etiqueta',
                'Tableros',
                'Cartas 1',
                'Cartas 2',
                'Portada',
                'Interior',
                'Interior catálogo',
            ],
            looks: [
                'Mate',
                'Brillante',
                'Kraft',
                'Reverso Kraft',
                'Blanco',
            ]
        }
    },
    components: {
        AppLayout,
        Back,
        InputLabel,
        InputError,
        PrimaryButton,
    },
    props: {
        nextProduction: {
            type: Number,
            required: true,
        },
    },
    emits: ['created'],
    methods: {
        handleVarnish() {
            if (!this.form.has_varnish) {
                this.form.varnish_type = null;
            }
        },
        store() {
            this.form.post(route('productions.store'), {
                onSuccess: () => {
                    this.cleanForm();
                    // sumar unidad a folio para poder crear otra producción
                    this.form.folio++;

                    this.$notify({
                        title: 'Orden de producción creada',
                        message: '',
                        type: 'success',
                    });
                    this.$emit('created');
                },
                onError: () => {
                    console.log(this.form.errors);
                    this.$refs.formContainer.scrollIntoView({
                        behavior: 'smooth',
                    });

                    this.$notify({
                        title: 'Verifique los campos requeridos',
                        type: 'warning',
                    });
                },
            });
        },
        handleChangeProduct() {
            const productSelected = this.products.find(product => product.id === this.form.product_id);
            this.form.material = productSelected.material;
        },
        cleanForm() {
            this.form.reset();
            this.dfh = null;
        },
        handleDfh() {
            if (this.form.width && this.form.large) {
                this.dfh = this.form.width + ' x ' + this.form.large;
            } else {
                this.dfh = null;
            }
        },
        handleSheet() {
            if (this.form.quantity && this.form.pps > 0) {
                this.form.sheets = Math.ceil(this.form.quantity / this.form.pps);
            } else {
                this.form.sheets = null;
            }
            if (this.form.adjust && this.form.pf) {
                this.form.ha = Math.ceil(this.form.adjust / this.form.pf);
            } else {
                this.form.ha = null;
            }
            if (this.form.sheets && this.form.adjust) {
                this.form.ts = Math.ceil(this.form.sheets + this.form.adjust);
            } else {
                this.form.ts = null;
            }
            if (this.form.sheets && this.form.pps) {
                this.form.ps = Math.ceil(this.form.sheets / this.form.pps);
            } else {
                this.form.ps = null;
            }
            if (this.form.ts && this.form.pps) {
                this.form.tps = Math.ceil(this.form.ts / this.form.pps);
            } else {
                this.form.tps = null;
            }
        },
        handleHa() {
            if (this.form.adjust && this.form.pf) {
                this.form.ha = Math.ceil(this.form.adjust / this.form.pf);
            } else {
                this.form.ha = null;
            }
            if (this.form.sheets && this.form.adjust) {
                this.form.ts = Math.ceil(this.form.sheets + this.form.adjust);
            } else {
                this.form.ts = null;
            }
            if (this.form.sheets && this.form.pps) {
                this.form.ps = Math.ceil(this.form.sheets / this.form.pps);
            } else {
                this.form.ps = null;
            }
            if (this.form.ts && this.form.pps) {
                this.form.tps = Math.ceil(this.form.ts / this.form.pps);
            } else {
                this.form.tps = null;
            }
        },
        async fetchProductsMatch(query) {
            if (!query) {
                this.products = [];
                return;
            }

            this.fetchingProducts = true;
            try {
                const response = await axios.get(route('products.get-match', { query }));

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
        async fetchClients() {
            this.fetchingClients = true;
            try {
                const response = await axios.get(route('clients.get-all'));

                if (response.status === 200) {
                    this.clients = response.data.items;
                }
            } catch (error) {
                console.error('Error fetching clients:', error);
            } finally {
                this.fetchingClients = false;
            }
        },
    },
    mounted() {
        this.fetchMachines();
        this.fetchClients();
    },
}
</script>
