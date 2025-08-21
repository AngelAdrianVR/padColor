<template>
    <DialogModal :show="show" @close="closeModal" max-width="xl">
        <template #title>
            <h2 class="font-semibold">Detalles de la materia prima</h2>
        </template>
        <template #content>
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="font-bold text-gray3F">{{ rawMaterialData.name }}</h3>
                    <p class="text-sm text-gray3F">Código: {{ rawMaterialData.sku }}</p>
                </div>
                <PrimaryButton @click="editRawMaterial"
                    class="!text-primary !bg-white border !border-gray-300 !rounded-md text-sm !py-1">
                    <PencilIcon class="size-4 inline mr-1" />
                    Editar
                </PrimaryButton>
            </div>
            <div class="divide-y divide-gray-200 mt-6">
                <div class="py-2 flex justify-between text-sm">
                    <span class="text-gray3F">Creado por</span>
                    <span class="text-black">{{ rawMaterialData.user?.name ?? 'N/A' }}</span>
                </div>
                <div class="py-2 flex justify-between text-sm">
                    <span class="text-gray3F">Creado el</span>
                    <span class="text-black">{{ formatDate(rawMaterialData.created_at) }}</span>
                </div>
                <div class="py-2 flex justify-between text-sm">
                    <span class="text-gray3F">Nombre</span>
                    <span class="text-black">{{ rawMaterialData.name }}</span>
                </div>
                <div class="py-2 flex justify-between text-sm">
                    <span class="text-gray3F">Unidad de medida</span>
                    <span class="text-black">{{ rawMaterialData.measure_unit }}</span>
                </div>
                <div class="py-2 flex justify-between text-sm">
                    <span class="text-gray3F">Descripción</span>
                    <span class="text-black">{{ rawMaterialData.description ?? '-' }}</span>
                </div>
            </div>
        </template>
    </DialogModal>
</template>

<script>
import DialogModal from '@/Components/DialogModal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { PencilIcon } from '@heroicons/vue/24/outline';
import { router } from '@inertiajs/vue3';

export default {
    components: {
        DialogModal,
        PrimaryButton,
        PencilIcon,
    },
    props: {
        show: {
            type: Boolean,
            default: false,
        },
        rawMaterialData: {
            type: Object,
            required: true,
        },
    },
    emits: ['close'],
    methods: {
        closeModal() {
            this.$emit('close');
        },
        editRawMaterial() {
            router.get(route('raw-materials.edit', this.rawMaterialData.id));
        },
        formatDate(dateString) {
            if (!dateString) return 'N/A';
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            return new Date(dateString).toLocaleDateString('es-ES', options);
        },
    },
};
</script>
