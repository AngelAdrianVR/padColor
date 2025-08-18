<template>
    <AppLayout title="Crear nueva importación">
        <main class="px-2 lg:px-14">
            <form @submit.prevent="store" class="rounded-lg border border-gray-200 lg:p-5 p-3 lg:w-3/4 mx-auto mt-7">

                <!-- Encabezado -->
                <div class="flex items-center space-x-2 mb-3">
                    <Back />
                    <h1 class="font-semibold">Crear nueva importación</h1>
                </div>

                <!-- Sección: Información General -->
                <h2 class="text-gray66 font-semibold col-span-full my-3">Información general</h2>
                <div class="lg:grid grid-cols-2 gap-3">
                    <div>
                        <InputLabel value="Proveedor*" />
                        <el-select v-model="form.supplier_id" placeholder="Selecciona el proveedor" class="!w-full"
                            filterable>
                            <el-option v-for="supplier in suppliers" :key="supplier.id" :label="supplier.name"
                                :value="supplier.id" />
                        </el-select>
                        <InputError :message="form.errors.supplier_id" />
                    </div>
                    <div>
                        <InputLabel value="Agente aduanal" />
                        <el-select v-model="form.customs_agent_id" placeholder="Selecciona el agente aduanal"
                            class="!w-full" filterable>
                            <el-option v-for="agent in customsAgents" :key="agent.id" :label="agent.name"
                                :value="agent.id" />
                        </el-select>
                        <InputError :message="form.errors.customs_agent_id" />
                    </div>
                    <div>
                        <InputLabel value="Incoterm" />
                        <el-select v-model="form.incoterm" placeholder="Selecciona el incoterm" class="!w-full">
                            <el-option v-for="item in incoterms" :key="item" :label="item" :value="item" />
                        </el-select>
                        <InputError :message="form.errors.incoterm" />
                    </div>
                    <div>
                        <InputLabel value="Fecha estimada de embarque" />
                        <el-date-picker class="!w-full" v-model="form.estimated_ship_date" type="date"
                            placeholder="dd/mm/aa" value-format="YYYY-MM-DD" format="DD/MM/YYYY" />
                        <InputError :message="form.errors.estimated_ship_date" />
                    </div>
                    <div>
                        <InputLabel value="Fecha estimada de llegada (ETA)" />
                        <el-date-picker class="!w-full" v-model="form.estimated_arrival_date" type="date"
                            placeholder="dd/mm/aa" value-format="YYYY-MM-DD" format="DD/MM/YYYY" />
                        <InputError :message="form.errors.estimated_arrival_date" />
                    </div>
                    <div class="col-span-full">
                        <InputLabel value="Notas" />
                        <el-input v-model="form.notes" :autosize="{ minRows: 3, maxRows: 5 }" type="textarea"
                            placeholder="Agrega notas o comentarios adicionales" show-word-limit clearable />
                        <InputError :message="form.errors.notes" />
                    </div>
                </div>

                <!-- Sección: Productos -->
                <div class="flex items-center justify-between col-span-full my-5 pt-3 border-t">
                    <h2 class="text-gray66 font-bold">Productos</h2>
                    <PrimaryButton @click="addProduct" type="button"
                        class="!text-primary !bg-white border !border-grayD9 !rounded-[5px] text-sm">
                        + Agregar producto
                    </PrimaryButton>
                </div>
                <div v-for="(product, index) in form.products" :key="index" class="flex items-end space-x-3 mb-3">
                    <div class="!w-[32%]">
                        <InputLabel value="Producto *" />
                        <el-select v-model="product.raw_material_id" placeholder="Busca el producto" filterable>
                            <el-option v-for="item in rawMaterials" :key="item.id" :label="item.name"
                                :value="item.id" />
                        </el-select>
                    </div>
                    <div class="!w-[18%]">
                        <InputLabel value="Cantidad *" />
                        <el-input-number v-model="product.quantity" :min="1" placeholder="Ej. 100" />
                    </div>
                    <div class="!w-[22%]">
                        <InputLabel value="Costo unitario *" />
                        <el-input v-model="product.unit_cost" placeholder="Ej. 500.00">
                            <template #prepend>$</template>
                        </el-input>
                    </div>
                    <div class="!w-[18%]">
                        <InputLabel value="Moneda" />
                        <el-select v-model="product.currency" placeholder="Moneda">
                            <el-option label="USD" value="USD" />
                            <el-option label="MXN" value="MXN" />
                        </el-select>
                    </div>
                    <button @click="removeProduct(index)" type="button"
                        class="text-primary bg-primarylight rounded-[10px] size-7 flex items-center justify-center mb-1">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.134-2.033-2.134H8.033C6.91 2.75 6 3.664 6 4.834v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                        </svg>
                    </button>
                </div>
                <InputError :message="form.errors.products" />

                <!-- Sección: Documentos -->
                <h2 class="text-gray-600 font-semibold col-span-full my-4 pt-4 border-t">Documentos adjuntos</h2>
                <FileUploader @files-selected="handleFiles" :multiple="true" />
                <div v-if="form.documents.length" class="mt-4 space-y-3">
                    <div v-for="(doc, index) in form.documents" :key="index"
                        class="flex items-center space-x-3 p-2 border rounded-lg">
                        <p class="flex-1 text-sm">{{ doc.name }}</p>
                        <el-select v-model="doc.classification" placeholder="Clasificar*" style="width: 200px">
                            <el-option label="Pedimento" value="Pedimento" />
                            <el-option label="Factura" value="Factura" />
                            <el-option label="BL" value="BL" />
                            <el-option label="Packing List" value="Packing List" />
                        </el-select>
                        <button @click="removeDocument(index)" type="button"
                            class="text-red-500 hover:bg-red-100 rounded-full p-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.134-2.033-2.134H8.033C6.91 2.75 6 3.664 6 4.834v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="col-span-2 text-right mt-6 pt-4 border-t">
                    <CancelButton @click="$inertia.visit(route('imports.index'))" type="button"
                        class="text-gray-600 mr-1">
                        Cancelar
                    </CancelButton>
                    <PrimaryButton :disabled="form.processing">
                        <i v-if="form.processing" class="fa-solid fa-circle-notch fa-spin mr-2"></i>
                        Crear importación
                    </PrimaryButton>
                </div>
            </form>
        </main>
    </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import Back from '@/Components/MyComponents/Back.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import FileUploader from "@/Components/MyComponents/FileUploader.vue";
import { useForm } from '@inertiajs/vue3';
import CancelButton from '@/Components/MyComponents/CancelButton.vue';

export default {
    components: {
        AppLayout,
        Back,
        InputError,
        InputLabel,
        PrimaryButton,
        CancelButton,
        FileUploader,
    },
    props: {
        suppliers: Array,
        customsAgents: Array,
        rawMaterials: Array,
    },
    data() {
        const form = useForm({
            supplier_id: null,
            customs_agent_id: null,
            incoterm: 'FOB',
            estimated_ship_date: null,
            estimated_arrival_date: null,
            notes: null,
            products: [], // Array para los productos
            documents: [], // Array para los documentos
        });

        return {
            form,
            incoterms: ['FOB', 'CIF', 'EXW', 'DDP'],
        };
    },
    methods: {
        store() {
            this.form.post(route('imports.store'), {
                onSuccess: () => {
                    this.$notify({
                        title: 'Éxito',
                        message: 'Importación creada correctamente',
                        type: 'success',
                    });
                },
            });
        },
        addProduct() {
            this.form.products.push({
                raw_material_id: null,
                quantity: 1,
                unit_cost: null,
                currency: 'USD',
            });
        },
        removeProduct(index) {
            this.form.products.splice(index, 1);
        },
        handleFiles(files) {
            // files es un array de objetos File
            files.forEach(file => {
                this.form.documents.push({
                    file: file, // El objeto File real
                    name: file.name, // Guardamos el nombre para mostrarlo
                    classification: null, // El usuario lo seleccionará
                });
            });
        },
        removeDocument(index) {
            this.form.documents.splice(index, 1);
        },
    },
};
</script>
