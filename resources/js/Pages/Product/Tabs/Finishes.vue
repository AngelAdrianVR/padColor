<template>
    <div class="pt-1">
        <p class="text-gray-600 text-sm mb-6">{{ description }}</p>

        <div v-for="(fields, sectionName) in fieldsBySection" :key="sectionName" class="bg-white rounded-xl border border-gray-200 p-5">
             <h3 class="font-semibold text-gray-700 mb-4 capitalize flex items-center">
                <SparklesIcon class="w-5 h-5 mr-2 text-gray-500" />
                {{ formatSectionName(sectionName) }}
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4">
                <!-- Columna Izquierda -->
                <div class="space-y-4">
                    <template v-for="field in fields.filter(f => !['checklist', 'textarea'].includes(f.type))" :key="field.slug">
                         <div class="flex justify-between items-start text-sm border-b pb-2">
                            <span class="text-gray-500 mr-2">{{ field.label }}</span>
                            <span class="text-gray-800 font-medium text-right">{{ product.sheet_data[field.slug] || '-' }}</span>
                        </div>
                    </template>
                </div>

                <!-- Columna Derecha -->
                <div class="space-y-4">
                     <template v-for="field in fields.filter(f => f.type === 'checklist')" :key="field.slug">
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 mb-2">{{ field.label }}</h4>
                            <div v-if="product.sheet_data[field.slug] && product.sheet_data[field.slug].length > 0" class="space-y-1">
                                <p v-for="item in product.sheet_data[field.slug]" :key="item" class="flex items-center text-sm text-gray-800">
                                    <CheckCircleIcon class="h-5 w-5 text-blue-500 mr-2" /> {{ item }}
                                </p>
                            </div>
                            <p v-else class="text-gray-400 italic text-sm">N/A</p>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Notas (ocupa todo el ancho) -->
            <template v-for="field in fields.filter(f => f.type === 'textarea')" :key="field.slug">
                <div class="mt-6 border-t pt-4">
                    <h4 class="text-sm font-medium text-gray-500">{{ field.label }}</h4>
                    <p class="text-sm text-gray-800 whitespace-pre-line mt-1">{{ product.sheet_data[field.slug] || '-' }}</p>
                </div>
            </template>
        </div>
    </div>
</template>

<script>
import { SparklesIcon, CheckCircleIcon } from '@heroicons/vue/24/outline';

export default {
    name: 'FinishesTab',
    components: { SparklesIcon, CheckCircleIcon },
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
