<template>
    <div class="my-4">
        <div v-if="emails.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            <article v-for="email in emails" :key="email" class="border border-grayD9 px-4 py-3 rounded-xl">
                <div class="flex">
                    <div class="w-[70%] flex space-x-2">
                        <span class="bg-grayED text-[#666666] rounded-full size-10 flex items-center justify-center">
                            {{ email.charAt(0).toUpperCase() }}
                        </span>
                        <div class="flex flex-col space-y-1 text-xs">
                            <span class="font-bold">{{ email }}</span>
                            <span class="text[#666666]">{{ email }}</span>
                        </div>
                    </div>
                    <div class="w-[30%] flex items-start justify-end">
                        <span class="text-xs text-[#6C3F0C] bg-[#FFEFD0] px-2 py-1 rounded-full">Externo</span>
                    </div>
                </div>
                <div class="flex justify-end">
                    <el-popconfirm title="¿Continuar con la eliminación?" @confirm="$emit('remove', email)" icon-color="#373737">
                        <template #reference>
                            <button type="button"
                                class="bg-[#B8C1E5] text-primary size-7 rounded-[10px] flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                            </button>
                        </template>
                    </el-popconfirm>
                </div>
            </article>
        </div>
        <div v-else>
            <p class="text-gray99 text-sm">No hay correos externos suscritos a este evento.</p>
        </div>
    </div>
</template>
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