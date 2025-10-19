<template>
    <!-- Note: You might want to make this modal wider in the parent component, e.g., by passing maxWidth="4xl" -->
    <DialogModal :show="show" @close="$emit('close')" maxWidth="3xl">
        <template #content>
            <div v-if="updatingDetails" class="my-60">
                <Loading />
            </div>
            <div v-else-if="selectedProduction" class="text-sm">
                <!-- ------------ Header ------------ -->
                <div class="flex justify-between items-start">
                    <div>
                        <div class="flex items-center space-x-3">
                            <h1 class="font-bold text-lg text-gray-800">Orden de producción Nº. {{
                                selectedProduction.folio }}</h1>
                            <!-- Status Chip (Hardcoded as "Finalizado" as requested) -->
                            <span
                                class="flex items-center bg-blue-100 text-blue-700 text-xs font-semibold px-2.5 py-1 rounded-full">
                                <CheckCircleIcon class="size-4 mr-1" />
                                Finalizado
                            </span>
                        </div>
                        <p class="text-sm text-gray-600 mt-1">Producto: {{ selectedProduction.product.name }}</p>
                        <p class="text-sm text-gray-600">Cantidad solicitada: <span class="font-semibold">{{
                            selectedProduction.quantity?.toLocaleString('es-MX') }}</span></p>
                    </div>
                    <div class="flex items-center space-x-2 flex-shrink-0">
                        <button @click="$inertia.visit(route('productions.hoja-viajera', selectedProduction.id))"
                            class="text-sm text-[#EC4899] border border-[#EC4899] rounded-md px-3 py-1.5 hover:bg-[#FCE7F3] transition-all">
                            Hoja viajera
                        </button>
                        <button v-if="$page.props.auth.user.permissions.includes('Editar producciones')"
                            @click="$inertia.visit(route('productions.edit', selectedProduction.id))"
                            class="text-sm text-gray-700 bg-gray-100 border border-gray-300 rounded-md px-3 py-1.5 hover:bg-gray-200 transition-all">
                            Editar
                        </button>
                    </div>
                </div>

                <!-- ------------ Tabs ------------ -->
                <div class="mt-4">
                    <el-tabs v-model="activeTab">
                        <el-tab-pane label="Detalles de la orden" name="details">
                            <div class="p-1 mt-2 space-y-6">
                                <!-- Información de la orden -->
                                <section>
                                    <h2 class="bg-gray-100 font-bold text-gray-700 py-2 px-3 rounded-md">Información de
                                        la orden</h2>
                                    <div class="grid grid-cols-3 gap-x-6 gap-y-2 mt-3 px-2">
                                        <p class="text-gray-500">N° de Orden:</p>
                                        <p class="font-semibold col-span-2">{{ selectedProduction.folio }}</p>
                                        <p class="text-gray-500">Fecha de emisión:</p>
                                        <p class="col-span-2">{{ formatDate(selectedProduction.start_date) }}</p>
                                        <p class="text-gray-500">Fecha estimada:</p>
                                        <p class="col-span-2">{{ formatDate(selectedProduction.estimated_date) }}</p>
                                        <p class="text-gray-500">Cliente:</p>
                                        <p class="col-span-2">{{ selectedProduction.client }}</p>
                                    </div>
                                </section>

                                <!-- Información del producto -->
                                <section>
                                    <h2 class="bg-gray-100 font-bold text-gray-700 py-2 px-3 rounded-md">Información del
                                        producto</h2>
                                    <div class="grid grid-cols-3 gap-x-6 gap-y-2 mt-3 px-2">
                                        <p class="text-gray-500">Código:</p>
                                        <p class="col-span-2">{{ selectedProduction.product.code }}</p>
                                        <p class="text-gray-500">Nombre:</p>
                                        <p class="col-span-2">{{ selectedProduction.product.name }}</p>
                                        <p class="text-gray-500">Temporada:</p>
                                        <p class="col-span-2">{{ selectedProduction.product.season }}</p>
                                        <p class="text-gray-500">Descripción:</p>
                                        <p class="col-span-2">{{ selectedProduction.product.description ?? '-' }}</p>
                                        <p class="text-gray-500">Lista de material:</p>
                                        <p class="col-span-2">{{ selectedProduction.materials?.join(', ') }}</p>
                                        <p class="text-gray-500">Estación actual:</p>
                                        <div class="col-span-2 flex items-center space-x-2">
                                            <div class="rounded-full size-6 flex items-center justify-center"
                                                :style="{ backgroundColor: stations.find(s => s.name === selectedProduction.station)?.light, color: stations.find(s => s.name === selectedProduction.station)?.dark }">
                                                <component
                                                    :is="stations.find(s => s.name === selectedProduction.station)?.icon"
                                                    class="size-4" />
                                            </div>
                                            <p>{{ selectedProduction.station }}</p>
                                        </div>
                                        <p class="text-gray-500">Máquina actual:</p>
                                        <p class="col-span-2">{{ selectedProduction.machine?.name ?? 'N/A' }}</p>
                                    </div>
                                </section>

                                <!-- Materiales y medidas -->
                                <section>
                                    <h2 class="bg-gray-100 font-bold text-gray-700 py-2 px-3 rounded-md">Materiales y
                                        medidas</h2>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-1 mt-3 px-2">
                                        <!-- Columna Izquierda -->
                                        <div class="grid grid-cols-2 *:border-b *:py-2">
                                            <p class="text-gray-500">Material:</p>
                                            <p>{{ selectedProduction.material ?? '-' }}</p>
                                            <p class="text-gray-500">Con Barníz:</p>
                                            <p>
                                                <i v-if="selectedProduction.varnish_type"
                                                    class="fa-solid fa-check text-green-500"></i>
                                                <i v-else class="fa-solid fa-xmark text-red-500"></i>
                                            </p>
                                            <p class="text-gray-500">Tipo de barníz:</p>
                                            <p>{{ selectedProduction.varnish_type ?? '-' }}</p>
                                            <p class="text-gray-500">Dimensiones hoja:</p>
                                            <p>{{ selectedProduction.width ?? '-' }} x {{ selectedProduction.large ??
                                                '-' }}</p>
                                            <p class="text-gray-500">Dimensiones impresión:</p>
                                            <p>{{ selectedProduction.dfi ?? '-' }}</p>
                                            <p class="text-gray-500">Caras:</p>
                                            <p>{{ selectedProduction.faces }}</p>
                                            <p class="text-gray-500">Pz/H:</p>
                                            <p>{{ selectedProduction.pps }}</p>
                                            <p class="text-gray-500">Calibre:</p>
                                            <p>{{ selectedProduction.gauge ?? '-' }}</p>
                                        </div>
                                        <!-- Columna Derecha -->
                                        <div class="grid grid-cols-2 self-start *:border-b *:py-2">
                                            <p class="text-gray-500">Hojas:</p>
                                            <p>{{ selectedProduction.sheets }}</p>
                                            <p class="text-gray-500">Ajuste:</p>
                                            <p>{{ selectedProduction.adjust }}</p>
                                            <p class="text-gray-500">H/A:</p>
                                            <p>{{ selectedProduction.ha }}</p>
                                            <p class="text-gray-500">P/F:</p>
                                            <p>{{ selectedProduction.pf }}</p>
                                            <p class="text-gray-500">Total de hojas:</p>
                                            <p>{{ selectedProduction.ts }}</p>
                                            <p class="text-gray-500">Ta/Im:</p>
                                            <p>{{ selectedProduction.ps }}</p>
                                            <p class="text-gray-500">Total Ta/Im:</p>
                                            <p>{{ selectedProduction.tps }}</p>
                                        </div>
                                    </div>
                                </section>
                            </div>
                        </el-tab-pane>
                        <el-tab-pane label="Producción" name="production">
                            <div class="p-1 mt-2 space-y-6">
                                <!-- Resumen general de tiempos -->
                                <section>
                                    <h2 class="bg-gray-100 font-bold text-gray-700 py-2 px-3 rounded-md">Resumen general
                                        de la orden</h2>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3 text-center">
                                        <div class="bg-green-100 border border-green-200 rounded-lg p-3">
                                            <p class="text-sm text-green-800">Tiempo efectivo total</p>
                                            <p class="font-bold text-xl text-green-900 mt-1">{{
                                                formatDuration(totalEffectiveTime) }}</p>
                                        </div>
                                        <div class="bg-orange-100 border border-orange-200 rounded-lg p-3">
                                            <p class="text-sm text-orange-800">Tiempo en pausa total</p>
                                            <p class="font-bold text-xl text-orange-900 mt-1">{{
                                                formatDuration(totalPausedTime) }}</p>
                                        </div>
                                        <div class="bg-gray-200 border border-gray-300 rounded-lg p-3">
                                            <p class="text-sm text-gray-800">Tiempo en espera total</p>
                                            <p class="font-bold text-xl text-gray-900 mt-1">{{
                                                formatDuration(totalWaitingTime) }}</p>
                                        </div>
                                    </div>
                                </section>

                                <!-- Control de estación -->
                                <section>
                                    <h2 class="bg-gray-100 font-bold text-gray-700 py-2 px-3 rounded-md">Control de
                                        estación</h2>
                                    <div v-if="currentStationRecord" class="p-4 border rounded-b-md">
                                        <!-- Estado: En espera -->
                                        <div v-if="currentStationStatus === 'En espera'">
                                            <div class="flex justify-between items-center bg-gray-100 p-3 rounded-lg">
                                                <p class="text-sm">Tiempo en esta estación</p>
                                                <p class="font-bold text-lg">{{ formatDuration(liveWaitingTime) }}</p>
                                            </div>
                                            <p class="text-center text-sm text-gray-600 my-4">Puedes iniciar la tarea en
                                                esta estación o mover la orden a la siguiente estación.</p>
                                            <div class="flex justify-center items-center space-x-3">
                                                <button @click="$emit('start-process')"
                                                    class="bg-green-500 text-white font-bold py-2 px-6 rounded-md hover:bg-green-600 transition-all flex items-center">
                                                    <PlayIcon class="size-5 mr-2" />
                                                    Iniciar
                                                </button>
                                                <el-select @change="val => $emit('skip-move-process', val)"
                                                    placeholder="Mover a la siguiente estación" class="!w-64">
                                                    <template #prefix>
                                                        <ArrowRightIcon class="size-4" />
                                                    </template>
                                                    <el-option v-for="station in filteredStations" :key="station.name"
                                                        :label="station.name" :value="station.name" />
                                                </el-select>
                                            </div>
                                        </div>

                                        <!-- Estado: En proceso -->
                                        <div v-else-if="currentStationStatus === 'En proceso'">
                                            <div class="flex justify-between items-center">
                                                <div class="flex items-center space-x-2">
                                                    <div class="rounded-full size-6 flex items-center justify-center"
                                                        :style="{ backgroundColor: stations.find(s => s.name === currentStationRecord.station_name)?.light, color: stations.find(s => s.name === currentStationRecord.station_name)?.dark }">
                                                        <component
                                                            :is="stations.find(s => s.name === currentStationRecord.station_name)?.icon"
                                                            class="size-4" />
                                                    </div>
                                                    <p class="font-bold">{{ currentStationRecord.station_name }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-xs text-right">Tiempo efectivo</p>
                                                    <p class="font-bold text-lg text-right">{{
                                                        formatDuration(liveEffectiveTime) }}</p>
                                                </div>
                                            </div>
                                            <div class="flex justify-center items-center space-x-3 mt-4">
                                                <button @click="showPauseModal = true"
                                                    class="bg-orange-500 text-white font-bold py-2 px-6 rounded-md hover:bg-orange-600 transition-all flex items-center">
                                                    <PauseIcon class="size-5 mr-2" />
                                                    Pausar
                                                </button>
                                                <el-select @change="val => $emit('finish-move-process', val)"
                                                    placeholder="Finalizar y mover" class="!w-64">
                                                    <template #prefix>
                                                        <CheckCircleIcon class="size-4" />
                                                    </template>
                                                    <el-option v-for="station in filteredStations" :key="station.name"
                                                        :label="station.name" :value="station.name" />
                                                </el-select>
                                            </div>
                                        </div>

                                        <!-- Estado: En pausa -->
                                        <div v-else-if="currentStationStatus === 'En pausa'">
                                            <div class="flex justify-between items-center">
                                                <div class="flex items-center space-x-2">
                                                    <div class="rounded-full size-6 flex items-center justify-center"
                                                        :style="{ backgroundColor: stations.find(s => s.name === currentStationRecord.station_name)?.light, color: stations.find(s => s.name === currentStationRecord.station_name)?.dark }">
                                                        <component
                                                            :is="stations.find(s => s.name === currentStationRecord.station_name)?.icon"
                                                            class="size-4" />
                                                    </div>
                                                    <p class="font-bold">{{ currentStationRecord.station_name }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-xs text-right">Tiempo en pausa</p>
                                                    <p class="font-bold text-lg text-right">{{
                                                        formatDuration(livePausedTime) }}</p>
                                                </div>
                                            </div>
                                            <p class="text-sm text-gray-700 mt-2">Motivo: <span
                                                    class="font-semibold italic">"{{ lastPauseReason }}"</span></p>
                                            <div class="flex justify-center items-center space-x-3 mt-4">
                                                <button @click="$emit('resume-process')"
                                                    class="bg-green-500 text-white font-bold py-2 px-6 rounded-md hover:bg-green-600 transition-all flex items-center">
                                                    <PlayIcon class="size-5 mr-2" />
                                                    Reanudar
                                                </button>
                                                <el-select @change="val => $emit('finish-move-process', val)"
                                                    placeholder="Finalizar y mover" class="!w-64">
                                                    <template #prefix>
                                                        <CheckCircleIcon class="size-4" />
                                                    </template>
                                                    <el-option v-for="station in filteredStations" :key="station.name"
                                                        :label="station.name" :value="station.name" />
                                                </el-select>
                                            </div>
                                        </div>
                                    </div>
                                </section>

                                <!-- Resumen de tiempos por estación -->
                                <section>
                                    <h2 class="bg-gray-100 font-bold text-gray-700 py-2 px-3 rounded-md">Resumen de
                                        tiempos por estación</h2>
                                    <StationTimeHistory :stationTimes="selectedProduction.station_times"
                                        :users="users" />
                                </section>
                            </div>
                        </el-tab-pane>
                        <el-tab-pane label="Entregas" name="deliveries">
                            <div class="p-1 mt-2 space-y-6">
                                <!-- Inspección -->
                                <section
                                    v-if="selectedProduction.station == 'Inspección' || selectedProduction.production_close_type">
                                    <div class="flex items-center justify-between bg-gray-100 py-2 px-3 rounded-md">
                                        <h2 class="font-bold text-gray-700">Entregas de Inspección</h2>
                                        <PrimaryButton v-if="!selectedProduction.production_close_type"
                                            @click="$emit('open-inspection-release')">
                                            Registrar entrega
                                        </PrimaryButton>
                                        <PrimaryButton
                                            v-else-if="selectedProduction.production_close_type == 'Parcialidades' && selectedProduction.station != 'Terminadas'"
                                            @click="$emit('open-add-partial')">
                                            Registrar parcialidad
                                        </PrimaryButton>
                                    </div>
                                    <div v-if="!selectedProduction.partials?.length && !selectedProduction.production_close_type"
                                        class="text-center text-gray-500 py-8">
                                        <p>No hay entregas registradas desde inspección.</p>
                                    </div>
                                    <!-- Entregas parciales -->
                                    <div v-if="selectedProduction.partials?.length">
                                        <section v-for="(partial, index) in selectedProduction.partials" :key="index"
                                            class="mt-3 px-2 pb-2"
                                            :class="{ 'border-b': index < selectedProduction.partials.length - 1 }">
                                            <div class="font-semibold text-gray-600 flex justify-between items-center">
                                                <span>Parcialidad {{ index + 1 }}</span>
                                                <span v-if="partial.is_last_delivery"
                                                    class="text-xs font-normal text-gray-500">(Última
                                                    entrega)</span>
                                            </div>
                                            <div class="grid grid-cols-3 gap-x-4 gap-y-2 mt-2">
                                                <p class="text-gray-500">Fecha:</p>
                                                <p class="col-span-2">{{ formatDateTime(partial.date) }}</p>
                                                <p class="text-gray-500">Cantidad:</p>
                                                <p class="col-span-2">{{ partial.quantity?.toLocaleString('es-MX') }}
                                                </p>
                                                <p class="text-gray-500">Notas:</p>
                                                <p class="col-span-2">{{ partial.notes ?? '-' }}</p>
                                            </div>
                                        </section>
                                        <div
                                            class="grid grid-cols-3 gap-x-4 gap-y-2 pt-2 mt-2 px-2 border-t font-semibold">
                                            <p class="text-gray-500">Total entregado:</p>
                                            <p class="col-span-2">{{
                                                selectedProduction.current_quantity?.toLocaleString('es-MX') }}</p>
                                            <p class="text-gray-500">Restante:</p>
                                            <p class="col-span-2">{{ (selectedProduction.quantity -
                                                selectedProduction.current_quantity)?.toLocaleString('es-MX') }}</p>
                                        </div>
                                    </div>
                                </section>

                                <!-- Empaques -->
                                <section
                                    v-if="selectedProduction.station == 'Empaques' || selectedProduction.packing_close_type">
                                    <div class="flex items-center justify-between bg-gray-100 py-2 px-3 rounded-md">
                                        <h2 class="font-bold text-gray-700">Entregas de Empaques</h2>
                                        <PrimaryButton v-if="!selectedProduction.packing_close_type"
                                            @click="$emit('open-packing-release')">
                                            Registrar entrega
                                        </PrimaryButton>
                                        <PrimaryButton
                                            v-else-if="selectedProduction.packing_close_type == 'Parcialidades' && selectedProduction.station != 'Empaques terminado' && selectedProduction.station != 'Terminadas'"
                                            @click="$emit('open-add-packing-partial')">
                                            Registrar parcialidad
                                        </PrimaryButton>
                                    </div>
                                    <div v-if="!selectedProduction.packing_partials?.length && !selectedProduction.packing_close_type"
                                        class="text-center text-gray-500 py-8">
                                        <p>No hay entregas registradas desde empaques.</p>
                                    </div>
                                    <div v-if="selectedProduction.packing_partials?.length">
                                        <section v-for="(partial, index) in selectedProduction.packing_partials"
                                            :key="index" class="mt-3 px-2 pb-2"
                                            :class="{ 'border-b': index < selectedProduction.packing_partials.length - 1 }">
                                            <div class="font-semibold text-gray-600 flex justify-between items-center">
                                                <span>Parcialidad de empaque {{ index + 1 }}</span>
                                                <span v-if="partial.is_last_delivery"
                                                    class="text-xs text-gray-500">(Última entrega)</span>
                                            </div>
                                            <div class="grid grid-cols-3 gap-x-4 gap-y-2 mt-2">
                                                <p class="text-gray-500">Fecha:</p>
                                                <p class="col-span-2">{{ formatDateTime(partial.date) }}</p>

                                                <p class="text-gray-500">Cantidad:</p>
                                                <p class="col-span-2">{{ partial.quantity?.toLocaleString('es-MX') }}
                                                </p>

                                                <p class="text-gray-500">Notas:</p>
                                                <p class="col-span-2">{{ partial.notes ?? '-' }}</p>
                                            </div>
                                        </section>
                                    </div>
                                </section>
                            </div>
                        </el-tab-pane>
                    </el-tabs>
                </div>
            </div>
        </template>
    </DialogModal>
</template>

<script>
import DialogModal from '@/Components/DialogModal.vue';
import Loading from '@/Components/MyComponents/Loading.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import CancelButton from '@/Components/MyComponents/CancelButton.vue';
import StationTimeHistory from './StationTimeHistory.vue';
import { format, parseISO, differenceInSeconds } from 'date-fns';
import es from 'date-fns/locale/es';
import { CheckCircleIcon, PlayIcon, PauseIcon, ArrowRightIcon } from '@heroicons/vue/24/solid';

export default {
    name: 'ProductionDetailsModal',
    components: {
       DialogModal,
        Loading,
        PrimaryButton,
        CancelButton,
        CheckCircleIcon,
        PlayIcon,
        PauseIcon,
        ArrowRightIcon,
        StationTimeHistory,
    },
    props: {
        show: Boolean,
        selectedProduction: Object,
        updatingDetails: Boolean,
        machines: Array,
        stations: Array,
        users: Object,
    },
    emits: [
        'close',
        'update-machine',
        'station-change-intent',
        'open-inspection-release',
        'open-add-partial',
        'open-packing-release',
        'open-add-packing-partial',
        'start-process',
        'pause-process',
        'resume-process',
        'finish-move-process',
        'skip-move-process',
    ],
    data() {
        return {
            tempStation: null,
            activeTab: 'details',
            liveTimeTrigger: null,
            now: new Date(),
            showPauseModal: false,
            pauseReason: '',
        };
    },
    watch: {
        selectedProduction(newVal) {
            if (newVal) {
                this.tempStation = newVal.station;
                this.activeTab = 'details';
            }
        },
        show(newVal) {
            if (newVal) {
                this.liveTimeTrigger = setInterval(() => {
                    this.now = new Date();
                }, 1000);
            } else {
                clearInterval(this.liveTimeTrigger);
            }
        }
    },
    computed: {
        currentStationRecord() {
            if (!this.selectedProduction?.station_times?.length) return null;
            return this.selectedProduction.station_times[this.selectedProduction.station_times.length - 1];
        },
        currentStationStatus() {
            return this.currentStationRecord?.status;
        },
        lastPauseReason() {
            if (this.currentStationStatus !== 'En pausa' || !this.currentStationRecord.pauses.length) return '';
            return this.currentStationRecord.pauses[this.currentStationRecord.pauses.length - 1].reason;
        },
        // --- Total time calculations ---
        totalEffectiveTime() {
            if (!this.selectedProduction?.station_times) return 0;
            const pastTime = this.selectedProduction.station_times.reduce((acc, station) => acc + (station.times?.effective_seconds ?? 0), 0);
            return pastTime + (this.currentStationStatus === 'En proceso' ? this.liveEffectiveTime : 0);
        },
        totalPausedTime() {
             if (!this.selectedProduction?.station_times) return 0;
            const pastTime = this.selectedProduction.station_times.reduce((acc, station) => acc + (station.times?.paused_seconds ?? 0), 0);
            return pastTime + (this.currentStationStatus === 'En pausa' ? this.livePausedTime : this.accumulatedPausedTimeInCurrentStation);
        },
        totalWaitingTime() {
            if (!this.selectedProduction?.station_times) return 0;
            const pastTime = this.selectedProduction.station_times.reduce((acc, station) => acc + (station.times?.waiting_seconds ?? 0), 0);
            return pastTime + (this.currentStationStatus === 'En espera' ? this.liveWaitingTime : 0);
        },
        // --- Live time calculations for current station ---
        liveWaitingTime() {
            if (this.currentStationStatus !== 'En espera' || !this.currentStationRecord.entered_at) return 0;
            return differenceInSeconds(this.now, parseISO(this.currentStationRecord.entered_at));
        },
        liveEffectiveTime() {
            if (this.currentStationStatus !== 'En proceso' || !this.currentStationRecord.started_at) return 0;
            const startedAt = parseISO(this.currentStationRecord.started_at);
            return differenceInSeconds(this.now, startedAt) - this.accumulatedPausedTimeInCurrentStation;
        },
        accumulatedPausedTimeInCurrentStation() {
             if (!this.currentStationRecord?.pauses?.length) return 0;
             return this.currentStationRecord.pauses.reduce((acc, pause) => {
                if (pause.resumed_at) {
                    return acc + differenceInSeconds(parseISO(pause.resumed_at), parseISO(pause.paused_at));
                }
                return acc;
            }, 0);
        },
        livePausedTime() {
            if (this.currentStationStatus !== 'En pausa' || !this.currentStationRecord.pauses.length) return 0;
            const lastPause = this.currentStationRecord.pauses[this.currentStationRecord.pauses.length - 1];
            const currentPauseDuration = differenceInSeconds(this.now, parseISO(lastPause.paused_at));
            return this.accumulatedPausedTimeInCurrentStation + currentPauseDuration;
        }
    },
    methods: {
         submitPause() {
            this.$emit('pause-process', this.pauseReason);
            this.showPauseModal = false;
            this.pauseReason = '';
        },
        formatDuration(totalSeconds) {
             if (totalSeconds === null || isNaN(totalSeconds) || totalSeconds < 0) return '0d 00:00:00';
            
            const days = Math.floor(totalSeconds / 86400);
            totalSeconds %= 86400;
            const hours = Math.floor(totalSeconds / 3600);
            totalSeconds %= 3600;
            const minutes = Math.floor(totalSeconds / 60);
            const seconds = Math.floor(totalSeconds % 60);

            let result = '';
            if (days > 0) result += `${days}d `;
            result += `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            return result;
        },
        onStationChange() {
            this.$emit('station-change-intent', { newStation: this.tempStation, oldStation: this.selectedProduction.station });
        },
        formatDate(dateString) {
            if (!dateString) return '-';
            return format(parseISO(dateString), 'dd MMMM yyyy', { locale: es });
        },
        formatDateTime(dateString) {
            if (!dateString) return '-';
            return format(parseISO(dateString), 'dd MMMM yyyy, hh:mm a', { locale: es });
        },
        revertStation() {
            if (this.selectedProduction) {
                this.tempStation = this.selectedProduction.station;
            }
        }
    },
    beforeUnmount() {
        clearInterval(this.liveTimeTrigger);
    },
};
</script>