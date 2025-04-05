<template>
    <div>
        <el-table :data="[]" @row-click="handleRowClick" max-height="670" style="width: 100%"
            @selection-change="handleSelectionChange" ref="multipleTableRef" :row-class-name="tableRowClassName">
            <!-- <el-table-column type="selection" width="45" /> -->
            <el-table-column prop="folio" label="Folio" width="120" />
            <el-table-column prop="product.name" label="Producto" />
            <el-table-column prop="station" label="Estación" width="140">
                <template #default="scope">
                    <b>{{ scope.row.station }}</b>
                </template>
            </el-table-column>
            <el-table-column prop="created_at" label="Creado el" width="180" />
            <el-table-column align="right">
                <template #default="scope">
                    <el-dropdown trigger="click" @command="handleCommand">
                        <button @click.stop
                            class="el-dropdown-link mr-3 justify-center items-center size-8 rounded-full text-primary hover:bg-gray2 transition-all duration-200 ease-in-out">
                            <i class="fa-solid fa-ellipsis-vertical"></i>
                        </button>
                        <template #dropdown>
                            <el-dropdown-menu>
                                <el-dropdown-item :command="'show-' + scope.row.id">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-4 mr-2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                    Ver</el-dropdown-item>
                                <el-dropdown-item v-if="$page.props.auth.user.permissions.includes('Editar cotizaciones')
                                    || scope.row.user.id == $page.props.auth.user.id"
                                    :command="'edit-' + scope.row.id">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-4 mr-2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                    </svg>
                                    Editar</el-dropdown-item>
                                <el-dropdown-item
                                    v-if="$page.props.auth.user.permissions.includes('Crear cotizaciones')"
                                    :command="'clone-' + scope.row.id">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-4 mr-2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                                    </svg>
                                    Clonar</el-dropdown-item>
                                <el-dropdown-item
                                    v-if="$page.props.auth.user.permissions.includes('Eliminar producciones')"
                                    :command="'delete-' + scope.row.id">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-4 mr-2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m4.5 12.75 6 6 9-13.5" />
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
</template>

<script>

export default {
    name: 'ProductionList',
    data() {
        return {

        }
    },
    components: {
    },
    props: {
    },
    methods: {
        handleRowClick(row) {
            const url = this.route('quotes.show', row);
            window.open(url, '_blank');
        },
        tableRowClassName({ row, rowIndex }) {
            return 'cursor-pointer text-xs';
        },
        handleCommand(command) {
            // const commandName = command.split('-')[0];
            // const rowId = command.split('-')[1];

            // if (commandName == 'clone') {
            //     this.clone(rowId);
            // } else if (commandName == 'make_so') {
            //     this.selectedQuoteId = rowId;
            //     this.showConversionConfirm = true;
            // } else if (commandName == 'authorize') {
            //     this.authorize(rowId);
            // } else {
            //     this.$inertia.get(route('quotes.' + commandName, rowId));
            // }
        },
    },
}
</script>