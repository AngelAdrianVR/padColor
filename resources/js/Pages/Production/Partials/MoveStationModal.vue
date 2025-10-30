<template>
    <DialogModal :show="show" @close="$emit('close')">
        <template #title>
            {{ title }}
        </template>
        <template #content>
            <p class="text-sm text-gray-600 mb-4">
                {{ infoText }}
            </p>
            <div class="space-y-4">
                <!-- Campos Principales -->
                <div>
                    <InputLabel value="Siguiente estación*" />
                    <el-select v-model="form.next_station" class="w-full" placeholder="Selecciona la siguiente estación"
                        @change="handleStationChange">
                        <el-option v-for="station in availableStations" :key="station.name" :label="station.name"
                            :value="station.name" />
                    </el-select>
                    <InputError :message="form.errors.next_station" />
                </div>
                <div>
                    <InputLabel value="Máquina" />
                    <el-select v-model="form.machine_id" class="w-full" placeholder="Selecciona la máquina" clearable>
                        <el-option v-for="machine in machines" :key="machine.id" :label="machine.name"
                            :value="machine.id" />
                    </el-select>
                    <InputError :message="form.errors.machine_id" />
                </div>

                <!-- Campos Condicionales para Entregas (Calidad, Inspección) -->
                <div v-if="showDeliveryFields" class="grid grid-cols-2 gap-x-3 gap-y-2 border-t pt-4">
                    <div class="col-span-full" v-if="isMoveToPacking">
                        <InputLabel value="Cantidad a mover a empaques*" />
                        <el-input-number v-model="form.quantity" placeholder="Ingresa la cantidad" :min="0"
                            class="!w-full" />
                        <InputError :message="form.errors.quantity" />
                    </div>
                     <div class="col-span-full" v-else>
                        <InputLabel value="Cantidad a entregar*" />
                        <el-input-number v-model="form.quantity" placeholder="Ingresa la cantidad" :min="0"
                            class="!w-full" />
                        <InputError :message="form.errors.quantity" />
                    </div>
                    <div>
                        <InputLabel value="Fecha*" />
                        <el-date-picker class="!w-full" v-model="form.date" type="datetime"
                            placeholder="dd/mm/aa hh:mm" value-format="YYYY-MM-DD HH:mm:ss"
                            format="DD/MM/YYYY hh:mm A" />
                        <InputError :message="form.errors.date" />
                    </div>
                    <div v-if="!isMoveToPacking">
                        <InputLabel value="Merma" />
                        <el-input-number v-model="form.scrap_quantity" placeholder="Ingresa la cantidad" :min="0"
                            class="!w-full" />
                        <InputError :message="form.errors.scrap_quantity" />
                    </div>
                    <div v-if="!isMoveToPacking">
                        <InputLabel value="Diferencia" />
                        <el-input-number v-model="form.shortage_quantity" placeholder="Ingresa la cantidad" :min="0"
                            class="!w-full" />
                        <InputError :message="form.errors.shortage_quantity" />
                    </div>
                </div>

                 <!-- Campos Condicionales para Regresos (Reproceso) -->
                <div v-if="showReturnFields" class="grid grid-cols-1 gap-y-2 border-t pt-4">
                    <div>
                        <InputLabel value="Cantidad a regresar*" />
                        <el-input-number v-model="form.quantity" placeholder="Ingresa la cantidad" :min="0" class="!w-full" />
                        <InputError :message="form.errors.quantity" />
                    </div>
                    <div>
                        <InputLabel value="Motivo de regreso*" />
                        <el-input v-model="form.reason" :rows="3" type="textarea" placeholder="Escribe el motivo del regreso" />
                        <InputError :message="form.errors.reason" />
                    </div>
                </div>

                <!-- Campo de Notas (siempre visible) -->
                <div class="border-t pt-4">
                    <InputLabel value="Notas (opcional)" />
                    <el-input v-model="form.notes" type="textarea" :rows="2"
                        placeholder="Escribe información útil para la persona que continuará la tarea." />
                    <InputError :message="form.errors.notes" />
                </div>
            </div>
        </template>
        <template #footer>
            <div class="flex justify-end space-x-2">
                <CancelButton @click="$emit('close')" :disabled="form.processing">
                    Cancelar
                </CancelButton>
                <PrimaryButton @click="submit" :disabled="form.processing || !isFormValid">
                    {{ submitButtonText }}
                </PrimaryButton>
            </div>
        </template>
    </DialogModal>
</template>

<script>
import DialogModal from '@/Components/DialogModal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import CancelButton from '@/Components/MyComponents/CancelButton.vue';
import { useForm } from '@inertiajs/vue3';
import { format } from 'date-fns';

export default {
    name: 'MoveStationModal',
    components: {
        DialogModal,
        InputLabel,
        InputError,
        PrimaryButton,
        CancelButton,
    },
    props: {
        show: Boolean,
        processType: String,
        availableStations: Array,
        machines: Array,
        production: Object,
        defaultQuantity: Number,
    },
    emits: ['close', 'submit'],
    data() {
        return {
            form: useForm({
                next_station: null,
                machine_id: null,
                notes: '',
                quantity: 0,
                date: null,
                scrap_quantity: 0,
                shortage_quantity: 0,
                reason: '',
                processType: this.processType,
            }),
        };
    },
    computed: {
        title() {
            if (this.showReturnFields) return 'Regresar de estación';
            return this.processType === 'skip' ? 'Mover a la siguiente estación' : 'Finalizar y mover de estación';
        },
        infoText() {
            if (this.showReturnFields) return 'La orden regresará a una estación anterior para su revisión o reproceso.';
            return this.processType === 'skip'
                ? 'La orden avanzará a la siguiente estación. El tiempo transcurrido en la estación actual se registrará como "Tiempo en espera".'
                : 'La estación actual se marcará como finalizada y la orden avanzará a la siguiente etapa del proceso.';
        },
        submitButtonText() {
            if (this.showReturnFields) return 'Regresar orden';
            return this.processType === 'skip' ? 'Mover orden' : 'Finalizar y mover';
        },
        currentStation() {
            return this.production?.station;
        },
        // --- Computed properties to show/hide conditional fields ---
        isMoveToQuality() {
            return this.form.next_station === 'Calidad' && this.currentStation !== 'Inspección';
        },
        isMoveToInspection() {
            return this.form.next_station === 'Inspección';
        },
        isMoveToPacking() {
            return this.form.next_station === 'Empaques';
        },
        showDeliveryFields() {
            return this.isMoveToQuality || this.isMoveToInspection || this.isMoveToPacking;
        },
        isReturnToReprocess() {
            return this.form.next_station === 'X Reproceso' && this.currentStation === 'Calidad';
        },
        isReturnToQuality() {
            return this.form.next_station === 'Calidad' && this.currentStation === 'Inspección';
        },
        showReturnFields() {
            return this.isReturnToReprocess || this.isReturnToQuality;
        },
        // --- Form validation ---
        isFormValid() {
            if (!this.form.next_station) return false;

            if (this.showDeliveryFields) {
                if (this.form.quantity === null || this.form.date === null) return false;
            }

            if (this.showReturnFields) {
                 if (this.form.quantity === null || !this.form.reason) return false;
            }

            return true;
        }
    },
    methods: {
        submit() {
            this.$emit('submit', { ...this.form.data() });
        },
        handleStationChange(newStation) {
            this.form.reset(
                'quantity', 'date', 'scrap_quantity', 'shortage_quantity', 'reason'
            );
            this.form.date = format(new Date(), "yyyy-MM-dd HH:mm:ss");

            if (this.isMoveToQuality) {
                this.form.quantity = this.production?.quantity ?? 0;
            } else if (this.isMoveToInspection) {
                this.form.quantity = this.production?.close_quantity ?? 0;
            } else if (this.isMoveToPacking) {
                 this.form.quantity = this.production?.quality_quantity ?? this.production?.close_quantity ?? this.production?.quantity;
            } else if (this.isReturnToReprocess || this.isReturnToQuality) {
                this.form.quantity = this.isReturnToReprocess 
                    ? this.production?.close_quantity 
                    : this.production?.quality_quantity;
            }
        }
    },
    watch: {
        show(newVal) {
            if (newVal) {
                this.form.processType = this.processType;
                this.form.machine_id = this.production?.machine_id;
                this.form.quantity = this.defaultQuantity;
            } else {
                this.form.reset();
                this.form.clearErrors();
            }
        }
    }
};
</script>

