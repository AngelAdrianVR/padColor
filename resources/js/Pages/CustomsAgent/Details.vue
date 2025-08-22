<template>
    <DialogModal :show="show" @close="closeModal" max-width="xl">
        <template #title>
            <h2 class="font-semibold">Detalles de agente aduanal</h2>
        </template>
        <template #content>
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="font-bold text-gray3F">{{ agentData.name }}</h3>
                    <p class="text-sm text-gray3F">ID: {{ agentData.id }}</p>
                </div>
                <PrimaryButton v-if="$page.props.auth.user.permissions.includes('Editar agentes aduanales')" @click="editAgent"
                    class="!text-primary !bg-white border !border-gray-300 !rounded-md text-sm !py-1">
                    <PencilIcon class="size-4 inline mr-1" />
                    Editar
                </PrimaryButton>
            </div>
            <div class="divide-y divide-gray-200 mt-6">
                <div class="py-3 flex justify-between text-sm">
                    <span class="text-gray3F w-[35%]">Nombre / Razón Social</span>
                    <span class="text-black w-[65%] text-right">{{ agentData.name }}</span>
                </div>
                <div class="py-3 flex justify-between text-sm">
                    <span class="text-gray3F w-[35%]">Nombre del contacto</span>
                    <span class="text-black w-[65%] text-right">{{ agentData.contact_person ?? '-' }}</span>
                </div>
                <div class="py-3 flex justify-between text-sm">
                    <span class="text-gray3F w-[35%]">Correo electrónico</span>
                    <span class="text-black w-[65%] text-right">{{ agentData.email ?? '-' }}</span>
                </div>
                <div class="py-3 flex justify-between text-sm">
                    <span class="text-gray3F w-[35%]">Teléfono</span>
                    <span class="text-black w-[65%] text-right">{{ agentData.phone ?? '-' }}</span>
                </div>
                <div class="py-3 flex justify-between text-sm">
                    <span class="text-gray3F w-[35%]">Notas</span>
                    <span class="text-black w-[65%] text-right" style="white-space: pre-line;">
                        {{ agentData.notes ?? '-' }}
                    </span>
                </div>
                <div class="py-3 flex justify-between text-sm">
                    <span class="text-gray3F w-[35%]">Creado por</span>
                    <span class="text-black w-[65%] text-right">{{ agentData.user?.name ?? 'N/A' }}</span>
                </div>
                <div class="py-3 flex justify-between text-sm">
                    <span class="text-gray3F w-[35%]">Creado el</span>
                    <span class="text-black w-[65%] text-right">{{ formatDate(agentData.created_at) }}</span>
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
        agentData: Object,
    },
    emits: ['close'],
    methods: {
        closeModal() {
            this.$emit('close');
        },
        editAgent() {
            router.get(route('customs-agents.edit', this.agentData.id));
        },
        formatDate(dateString) {
            if (!dateString) return 'N/A';
            return new Date(dateString).toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric' });
        },
    },
};
</script>
