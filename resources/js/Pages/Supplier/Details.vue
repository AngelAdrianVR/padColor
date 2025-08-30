<template>
    <DialogModal :show="show" @close="closeModal" max-width="xl">
        <template #title>
            <h2 class="text-xl font-semibold">Detalles del proveedor</h2>
        </template>
        <template #content>
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="font-bold text-gray3F">{{ supplierData.name }}</h3>
                </div>
                <PrimaryButton v-if="$page.props.auth.user.permissions.includes('Editar proveedores')" @click="editSupplier"
                    class="!text-primary !bg-white border !border-gray-300 !rounded-md text-sm !py-1">
                    <PencilIcon class="size-4 inline mr-1" />
                    Editar
                </PrimaryButton>
            </div>
            <div class="divide-y divide-gray-200 mt-6">
                <div class="py-2 flex justify-between text-sm">
                    <span class="text-gray3F w-[35%]">Nombre / Razón Social</span>
                    <span class="text-black w-[65%] text-right">{{ supplierData.name }}</span>
                </div>
                <div class="py-2 flex justify-between text-sm">
                    <span class="text-gray3F w-[35%]">Contacto</span>
                    <span class="text-black w-[65%] text-right">{{ supplierData.contact_person ?? '-' }}</span>
                </div>
                <div class="py-2 flex justify-between text-sm">
                    <span class="text-gray3F w-[35%]">Email</span>
                    <span class="text-black w-[65%] text-right">{{ supplierData.email ?? '-' }}</span>
                </div>
                <div class="py-2 flex justify-between text-sm">
                    <span class="text-gray3F w-[35%]">Teléfono</span>
                    <span class="text-black w-[65%] text-right">{{ supplierData.phone ?? '-' }}</span>
                </div>
                <div class="py-3 flex justify-between text-sm">
                    <span class="text-gray3F w-[35%]">Dirección</span>
                    <span class="text-black w-[65%] text-right" style="white-space: pre-line;">
                        {{ supplierData.address ?? '-' }}
                    </span>
                </div>
                <div class="py-3 flex justify-between text-sm">
                    <span class="text-gray3F w-[35%]">Notas</span>
                    <span class="text-black w-[65%] text-right" style="white-space: pre-line;">
                        {{ supplierData.notes ?? '-' }}
                    </span>
                </div>
                <div class="py-2 flex justify-between text-sm">
                    <span class="text-gray3F w-[35%]">Creado por</span>
                    <span class="text-black w-[65%] text-right">{{ supplierData.user?.name ?? 'N/A' }}</span>
                </div>
                <div class="py-2 flex justify-between text-sm">
                    <span class="text-gray3F w-[35%]">Creado el</span>
                    <span class="text-black w-[65%] text-right">{{ formatDate(supplierData.created_at) }}</span>
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
        show: Boolean,
        supplierData: Object,
    },
    emits: ['close'],
    methods: {
        closeModal() {
            this.$emit('close');
        },
        editSupplier() {
            router.get(route('suppliers.edit', this.supplierData.id));
        },
        formatDate(dateString) {
            if (!dateString) return 'N/A';
            return new Date(dateString).toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric' });
        },
    },
};
</script>
