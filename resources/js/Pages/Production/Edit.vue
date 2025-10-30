<template>
    <AppLayout title="Editar producción">
        <div class="mb-4">
            <Back class="mt-5 mx-2 lg:mx-20" />
            <form @submit.prevent="update" ref="formContainer"
                class="rounded-lg border border-grayD9 lg:p-5 p-3 lg:w-3/4 mx-auto mt-7 md:grid grid-cols-2 gap-x-3 gap-y-2">
                <h1 class="font-semibold ml-2 col-span-full">Editar orden de producción</h1>
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
                        <el-select v-model="form.client" placeholder="Escribe para buscar..." class="!w-full"
                            filterable remote reserve-keyword :remote-method="fetchClientsMatch"
                            :loading="fetchingClients" no-match-text="No hay clientes coincidentes">
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
                    <el-select v-model="form.product_id" filterable placeholder="Selecciona el producto" remote
                        reserve-keyword :remote-method="fetchProductsMatch" :loading="fetchingProducts" class="!w-full"
                        no-match-text="No hay productos coincidentes">
                        <el-option v-for="product in products" :key="product.id" :label="product.name"
                            :value="product.id" />
                    </el-select>
                    <InputError :message="form.errors.product_id" />
                </div>
                <div>
                    <InputLabel value="Cantidad solicitada*" />
                    <el-input-number v-model="form.quantity" @change="handleSheet" placeholder="Ingresa la cantidad"
                        :step="0.01" :min="0.01" class="!w-full" />
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
                    <InputLabel value="Dim. F/H" />
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
                    <InputLabel value="Total de hojas" />
                    <el-input v-model="form.ts" placeholder="Hojas" disabled />
                </div>
                <div>
                    <InputLabel value="Ta/Im" />
                    <el-input v-model="form.ps" placeholder="Tamaño de impresión" disabled />
                </div>
                <div>
                    <InputLabel value="Total Ta/Im" />
                    <el-input v-model="form.tps" placeholder="Total de tamaños de impresión" disabled />
                </div>
                <div class="col-span-2 text-right mt-4 space-x-2">
                    <PrimaryButton type="button" @click="$inertia.visit(route('productions.index'))"
                        :disabled="form.processing" class="!bg-[#CFCFCF] !text-[#6E6E6E]">
                        Cancelar
                    </PrimaryButton>
                    <PrimaryButton :disabled="form.processing">
                        <i v-if="form.processing" class="fa-solid fa-circle-notch fa-spin mr-2"></i>
                        <span>Guardar cambios</span>
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
import Back from '@/Components/MyComponents/Back.vue';

export default {
    name: 'EditeProduction',
    data() {
        const form = useForm({
            folio: this.production.folio,
            type: this.production.type,
            client: this.production.client,
            changes: this.production.changes,
            season: this.production.season,
            station: this.production.station,
            quantity: this.production.quantity,
            materials: this.production.materials?.length ? this.production.materials[0] : null,
            notes: this.production.notes,
            product_id: this.production.product_id,
            machine_id: this.production.machine_id,
            material: this.production.material,
            width: this.production.width,
            gauge: this.production.gauge,
            large: this.production.large,
            pps: this.production.pps,
            adjust: this.production.adjust,
            look: this.production.look,
            faces: this.production.faces,
            dfi: this.production.dfi,
            sheets: this.production.sheets,
            ha: this.production.ha,
            pf: this.production.pf,
            ts: this.production.ts,
            ps: this.production.ps,
            tps: this.production.tps,
            has_varnish: !!this.production.varnish_type,
            varnish_type: this.production.varnish_type,
            start_date: this.production.start_date,
            estimated_date: this.production.estimated_date,
        });

        return {
            form,
            products: [this.product],
            machines: [],
            // --- MODIFICADO ---
            // Inicializa 'clients' con el cliente actual para que se muestre.
            // Usamos el nombre como 'id' y 'name' porque 'form.client' guarda el nombre.
            clients: this.production.client ? [{ id: this.production.client, name: this.production.client }] : [],
            // --- FIN MODIFICADO ---
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
        InputLabel,
        InputError,
        PrimaryButton,
        Back,
    },
    props: {
        production: Object,
        product: Object,
    },
    methods: {
        update() {
            this.form.put(route('productions.update', this.production.id), {
                onSuccess: () => {
                    this.$notify({
                        title: 'Orden de producción actualizada',
                        message: '',
                        type: 'success',
                    });
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
        cleanForm() {
            this.form.reset();
            this.dfh = null;
        },
        handleVarnish() {
            if (!this.form.has_varnish) {
                this.form.varnish_type = null;
            }
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
        handleChangeProduct() {
            const productSelected = this.products.find(product => product.id === this.form.product_id);
            this.form.material = productSelected.material;
        },
        async fetchProductsMatch(query) {
            if (!query) {
                // No limpiar 'products' para que la opción actual siga seleccionada
                // this.products = []; 
                return;
            }

            this.fetchingProducts = true;
            try {
                const response = await axios.get(route('products.get-match', { query }));

                if (response.status === 200) {
                    // Mantenemos el producto actual en la lista si no está en los resultados
                    const currentProduct = this.product;
                    if (currentProduct && !response.data.items.some(p => p.id === currentProduct.id)) {
                        this.products = [currentProduct, ...response.data.items];
                    } else {
                        this.products = response.data.items;
                    }
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
        async fetchClientsMatch(query) {
            if (!query) {
                // No limpiar 'clients' para que la opción actual siga seleccionada
                return;
            }

            this.fetchingClients = true;
            try {
                const response = await axios.get(route('clients.get-match', { query }));

                if (response.status === 200) {
                    // Mantenemos el cliente actual en la lista si no está en los resultados
                    const currentClient = { id: this.form.client, name: this.form.client };
                    if (currentClient.name && !response.data.items.some(c => c.name === currentClient.name)) {
                        this.clients = [currentClient, ...response.data.items];
                    } else {
                        this.clients = response.data.items;
                    }
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
        this.handleDfh(); // Ejecuta esto al montar para calcular el 'dfh' inicial
        this.handleSheet(); // Ejecuta esto para calcular todo lo demás
    },
}
</script>