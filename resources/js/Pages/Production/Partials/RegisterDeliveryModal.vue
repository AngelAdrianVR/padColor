<template>
    <DialogModal :show="show" @close="$emit('close')">
        <template #title>
            <h1 class="font-semibold">{{ title }}</h1>
        </template>
        <template #content>
            <div class="space-y-4">
                <div>
                    <InputLabel value="Tipo de entrega" />
                    <el-select v-model="form.type" placeholder="Seleccione" class="!w-full">
                        <el-option v-for="item in ['Única', 'Parcialidades']" :key="item" :label="item" :value="item" />
                    </el-select>
                    <InputError :message="form.errors.type" />
                </div>

                <div v-if="form.type" class="border-t pt-4 space-y-4">
                    <div v-if="form.type === 'Parcialidades'"
                        class="col-span-full bg-[#E9E9E9] py-2 px-4 rounded-full text-sm">
                        Parcialidad {{ partialNumber }}
                    </div>
                    <div>
                        <InputLabel :value="quantityLabel" />
                        <el-input-number v-model="form.quantity" class="!w-full" :min="0" />
                        <InputError :message="form.errors.quantity" />
                    </div>
                    <div>
                        <InputLabel value="Fecha de entrega" />
                        <el-date-picker v-model="form.date" class="!w-full" type="datetime" placeholder="dd/mm/aa hh:mm"
                            value-format="YYYY-MM-DD HH:mm:ss" format="DD/MM/YYYY hh:mm A" />
                        <InputError :message="form.errors.date" />
                    </div>
                    <div v-if="form.type === 'Parcialidades'">
                        <el-checkbox v-model="form.is_last_delivery" label="Es la última entrega" size="large" />
                    </div>
                    <template v-if="context === 'inspection' && (form.type === 'Única' || form.is_last_delivery)">
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
                    <div>
                        <InputLabel value="Notas" />
                        <el-input v-model="form.notes" :rows="2" type="textarea"
                            placeholder="Notas relacionadas con la entrega" />
                        <InputError :message="form.errors.notes" />
                    </div>
                </div>
            </div>
        </template>
        <template #footer>
            <div class="flex justify-end space-x-2">
                <CancelButton @click="$emit('close')" :disabled="form.processing">Cancelar</CancelButton>
                <PrimaryButton @click="submit" :disabled="form.processing || !form.type">Registrar</PrimaryButton>
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
    components: {
        DialogModal,
        InputLabel,
        InputError,
        PrimaryButton,
        CancelButton
    },
    props: {
        show: Boolean,
        production: Object,
        context: String, // 'inspection' or 'packing'
        defaultQuantity: Number,
    },
    emits: ['close', 'submit'],
    data() {
        return {
            form: useForm({
                context: this.context,
                type: null,
                quantity: null,
                date: null,
                notes: null,
                is_last_delivery: false,
                scrap_quantity: 0,
                shortage_quantity: 0,
            }),
        };
    },
    watch: {
        show(newVal) {
            if (newVal) {
                this.form.reset();
                this.form.context = this.context;
                this.form.date = format(new Date(), "yyyy-MM-dd HH:mm:ss");

                // Set default quantity for unique delivery or first partial
                this.form.quantity = this.defaultQuantity;
            }
        },
        'form.type'(newVal) {
            if (newVal === 'Única') {
                this.form.is_last_delivery = true;
                this.form.quantity = this.defaultQuantity;
            } else {
                this.form.is_last_delivery = false;
            }
        }
    },
    computed: {
        title() {
            return `Registrar Entrega de ${this.context === 'inspection' ? 'Inspección' : 'Empaques'}`;
        },
        quantityLabel() {
            return this.form.type === 'Única' ? 'Cantidad a entregar' : 'Cantidad entregada';
        },
        partialNumber() {
            if (this.context === 'inspection') {
                return (this.production.partials?.length ?? 0) + 1;
            } else if (this.context === 'packing') {
                return (this.production.packing_partials?.length ?? 0) + 1;
            }
            return 1;
        }
    },
    methods: {
        submit() {
            this.$emit('submit', this.form);
        },
    }
};
</script>