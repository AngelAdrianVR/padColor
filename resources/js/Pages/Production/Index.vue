<template>
    <AppLayout title="Gestión de producción">
        <div class="min-h-[80vh] mx-2 lg:mx-8">
            <ActionHeader v-model="searchTemp" @search="handleSearch" @show-import="showImportModal = true"
                @show-export="showExportFilters = true" />

            <div class="mt-2 text-center">
                <el-tag v-if="search" size="large" closable @close="handleTagClose">
                    Estas buscando: <b>{{ search }}</b>
                </el-tag>
            </div>
            <div class="mt-6">
                <el-pagination layout="total, prev, pager, next" size="small" v-model:page-size="pageSize"
                    v-model:current-page="currentPage" :total="total" @current-change="handleCurrentChange"
                    class="ml-2" />
                <Loading v-if="fetching" />
                <ProductionTable v-else :productions="productions" :stations="stations" @row-click="handleRowClick"
                    @command="handleCommand" />
            </div>
        </div>

        <!-- Modals -->
        <ProductionDetailsModal :show="showDetails" :selectedProduction="selectedProduction"
            :updatingDetails="updatingDetails" :machines="machines" :stations="stations" :users="users"
            @close="showDetails = false" @update-machine="updateMachine" @station-change-intent="handleStationChange"
            @open-inspection-release="showInspectionRelease = true" @open-add-partial="showAddPartial = true"
            @open-packing-release="showPackingReleaseModal = true"
            @open-add-packing-partial="showAddPackingPartialModal = true" @start-process="startProcess"
            @pause-process="pauseProcess" @resume-process="resumeProcess" @finish-move-process="finishAndMoveProcess"
            @skip-move-process="skipAndMoveProcess" />

        <DialogModal :show="showExportFilters" @close="showExportFilters = false">
            <template #title>
                <h1 class="font-semibold">Exportar órdenes de producción</h1>
            </template>
            <template #content>
                <div class="lg:grid grid-cols-2 gap-3">
                    <div>
                        <InputLabel value="Fecha de emisión*" />
                        <el-date-picker v-model="dateRange" class="!w-full" type="daterange" range-separator="A"
                            start-placeholder="Fecha de inicio" end-placeholder="Fecha de fin" format="DD/MMM/YYYY"
                            value-format="YYYY-MM-DD" />
                    </div>
                    <div>
                        <InputLabel value="Temporada" />
                        <el-select class="w-full" v-model="season" placeholder="Seleccione la temporada"
                            no-data-text="No hay opciones registradas" no-match-text="No se encontraron coincidencias">
                            <el-option v-for="item in seasons" :key="item" :label="item" :value="item" />
                        </el-select>
                    </div>
                    <div>
                        <InputLabel value="Progreso" />
                        <el-select class="w-full" v-model="station" placeholder="Seleccione el pregreso"
                            no-data-text="No hay opciones registradas" no-match-text="No se encontraron coincidencias">
                            <el-option label="Todos" value="Todos" />
                            <el-option v-for="item in stations" :key="item.name" :label="item.name"
                                :value="item.name" />
                        </el-select>
                    </div>
                    <div class="col-span-full text-end text-xs">
                        <button @click="showImportModal = true; showExportFilters = false"
                            class="text-secondary font-semibold">Ir a importar</button>
                    </div>
                </div>
            </template>
            <template #footer>
                <div class="flex justify-end space-x-2">
                    <CancelButton @click="showExportFilters = false" :disabled="form.processing">
                        Cancelar
                    </CancelButton>
                    <PrimaryButton @click="exportExcel" :disabled="form.processing || !dateRange">
                        <i v-if="form.processing" class="fa-solid fa-circle-notch fa-spin mr-2"></i>
                        Continuar
                    </PrimaryButton>
                </div>
            </template>
        </DialogModal>

        <DialogModal :show="showImportModal" @close="showImportModal = false">
            <template #title>
                <h1 class="font-semibold">Importar órdenes de producción</h1>
            </template>
            <template #content>
                <h2 class="font-bold">Prepara tu archivo:</h2>
                <ul class="ml-5 text-xs">
                    <li class="list-disc">
                        Utiliza el mismo layout que puedes exportar desde esta sección (SwAssistant)
                        <span @click="showImportModal = false; showExportFilters = true"
                            class="cursor-pointer text-secondary font-semibold"> Ir a exportar</span>
                    </li>
                    <p>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-4 text-amber-600 inline-block mr-1">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                        </svg>
                        <span>Es importante cumplir con los siguientes formatos de fecha y en caso de cambiar alguno,
                            avisar
                            a desarrolladores para hacer los cambios necesarios:</span>
                    </p>
                    <ul class="ml-5 list-disc">
                        <li>Fecha de inicio: aaaa/mm/dd</li>
                        <li>Fecha esperada producción y fecha esperada empaque: dd/mm/aaaa</li>
                        <li>Fecha fin producción y producto terminado: aaaa-mm-dd hh:mm:ss</li>
                    </ul>
                    <p>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-4 text-amber-600 inline-block mr-1">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                        </svg>
                        <span>
                            Atención con los siguientes casos:
                        </span>
                    </p>
                    <ul class="ml-5 list-disc">
                        <li>Si una producción contiene un producto y/o máquina que no existen en el sistema, se
                            registrarán
                            como "POR DEFINIR"</li>
                        <li>Si una producción no contiene producto y/o máquina, se registrarán como "POR DEFINIR"</li>
                        <li>Si una producción no contiene progreso se registrará como "NO ESPECIFICADO"</li>
                        <li>El progreso "Producto Terminado" se cambiará automáticamente a "Terminadas" por estándares
                            del
                            sistema</li>
                        <li>El progreso "X Material" se cambiará automáticamente a "Material pendiente" por estándares
                            del
                            sistema</li>
                    </ul>
                </ul>
                <h2 class="font-bold mt-3">Proceso de importación:</h2>
                <ul class="list-disc ml-5 text-xs">
                    <li>Las órdenes que ya existan en el sistema se actualizarán automáticamente (progreso, máquina y
                        cantidad final)</li>
                    <li>Las órdenes nuevas se crearán con la información proporcionada en el archivo</li>
                </ul>
                <h2 class="font-bold mt-3">Sube tu archivo:</h2>
                <ul class="list-disc ml-5 text-xs">
                    <li>Haz clic en "Adjuntar archivo" para seleccionar tu archivo Excel</li>
                    <li>Al terminar la importación, se cargará nuevamente la lista de órdenes de la tabla con los nuevos
                        registros o las actualizaciones correspondientes.</li>
                </ul>

                <div class="ml-2 mt-8">
                    <FileUploader @files-selected="form.excel = $event" :multiple="false" acceptedFormat="excel" />
                    <InputError :message="form.errors.excel" />
                </div>
            </template>
            <template #footer>
                <div class="flex justify-end space-x-2">
                    <CancelButton @click="showImportModal = false; form.excel = []" :disabled="form.processing">
                        Cancelar
                    </CancelButton>
                    <PrimaryButton @click="importExcel" :disabled="form.processing || !form.excel.length">
                        <i v-if="form.processing" class="fa-solid fa-circle-notch fa-spin mr-2"></i>
                        Continuar
                    </PrimaryButton>
                </div>
            </template>
        </DialogModal>

        <DialogModal :show="showProductionRelease" @close="showProductionRelease = false">
            <template #title>
                <h1 class="font-semibold">Liberar producción</h1>
            </template>
            <template #content>
                <div class="grid grid-cols-2 gap-x-3 gap-y-2">
                    <div>
                        <InputLabel value="Cantidad a entregar*" />
                        <el-input-number v-model="form.close_quantity" placeholder="Ingresa la cantidad" :min="0"
                            class="!w-full" />
                        <InputError :message="form.errors.close_quantity" />
                    </div>
                    <div>
                        <InputLabel value="Fecha de cierre*" />
                        <el-date-picker class="!w-full" v-model="form.close_production_date" type="datetime"
                            placeholder="dd/mm/aa hh:mm" value-format="YYYY-MM-DD HH:mm:ss"
                            format="DD/MM/YYYY hh:mm A" />
                        <InputError :message="form.errors.close_production_date" />
                    </div>
                </div>
            </template>
            <template #footer>
                <div class="flex justify-end space-x-2">
                    <CancelButton @click="showProductionRelease = false" :disabled="form.processing">
                        Cancelar
                    </CancelButton>
                    <PrimaryButton @click="productionRelease" :disabled="form.processing">
                        <i v-if="form.processing" class="fa-solid fa-circle-notch fa-spin mr-2"></i>
                        Cerrar producción
                    </PrimaryButton>
                </div>
            </template>
        </DialogModal>

        <DialogModal :show="showQualityRelease" @close="showQualityRelease = false">
            <template #title>
                <h1 class="font-semibold">Liberar calidad</h1>
            </template>
            <template #content>
                <div class="grid grid-cols-2 gap-x-3 gap-y-2">
                    <div>
                        <InputLabel value="Cantidad a entregar" />
                        <el-input-number v-model="form.quality_quantity" :min="0" class="!w-full" />
                        <InputError :message="form.errors.quality_quantity" />
                    </div>
                    <div>
                        <InputLabel value="Fecha de cierre" />
                        <el-date-picker class="!w-full" v-model="form.quality_released_date" type="datetime"
                            placeholder="dd/mm/aa hh:mm" value-format="YYYY-MM-DD HH:mm:ss"
                            format="DD/MM/YYYY hh:mm A" />
                        <InputError :message="form.errors.quality_released_date" />
                    </div>
                    <div>
                        <InputLabel value="Merma" />
                        <el-input-number v-model="form.scrap_quantity" placeholder="Ingresa la cantidad" :min="0"
                            class="!w-full" />
                    </div>
                    <div>
                        <InputLabel value="Diferencia" />
                        <el-input-number v-model="form.shortage_quantity" placeholder="Ingresa la cantidad" :min="0"
                            class="!w-full" />
                    </div>
                    <div class="col-span-full">
                        <InputLabel value="Notas" />
                        <el-input v-model="form.notes" :rows="2" type="textarea"
                            placeholder="Específica la razón de la diferencia, la cantidad de merma o cualquier nota relacionada con la entrega." />
                        <InputError :message="form.errors.notes" />
                    </div>
                </div>
            </template>
            <template #footer>
                <div class="flex justify-end space-x-2">
                    <CancelButton @click="showQualityRelease = false" :disabled="form.processing">
                        Cancelar
                    </CancelButton>
                    <PrimaryButton @click="qualityReleased" :disabled="form.processing">
                        <i v-if="form.processing" class="fa-solid fa-circle-notch fa-spin mr-2"></i>
                        Cerrar calidad
                    </PrimaryButton>
                </div>
            </template>
        </DialogModal>

        <DialogModal :show="showMoveToPackingModal" @close="showMoveToPackingModal = false">
            <template #title>
                <h1 class="font-semibold">Mover a Empaques</h1>
            </template>
            <template #content>
                <div class="grid grid-cols-2 gap-x-3 gap-y-2">
                    <div>
                        <InputLabel value="Cantidad a mover a empaques*" />
                        <el-input-number v-model="form.packing_received_quantity" placeholder="Ingresa la cantidad"
                            :min="0" class="!w-full" />
                        <InputError :message="form.errors.packing_received_quantity" />
                    </div>
                    <div>
                        <InputLabel value="Fecha de movimiento*" />
                        <el-date-picker class="!w-full" v-model="form.packing_received_date" type="datetime"
                            placeholder="dd/mm/aa hh:mm" value-format="YYYY-MM-DD HH:mm:ss"
                            format="DD/MM/YYYY hh:mm A" />
                        <InputError :message="form.errors.packing_received_date" />
                    </div>
                </div>
            </template>
            <template #footer>
                <div class="flex justify-end space-x-2">
                    <CancelButton @click="showMoveToPackingModal = false; $refs.detailsModal.revertStation()"
                        :disabled="form.processing">
                        Cancelar
                    </CancelButton>
                    <PrimaryButton @click="moveToPacking" :disabled="form.processing">
                        <i v-if="form.processing" class="fa-solid fa-circle-notch fa-spin mr-2"></i>
                        Mover a Empaques
                    </PrimaryButton>
                </div>
            </template>
        </DialogModal>

        <DialogModal :show="showPackingReleaseModal" @close="showPackingReleaseModal = false">
            <template #title>
                <h1 class="font-semibold">Registrar Entrega de Empaques</h1>
            </template>
            <template #content>
                <div class="grid grid-cols-2 gap-x-3 gap-y-2">
                    <div class="col-span-full">
                        <InputLabel value="Tipo de entrega" />
                        <el-select v-model="form.packing_close_type" @change="handlePackingReleaseTypeChange"
                            placeholder="Seleccione">
                            <el-option v-for="item in ['Única', 'Parcialidades']" :key="item" :label="item"
                                :value="item" />
                        </el-select>
                        <InputError :message="form.errors.packing_close_type" />
                    </div>

                    <template v-if="form.packing_close_type === 'Única'">
                        <div>
                            <InputLabel value="Cantidad a entregar" />
                            <el-input-number v-model="form.quantity" :min="0" class="!w-full" />
                            <InputError :message="form.errors.quantity" />
                        </div>
                        <div>
                            <InputLabel value="Fecha de entrega" />
                            <el-date-picker class="!w-full" v-model="form.date" type="datetime"
                                placeholder="dd/mm/aa hh:mm" value-format="YYYY-MM-DD HH:mm:ss"
                                format="DD/MM/YYYY hh:mm A" />
                            <InputError :message="form.errors.date" />
                        </div>
                    </template>
                    <template v-else-if="form.packing_close_type === 'Parcialidades'">
                        <div class="col-span-full bg-[#E9E9E9] py-2 px-4 rounded-full">
                            Parcialidad 1
                        </div>
                        <div>
                            <InputLabel value="Cantidad entregada" />
                            <el-input-number v-model="form.quantity" :min="0" class="!w-full" />
                            <InputError :message="form.errors.quantity" />
                        </div>
                        <div>
                            <InputLabel value="Fecha de entrega" />
                            <el-date-picker class="!w-full" v-model="form.date" type="datetime" placeholder="dd/mm/aa"
                                value-format="YYYY-MM-DD HH:mm:ss" format="DD/MM/YYYY hh:mm A" />
                            <InputError :message="form.errors.date" />
                        </div>
                    </template>

                    <div class="col-span-full">
                        <InputLabel value="Notas" />
                        <el-input v-model="form.notes" :rows="2" type="textarea"
                            placeholder="Notas relacionadas con la entrega a empaques." />
                        <InputError :message="form.errors.notes" />
                    </div>
                </div>
            </template>
            <template #footer>
                <div class="flex justify-end space-x-2">
                    <CancelButton @click="showPackingReleaseModal = false" :disabled="form.processing">
                        Cancelar
                    </CancelButton>
                    <PrimaryButton @click="packingRelease" :disabled="form.processing || !form.packing_close_type">
                        <i v-if="form.processing" class="fa-solid fa-circle-notch fa-spin mr-2"></i>
                        <span v-if="form.packing_close_type === 'Única'">Finalizar Empaque</span>
                        <span v-else>Registrar Parcialidad</span>
                    </PrimaryButton>
                </div>
            </template>
        </DialogModal>

        <DialogModal :show="showAddPackingPartialModal" @close="showAddPackingPartialModal = false">
            <template #title>
                <h1 class="font-semibold">Registrar Parcialidad de Empaque</h1>
            </template>
            <template #content>
                <div class="grid grid-cols-2 gap-x-3 gap-y-2">
                    <div class="col-span-full bg-[#E9E9E9] py-2 px-4 rounded-full">
                        Parcialidad {{ (selectedProduction.packing_partials?.length ?? 0) + 1 }}
                    </div>
                    <div>
                        <InputLabel value="Cantidad entregada" />
                        <el-input-number v-model="form.quantity" placeholder="Ingresa la cantidad" :min="0"
                            class="!w-full" />
                        <InputError :message="form.errors.quantity" />
                    </div>
                    <div>
                        <InputLabel value="Fecha de entrega" />
                        <el-date-picker class="!w-full" v-model="form.date" type="datetime" placeholder="dd/mm/aa"
                            value-format="YYYY-MM-DD HH:mm:ss" format="DD/MM/YYYY hh:mm A" />
                        <InputError :message="form.errors.date" />
                    </div>
                    <div class="col-span-full">
                        <el-checkbox v-model="form.is_last_delivery" label="Última entrega" size="large" />
                    </div>
                    <div class="col-span-full">
                        <InputLabel value="Notas" />
                        <el-input v-model="form.notes" :rows="2" type="textarea"
                            placeholder="Notas de la parcialidad." />
                        <InputError :message="form.errors.notes" />
                    </div>
                </div>
            </template>
            <template #footer>
                <div class="flex justify-end space-x-2">
                    <CancelButton @click="showAddPackingPartialModal = false" :disabled="form.processing">
                        Cancelar
                    </CancelButton>
                    <PrimaryButton @click="addPackingPartial" :disabled="form.processing">
                        <i v-if="form.processing" class="fa-solid fa-circle-notch fa-spin mr-2"></i>
                        Registrar Parcialidad
                    </PrimaryButton>
                </div>
            </template>
        </DialogModal>

        <DialogModal :show="showInspectionRelease" @close="showInspectionRelease = false">
            <template #title>
                <h1 class="font-semibold">Inspección</h1>
            </template>
            <template #content>
                <div class="grid grid-cols-2 gap-x-3 gap-y-2">
                    <div class="col-span-full">
                        <InputLabel value="Tipo de entrega" />
                        <el-select v-model="form.production_close_type" @change="handleInspectionReleaseTypeChange"
                            placeholder="Seleccione">
                            <el-option v-for="item in ['Única', 'Parcialidades']" :key="item" :label="item"
                                :value="item" />
                        </el-select>
                        <InputError :message="form.errors.production_close_type" />
                    </div>

                    <template v-if="form.production_close_type === 'Única'">
                        <div>
                            <InputLabel value="Cantidad a entregar" />
                            <el-input-number v-model="form.quantity" :min="0" class="!w-full" />
                            <InputError :message="form.errors.quantity" />
                        </div>
                        <div>
                            <InputLabel value="Fecha de entrega" />
                            <el-date-picker class="!w-full" v-model="form.date" type="datetime"
                                placeholder="dd/mm/aa hh:mm" value-format="YYYY-MM-DD HH:mm:ss"
                                format="DD/MM/YYYY hh:mm A" />
                            <InputError :message="form.errors.date" />
                        </div>
                        <div>
                            <InputLabel value="Merma" />
                            <el-input-number v-model="form.scrap_quantity" :min="0" class="!w-full" />
                            <InputError :message="form.errors.scrap_quantity" />
                        </div>
                        <div>
                            <InputLabel value="Diferencia" />
                            <el-input-number v-model="form.shortage_quantity" :min="0" class="!w-full" />
                            <InputError :message="form.errors.shortage_quantity" />
                        </div>
                    </template>
                    <template v-else-if="form.production_close_type === 'Parcialidades'">
                        <div class="col-span-full bg-[#E9E9E9] py-2 px-4 rounded-full">
                            Parcialidad 1
                        </div>
                        <div>
                            <InputLabel value="Cantidad entregada" />
                            <el-input-number v-model="form.quantity" :min="0" class="!w-full" />
                            <InputError :message="form.errors.quantity" />
                        </div>
                        <div>
                            <InputLabel value="Fecha de entrega" />
                            <el-date-picker class="!w-full" v-model="form.date" type="datetime" placeholder="dd/mm/aa"
                                value-format="YYYY-MM-DD HH:mm:ss" format="DD/MM/YYYY hh:mm A" />
                            <InputError :message="form.errors.date" />
                        </div>
                    </template>

                    <div class="col-span-full">
                        <InputLabel value="Notas" />
                        <el-input v-model="form.notes" :rows="2" type="textarea"
                            placeholder="Específica la razón de la diferencia, la cantidad de merma o cualquier nota relacionada con la entrega." />
                        <InputError :message="form.errors.notes" />
                    </div>
                </div>
            </template>
            <template #footer>
                <div class="flex justify-end space-x-2">
                    <CancelButton @click="showInspectionRelease = false" :disabled="form.processing">
                        Cancelar
                    </CancelButton>
                    <PrimaryButton @click="inspectionRelease"
                        :disabled="form.processing || !form.production_close_type">
                        <i v-if="form.processing" class="fa-solid fa-circle-notch fa-spin mr-2"></i>
                        <span v-if="form.production_close_type === 'Única'">Cerrar calidad</span>
                        <span v-else>Registrar parcialidad</span>
                    </PrimaryButton>
                </div>
            </template>
        </DialogModal>

        <DialogModal :show="showAddPartial" @close="showAddPartial = false">
            <template #title>
                <h1 class="font-semibold">Registrar parcialidad</h1>
            </template>
            <template #content>
                <div class="grid grid-cols-2 gap-x-3 gap-y-2">
                    <div class="col-span-full bg-[#E9E9E9] py-2 px-4 rounded-full">
                        Parcialidad {{ selectedProduction.partials?.length ? selectedProduction.partials?.length + 1 : 1
                        }}
                    </div>
                    <div>
                        <InputLabel value="Cantidad entregada" />
                        <el-input-number v-model="form.quantity" placeholder="Ingresa la cantidad" :min="0"
                            class="!w-full" />
                        <InputError :message="form.errors.quantity" />
                    </div>
                    <div>
                        <InputLabel value="Fecha de entrega" />
                        <el-date-picker class="!w-full" v-model="form.date" type="datetime" placeholder="dd/mm/aa"
                            value-format="YYYY-MM-DD HH:mm:ss" format="DD/MM/YYYY hh:mm A" />
                        <InputError :message="form.errors.date" />
                    </div>
                    <div class="col-span-full">
                        <el-checkbox v-model="form.is_last_delivery" label="Última entrega" size="large" />
                    </div>
                    <div v-if="form.is_last_delivery">
                        <InputLabel value="Merma total en inspección" />
                        <el-input-number v-model="form.scrap_quantity" :min="0" class="!w-full" />
                        <InputError :message="form.errors.scrap_quantity" />
                    </div>
                    <div v-if="form.is_last_delivery">
                        <InputLabel value="Diferencia total en inspección" />
                        <el-input-number v-model="form.shortage_quantity" :min="0" class="!w-full" />
                        <InputError :message="form.errors.shortage_quantity" />
                    </div>
                    <div class="col-span-full">
                        <InputLabel value="Notas" />
                        <el-input v-model="form.notes" :rows="2" type="textarea"
                            placeholder="Específica la razón de la diferencia, la cantidad de merma o cualquier nota relacionada con la entrega." />
                        <InputError :message="form.errors.notes" />
                    </div>
                </div>
            </template>
            <template #footer>
                <div class="flex justify-end space-x-2">
                    <CancelButton @click="showAddPartial = false" :disabled="form.processing">
                        Cancelar
                    </CancelButton>
                    <PrimaryButton @click="addPartial" :disabled="form.processing">
                        <i v-if="form.processing" class="fa-solid fa-circle-notch fa-spin mr-2"></i>
                        Registrar parcialidad
                    </PrimaryButton>
                </div>
            </template>
        </DialogModal>

        <DialogModal :show="showReturnModal" @close="showReturnModal = false">
            <template #title>
                <h1 class="font-semibold">Regresar de estación</h1>
            </template>
            <template #content>
                <div>
                    <InputLabel value="Cantidad a regresar:" />
                    <el-input-number v-model="returnForm.quantity" placeholder="Ingresa la cantidad" :min="0"
                        class="!w-full" />
                    <InputError :message="returnForm.errors.quantity" />
                </div>
                <div class="col-span-full">
                    <InputLabel value="Motivo de regreso:" />
                    <el-input v-model="returnForm.reason" :rows="3" type="textarea"
                        placeholder="Escribe el motivo del regreso" />
                    <InputError :message="returnForm.errors.reason" />
                </div>
            </template>
            <template #footer>
                <div class="flex justify-end space-x-2">
                    <CancelButton @click="showReturnModal = false" :disabled="returnForm.processing">
                        Cancelar
                    </CancelButton>
                    <PrimaryButton @click="returnStation" :disabled="returnForm.processing">
                        <i v-if="returnForm.processing" class="fa-solid fa-circle-notch fa-spin mr-2"></i>
                        Continuar
                    </PrimaryButton>
                </div>
            </template>
        </DialogModal>

        <ConfirmationModal :show="showFinishedConfirmation"
            @close="showFinishedConfirmation = false; $refs.detailsModal.revertStation()">
            <template #title>
                <h1 class="font-semibold">Terminar orden de producción</h1>
            </template>
            <template #content>
                <p class="text-sm">
                    Al marcar como terminada una orden de producción, se actualizará su estado y no podrá ser editada.
                    <br>
                    ¿Estás seguro de que deseas finalizar esta orden de producción?
                </p>
            </template>
            <template #footer>
                <div class="flex justify-end space-x-2">
                    <PrimaryButton @click="finishProduction" :disabled="form.processing">
                        <i v-if="form.processing" class="fa-solid fa-circle-notch fa-spin mr-2"></i>
                        Si, continuar
                    </PrimaryButton>
                    <CancelButton @click="showFinishedConfirmation = false; $refs.detailsModal.revertStation()"
                        :disabled="form.processing">
                        Cancelar
                    </CancelButton>
                </div>
            </template>
        </ConfirmationModal>

        <ConfirmationModal :show="showConfirmation" @close="showConfirmation = false">
            <template #title>
                <h1 class="font-semibold">Eliminar orden de producción</h1>
            </template>
            <template #content>
                <p class="text-sm">¿Estás seguro de que deseas eliminar esta orden de producción?</p>
                <p class="text-sm text-red-500 mt-2">Esta acción no se puede deshacer.</p>
            </template>
            <template #footer>
                <div class="flex justify-end space-x-2">
                    <PrimaryButton @click="deleteProduction" :disabled="form.processing">
                        <i v-if="form.processing" class="fa-solid fa-circle-notch fa-spin mr-2"></i>
                        Eliminar
                    </PrimaryButton>
                    <CancelButton @click="showConfirmation = false" :disabled="form.processing">
                        Cancelar
                    </CancelButton>
                </div>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import DialogModal from '@/Components/DialogModal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import CancelButton from '@/Components/MyComponents/CancelButton.vue';
import FileUploader from '@/Components/MyComponents/FileUploader.vue';
import Loading from '@/Components/MyComponents/Loading.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useForm } from '@inertiajs/vue3';
import { stations } from '@/Data/stations';
import { format, parseISO } from 'date-fns';
import es from 'date-fns/locale/es';
// Import new partials
import ActionHeader from './Partials/ActionHeader.vue';
import ProductionTable from './Partials/ProductionTable.vue';
import ProductionDetailsModal from './Partials/ProductionDetailsModal.vue';
import { PlusIcon } from '@heroicons/vue/24/outline';

export default {
    name: 'ProductionList',
    components: {
        AppLayout,
        DialogModal,
        ConfirmationModal,
        PrimaryButton,
        CancelButton,
        Loading,
        InputLabel,
        InputError,
        FileUploader,
        PlusIcon,
        ActionHeader,
        ProductionTable,
        ProductionDetailsModal,
    },
    data() {
        const form = useForm({
            // campos generales
            quantity: 0,
            shortage_quantity: 0,
            scrap_quantity: 0,
            notes: '',
            date: null,
            is_last_delivery: false,
            // campos para produccion -> calidad
            production_close_type: null,
            close_production_date: null,
            close_quantity: 0,
            // campos para calidad -> inspeccion
            quality_released_date: null,
            quality_quantity: 0,
            // --- CAMPOS NUEVOS PARA EMPAQUES ---
            packing_received_quantity: 0,
            packing_received_date: null,
            packing_close_type: null,
            // campo para excel
            excel: [],
        });

        const returnForm = useForm({
            reason: '',
            quantity: '',
        });

        return {
            form,
            returnForm,
            productions: [],
            fetching: false,
            // modal visibility
            showDetails: false,
            showConfirmation: false,
            showReturnModal: false,
            showProductionRelease: false,
            showInspectionRelease: false,
            showQualityRelease: false,
            showMoveToPackingModal: false,
            showPackingReleaseModal: false,
            showAddPackingPartialModal: false,
            showExportFilters: false,
            showImportModal: false,
            showAddPartial: false,
            showFinishedConfirmation: false,
            // component state
            selectedProduction: null,
            searchTemp: null,
            search: null,
            updatingDetails: false,
            dateRange: null,
            season: 'Todas',
            station: 'Todos',
            currentPage: 1,
            prevTotal: 0,
            total: 0,
            pageSize: 30,
            stations: stations,
            machines: [],
            users: {},
            seasons: [
                'Todas', 'Amistad', 'Escolar', 'Fiestas patrias', 'Monarca',
                'Muestos', 'Navidad', 'Servicios', 'Toda ocasión', 'UUPZ',
            ]
        }
    },
    methods: {
        // --- Event Handlers from Children ---
        handleRowClick(row) {
            this.form.reset();
            this.selectedProduction = row;
            this.showDetails = true;
        },
        handleCommand(command) {
            const commandName = command.split('-')[0];
            const rowId = command.split('-')[1];

            if (commandName == 'clone') {
                this.clone(rowId);
            } else if (commandName == 'delete') {
                this.selectedProduction = this.productions.find(p => p.id == rowId);
                this.showConfirmation = true;
            } else if (commandName == 'viajera') {
                const url = this.route('productions.hoja-viajera', rowId);
                window.open(url, '_blank');
            } else if (commandName == 'report') {
                this.selectedProduction = this.productions.find(p => p.id == rowId);
                this.exportReportExcel(this.selectedProduction.folio);
            } else {
                this.$inertia.get(route('productions.' + commandName, rowId));
            }
        },
        async handleStationChange({ newStation, oldStation }) {
            // 1. Si el destino es "Empaques", mostrar modal de movimiento inicial.
            if (newStation === 'Empaques') {
                this.form.reset();
                this.form.packing_received_quantity = this.selectedProduction.quality_quantity ?? this.selectedProduction.close_quantity ?? this.selectedProduction.quantity;
                this.form.packing_received_date = format(new Date(), "yyyy-MM-dd HH:mm:ss");
                this.showMoveToPackingModal = true;
                this.showDetails = false;
                return;
            }
            // 2. Si el destino es "Calidad" (y no viene de Inspección), mostrar modal de cierre de producción.
            if (newStation === 'Calidad' && oldStation !== 'Inspección') {
                this.form.reset();
                this.form.close_quantity = this.selectedProduction.quantity;
                this.form.close_production_date = format(new Date(), "yyyy-MM-dd HH:mm:ss");
                this.showProductionRelease = true;
                this.showDetails = false;
                return;
            }
            // 3. Si el destino es "Inspección", mostrar modal de liberación de calidad.
            if (newStation === 'Inspección') {
                this.form.reset();
                this.form.quality_quantity = this.selectedProduction.close_quantity;
                this.form.quality_released_date = format(new Date(), "yyyy-MM-dd HH:mm:ss");
                this.showQualityRelease = true;
                this.showDetails = false;
                return;
            }
            // 4. Si es un regreso a estación anterior.
            if ((newStation === 'X Reproceso' && oldStation === 'Calidad') || (newStation === 'Calidad' && oldStation === 'Inspección')) {
                this.returnForm.quantity = oldStation === 'Calidad' ? this.selectedProduction.close_quantity : this.selectedProduction.quality_quantity;
                this.showReturnModal = true;
                this.showDetails = false;
                return;
            }
            // 5. Si es para finalizar la producción.
            if (newStation === 'Terminadas' && ['Inspección', 'Empaques', 'Empaques terminado'].includes(oldStation)) {
                this.showFinishedConfirmation = true;
                return;
            }
            // 6. Si es un cambio simple de estación.
            try {
                const response = await axios.put(route('productions.update-station', this.selectedProduction.id), { station: newStation });
                if (response.status === 200) {
                    this.$notify({ title: "Progreso actualizado", type: "success" });
                    await this.updateDetails();
                    this.showDetails = false;
                }
            } catch (error) {
                this.$notify({ title: 'Error', message: 'No se pudo actualizar el progreso', type: 'error' });
                console.error('Error updating station:', error);
                this.$refs.detailsModal.revertStation();
            }
        },
        async updateMachine(machineId) {
            try {
                const response = await axios.put(route('productions.update-machine', this.selectedProduction.id),
                    { machine_id: machineId });

                if (response.status === 200) {
                    this.$notify({ title: "Máquina cambiada", type: "success" });
                    this.updateDetails();
                }
            } catch (error) {
                console.error('Error updating machine:', error);
            }
        },
        // --- Data Fetching & Actions ---
        handleSearch() {
            if (this.searchTemp) {
                this.search = this.searchTemp;
                this.searchTemp = null;
                if (this.search) {
                    this.currentPage = 1;
                    this.fetchProductions();
                }
            }
        },
        handleTagClose() {
            this.search = null;
            this.currentPage = 1;
            this.fetchProductions();
        },
        handleCurrentChange() {
            this.fetchProductions();
        },
        deleteProduction() {
            this.form.delete(route('productions.destroy', this.selectedProduction.id), {
                onSuccess: () => {
                    this.showConfirmation = false;
                    this.$notify({ title: "Orden de producción eliminada", type: "success" });
                    this.fetchProductions();
                    this.selectedProduction = null;
                },
                onError: () => this.$notify({ title: "Error al eliminar la orden", type: "error" }),
            });
        },
        clone(rowId) {
            this.form.post(route('productions.clone', rowId), {
                onSuccess: () => this.$notify({ title: "Orden clonada", type: "success" }),
                onError: () => this.$notify({ title: "Error al clonar", type: "error" }),
            });
        },
        addPartial() {
            this.form.post(route('productions.add-partial', this.selectedProduction.id), {
                onSuccess: () => {
                    this.showAddPartial = false;
                    this.$notify({ title: "Parcialidad registrada", type: "success" });
                    this.updateDetails();
                },
                onError: () => this.$notify({ title: "Error al registrar", type: "error" }),
            });
        },
        addPackingPartial() {
            this.form.post(route('productions.add-packing-partial', this.selectedProduction.id), {
                onSuccess: () => {
                    this.showAddPackingPartialModal = false;
                    this.$notify({ title: "Parcialidad de empaque registrada", type: "success" });
                    this.updateDetails();
                },
                onError: () => this.$notify({ title: "Error al registrar", type: "error" }),
            });
        },
        handleInspectionReleaseTypeChange(newVal) {
            this.form.quantity = (newVal == 'Parcialidades') ? 0 : this.selectedProduction.quality_quantity;
            this.form.date = format(new Date(), "yyyy-MM-dd HH:mm:ss");
        },
        handlePackingReleaseTypeChange(newVal) {
            this.form.quantity = (newVal == 'Parcialidades') ? 0 : this.selectedProduction.packing_received_quantity;
            this.form.date = format(new Date(), "yyyy-MM-dd HH:mm:ss");
        },
        returnStation() {
            this.returnForm.post(route('productions.return-station', this.selectedProduction.id), {
                onSuccess: async () => {
                    this.showReturnModal = false;
                    this.$notify({ title: "Regreso a estación anterior", type: "success" });
                    this.updateDetails();
                    this.returnForm.reset();
                },
                onError: () => this.$notify({ title: "Error al regresar", type: "error" }),
            });
        },
        productionRelease() {
            this.form.post(route('productions.production-release', this.selectedProduction.id), {
                onSuccess: () => {
                    this.showProductionRelease = false;
                    this.$notify({ title: "Entrega registrada", type: "success" });
                    this.updateDetails();
                },
                onError: () => this.$notify({ title: "Error al registrar", type: "error" }),
            });
        },
        qualityReleased() {
            this.form.post(route('productions.quality-release', this.selectedProduction.id), {
                onSuccess: () => {
                    this.showQualityRelease = false;
                    this.$notify({ title: "Entrega registrada", type: "success" });
                    this.updateDetails();
                },
                onError: () => this.$notify({ title: "Error al registrar", type: "error" }),
            });
        },
        moveToPacking() {
            this.form.post(route('productions.move-to-packing', this.selectedProduction.id), {
                onSuccess: () => {
                    this.showMoveToPackingModal = false;
                    this.$notify({ title: "Movimiento registrado", type: "success" });
                    this.updateDetails();
                },
                onError: () => this.$notify({ title: "Error al registrar", type: "error" }),
            });
        },
        packingRelease() {
            this.form.post(route('productions.packing-release', this.selectedProduction.id), {
                onSuccess: () => {
                    this.showPackingReleaseModal = false;
                    this.$notify({ title: "Entrega registrada", type: "success" });
                    this.updateDetails();
                },
                onError: () => this.$notify({ title: "Error al registrar", type: "error" }),
            });
        },
        inspectionRelease() {
            this.form.post(route('productions.inspection-release', this.selectedProduction.id), {
                onSuccess: async () => {
                    this.showInspectionRelease = false;
                    this.$notify({ title: "Entrega registrada", type: "success" });
                    this.updateDetails();
                },
                onError: () => this.$notify({ title: "Error al registrar", type: "error" }),
            });
        },
        finishProduction() {
            this.form.post(route('productions.finish-production', this.selectedProduction.id), {
                onSuccess: () => {
                    this.showFinishedConfirmation = false
                    this.showDetails = false;
                    this.$notify({ title: "Orden Terminada", type: "success" });
                    this.updateDetails();
                },
                onError: () => this.$notify({ title: "Error al terminar la orden", type: "error" }),
            });
        },
        async updateDetails() {
            this.updatingDetails = true;
            await this.fetchProductions();
            const updatedProduction = this.productions.find(p => p.id == this.selectedProduction?.id);
            if (updatedProduction) {
                this.selectedProduction = updatedProduction;
            } else {
                this.showDetails = false;
            }
            this.updatingDetails = false;
            this.form.reset();
        },
        async fetchProductions() {
            this.fetching = true;
            try {
                const response = await axios.get(route('productions.get-by-page', { page: this.currentPage, search: this.search }));
                if (response.status === 200) {
                    this.productions = response.data.items;
                    if ((this.total != this.prevTotal) || this.currentPage == 1) {
                        this.total = response.data.total;
                    }
                    this.prevTotal = this.total;
                }
            } catch (error) {
                console.error(error);
            } finally {
                this.fetching = false;
            }
        },
        async fetchMachines() {
            try {
                const response = await axios.get(route('machines.get-all'));
                if (response.status === 200) {
                    this.machines = response.data.items;
                }
            } catch (error) {
                console.error(error);
            }
        },
        exportExcel() {
            const url = route('productions.export-excel', {
                startDate: this.dateRange[0],
                endDate: this.dateRange[1],
                station: this.station,
                season: this.season,
            });
            window.open(url, '_blank');
        },
        exportReportExcel(folio) {
            const url = route('productions.export-report-excel', { folio });
            window.open(url, '_blank');
        },
        importExcel() {
            this.form.post(route('productions.import-excel'), {
                onSuccess: () => {
                    this.$notify({ title: "Importación exitosa", type: "success" });
                    this.form.reset();
                    this.showImportModal = false;
                    this.fetchProductions();
                },
                onError: (err) => this.$notify({ title: "Error en importación", type: "error" }),
            });
        },
        startProcess() {
            this.$inertia.post(route('productions.station-process.start', this.selectedProduction.id), {}, {
                onSuccess: () => this.updateDetails(),
                onError: () => this.$notify({ title: "Error", message: "No se pudo iniciar el proceso", type: "error" }),
            });
        },
        pauseProcess(reason) {
            this.$inertia.post(route('productions.station-process.pause', this.selectedProduction.id), { reason }, {
                onSuccess: () => this.updateDetails(),
                onError: () => this.$notify({ title: "Error", message: "No se pudo pausar el proceso", type: "error" }),
            });
        },
        resumeProcess() {
            this.$inertia.post(route('productions.station-process.resume', this.selectedProduction.id), {}, {
                onSuccess: () => this.updateDetails(),
                onError: () => this.$notify({ title: "Error", message: "No se pudo reanudar el proceso", type: "error" }),
            });
        },
        finishAndMoveProcess(nextStation) {
            this.$inertia.post(route('productions.station-process.finishAndMove', this.selectedProduction.id), { next_station: nextStation }, {
                onSuccess: () => this.updateDetails(true), // close modal on success
                onError: () => this.$notify({ title: "Error", message: "No se pudo finalizar y mover", type: "error" }),
            });
        },
        skipAndMoveProcess(nextStation) {
            this.$inertia.post(route('productions.update-station', this.selectedProduction.id), { station: nextStation }, {
                onSuccess: () => this.updateDetails(true), // close modal on success
                onError: () => this.$notify({ title: "Error", message: "No se pudo mover la estación", type: "error" }),
            });
        },
        async updateDetails(closeOnSuccess = false) {
            this.updatingDetails = true;
            await this.fetchProductions();
            const updatedProduction = this.productions.find(p => p.id == this.selectedProduction.id);
            if (updatedProduction) {
                this.selectedProduction = updatedProduction;
                if (closeOnSuccess) {
                    this.showDetails = false;
                }
            } else {
                this.showDetails = false;
            }
            this.updatingDetails = false;
            this.form.reset();
        },
        async fetchUsers() {
            try {
                const response = await axios.get(route('users.get-all'));
                if (response.status === 200) {
                    // Convert array to object map for easy lookup
                    this.users = response.data.items.reduce((acc, user) => {
                        acc[user.id] = user.name;
                        return acc;
                    }, {});
                }
            } catch (error) {
                console.error('Error fetching users:', error);
            }
        },
    },
    mounted() {
        this.fetchMachines();
        this.fetchUsers();
        // this.getFilter();
        this.fetchProductions();
    }
}
</script>