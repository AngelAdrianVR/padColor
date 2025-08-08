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
                    <el-input-number v-model="form.quantity" @change="handleSheet" placeholder="Ingresa la cantidad" step="0.01"
                        :min="1" class="!w-full" />
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
                                :style="{ backgroundColor: stations.find(s => s.name === form.station)?.light, color: stations.find(s => s.name === form.station)?.dark }"
                                v-html="stations.find(s => s.name === form.station)?.icon"></div>
                        </div>
                    </InputLabel>
                    <el-select v-model="form.station" filterable placeholder="Selecciona el progreso actual"
                        class="!w-full">
                        <el-option v-for="station in stations" :key="station" :label="station.name"
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
                    <el-input-number v-model="form.pps" @change="handleSheet" :min="1" placeholder="Piezas por hoja" step="0.01"
                        class="!w-full" />
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
                    <el-input-number v-model="form.adjust" @change="handleHa" placeholder="Ajuste" :min="0" step="0.01"
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
                    <el-input-number v-model="form.pf" @change="handleHa" placeholder="P/F" :min="0" class="!w-full" step="0.01" />
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
                    <PrimaryButton type="button" @click="$inertia.visit(route('productions.index', { currentTab: 2 }))"
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
import axios from 'axios';
import Back from '@/Components/MyComponents/Back.vue';

export default {
    name: 'EditeProduction',
    data() {
        const form = useForm({
            // tomar la primera letra de this.production.folio
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
            has_varnish: !! this.production.varnish_type,
            varnish_type: this.production.varnish_type,
            start_date: this.production.start_date,
            estimated_date: this.production.estimated_date,
        });

        return {
            form,
            products: [this.product],
            machines: [],
            clients: [],
            fetchingProducts: false,
            fetchingMachines: false,
            fetchingClients: false,
            // calculos
            dfh: null,
            // opciones
            stations: [
                {
                    name: 'Material pendiente',
                    dark: '#005DB5',
                    light: '#C2E1FF',
                    icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0-3-3m3 3 3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" /></svg>',
                },
                {
                    name: 'Solicitado',
                    dark: '#00A9B5',
                    light: '#bef4f8',
                    icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 7.5h-.75A2.25 2.25 0 0 0 4.5 9.75v7.5a2.25 2.25 0 0 0 2.25 2.25h7.5a2.25 2.25 0 0 0 2.25-2.25v-7.5a2.25 2.25 0 0 0-2.25-2.25h-.75m-6 3.75 3 3m0 0 3-3m-3 3V1.5m6 9h.75a2.25 2.25 0 0 1 2.25 2.25v7.5a2.25 2.25 0 0 1-2.25 2.25h-7.5a2.25 2.25 0 0 1-2.25-2.25v-.75" /></svg>',
                },
                {
                    name: 'X Offset',
                    dark: '#56A612',
                    light: '#E4FAD1',
                    icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" /></svg>',
                },
                {
                    name: 'X Pegado',
                    dark: '#D97706',
                    light: '#FFF6CC',
                    icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 0 0-5.78 1.128 2.25 2.25 0 0 1-2.4 2.245 4.5 4.5 0 0 0 8.4-2.245c0-.399-.078-.78-.22-1.128Zm0 0a15.998 15.998 0 0 0 3.388-1.62m-5.043-.025a15.994 15.994 0 0 1 1.622-3.395m3.42 3.42a15.995 15.995 0 0 0 4.764-4.648l3.876-5.814a1.151 1.151 0 0 0-1.597-1.597L14.146 6.32a15.996 15.996 0 0 0-4.649 4.763m3.42 3.42a6.776 6.776 0 0 0-3.42-3.42" /></svg>',
                },
                {
                    name: 'X Hojeado',
                    dark: '#EA580C',
                    light: '#FFE4C4',
                    icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" /></svg>',
                },
                {
                    name: 'X Barniz',
                    dark: '#9333EA',
                    light: '#EBD7FF',
                    icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" /></svg>',
                },
                {
                    name: 'X Corte',
                    dark: '#087F74',
                    light: '#D2FFF2',
                    icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="m7.848 8.25 1.536.887M7.848 8.25a3 3 0 1 1-5.196-3 3 3 0 0 1 5.196 3Zm1.536.887a2.165 2.165 0 0 1 1.083 1.839c.005.351.054.695.14 1.024M9.384 9.137l2.077 1.199M7.848 15.75l1.536-.887m-1.536.887a3 3 0 1 1-5.196 3 3 3 0 0 1 5.196-3Zm1.536-.887a2.165 2.165 0 0 0 1.083-1.838c.005-.352.054-.695.14-1.025m-1.223 2.863 2.077-1.199m0-3.328a4.323 4.323 0 0 1 2.068-1.379l5.325-1.628a4.5 4.5 0 0 1 2.48-.044l.803.215-7.794 4.5m-2.882-1.664A4.33 4.33 0 0 0 10.607 12m3.736 0 7.794 4.5-.802.215a4.5 4.5 0 0 1-2.48-.043l-5.326-1.629a4.324 4.324 0 0 1-2.068-1.379M14.343 12l-2.882 1.664" /></svg>',
                },
                {
                    name: 'X Suaje',
                    dark: '#065F09',
                    light: '#D6F5E3',
                    icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M14.25 6.087c0-.355.186-.676.401-.959.221-.29.349-.634.349-1.003 0-1.036-1.007-1.875-2.25-1.875s-2.25.84-2.25 1.875c0 .369.128.713.349 1.003.215.283.401.604.401.959v0a.64.64 0 0 1-.657.643 48.39 48.39 0 0 1-4.163-.3c.186 1.613.293 3.25.315 4.907a.656.656 0 0 1-.658.663v0c-.355 0-.676-.186-.959-.401a1.647 1.647 0 0 0-1.003-.349c-1.036 0-1.875 1.007-1.875 2.25s.84 2.25 1.875 2.25c.369 0 .713-.128 1.003-.349.283-.215.604-.401.959-.401v0c.31 0 .555.26.532.57a48.039 48.039 0 0 1-.642 5.056c1.518.19 3.058.309 4.616.354a.64.64 0 0 0 .657-.643v0c0-.355-.186-.676-.401-.959a1.647 1.647 0 0 1-.349-1.003c0-1.035 1.008-1.875 2.25-1.875 1.243 0 2.25.84 2.25 1.875 0 .369-.128.713-.349 1.003-.215.283-.4.604-.4.959v0c0 .333.277.599.61.58a48.1 48.1 0 0 0 5.427-.63 48.05 48.05 0 0 0 .582-4.717.532.532 0 0 0-.533-.57v0c-.355 0-.676.186-.959.401-.29.221-.634.349-1.003.349-1.035 0-1.875-1.007-1.875-2.25s.84-2.25 1.875-2.25c.37 0 .713.128 1.003.349.283.215.604.401.96.401v0a.656.656 0 0 0 .658-.663 48.422 48.422 0 0 0-.37-5.36c-1.886.342-3.81.574-5.766.689a.578.578 0 0 1-.61-.58v0Z" /></svg>',
                },
                {
                    name: 'Maquila',
                    dark: '#374151',
                    light: '#ECECEC',
                    icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.05 4.575a1.575 1.575 0 1 0-3.15 0v3m3.15-3v-1.5a1.575 1.575 0 0 1 3.15 0v1.5m-3.15 0 .075 5.925m3.075.75V4.575m0 0a1.575 1.575 0 0 1 3.15 0V15M6.9 7.575a1.575 1.575 0 1 0-3.15 0v8.175a6.75 6.75 0 0 0 6.75 6.75h2.018a5.25 5.25 0 0 0 3.712-1.538l1.732-1.732a5.25 5.25 0 0 0 1.538-3.712l.003-2.024a.668.668 0 0 1 .198-.471 1.575 1.575 0 1 0-2.228-2.228 3.818 3.818 0 0 0-1.12 2.687M6.9 7.575V12m6.27 4.318A4.49 4.49 0 0 1 16.35 15m.002 0h-.002" /></svg>',
                },
                {
                    name: 'X Realzado',
                    dark: '#64748B',
                    light: '#E1E5EA',
                    icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25H7.5a2.25 2.25 0 0 0-2.25 2.25v9a2.25 2.25 0 0 0 2.25 2.25h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25H15m0-3-3-3m0 0-3 3m3-3V15" /></svg>',
                },
                {
                    name: 'X Pleca',
                    dark: '#C6A317',
                    light: '#FAF2D1',
                    icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" /></svg>',
                },
                {
                    name: 'Calidad',
                    dark: '#558233',
                    light: '#b1db92',
                    icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0 1 18 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3 1.5 1.5 3-3.75" /></svg>',
                },
                {
                    name: 'Inspección',
                    dark: '#A21CAF',
                    light: '#F5D3F8',
                    icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>',
                },
                {
                    name: 'X Sellado',
                    dark: '#176799',
                    light: '#D3EAF8',
                    icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 0 0 .495-7.468 5.99 5.99 0 0 0-1.925 3.547 5.975 5.975 0 0 1-2.133-1.001A3.75 3.75 0 0 0 12 18Z" /></svg>',
                },
                {
                    name: 'en X Estampado',
                    dark: '#D00A95',
                    light: '#FDCEEF',
                    icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>',
                },
                {
                    name: 'X Serigrafía',
                    dark: '#78350F',
                    light: '#F9E0D2',
                    icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="m15 11.25 1.5 1.5.75-.75V8.758l2.276-.61a3 3 0 1 0-3.675-3.675l-.61 2.277H12l-.75.75 1.5 1.5M15 11.25l-8.47 8.47c-.34.34-.8.53-1.28.53s-.94.19-1.28.53l-.97.97-.75-.75.97-.97c.34-.34.53-.8.53-1.28s.19-.94.53-1.28L12.75 9M15 11.25 12.75 9" /></svg>',
                },
                {
                    name: 'X Troquel',
                    dark: '#1E40AF',
                    light: '#D3DCF8',
                    icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75H6A2.25 2.25 0 0 0 3.75 6v1.5M16.5 3.75H18A2.25 2.25 0 0 1 20.25 6v1.5m0 9V18A2.25 2.25 0 0 1 18 20.25h-1.5m-9 0H6A2.25 2.25 0 0 1 3.75 18v-1.5M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>',
                },
                {
                    name: 'Terminadas',
                    dark: '#3E8202',
                    light: '#E4FECD',
                    icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>',
                },
                {
                    name: 'Canceladas',
                    dark: '#951919',
                    light: '#FCD5D5',
                    icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>',
                },

            ],
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