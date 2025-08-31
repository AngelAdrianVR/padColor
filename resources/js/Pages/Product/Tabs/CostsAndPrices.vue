<template>
    <div class="pt-1">
        <p class="text-gray-600 text-sm mb-6">{{ description }}</p>

         <div v-for="(fields, sectionName) in fieldsBySection" :key="sectionName" class="bg-white rounded-xl border border-gray-200 p-5">
             <h3 class="font-semibold text-gray-700 mb-4 capitalize flex items-center">
                <BanknotesIcon class="w-5 h-5 mr-2 text-gray-500" />
                {{ formatSectionName(sectionName) }}
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3">
                <div v-for="field in fields" :key="field.slug" class="flex justify-between items-start text-sm border-b pb-2">
                    <span class="text-gray-500 mr-2">{{ field.label }}</span>
                    <span class="text-gray-800 font-medium text-right">{{ product.sheet_data[field.slug] || '-' }}</span>
                </div>
            </div>
        </div>
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
    },
     methods: {
        formatSectionName(slug) {
            if (!slug) return '';
            return slug.replace(/_/g, ' ');
        },
    }
}
</script>
