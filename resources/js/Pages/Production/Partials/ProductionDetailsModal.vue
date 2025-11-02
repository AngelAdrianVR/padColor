<template>
    <!-- Modal aumentado a 5xl para dar espacio a los componentes -->
    <DialogModal :show="show" @close="$emit('close')" maxWidth="4xl">
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
                            <!-- Status Chip: Ahora usa un computed para el padre o el hijo -->
                            <span class="flex items-center text-xs font-semibold px-2.5 py-1 rounded-full"
                                :class="[statusInfo(selectedProduction).bgColor, statusInfo(selectedProduction).textColor]">
                                <component :is="statusInfo(selectedProduction).icon" class="size-4 mr-1.5" />
                                {{ statusInfo(selectedProduction).text }}
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
                                <!-- Order Information (Parent) -->
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
                                        <p class="text-gray-500">Observaciones (Padre):</p>
                                        <p class="col-span-2">{{ selectedProduction.notes }}</p>
                                    </div>
                                </section>

                                <!-- Product Information (Shared) -->
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
                                        
                                        <!-- Estación (condicional) -->
                                        <p class="text-gray-500">Estación actual:</p>
                                        <div class="col-span-2 flex items-center space-x-2">
                                            <div class="rounded-full size-6 flex items-center justify-center"
                                                :style="{ backgroundColor: getStationStyling(selectedProduction.station).light, color: getStationStyling(selectedProduction.station).dark }">
                                                <component :is="getStationStyling(selectedProduction.station).icon" class="size-4" />
                                            </div>
                                            <p>{{ selectedProduction.station }}</p>
                                        </div>
                                    </div>
                                </section>

                                <!-- NUEVO: Información de Componentes (Hijos) -->
                                <section v-if="isDividedOrder">
                                    <h2 class="bg-gray-100 font-bold text-gray-700 py-2 px-3 rounded-md">Componentes de la orden</h2>
                                    <div v-if="isFetchingChildren" class="text-center py-6">
                                        <Loading />
                                    </div>
                                    <div v-else-if="children.length > 0" class="mt-3 px-2 space-y-4">
                                        <div v-for="child in children" :key="child.id" class="border rounded-lg p-3 grid grid-cols-4 gap-x-4 gap-y-2">
                                            <p class="text-gray-500 col-span-4 font-bold text-base">{{ child.component_name }}</p>
                                            
                                            <p class="text-gray-500">ID Componente:</p>
                                            <p class="font-medium">{{ child.id }}</p>
                                            <p class="text-gray-500">Cantidad:</p>
                                            <p class="font-medium">{{ child.quantity?.toLocaleString('es-MX') }} pzs</p>

                                            <p class="text-gray-500">Estación actual:</p>
                                            <div class="flex items-center space-x-2">
                                                <div class="rounded-full size-6 flex items-center justify-center"
                                                    :style="{ backgroundColor: getStationStyling(child.station).light, color: getStationStyling(child.station).dark }">
                                                    <component :is="getStationStyling(child.station).icon" class="size-4" />
                                                </div>
                                                <p class="font-medium">{{ child.station }}</p>
                                            </div>
                                            <p class="text-gray-500">Máquina actual:</p>
                                            <p class="font-medium">{{ child.machine?.name ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                </section>

                                <!-- Materials and Measures (Parent) -->
                                <section>
                                    <h2 class="bg-gray-100 font-bold text-gray-700 py-2 px-3 rounded-md">Materiales y
                                        medidas (General)</h2>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-1 mt-3 px-2">
                                        <!-- ... (toda la sección de materiales y medidas se queda igual) ... -->
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
                            
                            <!-- CASO 1: ORDEN SIMPLE -->
                            <div v-if="!isDividedOrder" class="p-1 mt-2 space-y-6">
                                <!-- General Time Summary -->
                                <section>
                                    <h2 class="bg-gray-100 font-bold text-gray-700 py-2 px-3 rounded-md">Resumen general
                                        de la orden</h2>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3 text-center">
                                        <div class="bg-green-100 border border-green-200 rounded-lg p-3">
                                            <p class="text-sm text-green-800">Tiempo efectivo total</p>
                                            <p class="font-bold text-xl text-green-900 mt-1">{{
                                                formatDuration(totalEffectiveTime(selectedProduction)) }}</p>
                                        </div>
                                        <div class="bg-orange-100 border border-orange-200 rounded-lg p-3">
                                            <p class="text-sm text-orange-800">Tiempo en pausa total</p>
                                            <p class="font-bold text-xl text-orange-900 mt-1">{{
                                                formatDuration(totalPausedTime(selectedProduction)) }}</p>
                                        </div>
                                        <div class="bg-gray-200 border border-gray-300 rounded-lg p-3">
                                            <p class="text-sm text-gray-800">Tiempo en espera total</p>
                                            <p class="font-bold text-xl text-gray-900 mt-1">{{
                                                formatDuration(totalWaitingTime(selectedProduction)) }}</p>
                                        </div>
                                    </div>
                                </section>

                                <!-- Station Control (Simple) -->
                                <section>
                                    <h2 class="font-bold px-3 pb-1">Control de estación</h2>
                                    <div v-if="currentStationRecord(selectedProduction) && currentStationRecord(selectedProduction).status !== 'Finalizada'"
                                        class="p-4 border rounded-lg bg-[#F2F2F2]">
                                        
                                        <!-- Waiting State -->
                                        <div v-if="currentStationStatus(selectedProduction) === 'En espera'">
                                            <div class="flex justify-between items-center bg-white px-3 rounded-lg">
                                                <div class="flex items-center space-x-2">
                                                    <div class="rounded-full size-6 flex items-center justify-center"
                                                        :style="{ backgroundColor: getStationStyling(currentStationRecord(selectedProduction).station_name).light, color: getStationStyling(currentStationRecord(selectedProduction).station_name).dark }">
                                                        <component :is="getStationStyling(currentStationRecord(selectedProduction).station_name).icon" class="size-4" />
                                                    </div>
                                                    <p class="font-bold">{{ currentStationRecord(selectedProduction).station_name }}</p>
                                                </div>
                                                <div class="flex justify-between items-center p-3 rounded-lg space-x-2">
                                                    <p class="text-xs text-right">Tiempo en espera</p>
                                                    <p class="font-bold text-lg text-right">{{ formatDuration(liveWaitingTime(selectedProduction)) }}</p>
                                                </div>
                                            </div>
                                            <div class="flex justify-center items-center space-x-3 mt-3">
                                                <PrimaryButton @click="$emit('start-process')" class="!bg-[#1FAE07] hover:bg-green-600 flex items-center !rounded-lg">
                                                    <PlayIcon class="size-5 mr-2" /> Iniciar
                                                </PrimaryButton>
                                                <PrimaryButton @click="openMoveModal('skip', selectedProduction)" class="!bg-[#D9D9D9] !text-[#373737] flex items-center !rounded-lg">
                                                    Mover a siguiente <ArrowRightIcon class="size-5 ml-2" />
                                                </PrimaryButton>
                                            </div>
                                        </div>

                                        <!-- In Process State -->
                                        <div v-else-if="currentStationStatus(selectedProduction) === 'En proceso'">
                                            <div class="flex justify-between items-center bg-white px-3 rounded-lg">
                                                <div class="flex items-center space-x-2">
                                                     <div class="rounded-full size-6 flex items-center justify-center"
                                                        :style="{ backgroundColor: getStationStyling(currentStationRecord(selectedProduction).station_name).light, color: getStationStyling(currentStationRecord(selectedProduction).station_name).dark }">
                                                        <component :is="getStationStyling(currentStationRecord(selectedProduction).station_name).icon" class="size-4" />
                                                    </div>
                                                    <p class="font-bold">{{ currentStationRecord(selectedProduction).station_name }}</p>
                                                </div>
                                                <div class="flex justify-between items-center p-3 rounded-lg space-x-2">
                                                    <p class="text-xs text-right">Tiempo efectivo</p>
                                                    <p class="font-bold text-lg text-right">{{ formatDuration(liveEffectiveTime(selectedProduction)) }}</p>
                                                </div>
                                            </div>
                                            <div class="flex justify-center items-center space-x-3 mt-4">
                                                <PrimaryButton @click="openPauseModal(selectedProduction)" class="!bg-[#FDA10F] flex items-center !rounded-lg">
                                                    <PauseIcon class="size-5 mr-2" /> Pausar
                                                </PrimaryButton>
                                                <PrimaryButton @click="openMoveModal('finish', selectedProduction)" class="!bg-[#008CF0] flex items-center !rounded-lg">
                                                    <CheckCircleIcon class="size-5 mr-2" /> Finalizar y mover
                                                </PrimaryButton>
                                            </div>
                                        </div>

                                        <!-- Paused State -->
                                        <div v-else-if="currentStationStatus(selectedProduction) === 'En pausa'">
                                            <div class="flex justify-between items-center bg-white px-3 rounded-lg">
                                                <div class="flex items-center space-x-2">
                                                    <div class="rounded-full size-6 flex items-center justify-center"
                                                        :style="{ backgroundColor: getStationStyling(currentStationRecord(selectedProduction).station_name).light, color: getStationStyling(currentStationRecord(selectedProduction).station_name).dark }">
                                                        <component :is="getStationStyling(currentStationRecord(selectedProduction).station_name).icon" class="size-4" />
                                                    </div>
                                                    <p class="font-bold">{{ currentStationRecord(selectedProduction).station_name }}</p>
                                                </div>
                                                <div class="flex justify-between items-center p-3 space-x-2 rounded-lg">
                                                    <p class="text-xs text-right">Tiempo en pausa</p>
                                                    <p class="font-bold text-lg text-right">{{ formatDuration(livePausedTime(selectedProduction)) }}</p>
                                                </div>
                                            </div>
                                            <p class="text-sm text-gray-700 text-center mt-2">Motivo: <span
                                                    class="font-semibold italic">"{{ lastPauseReason(selectedProduction) }}"</span></p>
                                            <div class="flex justify-center items-center space-x-3 mt-4">
                                                <PrimaryButton @click="$emit('resume-process')" class="!bg-[#1FAE07] hover:bg-green-600 flex items-center !rounded-lg">
                                                    <PlayIcon class="size-5 mr-2" /> Reanudar
                                                </PrimaryButton>
                                                <PrimaryButton @click="openMoveModal('finish', selectedProduction)" class="!bg-[#008CF0] flex items-center !rounded-lg">
                                                    <CheckCircleIcon class="size-5 mr-2" /> Finalizar y mover
                                                </PrimaryButton>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else class="text-center text-gray-500 py-8 border rounded-b-md">
                                        <p v-if="currentStationRecord(selectedProduction)?.status === 'Finalizada'">La estación actual ya ha
                                            sido finalizada.</p>
                                        <p v-else>No hay un registro de tiempo para la estación actual.</p>
                                    </div>
                                </section>

                                <!-- Station Time Summary (Simple) -->
                                <section>
                                    <h2 class="bg-gray-100 font-bold text-gray-700 py-2 px-3 rounded-md">Resumen de
                                        tiempos por estación</h2>
                                    <StationTimeHistory :stationTimes="selectedProduction.station_times"
                                        :users="users" />
                                </section>
                            </div>

                            <!-- CASO 2: ORDEN DIVIDIDA -->
                            <div v-if="isDividedOrder" class="p-1 mt-2 space-y-6">

                                <!-- ***** INICIO: NUEVA SECCIÓN DE GRAN TOTAL ***** -->
                                <section>
                                    <h2 class="bg-blue-100 font-bold text-blue-800 py-2 px-3 rounded-md">Resumen general de la orden (Todos los componentes)</h2>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3 text-center">
                                        <div class="bg-green-100 border border-green-200 rounded-lg p-3">
                                            <p class="text-sm text-green-800">Tiempo efectivo (Gran Total)</p>
                                            <p class="font-bold text-xl text-green-900 mt-1">{{ formatDuration(grandTotalEffectiveTime) }}</p>
                                        </div>
                                        <div class="bg-orange-100 border border-orange-200 rounded-lg p-3">
                                            <p class="text-sm text-orange-800">Tiempo en pausa (Gran Total)</p>
                                            <p class="font-bold text-xl text-orange-900 mt-1">{{ formatDuration(grandTotalPausedTime) }}</p>
                                        </div>
                                        <div class="bg-gray-200 border border-gray-300 rounded-lg p-3">
                                            <p class="text-sm text-gray-800">Tiempo en espera (Gran Total)</p>
                                            <p class="font-bold text-xl text-gray-900 mt-1">{{ formatDuration(grandTotalWaitingTime) }}</p>
                                        </div>
                                    </div>
                                </section>
                                <!-- ***** FIN: NUEVA SECCIÓN DE GRAN TOTAL ***** -->


                                <div v-if="isFetchingChildren" class="text-center py-12">
                                    <Loading />
                                </div>
                                <div v-else-if="children.length === 0" class="text-center py-12 text-gray-500">
                                    No se encontraron componentes para esta orden.
                                </div>
                                
                                <!-- Loop por cada componente -->
                                <section v-for="child in children" :key="child.id" class="border rounded-lg">
                                    <h2 class="bg-gray-100 font-bold text-gray-700 py-2 px-3 rounded-t-md">
                                        Componente: {{ child.component_name }} (ID: {{ child.id }})
                                    </h2>

                                    <!-- ***** INICIO: NUEVA SECCIÓN DE TOTALES POR HIJO ***** -->
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-white border-t">
                                        <div class="bg-green-100 border border-green-200 rounded-lg p-3">
                                            <p class="text-sm text-green-800">Tiempo efectivo total (Componente)</p>
                                            <p class="font-bold text-xl text-green-900 mt-1">{{ formatDuration(totalEffectiveTime(child)) }}</p>
                                        </div>
                                        <div class="bg-orange-100 border border-orange-200 rounded-lg p-3">
                                            <p class="text-sm text-orange-800">Tiempo en pausa total (Componente)</p>
                                            <p class="font-bold text-xl text-orange-900 mt-1">{{ formatDuration(totalPausedTime(child)) }}</p>
                                        </div>
                                        <div class="bg-gray-200 border border-gray-300 rounded-lg p-3">
                                            <p class="text-sm text-gray-800">Tiempo en espera total (Componente)</p>
                                            <p class="font-bold text-xl text-gray-900 mt-1">{{ formatDuration(totalWaitingTime(child)) }}</p>
                                        </div>
                                    </div>
                                    <!-- ***** FIN: NUEVA SECCIÓN DE TOTALES POR HIJO ***** -->


                                    <!-- Control de Estación (para este hijo) -->
                                     <div v-if="currentStationRecord(child) && currentStationRecord(child).status !== 'Finalizada'"
                                        class="p-4 border-t bg-[#F2F2F2]">
                                        
                                        <!-- Waiting State -->
                                        <div v-if="currentStationStatus(child) === 'En espera'">
                                            <div class="flex justify-between items-center bg-white px-3 rounded-lg">
                                                <div class="flex items-center space-x-2">
                                                    <div class="rounded-full size-6 flex items-center justify-center"
                                                        :style="{ backgroundColor: getStationStyling(currentStationRecord(child).station_name).light, color: getStationStyling(currentStationRecord(child).station_name).dark }">
                                                        <component :is="getStationStyling(currentStationRecord(child).station_name).icon" class="size-4" />
                                                    </div>
                                                    <p class="font-bold">{{ currentStationRecord(child).station_name }}</p>
                                                </div>
                                                <div class="flex justify-between items-center p-3 rounded-lg space-x-2">
                                                    <p class="text-xs text-right">Tiempo en espera</p>
                                                    <p class="font-bold text-lg text-right">{{ formatDuration(liveWaitingTime(child)) }}</p>
                                                </div>
                                            </div>
                                            <div class="flex justify-center items-center space-x-3 mt-3">
                                                <PrimaryButton @click="$emit('start-process', { productionId: child.id })" class="!bg-[#1FAE07] hover:bg-green-600 flex items-center !rounded-lg">
                                                    <PlayIcon class="size-5 mr-2" /> Iniciar
                                                </PrimaryButton>
                                                <PrimaryButton @click="openMoveModal('skip', child)" class="!bg-[#D9D9D9] !text-[#373737] flex items-center !rounded-lg">
                                                    Mover a siguiente <ArrowRightIcon class="size-5 ml-2" />
                                                </PrimaryButton>
                                            </div>
                                        </div>

                                        <!-- In Process State -->
                                        <div v-else-if="currentStationStatus(child) === 'En proceso'">
                                            <div class="flex justify-between items-center bg-white px-3 rounded-lg">
                                                <div class="flex items-center space-x-2">
                                                     <div class="rounded-full size-6 flex items-center justify-center"
                                                        :style="{ backgroundColor: getStationStyling(currentStationRecord(child).station_name).light, color: getStationStyling(currentStationRecord(child).station_name).dark }">
                                                        <component :is="getStationStyling(currentStationRecord(child).station_name).icon" class="size-4" />
                                                    </div>
                                                    <p class="font-bold">{{ currentStationRecord(child).station_name }}</p>
                                                </div>
                                                <div class="flex justify-between items-center p-3 rounded-lg space-x-2">
                                                    <p class="text-xs text-right">Tiempo efectivo</p>
                                                    <p class="font-bold text-lg text-right">{{ formatDuration(liveEffectiveTime(child)) }}</p>
                                                </div>
                                            </div>
                                            <div class="flex justify-center items-center space-x-3 mt-4">
                                                <PrimaryButton @click="openPauseModal(child)" class="!bg-[#FDA10F] flex items-center !rounded-lg">
                                                    <PauseIcon class="size-5 mr-2" /> Pausar
                                                </PrimaryButton>
                                                <PrimaryButton @click="openMoveModal('finish', child)" class="!bg-[#008CF0] flex items-center !rounded-lg">
                                                    <CheckCircleIcon class="size-5 mr-2" /> Finalizar y mover
                                                </PrimaryButton>
                                            </div>
                                        </div>

                                        <!-- Paused State -->
                                        <div v-else-if="currentStationStatus(child) === 'En pausa'">
                                            <div class="flex justify-between items-center bg-white px-3 rounded-lg">
                                                <div class="flex items-center space-x-2">
                                                    <div class="rounded-full size-6 flex items-center justify-center"
                                                        :style="{ backgroundColor: getStationStyling(currentStationRecord(child).station_name).light, color: getStationStyling(currentStationRecord(child).station_name).dark }">
                                                        <component :is="getStationStyling(currentStationRecord(child).station_name).icon" class="size-4" />
                                                    </div>
                                                    <p class="font-bold">{{ currentStationRecord(child).station_name }}</p>
                                                </div>
                                                <div class="flex justify-between items-center p-3 space-x-2 rounded-lg">
                                                    <p class="text-xs text-right">Tiempo en pausa</p>
                                                    <p class="font-bold text-lg text-right">{{ formatDuration(livePausedTime(child)) }}</p>
                                                </div>
                                            </div>
                                            <p class="text-sm text-gray-700 text-center mt-2">Motivo: <span
                                                    class="font-semibold italic">"{{ lastPauseReason(child) }}"</span></p>
                                            <div class="flex justify-center items-center space-x-3 mt-4">
                                                <PrimaryButton @click="$emit('resume-process', { productionId: child.id })" class="!bg-[#1FAE07] hover:bg-green-600 flex items-center !rounded-lg">
                                                    <PlayIcon class="size-5 mr-2" /> Reanudar
                                                </PrimaryButton>
                                                <PrimaryButton @click="openMoveModal('finish', child)" class="!bg-[#008CF0] flex items-center !rounded-lg">
                                                    <CheckCircleIcon class="size-5 mr-2" /> Finalizar y mover
                                                </PrimaryButton>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Si ya está finalizada -->
                                    <div v-else class="text-center text-gray-500 py-8 border-t">
                                        <p v-if="currentStationRecord(child)?.status === 'Finalizada'">La estación actual de este componente ({{ currentStationRecord(child).station_name }}) ya ha
                                            sido finalizada.</p>
                                        <p v-else>No hay un registro de tiempo para la estación actual de este componente.</p>
                                    </div>

                                    <!-- Resumen de Tiempos (para este hijo) -->
                                    <div class="border-t">
                                         <h3 class="font-semibold text-gray-600 px-3 pt-3 pb-1 text-xs">Historial de tiempos ({{ child.component_name }})</h3>
                                        <StationTimeHistory :stationTimes="child.station_times" :users="users" />
                                    </div>
                                </section>
                            </div>
                        </el-tab-pane>
                        <el-tab-pane label="Entregas" name="deliveries">
                            <!-- La pestaña de entregas funcionará igual, pero ahora los botones de
                                 "Registrar entrega" deben pasar el ID del componente (hijo) -->
                            <div class="p-1 mt-2 space-y-4">
                                <h2 class="font-bold text-gray-800">Registro de entregas</h2>

                                <div v-if="!hasAnyDeliveryInfo"
                                    class="text-center text-gray-500 py-12 bg-gray-50 rounded-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-12 mx-auto text-gray-300">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                    </svg>
                                    <p class="font-semibold mt-3">Aún no hay entregas registradas</p>
                                    <p class="text-xs mt-1">Aquí aparecerá la lista de entregas. <span v-if="isDividedOrder">Las entregas se registran a nivel de componente.</span></p>
                                </div>

                                <!-- Contenido (Orden Simple) -->
                                <div v-if="!isDividedOrder" class="space-y-4">
                                    <!-- ... (toda la lógica de entregas de orden simple se queda igual) ... -->
                                     <section v-if="selectedProduction.close_production_date" class="bg-gray-50 p-4 rounded-lg space-y-2">
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
                                    <section v-if="selectedProduction.quality_released_date" class="bg-gray-50 p-4 rounded-lg space-y-2">
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
                                    <section v-if="shouldShowInspectionSection(selectedProduction)" class="bg-gray-50 p-4 rounded-lg space-y-2">
                                        <div class="flex justify-between items-center">
                                            <h3 class="font-semibold text-gray-700">Inspección</h3>
                                            <PrimaryButton @click="openDeliveryModal('inspection', false, selectedProduction)" v-if="selectedProduction.station === 'Inspección' && !selectedProduction.production_close_type">
                                                Registrar entrega
                                            </PrimaryButton>
                                            <PrimaryButton @click="openDeliveryModal('inspection', true, selectedProduction)" v-if="selectedProduction.station === 'Inspección' && selectedProduction.production_close_type === 'Parcialidades'">
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
                                    <section v-if="shouldShowPackingSection(selectedProduction)" class="bg-gray-50 p-4 rounded-lg space-y-2">
                                        <div class="flex justify-between items-center">
                                            <h3 class="font-semibold text-gray-700">Empaques</h3>
                                            <PrimaryButton @click="openDeliveryModal('packing', false, selectedProduction)" v-if="selectedProduction.station === 'Empaques' && !selectedProduction.packing_close_type">
                                                Registrar entrega
                                            </PrimaryButton>
                                            <PrimaryButton @click="openDeliveryModal('packing', true, selectedProduction)" v-if="selectedProduction.station === 'Empaques' && selectedProduction.packing_close_type === 'Parcialidades'">
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

                                <!-- Contenido (Orden Dividida) -->
                                <div v-if="isDividedOrder && children.length > 0" class="space-y-4">
                                    <!-- Loop por cada componente -->
                                    <div v-for="child in children" :key="child.id" class="border rounded-lg">
                                        <h3 class="bg-gray-100 font-bold text-gray-700 py-2 px-3 rounded-t-md">Componente: {{ child.component_name }}</h3>
                                        <div class="p-4 space-y-4">
                                            <section v-if="child.close_production_date" class="bg-gray-50 p-4 rounded-lg space-y-2">
                                                <h3 class="font-semibold text-gray-700">Liberado por producción</h3>
                                                <div class="grid grid-cols-2 gap-x-4 text-xs">
                                                    <span>Fecha de liberación:</span> <span class="font-medium">{{
                                                        formatDateTime(child.close_production_date) }}</span>
                                                    <span>Cantidad entregada:</span> <span class="font-medium">{{
                                                        child.close_quantity?.toLocaleString('es-MX') }}</span>
                                                    <span>Notas:</span> <span class="font-medium">{{
                                                        child.close_production_notes ?? '-' }}</span>
                                                </div>
                                            </section>
                                            <section v-if="child.quality_released_date" class="bg-gray-50 p-4 rounded-lg space-y-2">
                                                <h3 class="font-semibold text-gray-700">Liberado por calidad</h3>
                                                <div class="grid grid-cols-2 gap-x-4 text-xs">
                                                    <span>Fecha de liberación:</span> <span class="font-medium">{{
                                                        formatDateTime(child.quality_released_date) }}</span>
                                                    <span>Cantidad entregada:</span> <span class="font-medium">{{
                                                        child.quality_quantity?.toLocaleString('es-MX') }}</span>
                                                    <span>Merma:</span> <span class="font-medium">{{
                                                        child.quality_scrap?.toLocaleString('es-MX') }} ({{
                                                            calculatePercentage(child.quality_scrap,
                                                                child.close_quantity) }}%)</span>
                                                    <span>Diferencia:</span> <span class="font-medium">{{
                                                        child.quality_shortage?.toLocaleString('es-MX') }}</span>
                                                    <span>Notas:</span> <span class="font-medium">{{
                                                        child.quality_notes ?? '-' }}</span>
                                                </div>
                                            </section>
                                            <!-- Inspección (por hijo) -->
                                            <section v-if="shouldShowInspectionSection(child)" class="bg-gray-50 p-4 rounded-lg space-y-2">
                                                <div class="flex justify-between items-center">
                                                    <h3 class="font-semibold text-gray-700">Inspección</h3>
                                                    <PrimaryButton @click="openDeliveryModal('inspection', false, child)" v-if="child.station === 'Inspección' && !child.production_close_type">
                                                        Registrar entrega
                                                    </PrimaryButton>
                                                    <PrimaryButton @click="openDeliveryModal('inspection', true, child)" v-if="child.station === 'Inspección' && child.production_close_type === 'Parcialidades'">
                                                        Registrar parcialidad
                                                    </PrimaryButton>
                                                </div>
                                                <div class="grid grid-cols-2 gap-x-4 text-xs">
                                                    <span>Tipo de entrega:</span> <span class="font-medium">{{ child.production_close_type ?? 'Pendiente' }}</span>
                                                </div>
                                                <!-- ***** INICIO: CORRECCIÓN ENTREGAS HIJOS ***** -->
                                                <div v-if="child.production_close_type === 'Única'" class="grid grid-cols-2 gap-x-4 text-xs border-t pt-2 mt-2">
                                                   <span>Cantidad entregada:</span> <span class="font-medium">{{
                                                        child.current_quantity?.toLocaleString('es-MX') }}</span>
                                                    <span>Merma:</span> <span class="font-medium">{{
                                                        child.inspection_scrap?.toLocaleString('es-MX') }} ({{
                                                            calculatePercentage(child.inspection_scrap,
                                                                child.quality_quantity) }}%)</span>
                                                    <span>Diferencia:</span> <span class="font-medium">{{
                                                        child.inspection_shortage?.toLocaleString('es-MX')
                                                    }}</span>
                                                    <span>Notas:</span> <span class="font-medium">{{
                                                        child.inspection_notes ?? '-' }}</span>
                                                </div>
                                                <div v-if="child.production_close_type === 'Parcialidades'" class="text-xs border-t pt-2 mt-2 space-y-3">
                                                    <div v-for="(partial, index) in child.partials" :key="index"
                                                        class="pb-2"
                                                        :class="{ 'border-b': index !== child.partials.length - 1 }">
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
                                                <!-- ***** FIN: CORRECCIÓN ENTREGAS HIJOS ***** -->
                                            </section>

                                            <!-- Packing (por hijo) -->
                                            <section v-if="shouldShowPackingSection(child)" class="bg-gray-50 p-4 rounded-lg space-y-2">
                                                <div class="flex justify-between items-center">
                                                    <h3 class="font-semibold text-gray-700">Empaques</h3>
                                                    <PrimaryButton @click="openDeliveryModal('packing', false, child)" v-if="child.station === 'Empaques' && !child.packing_close_type">
                                                        Registrar entrega
                                                    </PrimaryButton>
                                                    <PrimaryButton @click="openDeliveryModal('packing', true, child)" v-if="child.station === 'Empaques' && child.packing_close_type === 'Parcialidades'">
                                                        Registrar parcialidad
                                                    </PrimaryButton>
                                                </div>
                                                <!-- ***** INICIO: CORRECCIÓN ENTREGAS HIJOS ***** -->
                                                <div class="grid grid-cols-2 gap-x-4 text-xs">
                                                    <span>Cantidad recibida:</span> <span class="font-medium">{{
                                                        child.packing_received_quantity?.toLocaleString('es-MX')
                                                    }}</span>
                                                    <span>Fecha de recibido:</span> <span class="font-medium">{{
                                                        formatDateTime(child.packing_received_date) }}</span>
                                                    <span>Tipo de entrega:</span> <span class="font-medium">{{
                                                        child.packing_close_type ?? 'Pendiente' }}</span>
                                                </div>
                                                <div v-if="child.packing_close_type === 'Única'"
                                                    class="grid grid-cols-2 gap-x-4 text-xs border-t pt-2 mt-2">
                                                    <span>Cantidad entregada:</span> <span class="font-medium">{{
                                                        child.current_quantity?.toLocaleString('es-MX') }}</span>
                                                    <span>Fecha de entrega:</span> <span class="font-medium">{{
                                                        formatDateTime(child.packing_finished_date) }}</span>
                                                    <span>Notas:</span> <span class="font-medium">{{
                                                        child.packing_notes ?? '-' }}</span>
                                                </div>
                                                <div v-if="child.packing_close_type === 'Parcialidades'"
                                                    class="text-xs border-t pt-2 mt-2 space-y-3">
                                                    <div v-for="(partial, index) in child.packing_partials"
                                                        :key="index" class="pb-2"
                                                        :class="{ 'border-b': index !== child.packing_partials.length - 1 }">
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
                                                <!-- ***** FIN: CORRECCIÓN ENTREGAS HIJOS ***** -->
                                            </section>
                                        </div>
                                    </div>
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
    <MoveStationModal :show="showMoveModal" :processType="moveModalMode" :available-stations="availableNextStations"
        :machines="machines" :production="moveModalProduction" :default-quantity="moveDefaultQty"
        @close="showMoveModal = false" @submit="handleMoveSubmit" />
    <RegisterDeliveryModal :show="showDeliveryModal" :production="deliveryProduction" :context="deliveryContext"
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
import { CheckCircleIcon, PlayIcon, PauseIcon, ArrowRightIcon, ClockIcon, QuestionMarkCircleIcon, RectangleStackIcon } from '@heroicons/vue/24/solid';
import axios from 'axios';

export default {
    name: 'ProductionDetailsModal',
    components: {
        DialogModal, Loading, PrimaryButton, StationTimeHistory,
        PauseStationModal, MoveStationModal, RegisterDeliveryModal,
        CheckCircleIcon, PlayIcon, PauseIcon, ArrowRightIcon, ClockIcon,
        QuestionMarkCircleIcon, RectangleStackIcon,
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
            // --- Nuevos ---
            isFetchingChildren: false,
            children: [],
            pauseModalProduction: null, // Qué producción (padre o hijo) se está pausando
            moveModalProduction: null, // Qué producción se está moviendo
            deliveryProduction: null, // Qué producción está registrando entrega
            // --- Estados de Modales ---
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
        show(newVal) {
            if (newVal) {
                this.activeTab = 'details';
                this.liveTimeTrigger = setInterval(() => { this.now = new Date(); }, 1000);
                // Cargar hijos si es una orden dividida
                if (this.isDividedOrder) {
                    this.fetchChildren();
                }
            } else {
                clearInterval(this.liveTimeTrigger);
                this.children = []; // Limpiar hijos al cerrar
            }
        },
        // ***** INICIO: CORRECCIÓN BUG 1 *****
        // Observa la prop 'updatingDetails' que viene de Index.vue
        updatingDetails(newVal, oldVal) {
            // Cuando 'updatingDetails' pasa de true a false, significa que Index.vue terminó de actualizar.
            if (oldVal === true && newVal === false) {
                // Si este modal está abierto y es una orden dividida,
                // forzamos la recarga de los hijos para obtener el estado más reciente.
                if (this.show && this.isDividedOrder) {
                    this.fetchChildren();
                }
            }
        }
        // ***** FIN: CORRECCIÓN BUG 1 *****
    },
    computed: {
        isDividedOrder() {
            return this.selectedProduction?.station === 'Producción dividida';
        },
        // --- INICIO: NUEVAS PROPIEDADES COMPUTADAS PARA GRAN TOTAL ---
        grandTotalEffectiveTime() {
            if (!this.isDividedOrder || !this.children.length) return 0;
            return this.children.reduce((acc, child) => {
                return acc + this.totalEffectiveTime(child);
            }, 0);
        },
        grandTotalPausedTime() {
            if (!this.isDividedOrder || !this.children.length) return 0;
            return this.children.reduce((acc, child) => {
                return acc + this.totalPausedTime(child);
            }, 0);
        },
        grandTotalWaitingTime() {
            if (!this.isDividedOrder || !this.children.length) return 0;
            return this.children.reduce((acc, child) => {
                return acc + this.totalWaitingTime(child);
            }, 0);
        },
        // --- FIN: NUEVAS PROPIEDADES COMPUTADAS PARA GRAN TOTAL ---
        availableNextStations() {
            // Esta lógica ahora depende de la producción que se esté moviendo (moveModalProduction)
            if (!this.moveModalProduction) return [];
            const current = this.moveModalProduction.station;
            
            // Usamos this.stations (el prop) que debería tener todas las estaciones
            const allStations = this.stations; 

            if (current === 'Material pendiente') {
                return allStations.filter(s => ['X Compra', 'Surtido'].includes(s.name));
            }
            if (current === 'X Compra') {
                return allStations.filter(s => ['Surtido'].includes(s.name));
            }
            if (current === 'Calidad') {
                return allStations.filter(s => ['X Reproceso', 'Inspección', 'Empaques'].includes(s.name));
            }
            if (current === 'Empaques') {
                return allStations.filter(s => ['Empaques terminado'].includes(s.name));
            }
            if (current === 'Inspección') {
                return allStations.filter(s => ['Calidad', 'Terminadas'].includes(s.name));
            }
            const restricted = ['Inspección', 'X Reproceso', 'Empaques terminado', 'Producción dividida'];
            return allStations.filter(s => !restricted.includes(s.name) && s.name !== current);
        },
        hasAnyDeliveryInfo() {
            if (this.isDividedOrder) {
                return this.children.some(child => this.hasDeliveryInfo(child));
            }
            return this.hasDeliveryInfo(this.selectedProduction);
        },
    },
    methods: {
        // --- NUEVOS MÉTODOS ---
        async fetchChildren() {
            if (!this.selectedProduction) return;
            this.isFetchingChildren = true;
            try {
                const response = await axios.get(route('productions.get-children', this.selectedProduction.id));
                // Inyectamos los hijos en el objeto 'selectedProduction' para mantener la reactividad
                // this.selectedProduction.children = response.data.items; // Esto puede fallar si el prop no es mutable
                this.children = response.data.items; // Usar data local es más seguro
            } catch (error) {
                console.error("Error al cargar componentes:", error);
                this.$notify({ title: "Error", message: "No se pudieron cargar los componentes.", type: "error" });
            } finally {
                this.isFetchingChildren = false;
            }
        },
        // Devuelve el estilo de una estación (incluyendo la dividida)
        getStationStyling(stationName) {
            if (stationName === 'Producción dividida') {
                return { name: 'Producción dividida', icon: RectangleStackIcon, light: '#E5E7EB', dark: '#374151' };
            }
            return this.stations.find(s => s.name === stationName) || { name: stationName, icon: QuestionMarkCircleIcon, light: '#F3F4F6', dark: '#4B5563' };
        },

        // --- MÉTODOS DE CÁLCULO REFACTORIZADOS (ahora aceptan un 'production' como argumento) ---
        currentStationRecord(production) {
            if (!production?.station_times?.length) return null;
            return production.station_times[production.station_times.length - 1];
        },
        currentStationStatus(production) {
            return this.currentStationRecord(production)?.status;
        },
        statusInfo(production) {
            if (!production) return {};
            // Caso especial para el padre
            if (production.station === 'Producción dividida') {
                return { text: 'Orden Dividida', icon: RectangleStackIcon, bgColor: 'bg-gray-100', textColor: 'text-gray-600' };
            }
            // Lógica de estado para simple o hijo
            const status = this.currentStationStatus(production);
            if (!status) return { text: production.station, icon: QuestionMarkCircleIcon, bgColor: 'bg-gray-100', textColor: 'text-gray-600' };
            switch (status) {
                case 'En proceso': return { text: 'En proceso', icon: PlayIcon, bgColor: 'bg-green-100', textColor: 'text-green-700' };
                case 'En pausa': return { text: 'En pausa', icon: PauseIcon, bgColor: 'bg-[#FFEEDF]', textColor: 'text-[#F09400]' };
                case 'En espera': return { text: 'En espera', icon: ClockIcon, bgColor: 'bg-[#f2f2f2]', textColor: 'text-[#373737]' };
                case 'Finalizada': return { text: 'Finalizada', icon: CheckCircleIcon, bgColor: 'bg-[#DDF2FF]', textColor: 'text-[#008CF0]' };
                default: return { text: status, icon: QuestionMarkCircleIcon, bgColor: 'bg-gray-100', textColor: 'text-gray-600' };
            }
        },
        lastPauseReason(production) {
            const record = this.currentStationRecord(production);
            if (this.currentStationStatus(production) !== 'En pausa' || !record.pauses.length) return '';
            return record.pauses[record.pauses.length - 1].reason;
        },
        hasDeliveryInfo(production) {
            return production.close_production_date || production.quality_released_date || production.production_close_type || production.packing_received_date;
        },
        shouldShowInspectionSection(production) {
            const relevantStations = ['Inspección', 'Empaques', 'Empaques terminado', 'Terminadas'];
            return relevantStations.includes(production.station) || production.partials?.length > 0;
        },
        shouldShowPackingSection(production) {
            const relevantStations = ['Empaques', 'Empaques terminado', 'Terminadas'];
            return relevantStations.includes(production.station) || production.packing_partials?.length > 0;
        },
        // --- CÁLCULOS DE TIEMPO (revisados) ---
        totalEffectiveTime(production) {
            if (!production?.station_times) return 0;
            let total = production.station_times.reduce((acc, station) => acc + (station.times?.effective_seconds ?? 0), 0);
            if (this.currentStationStatus(production) === 'En proceso') {
                total += this.liveEffectiveTime(production);
            }
            return total;
        },
        totalPausedTime(production) {
            if (!production?.station_times) return 0;
            let total = production.station_times.reduce((acc, station) => acc + (station.times?.paused_seconds ?? 0), 0);
            if (this.currentStationStatus(production) === 'En pausa') {
                total += this.livePausedTime(production);
            }
            return total;
        },
        totalWaitingTime(production) {
            if (!production?.station_times) return 0;
            let total = production.station_times.reduce((acc, station) => acc + (station.times?.waiting_seconds ?? 0), 0);
            if (this.currentStationStatus(production) === 'En espera') {
                total += this.liveWaitingTime(production);
            }
            return total;
        },
        liveWaitingTime(production) {
            const record = this.currentStationRecord(production);
            if (this.currentStationStatus(production) !== 'En espera' || !record.entered_at) return 0;
            return differenceInSeconds(this.now, parseISO(record.entered_at));
        },
        accumulatedPausedTimeInStation(production) {
            const record = this.currentStationRecord(production);
            if (!record?.pauses?.length) return 0;
            return record.pauses.reduce((acc, pause) => {
                if (pause.resumed_at) {
                    return acc + differenceInSeconds(parseISO(pause.resumed_at), parseISO(pause.paused_at));
                }
                return acc;
            }, 0);
        },
        liveEffectiveTime(production) {
            const record = this.currentStationRecord(production);
            if (this.currentStationStatus(production) !== 'En proceso' || !record.started_at) return 0;
            const startedAt = parseISO(record.started_at);
            return differenceInSeconds(this.now, startedAt) - this.accumulatedPausedTimeInStation(production);
        },
        livePausedTime(production) {
            const record = this.currentStationRecord(production);
            if (this.currentStationStatus(production) !== 'En pausa' || !record.pauses.length) return 0;
            const lastPause = record.pauses[record.pauses.length - 1];
            if (!lastPause.resumed_at) {
                const currentPauseDuration = differenceInSeconds(this.now, parseISO(lastPause.paused_at));
                return currentPauseDuration;
            }
            return 0;
        },

        // --- MANEJADORES DE MODALES (revisados) ---
        openPauseModal(production) {
            this.pauseModalProduction = production; // Guardar qué producción se pausa
            this.showPauseModal = true;
        },
        openMoveModal(mode, production) {
            this.moveModalMode = mode;
            this.moveModalProduction = production; // Guardar qué producción se mueve
            this.showMoveModal = true;
        },
        openDeliveryModal(context, isPartial, production) {
            this.deliveryContext = context;
            this.isDeliveryPartial = isPartial;
            this.deliveryProduction = production; // Guardar en qué producción se registra

            // Calcular cantidad restante para esta producción (hijo o simple)
            let remainingQty = 0;
            if (context === 'inspection') {
                const delivered = production.partials?.reduce((acc, curr) => acc + curr.quantity, 0) ?? 0;
                remainingQty = (production.quality_quantity ?? production.close_quantity ?? 0) - delivered;
            } else if (context === 'packing') {
                const delivered = production.packing_partials?.reduce((acc, curr) => acc + curr.quantity, 0) ?? 0;
                remainingQty = (production.packing_received_quantity ?? 0) - delivered;
            }
            this.deliveryDefaultQty = remainingQty > 0 ? remainingQty : 0;
            
            this.showMoveModal = false; // <-- ERROR SUTIL EN TU CÓDIGO. Debe ser showDeliveryModal = true
            this.showDeliveryModal = true; // <-- Esta es la corrección
        },
        handlePauseSubmit(reason) {
            // Enviar el ID de la producción pausada
            this.$emit('pause-process', { 
                reason: reason.reason, 
                notes: reason.notes,
                productionId: this.pauseModalProduction.id 
            });
            this.showPauseModal = false;
            this.pauseModalProduction = null;
        },
        handleMoveSubmit(payload) {
            // Añadir el ID y el objeto de la producción que se mueve
            const eventPayload = {
                ...payload,
                productionId: this.moveModalProduction.id,
                productionObject: this.moveModalProduction 
            };
            if (this.moveModalMode === 'skip') this.$emit('skip-move-process', eventPayload);
            else this.$emit('finish-move-process', eventPayload);
            this.showMoveModal = false;
            this.moveModalProduction = null;
        },
        handleDeliverySubmit(form) {
            // Enviar el ID de la producción en la que se registra
            this.$emit('register-delivery', {
                form: form,
                context: this.deliveryContext,
                isPartial: this.isDeliveryPartial,
                productionId: this.deliveryProduction.id 
            });
            this.showDeliveryModal = false;
            this.deliveryProduction = null;
        },

        // --- Métodos de Formato (sin cambios) ---
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
