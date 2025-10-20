<template>
    <DialogModal :show="show" @close="$emit('close')">
        <template #title>
            Pausar tarea
        </template>
        <template #content>
            <p class="text-sm text-gray-600 mb-4">
                Menciona el motivo de pausa de la tarea.
            </p>
            <div class="space-y-4">
                <div>
                    <InputLabel value="Motivo de pausa*" />
                    <el-select v-model="form.reason" class="w-full" placeholder="Selecciona el motivo de pausa">
                        <el-option v-for="reason in pauseReasons" :key="reason" :label="reason" :value="reason" />
                    </el-select>
                    <InputError :message="form.errors.reason" />
                </div>
                <div>
                    <InputLabel :value="notesLabel" />
                    <el-input v-model="form.notes" type="textarea" :rows="2"
                        placeholder="Escribe aquí los detalles..." />
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
                    Pausar
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

export default {
    name: 'PauseStationModal',
    components: {
        DialogModal,
        InputLabel,
        InputError,
        PrimaryButton,
        CancelButton,
    },
    props: {
        show: Boolean,
    },
    emits: ['close', 'submit'],
    data() {
        return {
            form: useForm({
                reason: null,
                notes: '',
            }),
            pauseReasons: [
                'Falta de materia',
                'Mantenimiento o ajuste de máquina',
                'Cambio de turno',
                'Espera de instrucciones o aprobación',
                'Descanso',
                'Corte de energía o falla externa',
                'Otro',
            ],
        };
    },
    computed: {
        isOtherReasonSelected() {
            return this.form.reason === 'Otro';
        },
        notesLabel() {
            return this.isOtherReasonSelected ? 'Describe el motivo de la pausa*' : 'Notas (opcional)';
        },
        isFormValid() {
            if (!this.form.reason) {
                return false;
            }
            if (this.isOtherReasonSelected && !this.form.notes) {
                return false;
            }
            return true;
        }
    },
    methods: {
        submit() {
            // Simple validation before emitting
            this.form.errors.reason = !this.form.reason ? 'El motivo es obligatorio.' : null;
            this.form.errors.notes = (this.isOtherReasonSelected && !this.form.notes) ? 'La descripción es obligatoria si el motivo es "Otro".' : null;

            if (this.form.hasErrors) {
                return;
            }

            this.$emit('submit', {
                reason: this.form.reason,
                // Si el motivo es "Otro", las notas se convierten en la razón principal.
                notes: this.isOtherReasonSelected ? this.form.notes : `${this.form.reason}${this.form.notes ? ': ' + this.form.notes : ''}`
            });
            this.form.reset();
        }
    },
    watch: {
        show(newVal) {
            if (!newVal) {
                this.form.reset();
                this.form.clearErrors();
            }
        }
    }
};
</script>