<script>
export default {
    props: {
        emails: {
            type: Array,
            required: true,
        }
    },
    emits: ['remove'],
}
</script>

<template>
    <div class="mb-6">
        <h3 class="text-xl font-semibold mb-3">Correos Externos Suscritos:</h3>
        <div v-if="emails.length > 0" class="flex flex-wrap gap-2">
            <span v-for="email in emails" :key="email" class="bg-gray-700 text-gray-200 text-sm font-medium px-3 py-1.5 rounded-full flex items-center">
                {{ email }}
                <button @click="$emit('remove', email)" class="remove-external-email-btn ml-2 text-gray-400 hover:text-white">
                    <i class="fas fa-times-circle"></i>
                </button>
            </span>
        </div>
        <div v-else>
            <p class="text-gray-500">No hay correos externos suscritos a este evento.</p>
        </div>
    </div>
</template>
