<template>
    <el-table :data="productions" @row-click="handleRowClick" max-height="670" style="width: 100%" class="mt-1"
        :row-class-name="tableRowClassName">
        <el-table-column prop="folio" label="N° Orden">
            <template #default="scope">
                <div v-if="!scope.row.quantity" class="flex items-center space-x-2"
                    :class="!scope.row.quantity ? 'text-red-600' : null">
                    <span>{{ scope.row.folio }}</span>
                    <el-tooltip content="Llenar información faltante" placement="top">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                    </el-tooltip>
                </div>
                 <span v-else>{{ scope.row.folio }}</span>
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
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 mr-2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" />
                                </svg>
                                Hoja viajera
                            </el-dropdown-item>
                            <el-dropdown-item class="!text-xs" v-if="$page.props.auth.user.permissions.includes('Editar producciones')" :command="'edit-' + scope.row.id">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 mr-2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                </svg>
                                Editar
                            </el-dropdown-item>
                            <el-dropdown-item class="!text-xs" v-if="$page.props.auth.user.permissions.includes('Crear producciones')" :command="'clone-' + scope.row.id">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 mr-2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                </svg>
                                Repetir
                            </el-dropdown-item>
                            <el-dropdown-item class="!text-xs" :command="'report-' + scope.row.id">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 mr-2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 0 1-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0 1 12 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m17.25-3.75h-7.5c-.621 0-1.125.504-1.125 1.125m8.625-1.125c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5M12 10.875v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 10.875c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125M13.125 12h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125M20.625 12c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5M12 14.625v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 14.625c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125m0 1.5v-1.5m0 0c0-.621.504-1.125 1.125-1.125m0 0h7.5" />
                                </svg>
                                Reporte de entregas
                            </el-dropdown-item>
                            <el-dropdown-item class="!text-xs" v-if="$page.props.auth.user.permissions.includes('Eliminar producciones')" :command="'delete-' + scope.row.id">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 mr-2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                                Eliminar
                            </el-dropdown-item>
                        </el-dropdown-menu>
                    </template>
                </el-dropdown>
            </template>
        </el-table-column>
    </el-table>
</template>

<script>
export default {
    name: 'ProductionTable',
    props: {
        productions: Array,
        stations: Array,
    },
    emits: ['row-click', 'command'],
    methods: {
        handleRowClick(row) {
            this.$emit('row-click', row);
        },
        handleCommand(command) {
            this.$emit('command', command);
        },
        tableRowClassName({ row, rowIndex }) {
            return 'cursor-pointer text-xs';
        },
    }
}
</script>