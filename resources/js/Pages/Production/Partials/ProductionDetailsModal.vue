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
                            <!-- Status Chip -->
                            <span class="flex items-center text-xs font-semibold px-2.5 py-1 rounded-full"
                                :class="[statusInfo.bgColor, statusInfo.textColor]">
                                <component :is="statusInfo.icon" class="size-4 mr-1.5" />
                                {{ statusInfo.text }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-600 mt-1">Producto: {{ selectedProduction.product.name }}</p>
                        <p class="text-sm text-gray-600">Cantidad solicitada: <span class="font-semibold">{{
                            selectedProduction.quantity?.toLocaleString('es-MX') }}</span></p>
                    </div>
                    <div class="flex items-center space-x-2 flex-shrink-0">
                        <a :href="route('productions.hoja-viajera', selectedProduction.id)" target="_blank"
                            class="text-sm text-[#EC4899] border border-[#EC4899] rounded-md px-3 py-1.5 hover:bg-[#FCE7F3] transition-all">
                            Hoja viajera
                        </a>
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
                                <!-- Order Information -->
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

                                <!-- Product Information -->
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

                                <!-- Materials and Measures -->
                                <section>
                                    <h2 class="bg-gray-100 font-bold text-gray-700 py-2 px-3 rounded-md">Materiales y
                                        medidas</h2>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-1 mt-3 px-2">
                                        <!-- Left Column -->
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
                                        <!-- Right Column -->
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
                                <!-- General Time Summary -->
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

                                <!-- Station Control -->
                                <section>
                                    <h2 class="font-bold px-3 pb-1">Control de estación</h2>
                                    <div v-if="currentStationRecord && currentStationRecord.status !== 'Finalizada'"
                                        class="p-4 border rounded-lg bg-[#F2F2F2]">
                                        <!-- Waiting State -->
                                        <div v-if="currentStationStatus === 'En espera'">
                                            <div class="flex justify-between items-center bg-white px-3 rounded-lg">
                                                <div class="flex items-center space-x-2">
                                                    <div class="rounded-full size-6 flex items-center justify-center"
                                                        :style="{ backgroundColor: stations.find(s => s.name === currentStationRecord.station_name)?.light, color: stations.find(s => s.name === currentStationRecord.station_name)?.dark }">
                                                        <component
                                                            :is="stations.find(s => s.name === currentStationRecord.station_name)?.icon"
                                                            class="size-4" />
                                                    </div>
                                                    <p class="font-bold">{{ currentStationRecord.station_name }}</p>
                                                </div>
                                                <div class="flex justify-between items-center p-3 rounded-lg space-x-2">
                                                    <p class="text-xs text-right">Tiempo en espera</p>
                                                    <p class="font-bold text-lg text-right">{{
                                                        formatDuration(liveWaitingTime) }}</p>
                                                </div>
                                            </div>
                                            <div class="flex justify-center items-center space-x-3 mt-3">
                                                <PrimaryButton @click="$emit('start-process')"
                                                    class="!bg-[#1FAE07] hover:bg-green-600 flex items-center !rounded-lg">
                                                    <PlayIcon class="size-5 mr-2" />
                                                    Iniciar
                                                </PrimaryButton>
                                                <PrimaryButton @click="openMoveModal('skip')" class="!bg-[#D9D9D9] text-[#373737] flex items-center !rounded-lg">
                                                    Mover a siguiente
                                                    <ArrowRightIcon class="size-5 ml-2" />
                                                </PrimaryButton>
                                            </div>
                                        </div>

                                        <!-- In Process State -->
                                        <div v-else-if="currentStationStatus === 'En proceso'">
                                            <div class="flex justify-between items-center bg-white px-3 rounded-lg">
                                                <div class="flex items-center space-x-2">
                                                    <div class="rounded-full size-6 flex items-center justify-center"
                                                        :style="{ backgroundColor: stations.find(s => s.name === currentStationRecord.station_name)?.light, color: stations.find(s => s.name === currentStationRecord.station_name)?.dark }">
                                                        <component
                                                            :is="stations.find(s => s.name === currentStationRecord.station_name)?.icon"
                                                            class="size-4" />
                                                    </div>
                                                    <p class="font-bold">{{ currentStationRecord.station_name }}</p>
                                                </div>
                                                <div class="flex justify-between items-center p-3 rounded-lg space-x-2">
                                                    <p class="text-xs text-right">Tiempo efectivo</p>
                                                    <p class="font-bold text-lg text-right">{{
                                                        formatDuration(liveEffectiveTime) }}</p>
                                                </div>
                                            </div>
                                            <div class="flex justify-center items-center space-x-3 mt-4">
                                                <PrimaryButton @click="showPauseModal = true"
                                                    class="!bg-[#FDA10F] flex items-center !rounded-lg">
                                                    <PauseIcon class="size-5 mr-2" />
                                                    Pausar
                                                </PrimaryButton>
                                                <PrimaryButton @click="openMoveModal('finish')" class="!bg-[#008CF0] flex items-center !rounded-lg">
                                                    <CheckCircleIcon class="size-5 mr-2" />
                                                    Finalizar y mover
                                                </PrimaryButton>
                                            </div>
                                        </div>

                                        <!-- Paused State -->
                                        <div v-else-if="currentStationStatus === 'En pausa'">
                                            <div class="flex justify-between items-center bg-white px-3 rounded-lg">
                                                <div class="flex items-center space-x-2">
                                                    <div class="rounded-full size-6 flex items-center justify-center"
                                                        :style="{ backgroundColor: stations.find(s => s.name === currentStationRecord.station_name)?.light, color: stations.find(s => s.name === currentStationRecord.station_name)?.dark }">
                                                        <component
                                                            :is="stations.find(s => s.name === currentStationRecord.station_name)?.icon"
                                                            class="size-4" />
                                                    </div>
                                                    <p class="font-bold">{{ currentStationRecord.station_name }}</p>
                                                </div>
                                                <div class="flex justify-between items-center p-3 space-x-2 rounded-lg">
                                                    <p class="text-xs text-right">Tiempo en pausa</p>
                                                    <p class="font-bold text-lg text-right">{{
                                                        formatDuration(livePausedTime) }}</p>
                                                </div>
                                            </div>
                                            <p class="text-sm text-gray-700 text-center mt-2">Motivo: <span
                                                    class="font-semibold italic">"{{ lastPauseReason }}"</span></p>
                                            <div class="flex justify-center items-center space-x-3 mt-4">
                                                <PrimaryButton @click="$emit('resume-process')"
                                                    class="!bg-[#1FAE07] hover:bg-green-600 flex items-center !rounded-lg">
                                                    <PlayIcon class="size-5 mr-2" />
                                                    Reanudar
                                                </PrimaryButton>
                                                <PrimaryButton @click="openMoveModal('finish')" class="!bg-[#008CF0] flex items-center !rounded-lg">
                                                    <CheckCircleIcon class="size-5 mr-2" />
                                                    Finalizar y mover
                                                </PrimaryButton>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else class="text-center text-gray-500 py-8 border rounded-b-md">
                                        <p v-if="currentStationRecord?.status === 'Finalizada'">La estación actual ya ha
                                            sido finalizada.</p>
                                        <p v-else>No hay un registro de tiempo para la estación actual.</p>
                                    </div>
                                </section>

                                <!-- Station Time Summary -->
                                <section>
                                    <h2 class="bg-gray-100 font-bold text-gray-700 py-2 px-3 rounded-md">Resumen de
                                        tiempos por estación</h2>
                                    <StationTimeHistory :stationTimes="selectedProduction.station_times"
                                        :users="users" />
                                </section>
                            </div>
                        </el-tab-pane>
                        <el-tab-pane label="Entregas" name="deliveries">
                            <div class="p-1 mt-2 space-y-4">
                                <h2 class="font-bold text-gray-800">Registro de entregas</h2>

                                <!-- Empty State -->
                                <div v-if="!hasAnyDeliveryInfo"
                                    class="text-center text-gray-500 py-12 bg-gray-50 rounded-lg">
                                    <div class="flex justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-12 text-gray-300">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                        </svg>
                                    </div>
                                    <p class="font-semibold mt-3">Aún no hay entregas registradas</p>
                                    <p class="text-xs mt-1">Aquí aparecerá la lista de entregas de producción, calidad,
                                        inspección y empaques.</p>
                                </div>

                                <!-- Content -->
                                <div v-else class="space-y-4">
                                    <!-- Production Release -->
                                    <section v-if="selectedProduction.close_production_date"
                                        class="bg-gray-50 p-4 rounded-lg space-y-2">
                                        <h3 class="font-semibold text-gray-700">Liberado por producción</h3>
                                        <div class="grid grid-cols-2 gap-x-4 text-xs">
                                            <span>Fecha de liberación:</span> <span class="font-medium">{{
                                                formatDateTime(selectedProduction.close_production_date) }}</span>
                                            <span>Cantidad entregada:</span> <span class="font-medium">{{
                                                selectedProduction.close_quantity?.toLocaleString('es-MX') }}</span>
                                            <span>Notas:</span> <span class="font-medium">{{
                                                selectedProduction.close_production_notes ?? '-' }}</span>
                                        </div>
                                    </section>

                                    <!-- Quality Release -->
                                    <section v-if="selectedProduction.quality_released_date"
                                        class="bg-gray-50 p-4 rounded-lg space-y-2">
                                        <h3 class="font-semibold text-gray-700">Liberado por calidad</h3>
                                        <div class="grid grid-cols-2 gap-x-4 text-xs">
                                            <span>Fecha de liberación:</span> <span class="font-medium">{{
                                                formatDateTime(selectedProduction.quality_released_date) }}</span>
                                            <span>Cantidad entregada:</span> <span class="font-medium">{{
                                                selectedProduction.quality_quantity?.toLocaleString('es-MX') }}</span>
                                            <span>Merma:</span> <span class="font-medium">{{
                                                selectedProduction.quality_scrap?.toLocaleString('es-MX') }} ({{
                                                    calculatePercentage(selectedProduction.quality_scrap,
                                                selectedProduction.close_quantity) }}%)</span>
                                            <span>Diferencia:</span> <span class="font-medium">{{
                                                selectedProduction.quality_shortage?.toLocaleString('es-MX') }}</span>
                                            <span>Notas:</span> <span class="font-medium">{{
                                                selectedProduction.quality_notes ?? '-' }}</span>
                                        </div>
                                    </section>

                                    <!-- Inspection -->
                                    <section v-if="shouldShowInspectionSection"
                                        class="bg-gray-50 p-4 rounded-lg space-y-2">
                                        <div class="flex justify-between items-center">
                                            <h3 class="font-semibold text-gray-700">Inspección</h3>
                                            <PrimaryButton @click="openDeliveryModal('inspection', false)"
                                                v-if="selectedProduction.station === 'Inspección' && !selectedProduction.production_close_type">
                                                Registrar entrega
                                            </PrimaryButton>
                                            <PrimaryButton @click="openDeliveryModal('inspection', true)"
                                                v-if="selectedProduction.station === 'Inspección' && selectedProduction.production_close_type === 'Parcialidades'">
                                                Registrar parcialidad
                                            </PrimaryButton>
                                        </div>
                                        <div class="grid grid-cols-2 gap-x-4 text-xs">
                                            <span>Tipo de entrega:</span> <span class="font-medium">{{
                                                selectedProduction.production_close_type ?? 'Pendiente' }}</span>
                                        </div>
                                        <div v-if="selectedProduction.production_close_type === 'Única'"
                                            class="grid grid-cols-2 gap-x-4 text-xs border-t pt-2 mt-2">
                                            <span>Cantidad entregada:</span> <span class="font-medium">{{
                                                selectedProduction.current_quantity?.toLocaleString('es-MX') }}</span>
                                            <span>Merma:</span> <span class="font-medium">{{
                                                selectedProduction.inspection_scrap?.toLocaleString('es-MX') }} ({{
                                                    calculatePercentage(selectedProduction.inspection_scrap,
                                                selectedProduction.quality_quantity) }}%)</span>
                                            <span>Diferencia:</span> <span class="font-medium">{{
                                                selectedProduction.inspection_shortage?.toLocaleString('es-MX')
                                                }}</span>
                                            <span>Notas:</span> <span class="font-medium">{{
                                                selectedProduction.inspection_notes ?? '-' }}</span>
                                        </div>
                                        <div v-if="selectedProduction.production_close_type === 'Parcialidades'"
                                            class="text-xs border-t pt-2 mt-2 space-y-3">
                                            <div v-for="(partial, index) in selectedProduction.partials" :key="index"
                                                class="pb-2"
                                                :class="{ 'border-b': index !== selectedProduction.partials.length - 1 }">
                                                <p class="font-semibold mb-1">Parcialidad {{ index + 1 }} <span
                                                        v-if="partial.is_last_delivery"
                                                        class="font-normal text-gray-500">(Última entrega)</span></p>
                                                <div class="grid grid-cols-2 gap-x-4">
                                                    <span>Fecha:</span> <span class="font-medium">{{
                                                        formatDateTime(partial.date) }}</span>
                                                    <span>Cantidad:</span> <span class="font-medium">{{
                                                        partial.quantity?.toLocaleString('es-MX') }}</span>
                                                    <span>Notas:</span> <span class="font-medium">{{ partial.notes ??
                                                        '-' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </section>

                                    <!-- Packing -->
                                    <section v-if="shouldShowPackingSection"
                                        class="bg-gray-50 p-4 rounded-lg space-y-2">
                                        <div class="flex justify-between items-center">
                                            <h3 class="font-semibold text-gray-700">Empaques</h3>
                                            <PrimaryButton @click="openDeliveryModal('packing', false)"
                                                v-if="selectedProduction.station === 'Empaques' && !selectedProduction.packing_close_type">
                                                Registrar entrega
                                            </PrimaryButton>
                                            <PrimaryButton @click="openDeliveryModal('packing', true)"
                                                v-if="selectedProduction.station === 'Empaques' && selectedProduction.packing_close_type === 'Parcialidades'">
                                                Registrar parcialidad
                                            </PrimaryButton>
                                        </div>
                                        <div class="grid grid-cols-2 gap-x-4 text-xs">
                                            <span>Cantidad recibida:</span> <span class="font-medium">{{
                                                selectedProduction.packing_received_quantity?.toLocaleString('es-MX')
                                                }}</span>
                                            <span>Fecha de recibido:</span> <span class="font-medium">{{
                                                formatDateTime(selectedProduction.packing_received_date) }}</span>
                                            <span>Tipo de entrega:</span> <span class="font-medium">{{
                                                selectedProduction.packing_close_type ?? 'Pendiente' }}</span>
                                        </div>
                                        <div v-if="selectedProduction.packing_close_type === 'Única'"
                                            class="grid grid-cols-2 gap-x-4 text-xs border-t pt-2 mt-2">
                                            <span>Cantidad entregada:</span> <span class="font-medium">{{
                                                selectedProduction.current_quantity?.toLocaleString('es-MX') }}</span>
                                            <span>Fecha de entrega:</span> <span class="font-medium">{{
                                                formatDateTime(selectedProduction.packing_finished_date) }}</span>
                                            <span>Notas:</span> <span class="font-medium">{{
                                                selectedProduction.packing_notes ?? '-' }}</span>
                                        </div>
                                        <div v-if="selectedProduction.packing_close_type === 'Parcialidades'"
                                            class="text-xs border-t pt-2 mt-2 space-y-3">
                                            <div v-for="(partial, index) in selectedProduction.packing_partials"
                                                :key="index" class="pb-2"
                                                :class="{ 'border-b': index !== selectedProduction.packing_partials.length - 1 }">
                                                <p class="font-semibold mb-1">Parcialidad {{ index + 1 }} <span
                                                        v-if="partial.is_last_delivery"
                                                        class="font-normal text-gray-500">(Última entrega)</span></p>
                                                <div class="grid grid-cols-2 gap-x-4">
                                                    <span>Fecha:</span> <span class="font-medium">{{
                                                        formatDateTime(partial.date) }}</span>
                                                    <span>Cantidad:</span> <span class="font-medium">{{
                                                        partial.quantity?.toLocaleString('es-MX') }}</span>
                                                    <span>Notas:</span> <span class="font-medium">{{ partial.notes ??
                                                        '-' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </section>

                                </div>
                            </div>
                        </el-tab-pane>
                    </el-tabs>
                </div>
            </div>
        </template>
    </DialogModal>

    <!-- Other Modals -->
    <PauseStationModal :show="showPauseModal" @close="showPauseModal = false" @submit="handlePauseSubmit" />
    <MoveStationModal :show="showMoveModal" :processType="moveModalMode" :available-stations="availableNextStations" :machines="machines"
        :production="selectedProduction" :default-quantity="moveDefaultQty" @close="showMoveModal = false"
        @submit="handleMoveSubmit" />
    <RegisterDeliveryModal :show="showDeliveryModal" :production="selectedProduction" :context="deliveryContext"
        :is-partial="isDeliveryPartial" :default-quantity="deliveryDefaultQty" @close="showDeliveryModal = false"
        @submit="handleDeliverySubmit" />

</template>

<script>
import DialogModal from '@/Components/DialogModal.vue';
import Loading from '@/Components/MyComponents/Loading.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import StationTimeHistory from './StationTimeHistory.vue';
import PauseStationModal from './PauseStationModal.vue';
import MoveStationModal from './MoveStationModal.vue';
import RegisterDeliveryModal from './RegisterDeliveryModal.vue';
import { format, parseISO, differenceInSeconds } from 'date-fns';
import es from 'date-fns/locale/es';
import { CheckCircleIcon, PlayIcon, PauseIcon, ArrowRightIcon, ClockIcon, QuestionMarkCircleIcon } from '@heroicons/vue/24/solid';

export default {
    name: 'ProductionDetailsModal',
    components: {
        DialogModal, Loading, PrimaryButton, StationTimeHistory,
        PauseStationModal, MoveStationModal, RegisterDeliveryModal,
        CheckCircleIcon, PlayIcon, PauseIcon, ArrowRightIcon, ClockIcon,
        QuestionMarkCircleIcon
    },
    props: {
        show: Boolean, selectedProduction: Object, updatingDetails: Boolean,
        machines: Array, stations: Array, users: Object,
    },
    emits: [
        'close', 'start-process', 'pause-process', 'resume-process',
        'finish-move-process', 'skip-move-process', 'register-delivery',
    ],
    data() {
        return {
            activeTab: 'details',
            liveTimeTrigger: null,
            now: new Date(),
            showPauseModal: false,
            showMoveModal: false,
            showDeliveryModal: false,
            moveModalMode: 'skip',
            deliveryContext: null,
            isDeliveryPartial: false,
            deliveryDefaultQty: 0,
            moveDefaultQty: 0,
        };
    },
    watch: {
        selectedProduction(newVal) {
            if (newVal) this.activeTab = 'details';
        },
        show(newVal) {
            if (newVal) this.liveTimeTrigger = setInterval(() => { this.now = new Date(); }, 1000);
            else clearInterval(this.liveTimeTrigger);
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
        statusInfo() {
            const status = this.currentStationStatus;
            if (!status) return { text: this.selectedProduction.station, icon: QuestionMarkCircleIcon, bgColor: 'bg-gray-100', textColor: 'text-gray-600' };
            switch (status) {
                case 'En proceso': return { text: 'En proceso', icon: PlayIcon, bgColor: 'bg-green-100', textColor: 'text-green-700' };
                case 'En pausa': return { text: 'En pausa', icon: PauseIcon, bgColor: 'bg-[#FFEEDF]', textColor: 'text-[#F09400]' };
                case 'En espera': return { text: 'En espera', icon: ClockIcon, bgColor: 'bg-[#f2f2f2]', textColor: 'text-[#373737]' };
                case 'Finalizada': return { text: 'Finalizada', icon: CheckCircleIcon, bgColor: 'bg-blue-100', textColor: 'text-blue-700' };
                default: return { text: status, icon: QuestionMarkCircleIcon, bgColor: 'bg-gray-100', textColor: 'text-gray-600' };
            }
        },
        lastPauseReason() {
            if (this.currentStationStatus !== 'En pausa' || !this.currentStationRecord.pauses.length) return '';
            return this.currentStationRecord.pauses[this.currentStationRecord.pauses.length - 1].reason;
        },
        hasAnyDeliveryInfo() {
            return this.selectedProduction.close_production_date || this.selectedProduction.quality_released_date || this.selectedProduction.production_close_type || this.selectedProduction.packing_received_date;
        },
        shouldShowInspectionSection() {
            const relevantStations = ['Inspección', 'Empaques', 'Empaques terminado', 'Terminadas'];
            return relevantStations.includes(this.selectedProduction.station) || this.selectedProduction.partials?.length > 0;
        },
        shouldShowPackingSection() {
            const relevantStations = ['Empaques', 'Empaques terminado', 'Terminadas'];
            return relevantStations.includes(this.selectedProduction.station) || this.selectedProduction.packing_partials?.length > 0;
        },
        availableNextStations() {
            if (!this.selectedProduction) return [];
            const current = this.selectedProduction.station;
            const allStations = this.stations;

            if (current === 'Calidad') {
                return allStations.filter(s => ['X Reproceso', 'Inspección', 'Empaques'].includes(s.name));
            }
            if (current === 'Empaques') {
                return allStations.filter(s => ['Empaques terminado'].includes(s.name));
            }
            if (current === 'Inspección') {
                return allStations.filter(s => ['Calidad', 'Terminadas'].includes(s.name));
            }
            const restricted = ['Inspección', 'X Reproceso', 'Empaques terminado'];
            return allStations.filter(s => !restricted.includes(s.name) && s.name !== current);
        },
        totalEffectiveTime() {
            if (!this.selectedProduction?.station_times) return 0;
            let total = this.selectedProduction.station_times.reduce((acc, station) => acc + (station.times?.effective_seconds ?? 0), 0);
            if (this.currentStationStatus === 'En proceso') {
                total += this.liveEffectiveTime;
            }
            return total;
        },
        totalPausedTime() {
            if (!this.selectedProduction?.station_times) return 0;
            let total = this.selectedProduction.station_times.reduce((acc, station) => acc + (station.times?.paused_seconds ?? 0), 0);
            if (this.currentStationStatus === 'En pausa') {
                total += this.livePausedTime;
            }
            return total;
        },
        totalWaitingTime() {
            if (!this.selectedProduction?.station_times) return 0;
            let total = this.selectedProduction.station_times.reduce((acc, station) => acc + (station.times?.waiting_seconds ?? 0), 0);
            if (this.currentStationStatus === 'En espera') {
                total += this.liveWaitingTime;
            }
            return total;
        },
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
            if (!lastPause.resumed_at) {
                const currentPauseDuration = differenceInSeconds(this.now, parseISO(lastPause.paused_at));
                return currentPauseDuration;
            }
            return 0;
        }
    },
    methods: {
        openMoveModal(mode) {
            this.moveModalMode = mode;
            this.showMoveModal = true;
        },
        openDeliveryModal(context, isPartial) {
            this.deliveryContext = context;
            this.isDeliveryPartial = isPartial;
            
            // --- NEW: Calculate remaining quantity ---
            const production = this.selectedProduction;
            let remainingQty = 0;
            if (context === 'inspection') {
                const delivered = production.partials?.reduce((acc, curr) => acc + curr.quantity, 0) ?? 0;
                remainingQty = (production.quality_quantity ?? production.close_quantity ?? 0) - delivered;
            } else if (context === 'packing') {
                const delivered = production.packing_partials?.reduce((acc, curr) => acc + curr.quantity, 0) ?? 0;
                remainingQty = (production.packing_received_quantity ?? 0) - delivered;
            }
            this.deliveryDefaultQty = remainingQty > 0 ? remainingQty : 0;
            // --- END NEW ---

            this.showDeliveryModal = true;
        },
        handlePauseSubmit(reason) {
            this.$emit('pause-process', reason);
            this.showPauseModal = false;
        },
        handleMoveSubmit(payload) {
            if (this.moveModalMode === 'skip') this.$emit('skip-move-process', payload);
            else this.$emit('finish-move-process', payload);
            this.showMoveModal = false;
        },
        handleDeliverySubmit(form) {
            this.$emit('register-delivery', {
                form: form,
                context: this.deliveryContext,
                isPartial: this.isDeliveryPartial,
            });
            this.showDeliveryModal = false;
        },
        calculatePercentage(value, total) {
            if (!total || !value) return '0.0';
            return ((value / total) * 100).toFixed(1);
        },
        formatDuration(totalSeconds) {
            if (totalSeconds === null || isNaN(totalSeconds) || totalSeconds < 0) return '0d 00:00:00';

            const days = Math.floor(totalSeconds / 86400); totalSeconds %= 86400;
            const hours = Math.floor(totalSeconds / 3600); totalSeconds %= 3600;
            const minutes = Math.floor(totalSeconds / 60);
            const seconds = Math.floor(totalSeconds % 60);

            let result = '';
            if (days > 0) result += `${days}d `;
            result += `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            return result;
        },
        formatDate(dateString) {
            if (!dateString) return '-';
            return format(parseISO(dateString), 'dd MMMM yyyy', { locale: es });
        },
        formatDateTime(dateString) {
            if (!dateString) return '-';
            return format(parseISO(dateString), 'dd MMMM yyyy, hh:mm a', { locale: es });
        },
    },
    beforeUnmount() {
        clearInterval(this.liveTimeTrigger);
    },
};
</script>