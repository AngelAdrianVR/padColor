<template>
    <AppLayout title="Editar importación">
        <main class="px-2 lg:px-14">
            <form @submit.prevent="update" class="rounded-lg border border-gray-200 lg:p-5 p-3 lg:w-3/4 mx-auto my-7">

                <!-- Encabezado -->
                <div class="flex items-center space-x-2 mb-3">
                    <Back />
                    <h1 class="font-semibold">Editar importación</h1>
                </div>

                <!-- Sección: Información General -->
                <h2 class="text-gray-500 font-semibold col-span-full my-3">Información general</h2>
                <div class="lg:grid grid-cols-2 gap-3">
                    <div>
                        <InputLabel>
                            <div class="flex items-center justify-between">
                                <span>Proveedor*</span>
                                <button @click="showFastAddModal = true; addingSupplier = true" type="button"
                                    class="mr-2">
                                    <PlusCircleIcon class="size-5" />
                                </button>
                            </div>
                        </InputLabel>
                        <el-select v-model="form.supplier_id" placeholder="Selecciona el proveedor" class="!w-full"
                            filterable>
                            <el-option v-for="supplier in suppliers" :key="supplier.id" :label="supplier.name"
                                :value="supplier.id" />
                        </el-select>
                        <InputError :message="form.errors.supplier_id" />
                    </div>
                    <div>
                        <InputLabel>
                            <div class="flex items-center justify-between">
                                <span>Agente aduanal*</span>
                                <button @click="showFastAddModal = true; addingSupplier = false" type="button"
                                    class="mr-2">
                                    <PlusCircleIcon class="size-5" />
                                </button>
                            </div>
                        </InputLabel>
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
                    <div>
                        <InputLabel value="Moneda" />
                        <el-select v-model="form.currency" placeholder="Moneda">
                            <el-option label="USD" value="USD" />
                            <el-option label="MXN" value="MXN" />
                        </el-select>
                    </div>
                    <div class="col-span-full">
                        <InputLabel value="Notas" />
                        <el-input v-model="form.notes" :autosize="{ minRows: 3, maxRows: 5 }" type="textarea"
                            placeholder="Agrega notas o comentarios adicionales" show-word-limit clearable />
                        <InputError :message="form.errors.notes" />
                    </div>
                </div>

                <!-- Sección: Materias primas -->
                <div class="flex items-center justify-between col-span-full my-5 pt-3 border-t">
                    <h2 class="text-gray-600 font-bold">Materias primas</h2>
                    <PrimaryButton @click="addProduct" type="button"
                        class="!text-primary !bg-white border !border-gray-300 !rounded-md text-sm">
                        <PlusIcon class="h-4 w-4 mr-1 inline" />
                        Agregar materia prima
                    </PrimaryButton>
                </div>
                <div v-for="(product, index) in form.products" :key="index" class="flex items-end space-x-3 mb-3">
                    <div class="flex-grow">
                        <InputLabel value="Materia prima *" />
                        <el-select v-model="product.raw_material_id" placeholder="Busca el artículo" class="!w-full"
                            filterable>
                            <el-option v-for="item in rawMaterials" :key="item.id" :label="item.name"
                                :value="item.id" />
                        </el-select>
                    </div>
                    <div style="width: 150px;">
                        <InputLabel value="Cantidad *" />
                        <el-input-number v-model="product.quantity" :min="0.01" :step="0.01" placeholder="Ej. 100"
                            class="!w-full" />
                    </div>
                    <div style="width: 150px;">
                        <InputLabel value="Costo unitario *" />
                        <el-input v-model="product.unit_cost" placeholder="Ej. 500.00">
                            <template #prepend>$</template>
                        </el-input>
                    </div>
                    <div style="width: 150px;">
                        <InputLabel value="Total" />
                        <p class="ml-3">
                            ${{ (product.unit_cost * product.quantity)?.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",")
                            }}
                        </p>
                    </div>
                    <el-popconfirm title="¿Eliminar producto?" @confirm="removeProduct(index)" confirm-button-text="Sí"
                        icon-color="#373737" cancel-button-text="No">
                        <template #reference>
                            <button type="button"
                                class="bg-primarylight text-primary rounded-md size-7 flex items-center justify-center mb-1">
                                <TrashIcon class="size-4" />
                            </button>
                        </template>
                    </el-popconfirm>
                </div>
                <InputError :message="form.errors.products" />

                <!-- Sección: Documentos -->
                <div class="flex items-center justify-between col-span-full my-5 pt-3 border-t">
                    <h2 class="text-gray-600 font-bold">Documentos adjuntos</h2>
                    <input type="file" @change="handleFileSelection" ref="fileInput" multiple hidden>
                    <PrimaryButton @click="triggerFileInput" type="button"
                        class="!text-primary !bg-white border !border-gray-300 !rounded-md text-sm">
                        <ArrowUpTrayIcon class="h-4 w-4 mr-1 inline" />
                        Adjuntar archivo
                    </PrimaryButton>
                </div>
                <!-- Lista de documentos existentes y nuevos -->
                <div class="mt-4 space-y-3">
                    <!-- Documentos ya guardados -->
                    <div v-for="(doc, index) in form.documents" :key="doc.id"
                        class="grid grid-cols-3 gap-x-3 items-end">
                        <FileView :file="doc" />
                        <el-select v-model="doc.classification" placeholder="Clasificar*" class="!w-full" disabled>
                            <!-- Opciones -->
                        </el-select>
                        <el-popconfirm title="¿Eliminar archivo?" @confirm="removeExistingDocument(index)"
                            confirm-button-text="Sí" icon-color="#373737" cancel-button-text="No">
                            <template #reference>
                                <button type="button"
                                    class="bg-primarylight text-primary rounded-md size-7 flex items-center justify-center mb-1 justify-self-end">
                                    <TrashIcon class="size-4" />
                                </button>
                            </template>
                        </el-popconfirm>
                    </div>
                    <!-- Nuevos documentos a subir -->
                    <div v-for="(doc, index) in form.new_documents" :key="index"
                        class="grid grid-cols-3 gap-x-3 items-end">
                        <FileView :file="doc" />
                        <div>
                            <InputLabel value="Clasificar *" />
                            <el-select v-model="doc.classification" placeholder="Selecciona categoría" class="!w-full">
                                <el-option label="Pedimento" value="Pedimento" />
                                <el-option label="Factura" value="Factura" />
                                <el-option label="BL" value="BL" />
                                <el-option label="Packing List" value="Packing List" />
                            </el-select>
                        </div>
                        <el-popconfirm title="¿Quitar archivo?" @confirm="removeNewDocument(index)"
                            confirm-button-text="Sí" icon-color="#373737" cancel-button-text="No">
                            <template #reference>
                                <button type="button"
                                    class="bg-primarylight text-primary rounded-md size-7 flex items-center justify-center mb-1 justify-self-end">
                                    <TrashIcon class="size-4" />
                                </button>
                            </template>
                        </el-popconfirm>
                    </div>
                </div>
                <InputError :message="form.errors.documents" />

                <!-- Botones de Acción -->
                <div class="col-span-2 text-right mt-6 pt-4 border-t">
                    <CancelButton @click="$inertia.visit(route('imports.index'))" type="button">
                        Cancelar
                    </CancelButton>
                    <PrimaryButton :disabled="form.processing" class="ml-2">
                        <i v-if="form.processing" class="fa-solid fa-circle-notch fa-spin mr-2"></i>
                        Guardar cambios
                    </PrimaryButton>
                </div>
            </form>
        </main>

        <DialogModal :show="showFastAddModal" @close="showFastAddModal = false">
            <template #title>
                <h1 v-if="addingSupplier">Agregar nuevo proveedor</h1>
                <h1 v-else>Agregar nuevo Agente</h1>
            </template>
            <template #content>
                <div>
                    <InputLabel value="Nombre de compañía o agencia*" />
                    <el-input v-model="fastForm.name" placeholder="Ingresa el nombre" clearable />
                    <InputError :message="fastForm.errors.name" />
                </div>
                <div class="mt-3">
                    <InputLabel value="Nombre del contacto" />
                    <el-input v-model="fastForm.contact_person" placeholder="Ingresa el nombre" clearable />
                    <InputError :message="fastForm.errors.contact_person" />
                </div>
                <div class="mt-3">
                    <InputLabel value="Correo electrónico" />
                    <el-input v-model="fastForm.email" placeholder="Ingresa el correo" clearable />
                    <InputError :message="fastForm.errors.email" />
                </div>
                <div class="mt-3">
                    <InputLabel value="Teléfono" />
                    <el-input v-model="fastForm.phone" placeholder="Ingresa el número" clearable />
                    <InputError :message="fastForm.errors.phone" />
                </div>
            </template>
            <template #footer>
                <div class="text-right">
                    <CancelButton @click="closeFastModal" type="button">
                        Cancelar
                    </CancelButton>
                    <PrimaryButton @click="storeFastItem" :disabled="fastForm.processing" class="ml-2">
                        <i v-if="fastForm.processing" class="fa-solid fa-circle-notch fa-spin mr-2"></i>
                        Crear
                    </PrimaryButton>
                </div>
            </template>
        </DialogModal>
    </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import Back from '@/Components/MyComponents/Back.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import CancelButton from '@/Components/MyComponents/CancelButton.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ArrowUpTrayIcon, PlusCircleIcon, PlusIcon, TrashIcon } from '@heroicons/vue/24/outline';
import FileView from '@/Components/MyComponents/Ticket/FileView.vue';
import DialogModal from '@/Components/DialogModal.vue';

export default {
    components: {
        AppLayout,
        Back,
        InputError,
        InputLabel,
        PrimaryButton,
        CancelButton,
        FileView,
        DialogModal,
        TrashIcon,
        PlusIcon,
        PlusCircleIcon,
        ArrowUpTrayIcon,
    },
    props: {
        import: Object,
        suppliers: Array,
        customsAgents: Array,
        rawMaterials: Array,
    },
    data() {
        const form = useForm({
            _method: 'PUT', // Para que Laravel reconozca la petición como PUT
            supplier_id: this.import.supplier_id,
            customs_agent_id: this.import.customs_agent_id,
            incoterm: this.import.incoterm,
            estimated_ship_date: this.import.estimated_ship_date,
            estimated_arrival_date: this.import.estimated_arrival_date,
            notes: this.import.notes,
            currency: this.import.currency,
            products: this.import.products || [],
            documents: this.import.documents || [], // Documentos existentes
            new_documents: [], // Nuevos documentos a subir
            documents_to_delete: [], // IDs de documentos a eliminar
        });

        const fastForm = useForm({
            name: null,
            contact_person: null,
            email: null,
            phone: null,
        });

        return {
            fastForm,
            form,
            addingSupplier: false,
            showFastAddModal: false,
            incoterms: [
                'EXW: Ex Works (En fábrica)',
                'FCA: Free Carrier (Franco transportista)',
                'CPT: Carriage Paid To (Transporte pagado hasta)',
                'CIP: Carriage and Insurance Paid To (Transporte y seguro pagados hasta)',
                'DAP: Delivered At Place (Entregado en lugar)',
                'DPU: Delivered at Place Unloaded (Entregado en lugar descargado)',
                'DDP: Delivered Duty Paid (Entregado derechos pagados)',
                'FAS: Free Alongside Ship (Franco al costado del buque)',
                'FOB: Free On Board (Franco a bordo)',
                'CFR: Cost and Freight (Costo y flete)',
                'CIF: Cost, Insurance and Freight (Costo, seguro y flete)',
            ]
        };
    },
    methods: {
        update() {
            // Usamos router.post para poder enviar archivos (multipart/form-data)
            this.form.post(route('imports.update', this.import.id), this.form, {
                onSuccess: () => {
                    this.$notify({
                        title: 'Éxito',
                        message: 'Importación actualizada correctamente',
                        type: 'success',
                    });
                },
                onError: (err) => {
                    console.log(err)
                },
                onFinish: () => {
                }
            });
        },
        addProduct() {
            this.form.products.push({ raw_material_id: null, quantity: 1, unit_cost: null });
        },
        removeProduct(index) {
            this.form.products.splice(index, 1);
        },
        triggerFileInput() {
            this.$refs.fileInput.click();
        },
        handleFileSelection(event) {
            const files = event.target.files;
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                this.form.new_documents.push({
                    file: file,
                    file_name: file.name,
                    size: file.size,
                    classification: null,
                });
            }
            // Limpiar el input para poder seleccionar el mismo archivo de nuevo
            this.$refs.fileInput.value = '';
        },
        removeNewDocument(index) {
            this.form.new_documents.splice(index, 1);
        },
        removeExistingDocument(index) {
            // Agregamos el ID a la lista de borrado
            this.form.documents_to_delete.push(this.form.documents[index].id);
            // Lo quitamos de la vista
            this.form.documents.splice(index, 1);
        },
    },
};
</script>
