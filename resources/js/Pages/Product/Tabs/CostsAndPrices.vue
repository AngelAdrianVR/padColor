<template>
    <div class="pt-1">
        <!-- ==================================================== -->
        <!-- MODO EDICIÓN                                         -->
        <!-- ==================================================== -->
        <template v-if="isEditing">
            <p class="text-gray-600 text-sm mb-6">{{ description }}</p>
            <div v-for="(fields, sectionName) in fieldsBySection" :key="sectionName"
                class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-700 mb-4 capitalize flex items-center">
                    <BanknotesIcon class="w-5 h-5 mr-2 text-gray-500" />
                    {{ formatSectionName(sectionName) }}
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-4">
                    <div v-for="field in fields" :key="field.slug">
                        <label class="el-form-item__label">{{ field.label }}</label>
                        <el-input-number v-model="form.sheet_data[field.slug]" :precision="2" :step="0.01" :min="0"
                            controls-position="right" class="w-full">
                            <template #prefix>$</template>
                        </el-input-number>
                    </div>
                </div>
            </div>
        </template>

        <!-- ==================================================== -->
        <!-- MODO VISTA                                           -->
        <!-- ==================================================== -->
        <template v-else>
            <p class="text-gray-600 text-sm mb-6">{{ description }}</p>
            <div v-for="(fields, sectionName) in fieldsBySection" :key="sectionName"
                class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-700 mb-4 capitalize flex items-center">
                    <BanknotesIcon class="w-5 h-5 mr-2 text-gray-500" />
                    {{ formatSectionName(sectionName) }}
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3">
                    <div v-for="field in fields" :key="field.slug"
                        class="flex justify-between items-start text-sm border-b pb-2">
                        <span class="text-gray-500 mr-2">{{ field.label }}</span>
                        <span class="text-gray-800 font-medium text-right">{{
                            formatCurrency(product.sheet_data[field.slug]) }}</span>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<script>
import { BanknotesIcon } from '@heroicons/vue/24/outline';

export default {
    name: 'CostsAndPricesTab',
    components: { BanknotesIcon },
    props: {
        product: { type: Object, required: true },
        fieldsBySection: { type: Object, required: true },
        description: { type: String, default: '' },
        isEditing: { type: Boolean, default: false },
        form: { type: Object, required: true }
    },
    methods: {
        formatSectionName(slug) {
            if (!slug) return '';
            return slug.replace(/_/g, ' ');
        },
        // Nuevo método para formatear números como moneda en el modo de vista
        formatCurrency(value) {
            if (value === null || value === undefined || isNaN(Number(value))) {
                return '-';
            }
            const numberValue = Number(value);
            // Formatea el número a moneda local (MXN)
            return '$' + numberValue.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    }
}
</script>