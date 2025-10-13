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
                            {{ scope.row.quantity?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }} pzs
                        </template>
                    </el-table-column>
                    <el-table-column prop="client" label="Cliente" />
                    <el-table-column prop="station" label="Progreso" width="160">
                        <template #default="scope">
                            <div class="flex items-center space-x-2">
                                <div class="rounded-full size-6 flex items-center justify-center"
                                    :style="{ backgroundColor: stations.find(s => s.name === scope.row.station)?.light, color: stations.find(s => s.name === scope.row.station)?.dark }">
                                    <component :is="stations.find(s => s.name === scope.row.station)?.icon" class="size-4" />
                                </div>
                                <p>{{ scope.row.station }}</p>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="current_quantity" label="Cantidad actual">
                        <template #default="scope">
                            {{ scope.row.current_quantity?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }} pzs
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
                                        <el-dropdown-item class="!text-xs" :command="'report-' + scope.row.id">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="size-4 mr-2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 0 1-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0 1 12 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m17.25-3.75h-7.5c-.621 0-1.125.504-1.125 1.125m8.625-1.125c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5M12 10.875v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 10.875c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125M13.125 12h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125M20.625 12c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5M12 14.625v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 14.625c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125m0 1.5v-1.5m0 0c0-.621.504-1.125 1.125-1.125m0 0h7.5" />
                                            </svg>
                                            Reporte de entregas</el-dropdown-item>
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
                    <li>Si una producción contiene un producto y/o máquina que no existen en el sistema, se registrarán
                        como "POR DEFINIR"</li>
                    <li>Si una producción no contiene producto y/o máquina, se registrarán como "POR DEFINIR"</li>
                    <li>Si una producción no contiene progreso se registrará como "NO ESPECIFICADO"</li>
                    <li>El progreso "Producto Terminado" se cambiará automáticamente a "Terminadas" por estándares del
                        sistema</li>
                    <li>El progreso "X Material" se cambiará automáticamente a "Material pendiente" por estándares del
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
    <DialogModal :show="showDetails" @close="showDetails = false">
        <template #title>
            <h1 class="font-semibold">Orden de producción</h1>
            <div class="flex items-center space-x-2">
                <button v-if="$page.props.auth.user.permissions.includes('Editar producciones')"
                    @click="$inertia.visit(route('productions.hoja-viajera', selectedProduction.id))"
                    class="text-sm text-[#EC4899] border border-[#EC4899] rounded-md px-2 py-1 hover:bg-[#FCE7F3] transition-all">
                    Hoja viajera
                </button>
                <button v-if="$page.props.auth.user.permissions.includes('Editar producciones')"
                    @click="$inertia.visit(route('productions.edit', selectedProduction.id))"
                    class="text-sm text-gray-600 border border-gray-400 rounded-md px-2 py-1 hover:bg-gray-200 transition-all">
                    Editar
                </button>
            </div>
        </template>
        <template #content>
            <div v-if="updatingDetails" class="my-60">
                <Loading />
            </div>
            <div v-else class="text-sm">
                <!-- ------------ Información de la orden ------------ -->
                <h2 class="text-[#666666] font-bold">Información de la orden</h2>
                <div class="grid grid-cols-3 gap-x-2 gap-y-1 mt-2">
                    <p class="text-[#464646]">N° de Orden:</p>
                    <p class="col-span-2 font-semibold">{{ selectedProduction.folio }}</p>

                    <p class="text-[#464646]">Fecha de emisión:</p>
                    <p class="col-span-2">{{ formatDate(selectedProduction.start_date) }}</p>

                    <p class="text-[#464646]">Fecha estimada de entrega:</p>
                    <p class="col-span-2">{{ formatDate(selectedProduction.estimated_date) }}</p>

                    <p class="text-[#464646]">Cliente:</p>
                    <p class="col-span-2">{{ selectedProduction.client }}</p>
                </div>

                <!-- ------------ Información del producto ------------ -->
                <h2 class="text-[#666666] font-bold mt-5">Información del producto</h2>
                <div class="grid grid-cols-3 gap-x-2 gap-y-1 mt-2">
                    <p class="text-[#464646]">Código del producto</p>
                    <p class="col-span-2">{{ selectedProduction.product.code }}</p>

                    <p class="text-[#464646]">Nombre del producto:</p>
                    <p class="col-span-2">{{ selectedProduction.product.name }}</p>

                    <p class="text-[#464646]">Temporada:</p>
                    <p class="col-span-2">{{ selectedProduction.product.season }}</p>

                    <p class="text-[#464646]">Descripción:</p>
                    <p class="col-span-2">{{ selectedProduction.product.description ?? '-' }}</p>

                    <p class="text-[#464646]">Cantidad solicitada:</p>
                    <p class="col-span-2 font-bold text-base">
                        {{ selectedProduction.quantity?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}
                    </p>

                    <p class="text-[#464646]">Lista de material:</p>
                    <p class="col-span-2">{{ selectedProduction.materials?.join(', ') }}</p>
                </div>

                <!-- ------------ Proceso de producción ------------ -->
                <h2 class="text-[#666666] font-bold mt-5">Proceso de producción</h2>
                <div class="grid grid-cols-3 gap-x-2 gap-y-1 mt-2 items-center">
                    <p class="text-[#464646]">Progreso:</p>
                    <div class="col-span-2 flex items-center">
                        <el-select @change="updateStation" v-model="tempStation" class="!w-2/3"
                            :disabled="tempStation === 'Terminadas'">
                            <el-option v-for="station in filteredStations" :key="station.name" :label="station.name"
                                :value="station.name" />
                        </el-select>
                        <div class="rounded-full size-6 flex items-center justify-center ml-2"
                            :style="{ backgroundColor: stations.find(s => s.name === tempStation)?.light, color: stations.find(s => s.name === tempStation)?.dark }">
                            <component :is="stations.find(s => s.name === tempStation)?.icon" class="size-4" />
                        </div>
                    </div>

                    <p class="text-[#464646]">Máquina:</p>
                    <el-select @change="updateMachine" v-model="selectedProduction.machine.id"
                        class="col-span-2 !w-2/3">
                        <el-option v-for="machine in machines" :key="machine.id" :label="machine.name"
                            :value="machine.id" />
                    </el-select>

                    <p class="text-[#464646]">Notas:</p>
                    <p class="col-span-2" style="white-space: pre-line;">{{ selectedProduction.notes ?? '-' }}</p>
                </div>

                <!-- ------------ Devoluciones ------------ -->
                <div v-if="selectedProduction.returns?.length" class="bg-[#F3F4F6] py-3 px-4 rounded-lg mt-5">
                    <h2 class="font-bold text-gray-700">Devoluciones</h2>
                    <div v-for="(item, index) in selectedProduction.returns" :key="index" class="mt-2">
                        <div class="grid grid-cols-3 gap-x-2 gap-y-1">
                            <p class="text-[#464646]">Cambio de estación:</p>
                            <p class="col-span-2">
                                {{ item.old_station }} <i class="fa-solid fa-arrow-right mx-1 text-xs"></i> {{
                                    item.new_station }}
                            </p>
                            <p class="text-[#464646]">Fecha:</p>
                            <p class="col-span-2">{{ formatDateTime(item.date) }}</p>
                            <p class="text-[#464646]">Cantidad:</p>
                            <p class="col-span-2">{{ item.quantity?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}
                            </p>
                            <p class="text-[#464646]">Motivo:</p>
                            <p class="col-span-2" style="white-space: pre-line;">{{ item.reason }}</p>
                            <p class="text-[#464646]">Usuario:</p>
                            <p class="col-span-2">{{ item.user }}</p>
                        </div>
                        <hr v-if="index < selectedProduction.returns.length - 1"
                            class="col-span-full my-2 border-gray-300" />
                    </div>
                </div>

                <!-- ------------ Liberado por Producción ------------ -->
                <div v-if="selectedProduction.close_production_date" class="bg-[#F3F4F6] py-3 px-4 rounded-lg mt-5">
                    <h2 class="font-bold text-gray-700">Liberado por producción</h2>
                    <div class="grid grid-cols-3 gap-x-2 gap-y-1 mt-2">
                        <p class="text-[#464646]">Fecha de liberación:</p>
                        <p class="col-span-2">{{ formatDateTime(selectedProduction.close_production_date) }}</p>

                        <p class="text-[#464646]">Cantidad entregada:</p>
                        <p class="col-span-2">{{
                            selectedProduction.close_quantity?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}</p>
                    </div>
                </div>

                <!-- ------------ Liberado por Calidad ------------ -->
                <div v-if="selectedProduction.quality_released_date" class="bg-[#F3F4F6] py-3 px-4 rounded-lg mt-5">
                    <h2 class="font-bold text-gray-700">Liberado por calidad</h2>
                    <div class="grid grid-cols-3 gap-x-2 gap-y-1 mt-2">
                        <p class="text-[#464646]">Fecha de liberación:</p>
                        <p class="col-span-2">{{ formatDateTime(selectedProduction.quality_released_date) }}</p>

                        <p class="text-[#464646]">Cantidad entregada:</p>
                        <p class="col-span-2">{{
                            selectedProduction.quality_quantity?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}</p>

                        <p class="text-[#464646]">Merma:</p>
                        <p class="col-span-2">{{
                            (selectedProduction.quality_scrap)?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}
                            ({{ getScrapPercentage(selectedProduction.quality_scrap, selectedProduction.close_quantity)
                            }}% de los {{ selectedProduction.close_quantity }} recibidos)</p>

                        <p class="text-[#464646]">Diferencia:</p>
                        <p class="col-span-2">
                            {{
                                parseFloat(selectedProduction.quality_shortage)?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g,
                                    ",") }}
                        </p>

                        <p class="text-[#464646]">Notas:</p>
                        <p class="col-span-2" style="white-space: pre-line;">{{ selectedProduction.quality_notes ?? '-'
                            }}</p>
                    </div>
                </div>

                <!-- ------------ Inspección ------------ -->
                <div v-if="selectedProduction.station == 'Inspección' || selectedProduction.production_close_type"
                    class="bg-[#F3F4F6] py-3 px-4 rounded-lg mt-5">
                    <div class="flex items-center justify-between">
                        <h2 class="font-bold text-gray-700">Inspección</h2>
                        <PrimaryButton v-if="!selectedProduction.production_close_type"
                            @click="showInspectionRelease = true">
                            Registrar entrega
                        </PrimaryButton>
                        <PrimaryButton
                            v-else-if="selectedProduction.production_close_type == 'Parcialidades' && selectedProduction.station != 'Terminadas'"
                            @click="showAddPartial = true">
                            Registrar parcialidad
                        </PrimaryButton>
                    </div>

                    <div v-if="selectedProduction.production_close_type" class="grid grid-cols-3 gap-x-2 gap-y-1 mt-3">
                        <p class="text-[#464646]">Tipo de entrega:</p>
                        <p class="col-span-2">{{ selectedProduction.production_close_type }}</p>

                        <div v-if="selectedProduction.production_close_type != 'Parcialidades'"
                            class="grid grid-cols-3 gap-x-2 gap-y-1 col-span-full">
                            <div class="col-span-full contents">
                                <p class="text-[#464646]">Fecha de entrega:</p>
                                <p class="col-span-2">{{ formatDateTime(selectedProduction.finish_date) }}</p>
                            </div>
                            <p class="text-[#464646]">Cantidad entregada:</p>
                            <p class="col-span-2">{{
                                selectedProduction.current_quantity?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}
                            </p>

                            <p class="text-[#464646]">Merma:</p>
                            <p class="col-span-2">{{
                                (selectedProduction.inspection_scrap)?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",")
                            }}
                                ({{ getScrapPercentage(selectedProduction.inspection_scrap,
                                    selectedProduction.quality_quantity)
                                }}% de los {{ selectedProduction.quality_quantity }} recibidos)</p>

                            <p class="text-[#464646]">Diferencia:</p>
                            <p class="col-span-2">
                                {{
                                    parseFloat(selectedProduction.inspection_shortage)?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g,
                                        ",") }}
                            </p>

                            <p class="text-[#464646]">Notas:</p>
                            <p class="col-span-2" style="white-space: pre-line;">{{ selectedProduction.inspection_notes
                                ??
                                '-'
                            }}</p>
                        </div>
                    </div>

                    <section v-for="(partial, index) in selectedProduction.partials" :key="index" class="mt-3">
                        <div class="bg-white py-1 px-3 rounded-full flex justify-between items-center">
                            <span>Parcialidad {{ index + 1 }}</span>
                            <span v-if="partial.is_last_delivery" class="text-xs text-gray-500">(Última entrega)</span>
                        </div>
                        <div class="grid grid-cols-3 gap-x-2 gap-y-1 mt-2">
                            <p class="text-[#464646]">Fecha de entrega:</p>
                            <p class="col-span-2">{{ formatDateTime(partial.date) }}</p>

                            <p class="text-[#464646]">Cantidad entregada:</p>
                            <p class="col-span-2">{{ partial.quantity?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",")
                                }}</p>

                            <p class="text-[#464646]">Notas:</p>
                            <p class="col-span-2">{{ partial.notes ?? '-' }}</p>
                        </div>
                    </section>
                    <div v-if="selectedProduction.partials?.length"
                        class="grid grid-cols-3 gap-x-2 gap-y-1 pt-2 mt-2 border-t border-gray-300">
                        <p class="font-semibold text-[#464646]">Cantidad total entregada:</p>
                        <p class="col-span-2 font-semibold">{{
                            selectedProduction.current_quantity?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}</p>

                        <p class="font-semibold text-[#464646]">Cantidad restante:</p>
                        <p class="col-span-2 font-semibold">{{ (selectedProduction.quantity -
                            selectedProduction.current_quantity)?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}
                        </p>

                        <p class="text-[#464646]">Merma:</p>
                        <p class="col-span-2">{{
                            (selectedProduction.inspection_scrap)?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}
                            ({{ getScrapPercentage(selectedProduction.inspection_scrap,
                                selectedProduction.quality_quantity)
                            }}% de los {{ selectedProduction.quality_quantity }} recibidos)</p>

                        <p class="text-[#464646]">Diferencia:</p>
                        <p class="col-span-2">
                            {{
                                parseFloat(selectedProduction.inspection_shortage)?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g,
                                    ",") }}
                        </p>
                    </div>
                </div>

                <!-- ------------ Materiales y medidas ------------ -->
                <h2 class="text-[#666666] font-bold mt-5">Materiales y medidas</h2>
                <div class="border rounded-lg p-4 mt-2">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-1">
                        <!-- Columna Izquierda -->
                        <div class="grid grid-cols-2 *:border-b *:py-px">
                            <p class="text-[#464646]">Material:</p>
                            <p>{{ selectedProduction.material ?? '-' }}</p>
                            <p class="text-[#464646]">Con Barníz:</p>
                            <p>
                                <i v-if="selectedProduction.varnish_type" class="fa-solid fa-check text-green-500"></i>
                                <i v-else class="fa-solid fa-xmark text-red-500"></i>
                            </p>
                            <p class="text-[#464646]">Tipo de barníz:</p>
                            <p>{{ selectedProduction.varnish_type ?? '-' }}</p>
                            <p class="text-[#464646]">Dimensiones de la hoja:</p>
                            <p>{{ selectedProduction.width ?? '-' }} x {{ selectedProduction.large ?? '-' }}</p>
                            <p class="text-[#464646]">Dimensiones de impresión:</p>
                            <p>{{ selectedProduction.dfi ?? '-' }}</p>
                            <p class="text-[#464646]">Caras:</p>
                            <p>{{ selectedProduction.faces }}</p>
                            <p class="text-[#464646]">Pz/H:</p>
                            <p>{{ selectedProduction.pps }}</p>
                            <p class="text-[#464646]">Calibre:</p>
                            <p>{{ selectedProduction.gauge ?? '-' }}</p>
                        </div>
                        <!-- Columna Derecha -->
                        <div class="grid grid-cols-2 *:border-b *:py-px">
                            <p class="text-[#464646]">Hojas:</p>
                            <p>{{ selectedProduction.sheets }}</p>
                            <p class="text-[#464646]">Ajuste:</p>
                            <p>{{ selectedProduction.adjust }}</p>
                            <p class="text-[#464646]">H/A:</p>
                            <p>{{ selectedProduction.ha }}</p>
                            <p class="text-[#464646]">P/F:</p>
                            <p>{{ selectedProduction.pf }}</p>
                            <p class="text-[#464646]">Total de hojas:</p>
                            <p>{{ selectedProduction.ts }}</p>
                            <p class="text-[#464646]">Ta/Im:</p>
                            <p>{{ selectedProduction.ps }}</p>
                            <p class="text-[#464646]">Total Ta/Im:</p>
                            <p>{{ selectedProduction.tps }}</p>
                        </div>
                    </div>
                </div>
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
                        placeholder="dd/mm/aa hh:mm" value-format="YYYY-MM-DD HH:mm:ss" format="DD/MM/YYYY hh:mm A" />
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
                        placeholder="dd/mm/aa hh:mm" value-format="YYYY-MM-DD HH:mm:ss" format="DD/MM/YYYY hh:mm A" />
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
                        <el-option v-for="item in ['Única', 'Parcialidades']" :key="item" :label="item" :value="item" />
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
                        <el-date-picker class="!w-full" v-model="form.date" type="datetime" placeholder="dd/mm/aa hh:mm"
                            value-format="YYYY-MM-DD HH:mm:ss" format="DD/MM/YYYY hh:mm A" />
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
                <PrimaryButton @click="inspectionRelease" :disabled="form.processing || !form.production_close_type">
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
                    Parcialidad {{ selectedProduction.partials?.length ? selectedProduction.partials?.length + 1 : 1 }}
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
        @close="showFinishedConfirmation = false; tempStation = currentStation">
        <template #title>
            <h1 class="font-semibold">Terminar orden de producción</h1>
        </template>
        <template #content>
            <p class="text-sm">
                Al marcar como terminada una orden de producción, se actualizará su estado y no podrá ser editada. <br>
                ¿Estás seguro de que deseas finalizar esta orden de producción?
            </p>
        </template>
        <template #footer>
            <div class="flex justify-end space-x-2">
                <PrimaryButton @click="finishProduction" :disabled="form.processing">
                    <i v-if="form.processing" class="fa-solid fa-circle-notch fa-spin mr-2"></i>
                    Si, continuar
                </PrimaryButton>
                <CancelButton @click="showFinishedConfirmation = false; tempStation = currentStation"
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
import { stations } from '@/Data/stations';
import { format, parseISO } from 'date-fns';
import es from 'date-fns/locale/es';

export default {
    name: 'ProductionList',
    data() {
        const form = useForm({
            production_close_type: null,
            close_production_date: null,
            close_quantity: 0,
            quality_released_date: null,
            quality_quantity: 0,
            excel: [],
            quantity: 0, // para parcialidades
            shortage_quantity: 0,
            scrap_quantity: 0,
            notes: '',
            date: null, // para parcialidades
            is_last_delivery: false, // para parcialidades
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
            showDetails: false,
            showConfirmation: false,
            showReturnModal: false,
            showProductionRelease: false,
            showInspectionRelease: false,
            showQualityRelease: false,
            showExportFilters: false,
            showImportModal: false,
            showAddPartial: false,
            showFinishedConfirmation: false,
            selectedProduction: null,
            tempStation: null,
            currentStation: null,
            searchTemp: null,
            search: null,
            updatingDetails: false,
            //exportación
            dateRange: null,
            season: 'Todas',
            station: 'Todos',
            // paginación
            currentPage: 1,
            prevTotal: 0,
            total: 0,
            pageSize: 30,
            stations: stations,
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
    computed: {
        totalScrap() {
            if (!this.selectedProduction) return 0;
            return (this.selectedProduction.production_scrap ?? 0) +
                (this.selectedProduction.quality_scrap ?? 0) +
                (this.selectedProduction.inspection_scrap ?? 0);
        },
        totalShortage() {
            if (!this.selectedProduction) return 0;
            return (this.selectedProduction.production_shortage ?? 0) +
                (this.selectedProduction.quality_shortage ?? 0) +
                (this.selectedProduction.inspection_shortage ?? 0);
        },
        filteredStations() {
            if (this.currentStation === 'Terminadas' || this.currentStation === 'Empaques terminado') {
                return [];
            }

            if (this.currentStation === 'Inspección') {
                return this.stations.filter(station =>
                    station.name === 'Calidad' || station.name === 'Terminadas'
                );
            }

            if (this.currentStation === 'Calidad') {
                return this.stations.filter(station =>
                    station.name === 'X Reproceso' || station.name === 'Inspección' || station.name === 'Empaques'
                );
            }

            if (this.currentStation === 'Empaques') {
                return this.stations.filter(station =>
                    station.name === 'Empaques terminado'
                );
            }

            return this.stations.filter(station => station.name !== 'Inspección' && station.name !== 'X Reproceso');
        }
    },
    methods: {
        getScrapPercentage(scrap, total) {
            if (!total || !scrap) return '0.0';
            return ((scrap / total) * 100).toFixed(1);
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
            return format(parseISO(dateString), 'dd MMMM yyyy', { locale: es });
        },
        formatDateTime(dateString) {
            if (!dateString) return '-';
            return format(parseISO(dateString), 'dd MMMM yyyy hh:mm a', { locale: es });
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

            const currentURL = new URL(window.location.href);
            currentURL.searchParams.delete('filter');
            window.history.replaceState({}, document.title, currentURL);

            this.fetchProductions();
        },
        handleRowClick(row) {
            this.form.reset();
            this.showDetails = true;
            this.selectedProduction = row;
            this.tempStation = row.station;
            this.currentStation = row.station;
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
            } else if (commandName == 'report') {
                this.selectedProduction = this.productions.find(p => p.id == rowId);
                this.exportReportExcel(this.selectedProduction.folio);
            } else {
                this.$inertia.get(route('productions.' + commandName, rowId));
            }
        },
        getFilter() {
            const currentURL = new URL(window.location.href);
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
                    this.updateDetails();
                },
                onError: () => {
                    this.$notify({
                        title: "Error al registrar parcialidad",
                        type: "error",
                    });
                },
            });
        },
        handleInspectionReleaseTypeChange(newVal) {
            if (newVal == 'Parcialidades') {
                this.form.quantity = 0;
                this.form.date = format(new Date(), "yyyy-MM-dd HH:mm:ss");
            } else {
                this.form.quantity = this.selectedProduction.quality_quantity;
                this.form.date = format(new Date(), "yyyy-MM-dd HH:mm:ss");
            }
        },
        returnStation() {
            this.returnForm.post(route('productions.return-station', this.selectedProduction.id), {
                onSuccess: async () => {
                    this.showReturnModal = false;
                    this.$notify({
                        title: "Regreso a estación anterior",
                        type: "success",
                    });
                    this.updateDetails();
                    this.returnForm.reset();
                },
                onError: () => {
                    this.$notify({
                        title: "Error al regresar a estación anterior",
                        type: "error",
                    });
                },
            });
        },
        productionRelease() {
            this.form.post(route('productions.production-release', this.selectedProduction.id), {
                onSuccess: () => {
                    this.showProductionRelease = false;
                    this.$notify({ title: "Éxito", message: "Entrega de producción registrada", type: "success" });
                    this.updateDetails();
                },
                onError: () => {
                    this.$notify({ title: "Error", message: "No se pudo registrar la entrega", type: "error" });
                },
            });
        },
        qualityReleased() {
            this.form.post(route('productions.quality-release', this.selectedProduction.id), {
                onSuccess: () => {
                    this.showQualityRelease = false;
                    this.$notify({
                        title: "Entrega de calidad a inspeccíón",
                        type: "success",
                    });
                    this.updateDetails();
                },
                onError: () => {
                    this.$notify({
                        title: "Error al registrar entrega de calidad a inspeccíón",
                        type: "error",
                    });
                },
            });
        },
        inspectionRelease() {
            this.form.post(route('productions.inspection-release', this.selectedProduction.id), {
                onSuccess: async () => {
                    this.showInspectionRelease = false;
                    this.$notify({
                        title: "Entrega registrada",
                        type: "success",
                    });
                    this.updateDetails();
                },
                onError: (err) => {
                    console.log(err)
                    this.$notify({
                        title: "Error al registrar entrega",
                        type: "error",
                    });
                },
            });
        },
        finishProduction() {
            this.form.post(route('productions.finish-production', this.selectedProduction.id), {
                onSuccess: () => {
                    this.showQualityRelease = false;
                    this.$notify({
                        title: "Orden de producción Terminada",
                        type: "success",
                    });
                    this.updateDetails();
                    this.showFinishedConfirmation = false
                    this.showDetails = false;
                },
                onError: () => {
                    this.$notify({
                        title: "Error al liberar la orden de producción",
                        type: "error",
                    });
                },
            });
        },
        async updateDetails() {
            this.updatingDetails = true;
            await this.fetchProductions();
            this.selectedProduction = this.productions.find(p => p.id == this.selectedProduction.id);
            this.tempStation = this.selectedProduction.station;
            this.currentStation = this.selectedProduction.station;
            this.updatingDetails = false;
            this.form.reset();
        },
        async updateStation() {
            if (this.currentStation === 'Calidad') {
                if (this.tempStation === 'X Reproceso') {
                    // Regresar
                    this.returnForm.quantity = this.selectedProduction.close_quantity;
                    this.showReturnModal = true;
                } else {
                    this.form.reset();
                    this.form.quality_quantity = this.selectedProduction.close_quantity;
                    this.form.quality_released_date = format(new Date(), "yyyy-MM-dd HH:mm:ss");
                    this.showQualityRelease = true;
                }
                this.showDetails = false;
            } else if (this.currentStation === 'Inspección') {
                if (this.tempStation === 'Calidad') {
                    // Regresar
                    this.returnForm.quantity = this.selectedProduction.quality_quantity;
                    this.showReturnModal = true;
                } else {
                    this.showFinishedConfirmation = true;
                }
                this.showDetails = false;
            } else {
                // Para todas las demás estaciones
                if (this.tempStation === 'Calidad') {
                    this.showProductionRelease = true;
                    this.form.close_quantity = this.selectedProduction.quantity;
                    this.form.close_production_date = format(new Date(), "yyyy-MM-dd HH:mm:ss");
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
                this.showDetails = false;
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
                    if ((this.total != this.prevTotal) || this.currentPage == 1) {
                        this.total = response.data.total;
                    }
                    this.prevTotal = this.total;
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
        this.form.close_production_date = format(new Date(), "yyyy-MM-dd HH:mm:ss");
        this.form.quality_released_date = format(new Date(), "yyyy-MM-dd HH:mm:ss");
        this.form.date = format(new Date(), "yyyy-MM-dd HH:mm:ss");
    }
}
</script>
