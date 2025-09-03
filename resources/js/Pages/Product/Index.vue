<template>
  <AppLayout title="Productos">
    <main class="px-2 lg:px-14">
        <div>
            <h1 class="font-bold">Productos</h1>
            <!-- Buscador -->
            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center mt-4">
                <div class="lg:w-1/4 relative lg:mr-12">
                    <input v-model="searchTemp" @keyup.enter="handleSearch" class="input w-full pl-9"
                        placeholder="Buscar producto" type="search">
                    <i class="fa-solid fa-magnifying-glass text-xs text-gray99 absolute top-[10px] left-4"></i>
                </div>
                <PrimaryButton class="mt-3 lg:mt-0" v-if="this.$page.props.auth.user.permissions.includes('Crear productos')" @click="$inertia.get(route('products.create'))">Agregar producto</PrimaryButton>
            </div>
            <div class="mt-2 text-center">
                <el-tag v-if="search" size="large" closable @close="handleTagClose">
                    Estas buscando: <b>{{ search }}</b>
                </el-tag>
            </div>

            <!-- pagination -->
            <div class="overflow-auto mb-2 mt-8">
                <PaginationWithNoMeta v-if="!search" :pagination="products" class="py-2" />
            </div>

            <Loading v-if="loading" />

            <el-table v-else :data="filteredProducts.data" @row-click="handleRowClick" max-height="670" style="width: 100%" class="mt-4"
                :row-class-name="tableRowClassName">
                <!-- <el-table-column type="selection" width="45" /> -->
                <el-table-column prop="code" label="Código" />
                <el-table-column prop="name" label="Nombre del producto" />
                <el-table-column prop="season" label="Temporada" />
                <el-table-column prop="description" label="Descripción" />
                <el-table-column prop="material" label="Lista de material">
                    <template #default="scope">
                        <p class="truncate">{{ scope.row.material ?? 'Ninguna' }}</p>
                    </template>
                </el-table-column>
                <el-table-column align="right">
                    <template #default="scope">
                        <el-dropdown trigger="click" @command="handleCommand">
                            <button @click.stop
                                class="el-dropdown-link mr-3 justify-center items-center size-8 rounded-full text-primary hover:bg-gray2 transition-all duration-200 ease-in-out">
                                <i class="fa-solid fa-ellipsis-vertical"></i>
                            </button>
                            <template #dropdown>
                                <el-dropdown-menu>
                                    <el-dropdown-item class="!text-xs"
                                        v-if="$page.props.auth.user.permissions.includes('Editar productos')"
                                        :command="'edit-' + scope.row.id">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-4 mr-2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                        </svg>
                                        Editar</el-dropdown-item>
                                    <el-dropdown-item class="!text-xs"
                                        v-if="$page.props.auth.user.permissions.includes('Crear productos')"
                                        :command="'clone-' + scope.row.id">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-4 mr-2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                        </svg>
                                        Clonar</el-dropdown-item>
                                    <el-dropdown-item class="!text-xs"
                                        v-if="$page.props.auth.user.permissions.includes('Eliminar productos')"
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
    </main>

    <!-- Modal para ver detalles del producto -->
    <DialogModal :show="showDetailsModal" @close="showDetailsModal = false" :max-width="'3xl'">
        <template #title>
            <div class="flex justify-between items-center w-full mr-8"> 
                <h1>Detalles del producto</h1>
                <ThirthButton @click="$inertia.get(route('products.edit', selectedProduct.id))" class="!rounded-md !px-3 !py-1">Editar</ThirthButton>
            </div>
        </template>
        <template #content>
            <section class="md:grid grid-cols-2">
                <figure class="border border-[#D9D9D9] rounded-xl w-[80%] h-48 flex items-center justify-center">
                    <img v-if="selectedProduct.media?.some(m => m.collection_name === 'image')" class="h-full object-contain" 
                        :src="selectedProduct.media?.find(m => m.collection_name === 'image')?.original_url" :alt="selectedProduct.media?.find(m => m.collection_name === 'image')?.file_name">
                    <div v-else>
                        <img class="h-full object-contain" src="/images/no-image.png" alt="No hay imagen disponible">
                    </div>
                </figure>
                <div>
                    <div class="mb-4">
                        <h2 class="text-[#727272]">Nombre del producto:</h2>
                        <p class="text-[15px] text-black font-semibold mt-1">{{ selectedProduct.name }}</p>
                    </div>

                    <section class="grid grid-cols-2 gap-1">
                        <!-- Parte izquierda -->
                        <article>
                            <div class="mb-2">
                                <h2 class="text-[#727272]">Código:</h2>
                                <p class="text-[15px] text-black mt-1">{{ selectedProduct.code }}</p>
                            </div>
                            <div class="mb-4">
                                <h2 class="text-[#727272]">Temporada:</h2>
                                <p class="text-[15px] text-black mt-1">{{ selectedProduct.season }}</p>
                            </div>
                        </article>
                        
                        <!-- Parte derecha -->
                        <article>
                            <div class="mb-2">
                                <h2 class="text-[#727272]">Fecha de creación:</h2>
                                <p class="text-[15px] text-black mt-1">{{ formatDate(selectedProduct.created_at) }}</p>
                            </div>
                            <div class="mb-2">
                                <h2 class="text-[#727272]">Material:</h2>
                                <p class="text-[15px] text-black mt-1">{{ selectedProduct.material }}</p>
                            </div>
                        </article>

                        <div class="mb-2 col-span-full">
                            <h2 class="text-[#727272]">Descripción:</h2>
                            <p class="text-[15px] text-black mt-1">{{ selectedProduct.description }}</p>
                        </div>
                        
                        <div class="colspan-full">
                            <p class="text-[#727272] mb-1">Archivos adjuntos:</p>
                            <div class="space-y-1" v-if="selectedProduct.media?.some(m => m.collection_name === 'files')">
                                <FileView v-for="file in selectedProduct.media?.filter(m => m.collection_name === 'files')"  
                                    :key="file" :file="file" @delete-file="deleteFile($event)" />
                            </div>
                            <p v-else class="text-xs text-gray-400">No hay archivos adjuntos</p>
                        </div>

                    </section>
                </div>
            </section>
        </template>
        <template #footer>
        </template>
    </DialogModal>
  </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import DialogModal from "@/Components/DialogModal.vue";
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ThirthButton from '@/Components/MyComponents/ThirthButton.vue';
import PaginationWithNoMeta from "@/Components/MyComponents/PaginationWithNoMeta.vue";
import FileView from "@/Components/MyComponents/Ticket/FileView.vue";
import Loading from "@/Components/MyComponents/Loading.vue";
import { format, parseISO } from 'date-fns';
import es from 'date-fns/locale/es';
import axios from 'axios';

export default {
data() {
    return {
        loading: false,
        search: null,
        searchTemp: null,
        filteredProducts: this.products,
        showDetailsModal: false, //mostrar detalles del producto (modal)
        selectedProduct: null, //Producto seleccionado para mostrar sus detalles
    }
},
components: {
    Loading,
    FileView,
    AppLayout,
    DialogModal,
    ThirthButton,
    PrimaryButton,
    PaginationWithNoMeta,
},
props: {
    products: Object,
},
methods: {
    async handleSearch() {
        this.loading = true;
        this.search = this.searchTemp;
        this.searchTemp = null;
        try {
            if (!this.search) {
                this.filteredProducts = this.products;
            } else {
                const response = await axios.get(route('products.get-matches', { query: this.search }));
                if (response.status === 200) {
                    this.filteredProducts = response.data.items;
                }
            }
        } catch (error) {
            console.log(error);
            this.$message({
                type: 'error',
                message: error
            });

        } finally {
            this.loading = false;
        }
    },
    handleTagClose() {
        this.search = null;
        this.filteredProducts = this.products;
    },
    handleRowClick(row) {
        this.$inertia.get(route('products.show', row.id));
        // this.showDetailsModal = true;
        // this.selectedProduct = row;

    },
    tableRowClassName({ row, rowIndex }) {
        return 'cursor-pointer text-xs';
    },
    handleCommand(command) {
        const commandName = command.split('-')[0];
        const rowId = command.split('-')[1];

        if (commandName == 'clone') {
            this.clone(rowId);
        } else if (commandName == 'edit') {
            this.$inertia.get(route('products.edit', rowId));
        } else if (commandName == 'delete') {
            this.delete(rowId);
        }
    },
    delete(id) {
        this.$confirm('¿Estás seguro de que deseas eliminar este producto?', 'Eliminar producto', {
            type: 'warning',
            showCancelButton: true,
            cancelButtonText: 'Cancelar',
            confirmButtonText: 'Eliminar',
        }).then(() => {
            axios.delete(route('products.destroy', id))
                .then(response => {
                    const index = this.filteredProducts.data.findIndex(product => product.id == id);
                    if (index !== -1) {
                        this.filteredProducts.data.splice(index, 1);
                    }

                    this.$message({
                        type: 'success',
                        message: 'Producto eliminado correctamente',
                    });
                })
                .catch(error => {
                    this.$message.error(error.response.data.message);
                });
        }).catch(() => {});
    },
    clone(id) {
        this.$confirm('¿Estás seguro de que deseas clonar este producto?', 'Clonar producto', {
            type: 'warning',
            showCancelButton: true,
            cancelButtonText: 'Cancelar',
            confirmButtonText: 'Clonar',
        }).then(() => {
            this.$inertia.get(route('products.clone', id));
            this.$message({
                type: 'success',
                message: 'Producto clonado correctamente',
            });
        }).catch(() => {});
    },
    formatDate(dateString) {
        return format(parseISO(dateString), 'dd MMMM, yyyy', { locale: es });
    },
}
}
</script>
