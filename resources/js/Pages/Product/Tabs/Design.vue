<template>
    <div class="pt-1">
        <p v-if="!isEditing" class="text-gray-600 text-sm mb-6">{{ description }}</p>

        <!-- ==================================================== -->
        <!-- MODO EDICIÓN                                         -->
        <!-- ==================================================== -->
        <template v-if="isEditing">
            <div class="space-y-6">
                <!-- Tarjeta: Identificación general -->
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <h3 class="font-semibold text-gray-700 mb-4 flex items-center">
                        <IdentificationIcon class="w-5 h-5 mr-2 text-gray-500" />Identificación general
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                        <div v-for="field in fieldsBySection['identificacion_general']" :key="field.slug">
                            <label class="el-form-item__label">{{ field.label }}</label>
                            <el-input v-model="localFormData[field.slug]" />
                        </div>
                    </div>
                </div>

                <!-- Tarjeta: Procesos de producción -->
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <h3 class="font-semibold text-gray-700 mb-4 flex items-center">
                        <Cog8ToothIcon class="w-5 h-5 mr-2 text-gray-500" />Proceso de producción
                    </h3>
                    <el-checkbox-group v-model="localFormData['production_processes']"
                        class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-x-4 gap-y-2">
                        <el-checkbox v-for="option in getFieldOptionsBySlug('production_processes')" :key="option.value"
                            :label="option.value" :value="option.value">
                            {{ option.label }}
                        </el-checkbox>
                    </el-checkbox-group>
                </div>

                <!-- Grid para tarjetas de 2 columnas -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Tarjeta: Dimensiones y formatos -->
                    <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
                        <h3 class="font-semibold text-gray-700 flex items-center">
                            <ArrowsRightLeftIcon class="w-5 h-5 mr-2 text-gray-500" />Dimensiones y formatos (cm)
                        </h3>
                        <div v-for="field in fieldsBySection['dimensiones_y_formatos_cm']" :key="field.slug">
                            <label class="el-form-item__label">{{ field.label }}</label>
                            <el-input v-model="localFormData[field.slug]" />
                        </div>
                    </div>

                    <!-- Tarjeta: Tintas -->
                    <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
                        <h3 class="font-semibold text-gray-700 flex items-center">
                            <PaintBrushIcon class="w-5 h-5 mr-2 text-gray-500" />Tintas
                        </h3>
                        <div>
                            <label class="el-form-item__label">Tintas BASES</label>
                            <el-checkbox-group v-model="localFormData['inks_bases']" class="flex space-x-4">
                                <el-checkbox v-for="option in getFieldOptionsBySlug('inks_bases')" :key="option.value"
                                    :label="option.value" :value="option.value" />
                            </el-checkbox-group>
                        </div>
                        <div>
                            <label class="el-form-item__label">Tintas TAPAS</label>
                            <el-input v-model="localFormData['inks_lids']" />
                        </div>
                         <div>
                            <label class="el-form-item__label">Tintas ETIQUETA</label>
                            <el-checkbox-group v-model="localFormData['inks_label']" class="flex flex-wrap gap-x-4 gap-y-1">
                                <el-checkbox v-for="option in getFieldOptionsBySlug('inks_label')" :key="option.value"
                                    :label="option.value" :value="option.value" />
                            </el-checkbox-group>
                        </div>
                    </div>

                    <!-- Tarjeta: Impresión y terminados -->
                    <div class="bg-white rounded-xl border border-gray-200 p-5">
                        <h3 class="font-semibold text-gray-700 mb-4 flex items-center">
                            <PrinterIcon class="w-5 h-5 mr-2 text-gray-500" />Impresión y terminados
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label class="el-form-item__label">Impresión</label>
                                <el-radio-group v-model="localFormData['impression_type']">
                                    <el-radio v-for="option in getFieldOptionsBySlug('impression_type')" :key="option.value" :label="option.value">{{ option.label }}</el-radio>
                                </el-radio-group>
                            </div>
                            <div>
                                <label class="el-form-item__label">Vuelta de</label>
                                <el-radio-group v-model="localFormData['impression_back_finish']">
                                    <el-radio v-for="option in getFieldOptionsBySlug('impression_back_finish')" :key="option.value" :label="option.value">{{ option.label }}</el-radio>
                                </el-radio-group>
                            </div>
                             <div>
                                <label class="el-form-item__label">Cara</label>
                                <el-radio-group v-model="localFormData['impression_face_finish']">
                                    <el-radio v-for="option in getFieldOptionsBySlug('impression_face_finish')" :key="option.value" :label="option.value">{{ option.label }}</el-radio>
                                </el-radio-group>
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="el-form-item__label">Terminados</label>
                            <el-checkbox-group v-model="localFormData['finishes_list']" class="grid grid-cols-2 gap-1">
                                <el-checkbox v-for="option in getFieldOptionsBySlug('finishes_list')" :key="option.value" :label="option.value" :value="option.value">{{ option.label }}</el-checkbox>
                            </el-checkbox-group>
                        </div>
                    </div>

                    <!-- Tarjeta: Información de suajes y Accesorios -->
                    <div class="space-y-6">
                        <div class="bg-white rounded-xl border border-gray-200 p-5">
                            <h3 class="font-semibold text-gray-700 mb-4 flex items-center">
                                <ScissorsIcon class="w-5 h-5 mr-2 text-gray-500" />Información de suajes
                            </h3>
                            <div class="space-y-4">
                                <div v-for="field in fieldsBySection['informacion_de_suajes'].filter(f => f.type === 'text')" :key="field.slug">
                                    <label class="el-form-item__label">{{ field.label }}</label>
                                    <el-input v-model="localFormData[field.slug]" />
                                </div>
                                <div>
                                     <el-radio-group v-model="localFormData['die_cut_type']">
                                        <el-radio v-for="option in getFieldOptionsBySlug('die_cut_type')" :key="option.value" :label="option.value">{{ option.label }}</el-radio>
                                    </el-radio-group>
                                </div>
                            </div>
                        </div>
                         <div class="bg-white rounded-xl border border-gray-200 p-5">
                            <h3 class="font-semibold text-gray-700 mb-4 flex items-center">
                                <CubeTransparentIcon class="w-5 h-5 mr-2 text-gray-500" />Accesorios
                            </h3>
                            <div class="space-y-4">
                                <div v-for="field in fieldsBySection['accesorios']" :key="field.slug">
                                    <label class="el-form-item__label">{{ field.label }}</label>
                                    <el-input v-model="localFormData[field.slug]" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta: Observaciones y especificaciones -->
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <h3 class="font-semibold text-gray-700 mb-4 flex items-center"><ClipboardDocumentListIcon class="w-5 h-5 mr-2 text-gray-500" />Observaciones y especificaciones</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="space-y-4">
                             <div v-for="field in fieldsBySection['observaciones_y_especificaciones'].filter(f => f.type === 'text')" :key="field.slug">
                                <label class="el-form-item__label">{{ field.label }}</label>
                                <el-input v-model="localFormData[field.slug]" />
                            </div>
                            <div>
                                <label class="el-form-item__label">Etiqueta</label>
                                <el-radio-group v-model="localFormData['label_type']">
                                    <el-radio v-for="option in getFieldOptionsBySlug('label_type')" :key="option.value" :label="option.value">{{ option.label }}</el-radio>
                                </el-radio-group>
                            </div>
                        </div>
                        <div>
                             <label class="el-form-item__label">Instrucciones adicionales</label>
                             <el-input type="textarea" :rows="5" v-model="localFormData['additional_instructions']" />
                        </div>
                    </div>
                </div>
                 <!-- TODO: Agregar tarjetas para carga de archivos -->
            </div>
        </template>

        <!-- ==================================================== -->
        <!-- MODO VISTA                                           -->
        <!-- ==================================================== -->
        <template v-else>
            <!-- Tarjeta de Identificación General -->
            <div class="bg-white rounded-xl border border-gray-200 p-5 mb-6">
                <h3 class="font-semibold text-gray-700 mb-4 capitalize flex items-center">
                    <IdentificationIcon class="w-5 h-5 mr-2 text-gray-500" />Identificación general
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3">
                    <div v-for="field in fieldsBySection['identificacion_general']" :key="field.slug"
                        class="flex justify-between items-start text-sm border-b pb-2">
                        <span class="text-gray-500 mr-2">{{ field.label }}</span>
                        <span class="text-gray-800 font-medium text-right">{{ product.sheet_data[field.slug] || '-'}}</span>
                    </div>
                </div>
            </div>

            <!-- Grid para las demás tarjetas -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Iteramos sobre las demás secciones -->
                <template v-for="(fields, sectionName) in otherSections" :key="sectionName">
                    <!-- Layout especial para observaciones -->
                    <div v-if="sectionName === 'observaciones_y_especificaciones'"
                        class="md:col-span-2 lg:col-span-3 bg-white rounded-xl border border-gray-200 p-5">
                        <h3 class="font-semibold text-gray-700 mb-4 capitalize flex items-center">
                            <ClipboardDocumentListIcon class="w-5 h-5 mr-2 text-gray-500" />{{ formatSectionName(sectionName) }}
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                            <div v-for="field in fields.filter(f => f.type !== 'textarea')" :key="field.slug"
                                class="flex justify-between items-start text-sm border-b pb-2">
                                <span class="text-gray-500 mr-2">{{ field.label }}</span>
                                <span class="text-gray-800 font-medium text-right">{{ product.sheet_data[field.slug] || '-'}}</span>
                            </div>
                        </div>
                        <div v-if="product.sheet_data['additional_instructions']" class="mt-4">
                            <h4 class="text-sm font-medium text-gray-500">Instrucciones adicionales</h4>
                            <p class="text-sm text-gray-800 whitespace-pre-line mt-1">{{ product.sheet_data['additional_instructions'] }}</p>
                        </div>
                    </div>

                    <!-- Layout normal para las demás tarjetas -->
                    <div v-else class="bg-white rounded-xl border border-gray-200 p-5">
                        <h3 class="font-semibold text-gray-700 mb-4 capitalize flex items-center">
                            <Component :is="getSectionIcon(sectionName)" class="w-5 h-5 mr-2 text-gray-500" />
                            {{ formatSectionName(sectionName) }}
                        </h3>
                        <div class="space-y-3">
                            <div v-for="field in fields" :key="field.slug">
                                <!-- Renderizado para tipo 'multiselect' / 'multicheckbox' (tags) -->
                                <div v-if="['multiselect', 'multicheckbox'].includes(field.type)">
                                    <div v-if="product.sheet_data[field.slug] && product.sheet_data[field.slug].length > 0">
                                        <el-tag v-for="item in product.sheet_data[field.slug]" :key="item" type="primary" effect="light" class="mr-2 mb-1">
                                            {{ item }}
                                        </el-tag>
                                    </div>
                                    <p v-else class="text-gray-400 italic text-sm">N/A</p>
                                </div>
                                <!-- Renderizado para tipo 'checklist' / radio -->
                                <div v-else-if="['checklist', 'radio'].includes(field.type)">
                                    <h4 class="text-sm font-medium text-gray-500 mb-1">{{ field.label }}</h4>
                                    <div v-if="product.sheet_data[field.slug]">
                                        <p v-if="Array.isArray(product.sheet_data[field.slug])"
                                            v-for="item in product.sheet_data[field.slug]" :key="item"
                                            class="flex items-center text-sm text-gray-800">
                                            <CheckCircleIcon class="h-5 w-5 text-blue-500 mr-2" /> {{ item }}
                                        </p>
                                        <p v-else class="flex items-center text-sm text-gray-800">
                                            <CheckCircleIcon class="h-5 w-5 text-blue-500 mr-2" /> {{ product.sheet_data[field.slug] }}
                                        </p>
                                    </div>
                                    <p v-else class="text-gray-400 italic text-sm">N/A</p>
                                </div>
                                <!-- Renderizado por defecto (texto key-value) -->
                                <div v-else class="flex justify-between items-start text-sm">
                                    <span class="text-gray-500 mr-2">{{ field.label }}</span>
                                    <span class="text-gray-800 font-medium text-right">{{ product.sheet_data[field.slug] || '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Tarjeta de Documentos -->
                <div v-if="product.media && product.media.length"
                    class="md:col-span-2 lg:col-span-3 bg-white rounded-xl border border-gray-200 p-5">
                    <h3 class="font-semibold text-gray-700 mb-4 capitalize flex items-center">
                        <DocumentTextIcon class="w-5 h-5 mr-2 text-gray-500" />Documentos
                    </h3>
                    <div class="space-y-2">
                        <a v-for="doc in product.media" :key="doc.id" :href="doc.original_url" target="_blank"
                            class="flex items-center justify-between p-2 border rounded-md hover:bg-gray-50 cursor-pointer">
                            <div class="flex items-center min-w-0">
                                <PhotoIcon v-if="doc.mime_type.startsWith('image/')" class="w-6 h-6 mr-3 text-gray-500 flex-shrink-0" />
                                <DocumentTextIcon v-else class="w-6 h-6 mr-3 text-red-500 flex-shrink-0" />
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-800 truncate">{{ doc.file_name }}</p>
                                    <p class="text-xs text-gray-500">{{ (doc.size / 1024).toFixed(2) }} KB</p>
                                </div>
                            </div>
                            <ArrowDownTrayIcon class="w-5 h-5 text-gray-400 ml-2 flex-shrink-0" />
                        </a>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<script>
import {
    ArrowsRightLeftIcon, ArrowDownTrayIcon, CheckCircleIcon, ClipboardDocumentListIcon, Cog8ToothIcon,
    CubeTransparentIcon, DocumentTextIcon, IdentificationIcon, PaintBrushIcon, PhotoIcon,
    PrinterIcon, ScissorsIcon
} from '@heroicons/vue/24/outline';

export default {
    name: 'DesignTab',
    components: {
        ArrowsRightLeftIcon, ArrowDownTrayIcon, CheckCircleIcon, ClipboardDocumentListIcon, Cog8ToothIcon,
        CubeTransparentIcon, DocumentTextIcon, IdentificationIcon, PaintBrushIcon, PhotoIcon,
        PrinterIcon, ScissorsIcon
    },
    props: {
        product: { type: Object, required: true },
        fieldsBySection: { type: Object, required: true },
        description: { type: String, default: '' },
        isEditing: { type: Boolean, default: false },
        formData: { type: Object, required: true }
    },
    emits: ['update:formData'],
    computed: {
        localFormData: {
            get() {
                // Inicializa los campos de tipo multicheckbox como array si no existen
                const multicheckboxSlugs = ['production_processes', 'inks_bases', 'inks_label', 'finishes_list'];
                multicheckboxSlugs.forEach(slug => {
                    if (!Array.isArray(this.formData[slug])) {
                        this.formData[slug] = [];
                    }
                });
                return this.formData;
            },
            set(value) {
                this.$emit('update:formData', value);
            }
        },
        otherSections() {
            const { identificacion_general, ...otherSections } = this.fieldsBySection;
            return otherSections;
        }
    },
    methods: {
        formatSectionName(slug) {
            if (!slug) return '';
            return slug.replace(/_/g, ' ');
        },
        getFieldOptionsBySlug(slug) {
            const field = Object.values(this.fieldsBySection).flat().find(f => f.slug === slug);
            return field?.options || [];
        },
        getSectionIcon(sectionName) {
            const icons = {
                procesos_de_produccion: Cog8ToothIcon,
                accesorios: CubeTransparentIcon,
                tintas: PaintBrushIcon,
                impresion_y_terminados: PrinterIcon,
                informacion_de_suajes: ScissorsIcon,
                dimensiones_y_formatos_cm: ArrowsRightLeftIcon,
            };
            return icons[sectionName] || DocumentTextIcon;
        }
    }
}
</script>