<template>
    <AppLayout title="Crear nueva importación">
        <main class="px-2 lg:px-14">
            <form @submit.prevent="store" class="rounded-lg border border-gray-200 lg:p-5 p-3 lg:w-3/4 mx-auto my-7">

                <!-- Encabezado -->
                <div class="flex items-center space-x-2 mb-3">
                    <Back />
                    <h1 class="font-semibold">Crear nueva importación</h1>
                </div>

                <!-- Sección: Información General -->
                <h2 class="text-gray-500 font-semibold col-span-full my-3">Información general</h2>
                <div class="lg:grid grid-cols-2 gap-3">
                    <div>
                        <InputLabel value="Cliente que solicita*" />
                        <el-select v-model="form.client" placeholder="Selecciona" class="!w-full">
                            <el-option v-for="item in ['PadColor Insumos graficos', 'Papel, diseño y color']"
                                :key="item" :label="item" :value="item" />
                        </el-select>
                        <InputError :message="form.errors.client" />
                    </div>
                    <div>
                        <InputLabel value="Cedis*" />
                        <el-select v-model="form.cedis" placeholder="Selecciona" class="!w-full">
                            <el-option v-for="item in ['GDL', 'CDMX', 'Pendiente']" :key="item" :label="item"
                                :value="item" />
                        </el-select>
                        <InputError :message="form.errors.cedis" />
                    </div>
                    <div>
                        <InputLabel value="Puerto de llegada*" />
                        <el-select v-model="form.arrival_port" placeholder="Selecciona" class="!w-full">
                            <el-option v-for="item in ['Altamira', 'Manzanillo']" :key="item" :label="item"
                                :value="item" />
                        </el-select>
                        <InputError :message="form.errors.arrival_port" />
                    </div>
                    <div>
                        <InputLabel value="Almacén*" />
                        <el-select v-model="form.warehouse" placeholder="Selecciona el incoterm" class="!w-full">
                            <el-option v-for="item in ['Tigre', 'Federalismo', 'Calle 2', 'Calle C']" :key="item"
                                :label="item" :value="item" />
                        </el-select>
                        <InputError :message="form.errors.warehouse" />
                    </div>
                    <div>
                        <InputLabel>
                            <div class="flex items-center justify-between">
                                <span>Proveedor*</span>
                                <button v-if="$page.props.auth.user.permissions.includes('Crear proveedores')"
                                    @click="showFastAddModal = true; addingSupplier = true" type="button" class="mr-2">
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
                                <button v-if="$page.props.auth.user.permissions.includes('Crear agentes aduanales')"
                                    @click="showFastAddModal = true; addingSupplier = false" type="button" class="mr-2">
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
                    <!-- <div>
                        <InputLabel value="Fecha estimada de embarque" />
                        <el-date-picker class="!w-full" v-model="form.estimated_ship_date" type="date"
                            placeholder="dd/mm/aa" value-format="YYYY-MM-DD" format="DD/MM/YYYY" />
                        <InputError :message="form.errors.estimated_ship_date" />
                    </div> -->
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

                <!-- Sección: Materia prima -->
                <div class="flex items-center justify-between col-span-full my-5 pt-3 border-t">
                    <h2 class="text-gray-600 font-bold">Materia prima</h2>
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
                        <ArrowUpTrayIcon class="size-4 mr-1 inline" />
                        Adjuntar archivo
                    </PrimaryButton>
                </div>
                <div v-if="form.documents.length" class="mt-4 space-y-3">
                    <div v-for="(doc, index) in form.documents" :key="index" class="grid grid-cols-3 gap-x-3 items-end">
                        <FileView :file="doc" />
                        <div>
                            <InputLabel value="Clasificar *" />
                            <el-select v-model="doc.classification" placeholder="Selecciona categoría" class="!w-full">
                                <el-option label="Pedimento" value="Pedimento" />
                                <el-option label="Factura" value="Factura" />
                                <el-option label="BL (Bill of landing)" value="BL (Bill of landing)" />
                                <el-option label="Carta porte" value="Carta porte" />
                                <el-option label="Packing List" value="Packing List" />
                                <el-option label="Certificado de origen" value="Certificado de origen" />
                                <el-option label="Póliza de seguro de carga" value="Póliza de seguro de carga" />
                                <el-option label="Orden de compra" value="Orden de compra" />
                                <el-option label="Otro" value="Otro" />
                            </el-select>
                        </div>
                        <el-popconfirm title="¿Eliminar archivo?" @confirm="removeDocument(index)" icon-color="#373737"
                            confirm-button-text="Sí" cancel-button-text="No">
                            <template #reference>
                                <button type="button"
                                    class="bg-primarylight text-primary rounded-md size-7 flex items-center justify-center justify-self-end">
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
                        Crear importación
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
import { useForm } from '@inertiajs/vue3';
import { ArrowUpTrayIcon, PlusCircleIcon, PlusIcon, TrashIcon } from '@heroicons/vue/24/outline';
import FileView from '@/Components/MyComponents/Ticket/FileView.vue';
import DialogModal from '@/Components/DialogModal.vue';
import axios from 'axios';

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
        ArrowUpTrayIcon,
        PlusCircleIcon,
    },
    props: {
        suppliers: Array,
        customsAgents: Array,
        rawMaterials: Array,
    },
    data() {
        const form = useForm({
            client: null,
            cedis: null,
            arrival_port: null,
            warehouse: null,
            supplier_id: null,
            customs_agent_id: null,
            incoterm: 'EXW: Ex Works (En fábrica)',
            estimated_ship_date: null,
            currency: 'USD',
            estimated_arrival_date: null,
            notes: null,
            products: [],
            documents: [],
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
        closeFastModal() {
            this.fastForm.reset();
            this.showFastAddModal = false;
        },
        storeFastItem() {
            let routeName = this.addingSupplier ? route('suppliers.store') : route('customs-agents.store');

            this.fastForm.processing = true;

            // Usamos axios para hacer la petición y manejar la respuesta JSON
            axios.post(routeName, this.fastForm.data())
                .then(response => {
                    const newItem = response.data; // El nuevo proveedor o agente devuelto por el controlador

                    if (this.addingSupplier) {
                        // 1. Añadir el nuevo proveedor a la lista de props
                        this.suppliers.push(newItem);
                        // 2. Seleccionarlo automáticamente en el formulario principal
                        this.form.supplier_id = newItem.id;
                        this.$notify({ title: 'Éxito', message: 'Nuevo proveedor agregado', type: 'success' });
                    } else {
                        this.customsAgents.push(newItem);
                        this.form.customs_agent_id = newItem.id;
                        this.$notify({ title: 'Éxito', message: 'Nuevo agente agregado', type: 'success' });
                    }

                    this.showFastAddModal = false;
                    this.fastForm.reset();
                })
                .catch(error => {
                    // Manejar errores de validación
                    if (error.response.status === 422) {
                        this.fastForm.errors = error.response.data.errors;
                    }
                })
                .finally(() => {
                    this.fastForm.processing = false;
                });
        },
        addProduct() {
            this.form.products.push({
                raw_material_id: null,
                quantity: 1,
                unit_cost: null,
            });
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
                this.form.documents.push({
                    file: file, // El objeto File real para el backend
                    file_name: file.name, // Para la prop de FileView
                    size: file.size, // Para la prop de FileView
                    classification: null,
                });
            }
        },
        removeDocument(index) {
            this.form.documents.splice(index, 1);
        },
    },
};
</script>
