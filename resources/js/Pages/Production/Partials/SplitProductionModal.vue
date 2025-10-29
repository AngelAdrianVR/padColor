<template>
    <DialogModal :show="show" @close="$emit('close')" maxWidth="2xl">
        <template #title>
            Dividir Orden de Producción ({{ production.folio }})
        </template>
        <template #content>
            <p class="text-sm text-gray-600">
                Define los componentes o lotes en los que se dividirá la orden. La suma de las cantidades de las nuevas
                partes se restará de la cantidad disponible.
            </p>
            <div class="my-3 p-3 bg-gray-100 rounded-lg">
                <div class="flex justify-between font-semibold">
                    <span>Cantidad total de la orden:</span>
                    <span>{{ production.quantity?.toLocaleString('es-MX') }}</span>
                </div>
                <div class="flex justify-between font-semibold text-blue-600">
                    <span>Cantidad disponible para asignar:</span>
                    <span>{{ availableQuantity?.toLocaleString('es-MX') }}</span>
                </div>
                <div class="flex justify-between font-bold text-lg mt-2 pt-2 border-t">
                    <span>Cantidad restante (después de esta división):</span>
                    <span :class="{ 'text-red-600': remainingQuantity < 0 }">{{
                        remainingQuantity?.toLocaleString('es-MX') }}</span>
                </div>
            </div>

            <div class="space-y-4 mt-4 max-h-60vh overflow-y-auto">
                <div v-for="(part, index) in form.parts" :key="index" class="p-4 border rounded-lg relative">
                    <button @click="removePart(index)"
                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full size-6 flex items-center justify-center hover:bg-red-600">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <!-- CAMPO NUEVO PARA NOMBRE DE COMPONENTE -->
                    <div>
                        <InputLabel :value="`Nombre de Componente / Parte ${index + 1}*`" />
                        <el-input v-model="part.identifier" placeholder="Ej. Base, Tapa, Lote A" />
                        <InputError :message="form.errors[`parts.${index}.identifier`]" />
                    </div>

                    <div class="grid grid-cols-2 gap-4 mt-2">
                        <div>
                            <InputLabel :value="`Cantidad*`" />
                            <el-input-number v-model="part.quantity" :min="1" :max="availableQuantity"
                                class="!w-full" />
                            <InputError :message="form.errors[`parts.${index}.quantity`]" />
                        </div>
                        <div>
                            <InputLabel value="Estación de Destino*" />
                            <el-select v-model="part.station" class="!w-full" placeholder="Selecciona estación">
                                <el-option v-for="station in availableStations" :key="station.name"
                                    :label="station.name" :value="station.name" />
                            </el-select>
                            <InputError :message="form.errors[`parts.${index}.station`]" />
                        </div>
                        <div class="col-span-2">
                            <InputLabel value="Máquina (Opcional)" />
                            <el-select v-model="part.machine_id" class="!w-full" placeholder="Selecciona máquina"
                                clearable>
                                <el-option v-for="machine in machines" :key="machine.id" :label="machine.name"
                                    :value="machine.id" />
                            </el-select>
                            <InputError :message="form.errors[`parts.${index}.machine_id`]" />
                        </div>
                    </div>
                </div>
            </div>

            <button @click="addPart"
                class="mt-4 text-sm text-blue-600 font-semibold hover:text-blue-800 flex items-center space-x-1">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="size-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Añadir otra parte / componente</span>
            </button>

            <InputError :message="form.errors.parts" class="mt-2 text-red-600 font-semibold" />
            <InputError :message="form.errors.general" class="mt-2 text-red-600 font-semibold" />
        </template>
        <template #footer>
            <div class="flex justify-end space-x-2">
                <CancelButton @click="$emit('close')" :disabled="form.processing">Cancelar</CancelButton>
                <PrimaryButton @click="submit" :disabled="isSubmitDisabled">
                    <span v-if="form.processing">Dividiendo...</span>
                    <span v-else>Confirmar División</span>
                </PrimaryButton>
            </div>
        </template>
    </DialogModal>
</template>

<script>
import { useForm } from '@inertiajs/vue3';
import DialogModal from '@/Components/DialogModal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import CancelButton from '@/Components/MyComponents/CancelButton.vue';

export default {
    name: 'SplitProductionModal',
    components: {
        DialogModal, InputLabel, InputError, PrimaryButton, CancelButton
    },
    props: {
        show: Boolean,
        production: Object,
        stations: Array,
        machines: Array,
    },
    emits: ['close'],
    data() {
        return {
            form: useForm({
                parts: []
            }),
            lastPartIdentifier: 'A', // Para la UI
        };
    },
    computed: {
        availableQuantity() {
            // Usa 'unassigned_quantity' si existe, si no, la cantidad total
            return this.production?.unassigned_quantity ?? this.production?.quantity ?? 0;
        },
        assignedQuantity() {
            return this.form.parts.reduce((sum, part) => sum + (part.quantity || 0), 0);
        },
        remainingQuantity() {
            return this.availableQuantity - this.assignedQuantity;
        },
        isSubmitDisabled() {
            return this.form.processing ||
                this.form.parts.length === 0 ||
                this.remainingQuantity < 0 ||
                this.form.parts.some(p => !p.identifier || !p.quantity || !p.station);
        }
    },
    methods: {
        addPart() {
            this.form.parts.push({
                identifier: null,
                quantity: null,
                station: null,
                machine_id: null,
            });
        },
        removePart(index) {
            this.form.parts.splice(index, 1);
        },
        getPartIdentifier(index) {
            // Genera 'A', 'B', 'C', etc.
            return String.fromCharCode(65 + index);
        },
        submit() {
            if (this.isSubmitDisabled) return;

            this.form.post(route('productions.split', this.production.id), {
                onSuccess: () => {
                    this.$emit('close');
                    this.form.reset();
                    this.$notify({ title: "Orden dividida", message: "Las nuevas partes aparecerán en la lista.", type: "success" });
                },
                onError: (errors) => {
                    this.$notify({ title: "Error", message: "No se pudo dividir la orden. Revisa los errores.", type: "error" });
                }
            });
        }
    },
    watch: {
        show(newVal) {
            if (newVal) {
                this.form.reset();
                this.addPart(); // Inicia con una parte por defecto
            }
        }
    }
}
</script>