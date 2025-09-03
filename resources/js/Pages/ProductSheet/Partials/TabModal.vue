<template>
    <el-dialog :model-value="isOpen" :title="form.id ? 'Editar Pestaña' : 'Nueva Pestaña'" @close="$emit('close')">
        <el-form :model="form" label-position="top">
            <el-form-item label="Nombre de la Pestaña" :error="form.errors.name">
                <el-input v-model="form.name" />
            </el-form-item>
            <el-form-item label="Slug (identificador único)" :error="form.errors.slug">
                <el-input v-model="form.slug" />
            </el-form-item>
            <el-form-item label="Orden" :error="form.errors.order">
                <el-input-number v-model="form.order" :min="0" />
            </el-form-item>
            <el-form-item label="Activa" :error="form.errors.is_active">
                <el-switch v-model="form.is_active" />
            </el-form-item>
        </el-form>
        <template #footer>
            <el-button @click="$emit('close')">Cancelar</el-button>
            <el-button type="primary" @click="submit" :loading="form.processing">
                {{ form.id ? 'Guardar Cambios' : 'Crear Pestaña' }}
            </el-button>
        </template>
    </el-dialog>
</template>

<script>
import { useForm } from '@inertiajs/vue3';
import { ElDialog, ElForm, ElFormItem, ElInput, ElInputNumber, ElSwitch, ElButton } from 'element-plus';

export default {
    name: 'TabModal',
    components: { ElDialog, ElForm, ElFormItem, ElInput, ElInputNumber, ElSwitch, ElButton },
    props: {
        isOpen: Boolean,
        tab: Object,
    },
    emits: ['close'],
    setup(props, { emit }) {
        const form = useForm({
            id: null,
            name: '',
            slug: '',
            order: 0,
            is_active: true,
        });

        const submit = () => {
            if (form.id) {
                form.put(route('product-sheet-structure.tabs.update', form.id), {
                    onSuccess: () => emit('close'),
                });
            } else {
                form.post(route('product-sheet-structure.tabs.store'), {
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
                if (this.tab) {
                    this.form.id = this.tab.id;
                    this.form.name = this.tab.name;
                    this.form.slug = this.tab.slug;
                    this.form.order = this.tab.order;
                    this.form.is_active = !!this.tab.is_active;
                } else {
                    this.form.reset();
                }
            }
        }
    }
}
</script>