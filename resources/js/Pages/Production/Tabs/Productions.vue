<template>
    <div class="min-h-[80vh]">
        <div>
            <h1 class="font-bold">Ordenes de producción</h1>
            <!-- Buscador -->
            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center mt-4 space-y-2 lg:space-y-0">
                <div class="lg:w-1/3 relative lg:mr-12">
                    <input v-model="searchTemp" @keyup.enter="handleSearch" class="input w-full pl-9"
                        placeholder="Buscar por folio, progreso, producto, cliente, temporada" type="search">
                    <i class="fa-solid fa-magnifying-glass text-xs text-gray99 absolute top-[10px] left-4"></i>
                </div>
                <el-dropdown split-button type="primary" @click="showImportModal = true" trigger="click">
                    Importar
                    <template #dropdown>
                        <el-dropdown-menu>
                            <el-dropdown-item @click="showExportFilters = true">Exportar</el-dropdown-item>
                        </el-dropdown-menu>
                    </template>
                </el-dropdown>
            </div>
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
                <el-table v-else :data="productions" @row-click="handleRowClick" max-height="670" style="width: 100%"
                    class="mt-1" :row-class-name="tableRowClassName">
                    <!-- <el-table-column type="selection" width="45" /> -->
                    <el-table-column prop="folio" label="N° Orden">
                        <template #default="scope">
                            <div v-if="!scope.row.quantity" class="flex items-center space-x-2"
                                :class="!scope.row.quantity ? 'text-red-600' : null">
                                <span>{{ scope.row.folio }}</span>
                                <el-tooltip content="Llenar información faltante" placement="top">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-4">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                    </svg>
                                </el-tooltip>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="product.name" label="Producto" />
                    <el-table-column prop="product.season" label="Temporada" />
                    <el-table-column prop="quantity" label="Cantidad solicitada">
                        <template #default="scope">
                            {{ scope.row.quantity?.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }} pzs
                        </template>
                    </el-table-column>
                    <el-table-column prop="client" label="Cliente" />
                    <el-table-column prop="station" label="Progreso" width="160">
                        <template #default="scope">
                            <div class="flex items-center space-x-2">
                                <div class="rounded-full size-6 flex items-center justify-center"
                                    :style="{ backgroundColor: stations.find(s => s.name === scope.row.station)?.light, color: stations.find(s => s.name === scope.row.station)?.dark }"
                                    v-html="stations.find(s => s.name === scope.row.station)?.icon"></div>
                                <p>{{ scope.row.station }}</p>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="current_quantity" label="Cantidad actual">
                        <template #default="scope">
                            {{ scope.row.current_quantity?.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }} pzs
                        </template>
                    </el-table-column>
                    <el-table-column prop="notes" label="Notas" width="160">
                        <template #default="scope">
                            <p class="truncate">{{ scope.row.notes }}</p>
                        </template>
                    </el-table-column>
                    <el-table-column prop="materials" label="Lista de material">
                        <template #default="scope">
                            <p class="truncate">{{ scope.row.materials?.join(', ') ?? 'Ninguna' }}</p>
                        </template>
                    </el-table-column>
                    <el-table-column prop="machine.name" label="Máquina" />
                    <el-table-column align="right">
                        <template #default="scope">
                            <el-dropdown trigger="click" @command="handleCommand">
                                <button @click.stop
                                    class="el-dropdown-link mr-3 justify-center items-center size-8 rounded-full text-primary hover:bg-gray2 transition-all duration-200 ease-in-out">
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </button>
                                <template #dropdown>
                                    <el-dropdown-menu>
                                        <el-dropdown-item class="!text-xs" :command="'viajera-' + scope.row.id">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="size-4 mr-2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" />
                                            </svg>
                                            Hoja viajera</el-dropdown-item>
                                        <el-dropdown-item class="!text-xs"
                                            v-if="$page.props.auth.user.permissions.includes('Editar producciones')"
                                            :command="'edit-' + scope.row.id">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="size-4 mr-2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                            </svg>
                                            Editar</el-dropdown-item>
                                        <el-dropdown-item class="!text-xs"
                                            v-if="$page.props.auth.user.permissions.includes('Crear producciones')"
                                            :command="'clone-' + scope.row.id">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="size-4 mr-2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                            </svg>
                                            Repetir</el-dropdown-item>
                                        <el-dropdown-item class="!text-xs"
                                            v-if="$page.props.auth.user.permissions.includes('Eliminar producciones')"
                                            :command="'delete-' + scope.row.id">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="size-4 mr-2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                            Eliminar
                                        </el-dropdown-item>
                                    </el-dropdown-menu>
                                </template>
                            </el-dropdown>
                        </template>
                    </el-table-column>
                </el-table>
            </div>
        </div>
    </div>
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
                        <el-option v-for="item in stations" :key="item.name" :label="item.name" :value="item.name" />
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
                    <span>Es importante cumplir con los siguientes formatos de fecha y en caso de cambiar alguno, avisar
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
                    <li>Si una producción contiene un producto y/o máquina que no existen en el sistema, se registrarán como "POR DEFINIR"</li>
                    <li>Si una producción no contiene producto y/o máquina, se registrarán como "POR DEFINIR"</li>
                    <li>Si una producción no contiene progreso se registrará como "NO ESPECIFICADO"</li>
                    <li>El progreso "Producto Terminado" se cambiará automáticamente a "Terminadas" por estándares del sistema</li>
                    <li>El progreso "X Material" se cambiará automáticamente a "Material pendiente" por estándares del sistema</li>
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

            <!-- <div class="mt-3">
                <a class="text-primary font-semibold" href="@/../../Layout_importar_producciones.xlsx"
                    download="Layout_importar_producciones.xlsx">
                    Descargar plantilla
                </a>
            </div> -->
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
    <DialogModal :show="showCloseProduction" @close="showCloseProduction = false">
        <template #title>
            <h1 class="font-semibold">Cerrar producción</h1>
        </template>
        <template #content>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <InputLabel value="Typo de entrega:" />
                    <el-select v-model="form.production_close_type">
                        <el-option v-for="item in ['Única', 'Parcialidades']" :key="item" :label="item" :value="item" />
                    </el-select>
                    <InputError :message="form.errors.production_close_type" />
                </div>
                <div v-if="form.production_close_type == 'Parcialidades'"
                    class="mt-3 bg-[#E9E9E9] py-2 px-4 rounded-full col-span-full">
                    Parcialidad 1
                </div>
                <div>
                    <InputLabel value="Cantidad entregada:" />
                    <el-input-number v-model="form.close_quantity" @change="handleSheet"
                        placeholder="Ingresa la cantidad" :min="0" class="!w-full" />
                    <InputError :message="form.errors.close_quantity" />
                </div>
                <div>
                    <InputLabel value="Fecha de cierre:" />
                    <el-date-picker class="!w-full" v-model="form.close_production_date" type="date"
                        placeholder="dd/mm/aa" value-format="YYYY-MM-DD" format="DD/MM/YYYY" />
                    <InputError :message="form.errors.close_production_date" />
                </div>
            </div>
        </template>
        <template #footer>
            <div class="flex justify-end space-x-2">
                <CancelButton @click="showCloseProduction = false" :disabled="form.processing">
                    Cancelar
                </CancelButton>
                <PrimaryButton @click="closeProduction" :disabled="form.processing">
                    <i v-if="form.processing" class="fa-solid fa-circle-notch fa-spin mr-2"></i>
                    Continuar
                </PrimaryButton>
            </div>
        </template>
    </DialogModal>
    <DialogModal :show="showQualityModal" @close="showQualityModal = false">
        <template #title>
            <h1 class="font-semibold">Liberado por calidad</h1>
        </template>
        <template #content>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <InputLabel value="Cantidad entregada:" />
                    <el-input-number v-model="form.quality_quantity" @change="handleSheet"
                        placeholder="Ingresa la cantidad" :min="0" class="!w-full" />
                    <InputError :message="form.errors.quality_quantity" />
                </div>
                <div>
                    <InputLabel value="Fecha de liberación:" />
                    <el-date-picker class="!w-full" v-model="form.quality_released_date" type="date"
                        placeholder="dd/mm/aa" value-format="YYYY-MM-DD" format="DD/MM/YYYY" />
                    <InputError :message="form.errors.quality_released_date" />
                </div>
            </div>
        </template>
        <template #footer>
            <div class="flex justify-end space-x-2">
                <CancelButton @click="showQualityModal = false" :disabled="form.processing">
                    Cancelar
                </CancelButton>
                <PrimaryButton @click="qualityReleased" :disabled="form.processing">
                    <i v-if="form.processing" class="fa-solid fa-circle-notch fa-spin mr-2"></i>
                    Continuar
                </PrimaryButton>
            </div>
        </template>
    </DialogModal>
    <DialogModal :show="showDetails" @close="showDetails = false">
        <template #title>
            <h1 class="font-semibold">Orden de producción</h1>
        </template>
        <template #content>
            <div class="flex items-center justify-between">
                <h2 class="text-[#666666] font-bold">Información de la orden</h2>
                <div class="flex items-center space-x-2">
                    <button v-if="$page.props.auth.user.permissions.includes('Editar producciones')"
                        @click="$inertia.visit(route('productions.hoja-viajera', selectedProduction.id))"
                        class="text-secondary border border-secondary rounded-md px-2 py-1">
                        Hoja viajera
                    </button>
                    <button v-if="$page.props.auth.user.permissions.includes('Editar producciones')"
                        @click="$inertia.visit(route('productions.edit', selectedProduction.id))"
                        class="text-primary border border-primary rounded-md px-2 py-1">
                        Editar
                    </button>
                </div>
            </div>
            <div class="text-sm grid grid-cols-3 gap-2 mt-3">
                <p class="text-[#464646]">N° de Orden:</p>
                <p class="col-span-2">{{ selectedProduction.folio }}</p>
                <p class="text-[#464646]">Fecha de inicio:</p>
                <p class="col-span-2">{{ formatDate(selectedProduction.start_date) }}</p>
                <p class="text-[#464646]">Fecha estimada de entrega:</p>
                <p class="col-span-2">{{ formatDate(selectedProduction.estimated_date) }}</p>
                <p class="text-[#464646]">Cliente:</p>
                <p class="col-span-2">{{ selectedProduction.client }}</p>
            </div>
            <h2 class="text-[#666666] font-bold mt-5">Información del producto</h2>
            <div class="text-sm grid grid-cols-3 gap-2 mt-3">
                <p class="text-[#464646]">Código del producto</p>
                <p class="col-span-2">{{ selectedProduction.product.code }}</p>
                <p class="text-[#464646]">Nombre del producto:</p>
                <p class="col-span-2">{{ selectedProduction.product.name }}</p>
                <p class="text-[#464646]">Temporada:</p>
                <p class="col-span-2">{{ selectedProduction.product.season }}</p>
                <p class="text-[#464646]">Descripción:</p>
                <p class="col-span-2">{{ selectedProduction.product.description ?? '-' }}</p>
                <p class="text-[#464646]">Cantidad solicitada:</p>
                <p class="col-span-2 font-bold">
                    {{ selectedProduction.quantity?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}
                </p>
                <p class="text-[#464646]">Lista de material:</p>
                <p class="col-span-2">{{ selectedProduction.materials?.join(', ') }}</p>
            </div>
            <h2 class="text-[#666666] font-bold mt-5">Proceso de producción</h2>
            <div class="text-sm grid grid-cols-3 gap-2 mt-3">
                <p class="text-[#464646]">Progreso:</p>
                <div class="col-span-2 relative">
                    <div class="absolute top-1 -left-8 rounded-full size-6 flex items-center justify-center"
                        :style="{ backgroundColor: stations.find(s => s.name === tempStation)?.light, color: stations.find(s => s.name === tempStation)?.dark }"
                        v-html="stations.find(s => s.name === tempStation)?.icon"></div>
                    <el-select @change="updateStation" v-model="tempStation" class="!w-2/3">
                        <el-option v-for="station in stations" :key="station" :label="station.name"
                            :value="station.name" />
                    </el-select>
                </div>
                <p class="text-[#464646]">Máquina:</p>
                <el-select @change="updateMachine" v-model="selectedProduction.machine.id" class="col-span-2 !w-2/3">
                    <el-option v-for="machine in machines" :key="machine.id" :label="machine.name"
                        :value="machine.id" />
                </el-select>
                <p class="text-[#464646]">Cantidad actual:</p>
                <p class="col-span-2">{{ selectedProduction.current_quantity }}</p>
                <p class="text-[#464646]">Notas:</p>
                <p class="col-span-2" style="white-space: pre-line;">{{ selectedProduction.notes ?? '-' }}</p>
            </div>
            <div v-if="selectedProduction.quality_released_date"
                class="bg-[#E9E9E9] py-3 px-3 rounded-[15px] grid grid-cols-2 gap-2 mt-3">
                <h2 class="font-bold col-span-full">Liberado por calidad</h2>
                <p>Fecha de liberación:</p>
                <p>{{ formatDate(selectedProduction.quality_released_date) }}</p>
                <p>Cantidad entregada:</p>
                <p>{{ selectedProduction.quality_quantity?.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}</p>
            </div>
            <div v-if="selectedProduction.close_production_date"
                class="bg-[#E9E9E9] py-3 px-3 rounded-[15px] grid grid-cols-2 gap-2 mt-3">
                <div class="flex items-center justify-between col-span-full">
                    <h2 class="font-bold">Inspección</h2>
                    <PrimaryButton
                        v-if="selectedProduction.station == 'Inspección'"
                        @click="showAddPartial = true">
                        Registrar parcialidad
                    </PrimaryButton>
                </div>
                <p>Tipo de entrega:</p>
                <p>{{ selectedProduction.production_close_type }}</p>
                <section v-for="(partial, index) in selectedProduction.partials" :key="index" class="col-span-full">
                    <div class="bg-white py-1 px-3 rounded-full mt-3">Parcialidad {{ index + 1 }}</div>
                    <div class="grid grid-cols-2 gap-2 mt-2">
                        <p>Fecha de entrega:</p>
                        <p>{{ formatDate(partial.date) }}</p>
                        <p>Cantidad entregada:</p>
                        <p>{{ partial.quantity?.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}</p>
                    </div>
                </section>
                <p v-if="selectedProduction.production_close_type != 'Parcialidades'">Fecha de entrega:</p>
                <p v-if="selectedProduction.production_close_type != 'Parcialidades'">{{
                    formatDate(selectedProduction.close_production_date) }}</p>
                <p class="pt-3 font-semibold">Cantidad total entregada:</p>
                <p class="pt-3 font-semibold">{{
                    selectedProduction.close_quantity?.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}</p>
                <p class="pt-3 font-semibold">Cantidad restante:</p>
                <p class="pt-3 font-semibold">{{
                    (selectedProduction.quantity - selectedProduction.close_quantity)?.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}</p>
            </div>
            <h2 class="text-[#666666] font-bold mt-5">Materiales y medidas</h2>
            <div class="text-sm grid grid-cols-3 gap-2 mt-3">
                <p class="text-[#464646]">Material:</p>
                <p class="col-span-2">{{ selectedProduction.material ?? '-' }}</p>
                <p class="text-[#464646]">Calibre:</p>
                <p class="col-span-2">{{ selectedProduction.gauge ?? '-' }}</p>
                <p class="text-[#464646]">Con barníz:</p>
                <p class="col-span-2">
                    <i v-if="selectedProduction.varnish_type" class="fa-solid fa-check text-[#059A05]"></i>
                    <i v-else class="fa-solid fa-xmark text-red-600"></i>
                </p>
                <p class="text-[#464646]">Tipo de barníz:</p>
                <p class="col-span-2">{{ selectedProduction.varnish_type ?? '-' }}</p>
                <p class="text-[#464646]">Dimensiones de la hoja:</p>
                <p class="col-span-2">{{ selectedProduction.width ?? '-' }} x {{ selectedProduction.large ?? '-' }}</p>
                <p class="text-[#464646]">Dimensiones de impresión:</p>
                <p class="col-span-2">{{ selectedProduction.dfi ?? '-' }}</p>
                <p class="text-[#464646]">Caras:</p>
                <p class="col-span-2">{{ selectedProduction.faces }}</p>
                <p class="text-[#464646]">Pz/H:</p>
                <p class="col-span-2">{{ selectedProduction.pps }}</p>
                <p class="text-[#464646]">Hojas:</p>
                <p class="col-span-2">{{ selectedProduction.sheets }}</p>
                <p class="text-[#464646]">Ajuste:</p>
                <p class="col-span-2">{{ selectedProduction.adjust }}</p>
                <p class="text-[#464646]">H/A:</p>
                <p class="col-span-2">{{ selectedProduction.ha }}</p>
                <p class="text-[#464646]">P/F:</p>
                <p class="col-span-2">{{ selectedProduction.pf }}</p>
                <p class="text-[#464646]">Total de hojas:</p>
                <p class="col-span-2">{{ selectedProduction.ts }}</p>
                <p class="text-[#464646]">Ta/Im:</p>
                <p class="col-span-2">{{ selectedProduction.ps }}</p>
                <p class="text-[#464646]">Total Ta/Im:</p>
                <p class="col-span-2">{{ selectedProduction.tps }}</p>
            </div>
        </template>
    </DialogModal>
    <DialogModal :show="showAddPartial" @close="showAddPartial = false">
        <template #title>
            <h1 class="font-semibold">Registrar parcialidad</h1>
        </template>
        <template #content>
            <div class="grid grid-cols-2 gap-3">
                <div class="col-span-full bg-[#E9E9E9] py-2 px-4 rounded-full">
                    Parcialidad {{ selectedProduction.partials.length + 1 }}
                </div>
                <div class="mt-3">
                    <InputLabel value="Cantidad entregada:" />
                    <el-input-number v-model="form.quantity" @change="handleSheet" placeholder="Ingresa la cantidad"
                        :min="0" class="!w-full" />
                    <InputError :message="form.errors.quantity" />
                </div>
                <div class="mt-3">
                    <InputLabel value="Fecha de entrega:" />
                    <el-date-picker class="!w-full" v-model="form.date" type="date" placeholder="dd/mm/aa"
                        value-format="YYYY-MM-DD" format="DD/MM/YYYY" />
                    <InputError :message="form.errors.date" />
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
                    Registrar
                </PrimaryButton>
            </div>
        </template>
    </DialogModal>
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
</template>

<script>
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import DialogModal from '@/Components/DialogModal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import CancelButton from '@/Components/MyComponents/CancelButton.vue';
import FileUploader from '@/Components/MyComponents/FileUploader.vue';
import Loading from '@/Components/MyComponents/Loading.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useForm } from '@inertiajs/vue3';
import { format, parseISO } from 'date-fns';
import es from 'date-fns/locale/es';

export default {
    name: 'ProductionList',
    data() {
        const form = useForm({
            production_close_type: 'Única',
            close_production_date: format(new Date(), "yyyy-MM-dd"), // Establece la fecha de hoy por defecto
            close_quantity: 0,
            quality_released_date: format(new Date(), "yyyy-MM-dd"), // Establece la fecha de hoy por defecto
            quality_quantity: 0,
            excel: [], // solo se usa en importación
            //parcialidades
            quantity: 0,
            date: format(new Date(), "yyyy-MM-dd"), // Establece la fecha de hoy por defecto,
        });

        return {
            form,
            productions: [],
            fetching: false,
            showDetails: false,
            showConfirmation: false,
            showCloseProduction: false,
            showQualityModal: false,
            showExportFilters: false,
            showImportModal: false,
            showAddPartial: false,
            selectedProduction: null,
            tempStation: null,
            searchTemp: null,
            search: null,
            sheets: null,
            ha: null,
            ts: null,
            ps: null,
            tps: null,
            //exportación
            dateRange: null,
            season: 'Todas',
            station: 'Todos',
            // paginación
            currentPage: 1,
            total: 0,
            pageSize: 30,
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
                    name: 'Liberado por calidad',
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
                    name: 'X Estampado',
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
            machines: [],
            seasons: [
                'Todas',
                'Amistad',
                'Escolar',
                'Fiestas patrias',
                'Monarca',
                'Muestos',
                'Navidad',
                'Servicios',
                'Toda ocasión',
                'UUPZ',
            ]
        }
    },
    components: {
        DialogModal,
        ConfirmationModal,
        PrimaryButton,
        CancelButton,
        Loading,
        InputLabel,
        InputError,
        FileUploader,
    },
    props: {
    },
    methods: {
        exportExcel() {
            const url = route('productions.export-excel', {
                startDate: this.dateRange[0],
                endDate: this.dateRange[1],
                station: this.station,
                season: this.season,
            });

            window.open(url, '_blank');
        },
        importExcel() {
            this.form.post(route('productions.import-excel'), {
                onSuccess: () => {
                    this.$notify({
                        title: "Órdenes de producción importadas",
                        type: "success",
                    });
                    this.form.reset();
                    this.showImportModal = false;
                    this.fetchProductions();
                },
                onError: (err) => {
                    console.log(err);
                    this.$notify({
                        title: "Error al importar las órdenes de producción",
                        type: "error",
                    });
                },
            });
        },
        formatDate(dateString) {
            if (!dateString) return '-';
            return format(parseISO(dateString), 'EEE, dd MMMM yyyy', { locale: es });
        },
        formatDateTime(dateString) {
            if (!dateString) return '-';
            return format(parseISO(dateString), 'EEE, dd MMMM yyyy hh:mm a', { locale: es });
        },
        handleChangeFilter() {
            this.handleTagClose();
            if (this.filter) {
                this.fetchFiltered();
            }
        },
        handleCurrentChange() {
            this.fetchProductions();
        },
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

            // quitar de url la variable filter
            const currentURL = new URL(window.location.href);
            currentURL.searchParams.delete('filter');
            window.history.replaceState({}, document.title, currentURL);

            this.fetchProductions();
        },
        handleRowClick(row) {
            this.showDetails = true;
            this.selectedProduction = row;
            this.tempStation = row.station;
        },
        tableRowClassName({ row, rowIndex }) {
            return 'cursor-pointer text-xs';
        },
        handleCommand(command) {
            const commandName = command.split('-')[0];
            const rowId = command.split('-')[1];

            if (commandName == 'clone') {
                this.clone(rowId);
            } else if (commandName == 'delete') {
                this.showConfirmation = true;
                this.selectedProduction = this.productions.find(p => p.id == rowId);
            } else if (commandName == 'viajera') {
                const url = this.route('productions.hoja-viajera', rowId);
                window.open(url, '_blank');
                // this.$inertia.get(route('productions.hoja-viajera', rowId));
            } else {
                this.$inertia.get(route('productions.' + commandName, rowId));
            }
        },
        getFilter() {
            // Obtener la URL actual
            const currentURL = new URL(window.location.href);
            // Extraer el valor de 'filter' de los parámetros de búsqueda
            const filterFromURL = currentURL.searchParams.get('filter');

            if (filterFromURL) {
                this.searchTemp = filterFromURL;
                this.handleSearch();
            } else {
                this.fetchProductions();
            }
        },
        deleteProduction() {
            this.form.delete(route('productions.destroy', this.selectedProduction.id), {
                onSuccess: () => {
                    this.showConfirmation = false;
                    this.$notify({
                        title: "Orden de producción eliminada",
                        type: "success",
                    });
                    this.fetchProductions();
                    this.selectedProduction = null;
                },
                onError: () => {
                    this.$notify({
                        title: "Error al eliminar la orden de producción",
                        type: "error",
                    });
                },
            });
        },
        clone(rowId) {
            this.form.post(route('productions.clone', rowId), {
                onSuccess: () => {
                    this.$notify({
                        title: "Orden de producción clonada. Edita lo que necesites",
                        type: "success",
                    });
                },
                onError: () => {
                    this.$notify({
                        title: "Error al clonar la orden de producción",
                        type: "error",
                    });
                },
            });
        },
        addPartial() {
            this.form.post(route('productions.add-partial', this.selectedProduction.id), {
                onSuccess: () => {
                    this.showAddPartial = false;
                    this.$notify({
                        title: "Parcialidad registrada",
                        type: "success",
                    });
                    // ageregar date y quantity nueva parcialidad
                    this.selectedProduction.partials.push({ date: this.form.date, quantity: this.form.quantity });
                    this.selectedProduction.close_quantity += this.form.quantity;
                    this.form.reset();
                },
                onError: () => {
                    this.$notify({
                        title: "Error al registrar parcialidad",
                        type: "error",
                    });
                },
            });
        },
        closeProduction() {
            this.form.post(route('productions.close', this.selectedProduction.id), {
                onSuccess: () => {
                    this.showCloseProduction = false;
                    this.$notify({
                        title: "Orden de producción cerrada",
                        type: "success",
                    });
                    this.selectedProduction.station = this.tempStation;
                    this.selectedProduction.close_production_date = new Date().toISOString();
                },
                onError: () => {
                    this.$notify({
                        title: "Error al cerrar la orden de producción",
                        type: "error",
                    });
                },
            });
        },
        qualityReleased() {
            this.form.post(route('productions.quality-release', this.selectedProduction.id), {
                onSuccess: () => {
                    this.showQualityModal = false;
                    this.$notify({
                        title: "Orden de producción liberada por calidad",
                        type: "success",
                    });
                    this.selectedProduction.station = this.tempStation;
                    this.selectedProduction.close_production_date = new Date().toISOString();
                },
                onError: () => {
                    this.$notify({
                        title: "Error al liberar la orden de producción",
                        type: "error",
                    });
                },
            });
        },
        async updateStation() {
            if (this.tempStation == 'Inspección') {
                this.showCloseProduction = true;
                this.showDetails = false;
            } else if (this.tempStation == 'Liberado por calidad') {
                this.showQualityModal = true;
                this.showDetails = false;
            } else {
                try {
                    const response = await axios.put(route('productions.update-station', this.selectedProduction.id),
                        { station: this.tempStation });
                    if (response.status === 200) {
                        this.$notify({
                            title: "Progreso actualizado",
                            type: "success",
                        });
                        this.selectedProduction.station = this.tempStation;
                    }
                } catch (error) {
                    console.error('Error updating station:', error);
                }
            }
        },
        async updateMachine() {
            try {
                const response = await axios.put(route('productions.update-machine', this.selectedProduction.id),
                    { machine_id: this.selectedProduction.machine.id });

                if (response.status === 200) {
                    const machineName = this.machines.find(m => m.id == this.selectedProduction.machine.id)?.name;
                    this.selectedProduction.machine.name = machineName;
                    this.$notify({
                        title: "Máquina cambiada",
                        type: "success",
                    });
                }
            } catch (error) {
                console.error('Error updating machine:', error);
            }
        },
        async fetchProductions() {
            this.fetching = true;
            try {
                const response = await axios.get(route('productions.get-by-page', { page: this.currentPage, search: this.search }));

                if (response.status === 200) {
                    this.productions = response.data.items;
                    this.total = response.data.total;
                }
            } catch (error) {
                console.error('Error fetching productions:', error);
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
                console.error('Error fetching machines:', error);
            }
        },
    },
    mounted() {
        this.fetchMachines();
        this.getFilter();
    }
}
</script>