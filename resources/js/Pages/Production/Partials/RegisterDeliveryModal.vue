<template>
    <DialogModal :show="show" @close="$emit('close')">
        <template #title>
            <h1 class="font-semibold">{{ title }}</h1>
        </template>
        <template #content>
            <div class="grid grid-cols-2 gap-x-3 gap-y-2 text-sm">
                <!-- Type Selector (only for first delivery) -->
                <div v-if="!isPartial" class="col-span-full">
                    <InputLabel value="Tipo de entrega*" />
                    <el-select v-model="form.type" placeholder="Seleccione" class="!w-full">
                        <el-option v-for="item in ['Única', 'Parcialidades']" :key="item" :label="item" :value="item" />
                    </el-select>
                    <InputError :message="form.errors.type" />
                </div>

                <!-- Partial Number Info -->
                <div v-if="isPartial" class="col-span-full bg-gray-100 py-2 px-4 rounded-full text-center font-semibold text-gray-600">
                    Parcialidad {{ production.partials?.length ? production.partials?.length + 1 : 1 }}
                </div>
                 <div v-if="form.type === 'Parcialidades' && !isPartial" class="col-span-full bg-gray-100 py-2 px-4 rounded-full text-center font-semibold text-gray-600">
                    Parcialidad 1
                </div>

                <!-- Fields for Unique or Partial deliveries -->
                <template v-if="form.type || isPartial">
                    <div>
                        <InputLabel value="Cantidad entregada*" />
                        <el-input-number v-model="form.quantity" placeholder="Ingresa la cantidad" :min="0" class="!w-full" />
                        <InputError :message="form.errors.quantity" />
                    </div>
                    <div>
                        <InputLabel value="Fecha de entrega*" />
                        <el-date-picker class="!w-full" v-model="form.date" type="datetime" placeholder="dd/mm/aa hh:mm"
                            value-format="YYYY-MM-DD HH:mm:ss" format="DD/MM/YYYY hh:mm A" />
                        <InputError :message="form.errors.date" />
                    </div>
                    
                    <!-- Fields specific to Inspection -->
                    <template v-if="context === 'inspection' && (form.type === 'Única' || (isPartial && form.is_last_delivery))">
                         <div>
                            <InputLabel value="Merma total en inspección" />
                            <el-input-number v-model="form.scrap_quantity" :min="0" class="!w-full" />
                            <InputError :message="form.errors.scrap_quantity" />
                        </div>
                        <div>
                            <InputLabel value="Diferencia total en inspección" />
                            <el-input-number v-model="form.shortage_quantity" :min="0" class="!w-full" />
                            <InputError :message="form.errors.shortage_quantity" />
                        </div>
                    </template>

                    <div class="col-span-full" v-if="isPartial">
                        <el-checkbox v-model="form.is_last_delivery" label="Esta es la última entrega" size="large" />
                    </div>

                    <div class="col-span-full">
                        <InputLabel value="Notas" />
                        <el-input v-model="form.notes" :rows="2" type="textarea"
                            placeholder="Notas relacionadas con la entrega." />
                        <InputError :message="form.errors.notes" />
                    </div>
                </template>
            </div>
        </template>
        <template #footer>
            <div class="flex justify-end space-x-2">
                <CancelButton @click="$emit('close')" :disabled="form.processing">
                    Cancelar
                </CancelButton>
                <PrimaryButton @click="submit" :disabled="form.processing || (!form.type && !isPartial)">
                    <i v-if="form.processing" class="fa-solid fa-circle-notch fa-spin mr-2"></i>
                    {{ submitButtonText }}
                </PrimaryButton>
            </div>
        </template>
    </DialogModal>
</template>

<script>
import DialogModal from '@/Components/DialogModal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import CancelButton from '@/Components/MyComponents/CancelButton.vue';
import { useForm } from '@inertiajs/vue3';
import { format } from 'date-fns';

export default {
    components: {
        DialogModal,
        InputError,
        InputLabel,
        PrimaryButton,
        CancelButton
    },
    props: {
        show: Boolean,
        production: Object,
        context: String, // 'inspection' or 'packing'
        isPartial: Boolean,
    },
    emits: ['close', 'submit'],
    data() {
        return {
            form: useForm({
                type: null,
                quantity: 0,
                date: null,
                scrap_quantity: 0,
                shortage_quantity: 0,
                notes: '',
                is_last_delivery: false,
            }),
        };
    },
    watch: {
        show(newVal) {
            if (newVal) {
                this.form.reset();
                this.form.date = format(new Date(), "yyyy-MM-dd HH:mm:ss");
                if (this.isPartial) {
                    this.form.type = 'Parcialidades'; // Pre-set for partials
                }
            }
        }
    },
    computed: {
        title() {
            if (this.isPartial) {
                return `Registrar Parcialidad de ${this.context === 'inspection' ? 'Inspección' : 'Empaques'}`;
            }
            return `Registrar Entrega de ${this.context === 'inspection' ? 'Inspección' : 'Empaques'}`;
        },
        submitButtonText() {
            if (this.form.type === 'Única') {
                return 'Finalizar y Entregar';
            }
            return 'Registrar Parcialidad';
        }
    },
    methods: {
        submit() {
            this.$emit('submit', this.form);
        }
    }
}
</script>