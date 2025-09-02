<template>
    <el-dialog :model-value="isOpen" :title="form.id ? 'Editar Campo' : 'Nuevo Campo'" @close="$emit('close')">
        <el-form :model="form" label-position="top">
            <el-form-item label="Etiqueta (Nombre visible)" :error="form.errors.label">
                <el-input v-model="form.label" />
            </el-form-item>
            <el-form-item label="Slug (identificador único)" :error="form.errors.slug">
                <el-input v-model="form.slug" />
            </el-form-item>
             <el-form-item label="Sección (Agrupa campos bajo un título)" :error="form.errors.section">
                <el-input v-model="form.section" placeholder="Ej: procesos_de_produccion" />
            </el-form-item>
            <el-form-item label="Tipo de Campo" :error="form.errors.type">
                <el-select v-model="form.type" placeholder="Selecciona un tipo">
                    <el-option label="Texto" value="text" />
                    <el-option label="Área de Texto" value="textarea" />
                    <el-option label="Selección (Select)" value="select" />
                    <el-option label="Opciones (Radio)" value="radio" />
                    <el-option label="Múltiples Opciones (Checkbox)" value="multicheckbox" />
                    <el-option label="Lista de Verificación" value="checklist" />
                </el-select>
            </el-form-item>
            <el-form-item label="Orden" :error="form.errors.order">
                <el-input-number v-model="form.order" :min="0" />
            </el-form-item>
            <el-form-item label="Activo" :error="form.errors.is_active">
                <el-switch v-model="form.is_active" />
            </el-form-item>
        </el-form>
        <template #footer>
            <el-button @click="$emit('close')">Cancelar</el-button>
            <el-button type="primary" @click="submit" :loading="form.processing">
                {{ form.id ? 'Guardar Cambios' : 'Crear Campo' }}
            </el-button>
        </template>
    </el-dialog>
</template>

<script>
import { useForm } from '@inertiajs/vue3';
import { ElDialog, ElForm, ElFormItem, ElInput, ElSelect, ElOption, ElInputNumber, ElSwitch, ElButton } from 'element-plus';

export default {
    name: 'FieldModal',
    components: { ElDialog, ElForm, ElFormItem, ElInput, ElSelect, ElOption, ElInputNumber, ElSwitch, ElButton },
    props: {
        isOpen: Boolean,
        field: Object,
        tabId: Number,
    },
    emits: ['close'],
    setup(props, { emit }) {
        const form = useForm({
            id: null,
            tab_id: null,
            label: '',
            slug: '',
            section: '',
            type: 'text',
            order: 0,
            is_active: true,
        });

        const submit = () => {
            if (form.id) {
                form.put(route('product-sheet-structure.fields.update', form.id), {
                    onSuccess: () => emit('close'),
                });
            } else {
                form.post(route('product-sheet-structure.fields.store'), {
                    onSuccess: () => emit('close'),
                });
            }
        };

        return { form, submit };
    },
    watch: {
        isOpen(newVal) {
            if (newVal) {
                this.form.clearErrors();
                if (this.field) {
                    this.form.id = this.field.id;
                    this.form.tab_id = this.field.tab_id;
                    this.form.label = this.field.label;
                    this.form.slug = this.field.slug;
                    this.form.section = this.field.section;
                    this.form.type = this.field.type;
                    this.form.order = this.field.order;
                    this.form.is_active = this.field.is_active;
                } else {
                    this.form.reset();
                    this.form.tab_id = this.tabId;
                }
            }
        }
    }
}
</script>
