<template>
    <DialogModal :show="show" @close="closeModal" max-width="3xl">
        <template #title>
            <h2 class="text-xl font-semibold">Detalles de importación</h2>
        </template>
        <template #content>
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <span class="text-sm font-bold text-gray-700">Nº {{ importData.id }}</span>
                    <p class="px-2 py-1 text-xs flex space-x-1 items-center font-semibold rounded-full"
                        :style="statusClass(importData.status)">
                        <component :is="statuses[importData.status]" class="size-4" />
                        <span>
                            {{ formatStatus(importData.status) }}
                        </span>
                    </p>
                </div>
                <PrimaryButton @click="editImport"
                    class="!text-primary !bg-white border !border-gray-300 !rounded-md text-sm">
                    <PencilIcon class="size-4 inline mr-1" />
                    Editar
                </PrimaryButton>
            </div>
            <el-tabs v-model="activeTab" class="mt-4">
                <el-tab-pane label="Información general" name="general">
                    <div class="divide-y divide-gray-200">
                        <div class="py-2 flex justify-between text-sm">
                            <span class="text-gray3F">Creado por</span>
                            <span>{{ importData.user?.name }}</span>
                        </div>
                        <div class="py-2 flex justify-between text-sm">
                            <span class="text-gray3F">Creado el</span>
                            <span>{{ formatDate(importData.created_at) }}</span>
                        </div>
                        <div class="py-2 flex justify-between text-sm">
                            <span class="text-gray3F">Proveedor</span>
                            <span>{{ importData.supplier?.name }}</span>
                        </div>
                        <div class="py-2 flex justify-between text-sm">
                            <span class="text-gray3F">Agente aduanal</span>
                            <span>{{ importData.customs_agent?.name }}</span>
                        </div>
                        <div class="py-2 flex justify-between text-sm">
                            <span class="text-gray3F">Incoterm</span>
                            <span>{{ importData.incoterm }}</span>
                        </div>
                        <div class="py-2 flex justify-between text-sm">
                            <span class="text-gray3F">ETA (Fecha estimada de llegada)</span>
                            <span>{{ formatDate(importData.estimated_arrival_date)
                                }}</span>
                        </div>
                    </div>
                </el-tab-pane>
                <el-tab-pane label="Documentos" name="documents">
                    <div v-if="importData.documents && importData.documents.length" class="border rounded-lg">
                        <div class="grid grid-cols-2 px-4 py-2 border-b bg-gray-50 text-xs text-gray-500 font-semibold">
                            <span>Documentos adjuntos</span>
                            <span class="ml-7">Tipo</span>
                        </div>
                        <div v-for="doc in importData.documents" :key="doc.id"
                            class="grid grid-cols-2 px-4 py-2 border-b text-sm items-center">
                            <FileView :file="doc" />
                            <span class="text-gray-700 ml-7">{{ doc.classification }}</span>
                        </div>
                    </div>
                    <div v-else class="text-center text-gray-500 p-4">
                        <p>No hay documentos adjuntos en esta importación.</p>
                    </div>
                </el-tab-pane>
                <el-tab-pane label="Mercancías" name="merchandises">
                    <div v-if="importData.raw_materials && importData.raw_materials.length" class="border rounded-lg">
                        <div class="grid grid-cols-4 px-4 py-2 border-b bg-gray-50 text-xs text-gray-500 font-semibold">
                            <span>Materia prima</span>
                            <span class="text-right">Cantidad</span>
                            <span class="text-right">Costo unitario</span>
                            <span class="text-right">Total</span>
                        </div>
                        <div v-for="product in importData.raw_materials" :key="product.id"
                            class="grid grid-cols-4 px-4 py-3 border-b text-sm">
                            <div class="flex items-center space-x-2">
                                <PhotoIcon class="h-8 w-8 text-gray-300" />
                                <span>{{ product.name }}</span>
                            </div>
                            <span class="text-right self-center">{{ product.pivot.quantity }} {{ product.measure_unit
                            }}</span>
                            <span class="text-right self-center">{{ formatCurrency(product.pivot.unit_cost) }}</span>
                            <span class="text-right self-center font-semibold">{{ formatCurrency(product.pivot.quantity
                                * product.pivot.unit_cost) }}</span>
                        </div>
                        <div class="grid grid-cols-4 px-4 py-3 bg-gray-50 text-sm font-semibold">
                            <span class="col-span-3 text-right">Total</span>
                            <span class="text-right">{{ formatCurrency(merchandiseTotal) }}</span>
                        </div>
                    </div>
                    <div v-else class="text-center text-gray-500 p-4">
                        <p>No hay mercancías registradas en esta importación.</p>
                    </div>
                </el-tab-pane>
                <el-tab-pane label="Costos y pagos" name="costss">
                    <div class="border rounded-lg p-4">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-gray-800">Costos</h3>
                            <PrimaryButton @click="openCostModal" type="button"
                                class="!text-primary !bg-white border !border-gray-300 !rounded-md text-sm">
                                <PlusIcon class="size-4 inline mr-1" />
                                Registrar costo
                            </PrimaryButton>
                        </div>
                        <div class="border rounded-lg">
                            <div
                                class="grid grid-cols-4 px-4 py-2 border-b bg-gray-50 text-xs text-gray-500 font-semibold">
                                <span>Concepto</span>
                                <span class="text-right">Cantidad</span>
                                <span class="text-right">Monto pagado</span>
                                <span class="text-right">Saldo pendiente</span>
                            </div>
                            <div v-for="cost in importData.costs" :key="cost.id"
                                class="grid grid-cols-4 px-4 py-3 border-b text-sm items-center">
                                <span>{{ cost.concept }}</span>
                                <span class="text-right">{{ formatCurrency(cost.amount, cost.currency) }}</span>
                                <span class="text-right">{{ formatCurrency(cost.payments_sum_amount, cost.currency)
                                }}</span>
                                <span class="text-right font-semibold"
                                    :class="cost.amount - cost.payments_sum_amount > 0 ? 'text-[#C20000]' : null">
                                    {{ formatCurrency(cost.amount - cost.payments_sum_amount, cost.currency) }}
                                </span>
                            </div>
                            <div class="grid grid-cols-4 px-4 py-3 bg-gray-50 text-sm font-semibold">
                                <span class="col-span-3 text-right">Total</span>
                                <span class="text-right">{{ formatCurrency(totalCosts) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="border rounded-lg p-4 mt-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-gray-800">Pagos realizados</h3>
                            <PrimaryButton @click="openPaymentModal" type="button"
                                class="!text-primary !bg-white border !border-gray-300 !rounded-md text-sm">
                                <PlusIcon class="size-4 inline mr-1" />
                                Registrar pago
                            </PrimaryButton>
                        </div>
                        <div class="border rounded-lg">
                            <!-- Encabezado -->
                            <div
                                class="grid grid-cols-5 px-4 py-2 border-b bg-gray-50 text-xs text-gray-500 font-semibold">
                                <span>Fecha</span>
                                <span>Monto</span>
                                <span>Concepto relacionado</span>
                                <span>Notas</span>
                                <span>Documento vinculado</span>
                            </div>
                            <!-- Filas de Pagos -->
                            <template v-for="cost in importData.costs" :key="cost.id">
                                <div v-for="payment in cost.payments" :key="payment.id"
                                    class="grid grid-cols-5 px-4 py-3 border-b text-sm items-center">
                                    <span>{{ formatDate(payment.payment_date) }}</span>
                                    <span>{{ formatCurrency(payment.amount, cost.currency)
                                    }}</span>
                                    <span>{{ cost.concept }}</span>
                                    <span class="text-gray-500 truncate">{{ payment.notes }}</span>
                                    <a href="#" class="text-blue-500 hover:underline">Factura_PROV_1122.pdf</a>
                                </div>
                            </template>
                        </div>
                    </div>
                </el-tab-pane>
                <el-tab-pane label="Historial" name="history">
                    <p class="text-gray-500 p-4">Sección de historial en construcción.</p>
                </el-tab-pane>
            </el-tabs>
        </template>
    </DialogModal>
    <DialogModal :show="showCostModal" @close="showCostModal = false">
        <template #title>
            Agregar costo
        </template>
        <template #content>
            <p class="text-sm text-gray-500 mb-4">Agrega los costos relacionados con la importación; en el apartado de
                pagos podrás registrar el pago de los mismos.</p>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <InputLabel value="Concepto*" />
                    <el-input v-model="costForm.concept" placeholder="Ej. Flete internacional" />
                    <InputError :message="costForm.errors.concept" />
                </div>
                <div>
                    <InputLabel value="Monto*" />
                    <el-input v-model="costForm.amount" placeholder="Ej. 15,000.00">
                        <template #prepend>$</template>
                    </el-input>
                    <InputError :message="costForm.errors.amount" />
                </div>
                <div class="col-span-1">
                    <InputLabel value="Moneda" />
                    <el-select v-model="costForm.currency" class="!w-full">
                        <el-option label="USD" value="USD" />
                        <el-option label="MXN" value="MXN" />
                    </el-select>
                    <InputError :message="costForm.errors.currency" />
                </div>
            </div>
        </template>
        <template #footer>
            <CancelButton @click="showCostModal = false">Cancelar</CancelButton>
            <PrimaryButton @click="storeCost" :disabled="costForm.processing" class="ml-2">
                Agregar costo
            </PrimaryButton>
        </template>
    </DialogModal>

    <!-- Modal para Registrar Pago -->
    <DialogModal :show="showPaymentModal" @close="showPaymentModal = false">
        <template #title>
            Registrar pago
        </template>
        <template #content>
            <p class="text-sm text-gray-500 mb-4">Registra los pagos de los costos agregados.</p>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-full">
                    <InputLabel value="Costo relacionado*" />
                    <el-select v-model="paymentForm.import_cost_id" placeholder="Selecciona el concepto del costo"
                        class="!w-full">
                        <el-option v-for="cost in importData.costs" :key="cost.id" :label="cost.concept"
                            :value="cost.id" />
                    </el-select>
                    <InputError :message="paymentForm.errors.import_cost_id" />
                </div>
                <div>
                    <InputLabel value="Monto del pago*" />
                    <el-input v-model="paymentForm.amount" placeholder="Ej. 7,000.00">
                        <template #append>USD</template> <!-- Esto debería ser dinámico -->
                    </el-input>
                    <InputError :message="paymentForm.errors.amount" />
                </div>
                <div>
                    <InputLabel value="Fecha de pago" />
                    <el-date-picker class="!w-full" v-model="paymentForm.payment_date" type="date"
                        placeholder="dd/mm/aa" value-format="YYYY-MM-DD" format="DD/MM/YYYY" />
                    <InputError :message="paymentForm.errors.payment_date" />
                </div>
                <div class="col-span-full">
                    <InputLabel value="Notas" />
                    <el-input v-model="paymentForm.notes" type="textarea" :rows="2"
                        placeholder="Ej. Anticipo del 50%" />
                    <InputError :message="paymentForm.errors.notes" />
                </div>
                <div class="col-span-full">
                    <InputLabel value="Documento" />
                    <button @click="triggerPaymentFileInput" type="button"
                        class="w-full mt-1 text-sm text-primary border border-gray-300 rounded-md py-2 hover:bg-gray-50">
                        <ArrowUpTrayIcon class="size-4 mr-1 inline" />
                        Adjuntar comprobante de pago
                    </button>
                    <input type="file" @change="handlePaymentFile" ref="paymentFileInput" hidden>
                    <div v-if="paymentForm.document" class="mt-2">
                        <FileView :file="{ file_name: paymentForm.document.name, size: paymentForm.document.size }"
                            can-delete @delete-file="paymentForm.document = null" />
                    </div>
                    <InputError :message="paymentForm.errors.document" />
                </div>
            </div>
        </template>
        <template #footer>
            <CancelButton @click="showPaymentModal = false">Cancelar</CancelButton>
            <PrimaryButton @click="storePayment" :disabled="paymentForm.processing" class="ml-2">
                Registrar pago
            </PrimaryButton>
        </template>
    </DialogModal>
</template>

<script>
import { useForm } from '@inertiajs/vue3';
import CancelButton from '@/Components/MyComponents/CancelButton.vue';
import { markRaw } from 'vue';
import DialogModal from '@/Components/DialogModal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ArchiveBoxIcon, PencilIcon, PhotoIcon, PlusIcon, TrashIcon, ArrowUpTrayIcon } from '@heroicons/vue/24/outline';
import { router } from '@inertiajs/vue3';
import AnclaIcon from '@/Components/MyComponents/Icons/AnclaIcon.vue';
import PalomitaIcon from '@/Components/MyComponents/Icons/PalomitaIcon.vue';
import MarkerIcon from '@/Components/MyComponents/Icons/MarkerIcon.vue';
import BarcoIcon from '@/Components/MyComponents/Icons/BarcoIcon.vue';
import FileView from '@/Components/MyComponents/Ticket/FileView.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

export default {
    components: {
        DialogModal,
        PrimaryButton,
        PencilIcon,
        ArchiveBoxIcon,
        PhotoIcon,
        PlusIcon,
        TrashIcon,
        ArrowUpTrayIcon,
        AnclaIcon,
        PalomitaIcon,
        MarkerIcon,
        BarcoIcon,
        FileView,
        CancelButton,
        InputLabel,
        InputError,
    },
    emits: ['reload', 'close'],
    props: {
        show: {
            type: Boolean,
            default: false,
        },
        importData: {
            type: Object,
            required: true,
        },
    },
    data() {
        const costForm = useForm({
            import_id: this.importData.id,
            concept: null,
            amount: null,
            currency: 'USD',
        });

        const paymentForm = useForm({
            import_cost_id: null,
            amount: null,
            payment_date: new Date().toISOString().slice(0, 10), // Fecha de hoy
            notes: null,
            document: null,
        });

        return {
            costForm,
            paymentForm,
            activeTab: 'general',
            showCostModal: false,
            showPaymentModal: false,
            statuses: {
                'Con proveedor': markRaw(ArchiveBoxIcon),
                'Puerto origen': markRaw(AnclaIcon),
                'En tránsito marítimo': markRaw(BarcoIcon),
                'Puerto destino': markRaw(MarkerIcon),
                'Entregado': markRaw(PalomitaIcon),
            },
        };
    },
    computed: {
        merchandiseTotal() {
            if (!this.importData.raw_materials) return 0;

            return this.importData.raw_materials.reduce((total, product) => {
                return total + (product.pivot.quantity * product.pivot.unit_cost);
            }, 0);
        },
        totalCosts() {
            if (!this.importData.costs) return 0;
            return this.importData.costs.reduce((total, cost) => {
                const amount = cost.pendent_amount; // Tasa de ejemplo
                return total + amount;
            }, 0);
        }
    },
    methods: {
        openCostModal() {
            this.costForm.reset();
            this.costForm.import_id = this.importData.id; // Asegurarse de tener el ID
            this.showCostModal = true;
        },
        storeCost() {
            this.costForm.post(route('imports.costs.store', this.importData.id), {
                preserveScroll: true,
                onSuccess: () => {
                    this.showCostModal = false;
                    this.$notify({ title: 'Éxito', message: 'Costo agregado', type: 'success' });
                    // recargar los datos para ver el cambio al instante
                    this.$emit('reload');
                },
            });
        },
        openPaymentModal() {
            this.paymentForm.reset();
            this.showPaymentModal = true;
        },
        storePayment() {
            this.paymentForm.post(route('imports.payments.store', this.importData.id), {
                preserveScroll: true,
                onSuccess: () => {
                    this.showPaymentModal = false;
                    this.$notify({ title: 'Éxito', message: 'Pago registrado', type: 'success' });
                    this.$emit('reload');
                },
            });
        },
        triggerPaymentFileInput() {
            this.$refs.paymentFileInput.click();
        },
        handlePaymentFile(event) {
            this.paymentForm.document = event.target.files[0];
        },
        formatCurrency(value, currency = 'USD') {
            return parseFloat(value).toLocaleString('en-US', {
                style: 'currency',
                currency: currency,
            });
        },
        closeModal() {
            this.$emit('close');
        },
        editImport() {
            router.get(route('imports.edit', this.importData.id));
        },
        formatDate(dateString) {
            if (!dateString) return 'N/A';
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            return new Date(dateString).toLocaleDateString('es-ES', options);
        },
        formatStatus(status) {
            if (!status) return '';
            return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
        },
        statusClass(status) {
            const classes = {
                'Con proveedor': { backgroundColor: '#EDEDED', color: '#3f3f3f' },
                'Puerto origen': { backgroundColor: '#FCFFD8', color: '#645E20' },
                'En tránsito marítimo': { backgroundColor: '#FFEFE2', color: '#C06102' },
                'Puerto destino': { backgroundColor: '#E9F6FF', color: '#004C7B' },
                'Entregado': { backgroundColor: '#E9FFDD', color: '#448734' },
            };
            return classes[status] || 'bg-gray-100 text-gray-700';
        }
    },
};
</script>
