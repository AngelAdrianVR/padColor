<template>
    <el-dialog :model-value="isOpen" :title="`Gestionar Opciones para '${field?.label}'`" @close="$emit('close')"
        width="50%">
        <div v-if="field">
            <!-- Lista de Opciones Existentes -->
            <div class="space-y-1 mb-6 max-h-64 overflow-y-auto pr-2">
                <div v-for="(option, index) in localOptions" :key="option.id || `new-${index}`"
                    class="flex items-center space-x-2 p-2 rounded-lg" :class="index % 2 === 0 ? 'bg-gray-50' : ''">
                    <el-input v-model="option.label" placeholder="Etiqueta" size="small" class="!w-1/3" />
                    <el-input v-model="option.value" placeholder="Valor" size="small" class="!w-1/3" />
                    <el-input-number v-model="option.order" :min="0" controls-position="right" size="small" class="!w-20" />
                    <el-button type="primary" size="small" @click="updateOption(option)"
                        :loading="processingStates[option.id]">Guardar</el-button>
                    <el-button type="danger" plain size="small" @click="deleteOption(option)">Eliminar</el-button>
                </div>
                <p v-if="!localOptions || localOptions.length === 0" class="text-center text-gray-500 py-4">No hay
                    opciones para este campo.</p>
            </div>

            <hr>

            <!-- Formulario para Nueva Opción -->
            <div class="mt-4">
                <h4 class="font-semibold mb-2">Añadir Nueva Opción</h4>
                <div class="flex items-start space-x-2">
                    <el-form-item :error="newOptionForm.errors.label" class="flex-1">
                        <el-input v-model="newOptionForm.label" placeholder="Etiqueta (ej. Azul Marino)" />
                    </el-form-item>
                    <el-form-item :error="newOptionForm.errors.value" class="flex-1">
                        <el-input v-model="newOptionForm.value" placeholder="Valor (ej. azul_marino)" />
                    </el-form-item>
                    <el-button type="success" @click="addOption" :loading="newOptionForm.processing">Añadir</el-button>
                </div>
            </div>
        </div>
        <template #footer>
            <el-button @click="$emit('close')">Cerrar</el-button>
        </template>
    </el-dialog>
</template>

<script>
import { useForm, router } from '@inertiajs/vue3';
import { ElDialog, ElInput, ElInputNumber, ElButton, ElFormItem, ElMessageBox } from 'element-plus';

export default {
    name: 'OptionsModal',
    components: { ElDialog, ElInput, ElInputNumber, ElButton, ElFormItem },
    props: {
        isOpen: Boolean,
        field: Object,
    },
    emits: ['close'],
    data() {
        return {
            localOptions: [],
            processingStates: {},
            newOptionForm: useForm({
                field_id: null,
                label: '',
                value: '',
            }),
        };
    },
     watch: {
        // Este watcher ahora reacciona a los cambios en las props del padre,
        // forzando la actualización de la lista local.
        'field.options': {
            handler(newOptions) {
                if (this.isOpen) {
                    this.localOptions = JSON.parse(JSON.stringify(newOptions || []));
                }
            },
            deep: true,
        },
        isOpen(newVal) {
            if (newVal && this.field) {
                this.localOptions = JSON.parse(JSON.stringify(this.field.options || []));
                this.processingStates = {};
                this.newOptionForm.reset();
            }
        }
    },
    methods: {
        addOption() {
            this.newOptionForm.field_id = this.field.id;
            this.newOptionForm.post(route('product-sheet-structure.options.store'), {
                preserveScroll: true,
                onSuccess: () => this.newOptionForm.reset(),
            });
        },
        updateOption(option) {
            this.processingStates[option.id] = true;
            router.put(route('product-sheet-structure.options.update', option.id), option, {
                preserveScroll: true,
                onFinish: () => {
                    this.processingStates[option.id] = false;
                }
            });
        },
        deleteOption(option) {
            ElMessageBox.confirm(`¿Estás seguro de que quieres eliminar la opción "${option.label}"?`, 'Confirmar Eliminación', { type: 'warning' })
                .then(() => {
                    router.delete(route('product-sheet-structure.options.destroy', option.id), {
                        preserveScroll: true,
                    });
                }).catch(() => { });
        }
    }
}
</script>
