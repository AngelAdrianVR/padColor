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
            return this.subscriptions.includes(userId);
        }
    }
}
</script>

<template>
    <div class="overflow-x-auto bg-gray-900/70 rounded-lg shadow-xl">
        <table class="w-full text-sm text-left text-gray-300">
            <thead class="text-xs text-gray-400 uppercase bg-gray-700">
                <tr>
                    <th scope="col" class="px-6 py-3">Nombre</th>
                    <th scope="col" class="px-6 py-3">Correo</th>
                    <th scope="col" class="px-6 py-3">Departamento</th>
                    <th scope="col" class="px-6 py-3">Compañía</th>
                    <th scope="col" class="px-6 py-3 text-center">Suscrito</th>
                </tr>
            </thead>
            <tbody v-if="users.length > 0">
                <tr v-for="user in users" :key="user.id" class="border-b border-gray-700 hover:bg-gray-800/50 transition-colors duration-200">
                    <td class="px-6 py-4 font-medium text-white">{{ user.name }}</td>
                    <td class="px-6 py-4">{{ user.email }}</td>
                    <td class="px-6 py-4">{{ user.department }}</td>
                    <td class="px-6 py-4">{{ user.company }}</td>
                    <td class="px-6 py-4 text-center">
                        <label class="relative inline-flex items-center cursor-pointer">
                          <input 
                              type="checkbox" 
                              :checked="isSubscribed(user.id)"
                              @change="$emit('toggle', user.id)"
                              class="sr-only peer">
                          <div class="w-11 h-6 bg-gray-600 rounded-full peer peer-focus:ring-4 peer-focus:ring-blue-800 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </td>
                </tr>
            </tbody>
            <tbody v-else>
                <tr>
                    <td colspan="5" class="text-center py-8 text-gray-400">
                        No se encontraron usuarios con los filtros aplicados.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
