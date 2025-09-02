<template>
    <div class="p-1">
        <!-- ==================================================== -->
        <!-- MODO EDICIÓN                                         -->
        <!-- ==================================================== -->
        <template v-if="isEditing">
            <div class="space-y-6">
                <!-- Tarjeta: Procesos de producción -->
                <div class="bg-white rounded-[14px] shadow-md border border-grayD9 p-4">
                    <h3 class="font-semibold text-black mb-4 flex items-center">
                        <ProduccionIcon class="size-5 mr-2" />Proceso de producción
                    </h3>
                    <el-checkbox-group v-model="form.sheet_data['production_processes']"
                        class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-x-2 gap-y-1">
                        <el-checkbox v-for="option in getFieldOptionsBySlug('production_processes')" :key="option.value"
                            :label="option.value" :value="option.value">
                            {{ option.label }}
                        </el-checkbox>
                    </el-checkbox-group>
                </div>

                <!-- Grid para tarjetas de 2 columnas -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Tarjeta: Dimensiones y formatos -->
                    <div class="bg-white rounded-[14px] shadow-md border border-grayD9 p-4 space-y-2 text-[13px]">
                        <h3 class="font-semibold text-black flex items-center text-base">
                            <ArrowsPointingOutIcon class="size-5 mr-2" />Dimensiones y formatos (cm)
                        </h3>
                        <div v-for="field in fieldsBySection['dimensiones_y_formatos_cm']" :key="field.slug">
                            <label class="text-gray3F font-semibold block">{{ field.label }}</label>
                            <el-input v-model="form.sheet_data[field.slug]" />
                        </div>
                    </div>

                    <!-- Tarjeta: Tintas -->
                    <div class="bg-white rounded-[14px] shadow-md border border-grayD9 p-4 space-y-3 text-[13px]">
                        <h3 class="font-semibold text-black flex items-center text-base">
                            <PaintBrushIcon class="size-5 mr-2" />Tintas
                        </h3>
                        <div>
                            <label class="text-gray3F font-semibold block">Tintas BASES</label>
                            <el-checkbox-group v-model="form.sheet_data['inks_bases']" class="flex space-x-4">
                                <el-checkbox v-for="option in getFieldOptionsBySlug('inks_bases')" :key="option.value"
                                    :label="option.value" :value="option.value" />
                            </el-checkbox-group>
                        </div>
                        <div>
                            <label class="text-gray3F font-semibold block">Tintas TAPAS</label>
                            <el-input v-model="form.sheet_data['inks_lids']" />
                        </div>
                        <div>
                            <label class="text-gray3F font-semibold block">Tintas ETIQUETA</label>
                            <el-checkbox-group v-model="form.sheet_data['inks_label']"
                                class="flex flex-wrap gap-x-4 gap-y-1">
                                <el-checkbox v-for="option in getFieldOptionsBySlug('inks_label')" :key="option.value"
                                    :label="option.value" :value="option.value" />
                            </el-checkbox-group>
                        </div>
                    </div>

                    <!-- Tarjeta: Impresión y terminados -->
                    <div class="bg-white rounded-[14px] shadow-md border border-grayD9 p-4 text-[13px]">
                        <h3 class="font-semibold text-black mb-4 flex items-center text-base">
                            <PrinterIcon class="size-5 mr-2" />Impresión y terminados
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label class="text-gray3F font-semibold block">Impresión</label>
                                <el-radio-group v-model="form.sheet_data['impression_type']">
                                    <el-radio v-for="option in getFieldOptionsBySlug('impression_type')"
                                        :key="option.value" :label="option.value">{{ option.label }}</el-radio>
                                </el-radio-group>
                            </div>
                            <div>
                                <label class="text-gray3F font-semibold block">Vuelta de</label>
                                <el-radio-group v-model="form.sheet_data['impression_back_finish']">
                                    <el-radio v-for="option in getFieldOptionsBySlug('impression_back_finish')"
                                        :key="option.value" :label="option.value">{{ option.label }}</el-radio>
                                </el-radio-group>
                            </div>
                            <div>
                                <label class="text-gray3F font-semibold block">Cara</label>
                                <el-radio-group v-model="form.sheet_data['impression_face_finish']">
                                    <el-radio v-for="option in getFieldOptionsBySlug('impression_face_finish')"
                                        :key="option.value" :label="option.value">{{ option.label }}</el-radio>
                                </el-radio-group>
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="text-gray3F font-semibold block">Terminados</label>
                            <el-checkbox-group v-model="form.sheet_data['finishes_list']"
                                class="grid grid-cols-2 gap-1">
                                <el-checkbox v-for="option in getFieldOptionsBySlug('finishes_list')"
                                    :key="option.value" :label="option.value" :value="option.value">{{ option.label
                                    }}</el-checkbox>
                            </el-checkbox-group>
                        </div>
                    </div>

                    <!-- Tarjeta: Información de suajes y Accesorios -->
                    <div class="space-y-6">
                        <div class="bg-white rounded-[14px] shadow-md border border-grayD9 p-4 text-[13px]">
                            <h3 class="font-semibold text-black mb-4 flex items-center text-base">
                                <PuzzlePieceIcon class="size-5 mr-2" />Información de suajes
                            </h3>
                            <div class="space-y-3">
                                <div v-for="field in fieldsBySection['informacion_de_suajes'].filter(f => f.type === 'text')"
                                    :key="field.slug">
                                    <label class="text-gray3F font-semibold block">{{ field.label }}</label>
                                    <el-input v-model="form.sheet_data[field.slug]" />
                                </div>
                                <div>
                                    <el-radio-group v-model="form.sheet_data['die_cut_type']">
                                        <el-radio v-for="option in getFieldOptionsBySlug('die_cut_type')"
                                            :key="option.value" :label="option.value">{{ option.label }}</el-radio>
                                    </el-radio-group>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-[14px] shadow-md border border-grayD9 p-4 text-[13px]">
                            <h3 class="font-semibold text-black mb-4 flex items-center text-base">
                                <SparklesIcon class="size-5 mr-2" />Accesorios
                            </h3>
                            <div class="space-y-3">
                                <div v-for="field in fieldsBySection['accesorios']" :key="field.slug">
                                    <label class="text-gray3F font-semibold block">{{ field.label }}</label>
                                    <el-input v-model="form.sheet_data[field.slug]" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta: Observaciones y especificaciones -->
                <div class="bg-white rounded-[14px] shadow-md border border-grayD9 p-4 text-[13px]">
                    <h3 class="font-semibold text-black mb-4 flex items-center text-base">
                        <ClipboardDocumentListIcon class="size-5 mr-2" />Observaciones y especificaciones
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="space-y-3">
                            <div v-for="field in fieldsBySection['observaciones_y_especificaciones'].filter(f => f.type === 'text')"
                                :key="field.slug">
                                <label class="text-gray3F font-semibold block">{{ field.label }}</label>
                                <el-input v-model="form.sheet_data[field.slug]" />
                            </div>
                            <div>
                                <label class="text-gray3F font-semibold block">Etiqueta</label>
                                <el-radio-group v-model="form.sheet_data['label_type']">
                                    <el-radio v-for="option in getFieldOptionsBySlug('label_type')" :key="option.value"
                                        :label="option.value">{{ option.label }}</el-radio>
                                </el-radio-group>
                            </div>
                        </div>
                        <div>
                            <label class="text-gray3F font-semibold block">Instrucciones adicionales</label>
                            <el-input type="textarea" :rows="5" v-model="form.sheet_data['additional_instructions']" />
                        </div>
                    </div>
                </div>

                <!-- NUEVA TARJETA: Documentos -->
                <div class="bg-white rounded-[14px] shadow-md border border-grayD9 p-4">
                    <h3 class="font-semibold text-black mb-4 flex items-center">
                        <DocumentArrowUpIcon class="size-5 mr-2 text-gray3F" />Documentos
                    </h3>

                    <!-- Lista de archivos existentes -->
                    <div v-if="product.media && product.media.length > 0" class="space-y-2 mb-6">
                        <h4 class="text-sm font-semibold text-gray-600">Archivos actuales:</h4>
                        <div v-for="doc in product.media" :key="doc.id"
                            class="flex items-center justify-between p-2 border rounded-md hover:bg-gray-50 transition-colors">
                            <div class="flex items-center min-w-0">
                                <PaperClipIcon class="size-5 mr-3 text-gray-400 flex-shrink-0" />
                                <div class="min-w-0">
                                    <a :href="doc.original_url" target="_blank"
                                        class="text-sm font-medium text-blue-600 hover:underline truncate">{{
                                            doc.file_name }}</a>
                                    <p class="text-xs text-gray3F">{{ (doc.size / 1024).toFixed(2) }} KB</p>
                                </div>
                            </div>
                            <el-button @click="confirmDelete(doc.id)" type="danger" :icon="TrashIcon"
                                :loading="deletingMediaId === doc.id" text circle />
                        </div>
                    </div>
                    <p v-else class="text-center text-sm text-gray3F mb-6 border-2 border-dashed rounded-lg p-4">
                        Este producto no tiene documentos adjuntos.
                    </p>
                    <hr class="my-6">

                    <!-- Componente para subir nuevos archivos -->
                    <h4 class="text-sm font-semibold text-gray-600 mb-2">Añadir nuevos archivos:</h4>
                    <CustomUploader v-model:files="form.sheet_data.new_documents" />
                </div>
            </div>
        </template>
        <!-- ==================================================== -->
        <!-- MODO VISTA                                           -->
        <!-- ==================================================== -->
        <template v-else>
            <p class="text-gray-600 text-sm mb-6">{{ description }}</p>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <template v-for="(fields, sectionName) in fieldsBySection" :key="sectionName">
                    <!-- Layout especial para observaciones -->
                    <div v-if="sectionName === 'observaciones_y_especificaciones'"
                        class="md:col-span-2 lg:col-span-3 bg-white rounded-[14px] shadow-md border border-grayD9 p-4 text-[13px]">
                        <h3 class="font-semibold text-black mb-4 capitalize flex items-center text-base">
                            <ClipboardDocumentListIcon class="size-5 mr-2" />{{
                                formatSectionName(sectionName) }}
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                            <div v-for="field in fields.filter(f => f.type !== 'textarea')" :key="field.slug"
                                class="flex justify-between items-start border-b pb-2">
                                <span class="text-gray3F font-semibold mr-2">{{ field.label }}</span>
                                <span class="text-black text-right">{{ product.sheet_data[field.slug] ||
                                    '-' }}</span>
                            </div>
                        </div>
                        <div v-if="product.sheet_data['additional_instructions']" class="mt-4">
                            <h4 class="font-semibold text-gray3F">Instrucciones adicionales</h4>
                            <p class="text-black whitespace-pre-line mt-1">{{
                                product.sheet_data['additional_instructions'] }}</p>
                        </div>
                    </div>

                    <!-- Layout especial para procesos de producción -->
                    <div v-else-if="sectionName === 'procesos_de_produccion'"
                        class="md:col-span-2 lg:col-span-3 bg-white rounded-[14px] shadow-md border border-grayD9 p-4">
                        <h3 class="font-semibold text-black mb-4 capitalize flex items-center">
                            <ProduccionIcon class="size-5 mr-2" />{{ formatSectionName(sectionName) }}
                        </h3>
                        <div v-if="product.sheet_data[fields[0].slug] && product.sheet_data[fields[0].slug].length > 0">
                            <el-tag v-for="item in product.sheet_data[fields[0].slug]" :key="item" round effect="light"
                                class="mr-2 mb-1 !bg-[#D3EBFF] !text-[#1155FF] !border-[#D3EBFF]">
                                <div class="flex items-center space-x-1">
                                    <CheckIcon class="size-3" />
                                    <span>{{ item }}</span>
                                </div>
                            </el-tag>
                        </div>
                        <p v-else class="text-gray-400 italic text-sm">N/A</p>
                    </div>

                    <!-- Layout normal para las demás tarjetas -->
                    <div v-else class="bg-white rounded-[14px] shadow-md border border-grayD9 p-4">
                        <h3
                            class="font-semibold text-black mb-4 capitalize flex items-center border-b border-grayD9 pb-2">
                            <Component :is="getSectionIcon(sectionName)" class="size-5 mr-2" />
                            {{ formatSectionName(sectionName) }}
                        </h3>
                        <div class="space-y-2">
                            <div v-for="field in fields" :key="field.slug" class="text-[13px]">
                                <div v-if="['multicheckbox'].includes(field.type)"
                                    class="flex items-start space-x-3 border-b border-hrayD9 pb-2">
                                    <span class="text-gray3F font-semibold w-1/2">{{ field.label }}</span>
                                    <div
                                        v-if="product.sheet_data[field.slug] && product.sheet_data[field.slug].length > 0">
                                        <p v-for="item in product.sheet_data[field.slug]" :key="item"
                                            class="flex items-center text-black">
                                            <CheckCircleIcon class="h-5 w-5 text-[#1155FF] mr-2" /> {{ item }}
                                        </p>
                                    </div>
                                    <p v-else class="text-gray-400 italic ">N/A</p>
                                </div>
                                <div v-else-if="['radio'].includes(field.type)"
                                    class="flex items-start space-x-3 border-b border-hrayD9 pb-2">
                                    <span class="text-gray3F font-semibold w-1/2">{{ field.label }}</span>
                                    <div v-if="product.sheet_data[field.slug]">
                                        <p class="flex items-center text-black">
                                            <CheckCircleIcon class="h-5 w-5 text-[#1155FF] mr-2" /> {{
                                                product.sheet_data[field.slug] }}
                                        </p>
                                    </div>
                                    <p v-else class="text-gray-400 italic">N/A</p>
                                </div>
                                <div v-else class="flex items-start space-x-3 border-b border-hrayD9 pb-2">
                                    <span class="text-gray3F font-semibold w-1/2 mr-2">{{ field.label }}</span>
                                    <span class="text-black text-right">{{ product.sheet_data[field.slug]
                                        || '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
            <!-- SECCIÓN DE DOCUMENTOS EN MODO VISTA -->
            <div class="mt-6 bg-white rounded-[14px] shadow-md border border-grayD9 p-4">
                <h3 class="font-semibold text-black mb-4 flex items-center">
                    <DocumentArrowUpIcon class="size-5 mr-2" />Documentos Adjuntos
                </h3>
                <div v-if="product.media && product.media.length > 0" class="space-y-2">
                    <div v-for="doc in product.media" :key="doc.id"
                        class="flex items-center justify-between p-2 border rounded-md">
                        <div class="flex items-center min-w-0">
                            <PaperClipIcon class="size-5 mr-3 text-gray-400 flex-shrink-0" />
                            <div class="min-w-0">
                                <a :href="doc.original_url" target="_blank"
                                    class="text-sm font-medium text-blue-600 hover:underline truncate">{{ doc.file_name
                                    }}</a>
                                <p class="text-xs text-gray3F">{{ (doc.size / 1024).toFixed(2) }} KB</p>
                            </div>
                        </div>
                    </div>
                </div>
                <p v-else class="text-gray-400 italic text-sm">No hay documentos adjuntos.</p>
            </div>
        </template>
    </div>
</template>

<script>
import { router } from '@inertiajs/vue3';
import { ElMessageBox, ElMessage } from 'element-plus';
import CustomUploader from '@/Components/MyComponents/CustomUploader.vue';
import {
    CheckCircleIcon, ClipboardDocumentListIcon, Cog8ToothIcon,
    SparklesIcon, PaintBrushIcon,
    PrinterIcon, PuzzlePieceIcon, CheckIcon,
    DocumentArrowUpIcon, PaperClipIcon, TrashIcon,
    ArrowsPointingOutIcon,
} from '@heroicons/vue/24/outline';
import ProduccionIcon from '@/Components/MyComponents/Icons/ProduccionIcon.vue';

export default {
    name: 'DesignTab',
    components: {
        CustomUploader, // Componente nuevo
        ArrowsPointingOutIcon, CheckCircleIcon, ClipboardDocumentListIcon, Cog8ToothIcon,
        SparklesIcon, PaintBrushIcon, CheckIcon,
        PrinterIcon, PuzzlePieceIcon, ProduccionIcon,
        DocumentArrowUpIcon, PaperClipIcon, TrashIcon
    },
    props: {
        product: { type: Object, required: true },
        fieldsBySection: { type: Object, required: true },
        description: { type: String, default: '' },
        isEditing: { type: Boolean, default: false },
        form: { type: Object, required: true } // Prop ajustada
    },
    data() {
        return {
            deletingMediaId: null, // Estado para la carga de borrado
            TrashIcon: TrashIcon, // Para poder usarlo en el template
        }
    },
    created() {
        // Asegura que los campos multicheckbox sean arrays para evitar errores.
        const multicheckboxSlugs = ['production_processes', 'inks_bases', 'inks_label', 'finishes_list'];
        multicheckboxSlugs.forEach(slug => {
            if (!Array.isArray(this.form[slug])) {
                this.form[slug] = [];
            }
        });
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
                accesorios: SparklesIcon,
                tintas: PaintBrushIcon,
                impresion_y_terminados: PrinterIcon,
                informacion_de_suajes: PuzzlePieceIcon,
                dimensiones_y_formatos_cm: ArrowsPointingOutIcon,
            };
            return icons[sectionName] || ClipboardDocumentListIcon;
        },
        // --- MÉTODOS PARA MANEJAR DOCUMENTOS ---
        confirmDelete(mediaId) {
            ElMessageBox.confirm(
                '¿Estás seguro de que quieres eliminar este archivo? Esta acción no se puede deshacer.',
                'Confirmar eliminación',
                {
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    type: 'warning',
                }
            )
                .then(() => {
                    this.deleteMedia(mediaId);
                })
                .catch(() => {
                    ElMessage.info('Eliminación cancelada.');
                });
        },
        deleteMedia(mediaId) {
            router.delete(route('media.destroy', mediaId), {
                preserveState: true,
                preserveScroll: true,
                onStart: () => {
                    this.deletingMediaId = mediaId;
                },
                onSuccess: () => {
                    ElMessage.success('El archivo ha sido eliminado.');
                },
                onError: (errors) => {
                    ElMessage.error('No se pudo eliminar el archivo.');
                },
                onFinish: () => {
                    this.deletingMediaId = null;
                },
            });
        }
    }
}
</script>