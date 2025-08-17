<template>
    <div class="my-4">
        <div v-if="users.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            <article v-for="user in users" :key="user.id" class="border border-grayD9 px-4 py-3 rounded-xl">
                <div class="flex pb-2 border-b border-grayD9">
                    <div class="w-[70%] flex space-x-2">
                        <figure class="size-10 rounded-full bg-grayED flex-shrink-0">
                            <img :src="user.profile_photo_url" class="size-10 object-contain rounded-full">
                        </figure>
                        <div class="flex flex-col space-y-1 text-xs">
                            <span class="font-bold">{{ user.name }}</span>
                            <span class="text-[#666666]">{{ user.email }}</span>
                            <p class="text-[#666666] flex items-center space-x-1 pt-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                                </svg>
                                <span>{{ user.employee_properties.company }}</span>
                            </p>
                            <p class="text-[#666666] flex items-center space-x-1">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0M12 12.75h.008v.008H12v-.008Z" />
                                </svg>
                                <span>{{ user.employee_properties.department }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="w-[30%] flex items-start justify-end">
                        <span class="text-xs text-[#340C6C] bg-[#E5D0FF] px-2 py-1 rounded-full">Interno</span>
                    </div>
                </div>
                <div class="flex justify-between text-xs pt-1">
                    <span v-if="isSubscribed(user.id)" class="text-primary">Notificación activada</span>
                    <span v-else class="text-gray99">Notificación desactivada</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" :checked="isSubscribed(user.id)" @change="$emit('toggle', user.id)"
                            class="sr-only peer">
                        <div
                            class="w-7 h-4 bg-gray99 rounded-full peer peer-focus:ring-0 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[3px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-primary">
                        </div>
                    </label>
                </div>
            </article>
        </div>
        <div v-else>
            <p class="text-gray99 text-sm">No se encontraron usuarios con los filtros aplicados.</p>
        </div>
    </div>
</template>
<script>
export default {
    props: {
        users: {
            type: Array,
            required: true,
        },
        subscriptions: {
            type: Array,
            required: true,
        }
    },
    emits: ['toggle'],
    methods: {
        isSubscribed(userId) {
            return this.subscriptions.includes(String(userId));
        }
    }
}
</script>