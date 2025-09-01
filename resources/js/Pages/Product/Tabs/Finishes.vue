<template>
    <div class="pt-1">
        <!-- ==================================================== -->
        <!-- MODO EDICIÓN                                         -->
        <!-- ==================================================== -->
        <template v-if="isEditing">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-700 mb-2 flex items-center">
                    <SparklesIcon class="w-5 h-5 mr-2 text-gray-500" />Procesos
                </h3>
                <p class="text-sm text-gray-500 mb-6">Agrega información de los procesos de acabado que se le da al
                    producto.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10">
                    <!-- Columna Izquierda: Selects y Textarea -->
                    <div>
                        <div v-for="field in getFieldsBySlugs(['eyeleting', 'bagging', 'heat_sealling', 'boxing'])"
                            :key="field.slug">
                            <label class="el-form-item__label">{{ field.label }}</label>
                            <el-select v-model="form.sheet_data[field.slug]" class="w-full"
                                :placeholder="getPlaceholder(field.label)" clearable>
                                <el-option v-for="option in field.options" :key="option.value" :label="option.label"
                                    :value="option.value" />
                            </el-select>
                        </div>
                        <div>
                            <label class="el-form-item__label">Notas</label>
                            <el-input v-model="form.sheet_data['acabado_notas']" type="textarea" :rows="4"
                                placeholder="Escribe las notas relevantes para los procesos de acabado" />
                        </div>
                    </div>

                    <!-- Columna Derecha: Checkboxes -->
                    <div>
                        <label class="el-form-item__label">Adicionales</label>
                        <el-checkbox-group v-model="form.sheet_data['adicionales']" class="flex flex-col">
                            <el-checkbox v-for="option in getFieldOptionsBySlug('finish_extras')" :key="option.value"
                                :label="option.value" :value="option.value">
                                {{ option.label }}
                            </el-checkbox>
                        </el-checkbox-group>
                    </div>
                </div>
            </div>
        </template>

        <!-- ==================================================== -->
        <!-- MODO VISTA                                           -->
        <!-- ==================================================== -->
        <template v-else>
            <p class="text-gray-600 text-sm mb-6">{{ description }}</p>
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-700 mb-4 flex items-center">
                    <SparklesIcon class="w-5 h-5 mr-2 text-gray-500" />Procesos de Acabado
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 text-sm">
                    <div class="space-y-3">
                        <div v-for="field in getFieldsBySlugs(['eyeleting', 'bagging', 'heat_sealling', 'boxing'])"
                            :key="field.slug" class="flex justify-between border-b pb-2">
                            <span class="text-gray-500">{{ field.label }}:</span>
                            <span class="text-gray-800 font-medium text-right">{{ getDisplayValue(field.slug,
                                field.options) || '-' }}</span>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-gray-500 font-medium mb-2">Adicionales:</h4>
                        <div v-if="product.sheet_data.adicionales && product.sheet_data.adicionales.length > 0">
                            <p v-for="item in product.sheet_data.adicionales" :key="item"
                                class="flex items-center text-gray-800">
                                <CheckCircleIcon class="h-5 w-5 text-blue-500 mr-2" /> {{ getOptionLabel('finish_extras',
                                item) }}
                            </p>
                        </div>
                        <p v-else class="text-gray-400 italic">N/A</p>
                    </div>
                    <div class="md:col-span-2 mt-4">
                        <h4 class="text-gray-500 font-medium">Notas:</h4>
                        <p class="text-gray-800 whitespace-pre-wrap mt-1">{{ product.sheet_data.acabado_notas || '-' }}
                        </p>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<script>
import { SparklesIcon, CheckCircleIcon } from '@heroicons/vue/24/outline';

export default {
    name: 'FinishesTab',
    components: {
        SparklesIcon,
        CheckCircleIcon
    },
    props: {
        product: { type: Object, required: true },
        fieldsBySection: { type: Object, required: true },
        description: { type: String, default: '' },
        isEditing: { type: Boolean, default: false },
        form: { type: Object, required: true }
    },
    created() {
        // Asegura que la propiedad para multicheckbox exista y sea un array para evitar errores.
        if (this.form.sheet_data && !Array.isArray(this.form.sheet_data['adicionales'])) {
            this.form.sheet_data['adicionales'] = [];
        }
    },
    methods: {
        // Devuelve todos los campos de la sección 'procesos'.
        getAllFields() {
            return this.fieldsBySection.procesos_de_acabado || [];
        },
        // Obtiene las opciones de un campo específico por su slug.
        getFieldOptionsBySlug(slug) {
            const field = this.getAllFields().find(f => f.slug === slug);
            return field?.options || [];
        },
        // Filtra los campos por una lista de slugs.
        getFieldsBySlugs(slugs) {
            const allFields = this.getAllFields();
            return slugs.map(slug => allFields.find(f => f.slug === slug)).filter(Boolean);
        },
        // Crea un placeholder dinámico para los selects.
        getPlaceholder(label) {
            const lowerLabel = label.toLowerCase();
            if (lowerLabel.includes('color')) return 'Selecciona el color';
            if (lowerLabel.includes('bolsa')) return 'Selecciona el tipo de bolsa';
            if (lowerLabel.includes('medida')) return 'Selecciona la medida';
            return `Selecciona ${lowerLabel}`;
        },
        // Para el modo vista, muestra la etiqueta (label) en lugar del valor (value).
        getDisplayValue(slug, options) {
            const value = this.product.sheet_data[slug];
            if (!value) return null;
            const option = options.find(o => o.value === value);
            return option ? option.label : value;
        },
        // Para el modo vista, muestra la etiqueta de las opciones del checkbox.
        getOptionLabel(slug, value) {
            const options = this.getFieldOptionsBySlug(slug);
            const option = options.find(o => o.value === value);
            return option ? option.label : value;
        }
    }
}
</script>