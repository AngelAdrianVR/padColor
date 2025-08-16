<script>
export default {
    props: {
        search: String,
        department: String,
        company: String,
    },
    emits: ['update:search', 'update:department', 'update:company', 'add-external'],
}
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6 p-4 bg-blue-100/50 rounded-lg shadow-md">
        <!-- Usamos :value y @input para crear un v-model bidireccional con el padre -->
        <!-- <el-input :value="search" @input="$emit('update:search', $event.target.value)"
            placeholder="Buscar por nombre o correo..." :maxlength="150" clearable />
        <el-input :value="department" @input="$emit('update:department', $event.target.value)"
            placeholder="Filtrar por departamento..." :maxlength="150" clearable />
        <el-input :value="company" @input="$emit('update:company', $event.target.value)"
            placeholder="Filtrar por compañía..." :maxlength="150" clearable /> -->

        <input :value="search" @input="$emit('update:search', $event.target.value)" type="text"
            placeholder="Buscar por nombre o correo..."
            class="col-span-1 md:col-span-1 bg-gray-800 border-gray-600 rounded-lg p-2 text-white placeholder-gray-400 focus:ring-blue-500 focus:border-blue-500">

        <input :value="department" @input="$emit('update:department', $event.target.value)" type="text"
            placeholder="Filtrar por departamento..."
            class="col-span-1 md:col-span-1 bg-gray-800 border-gray-600 rounded-lg p-2 text-white placeholder-gray-400 focus:ring-blue-500 focus:border-blue-500">

        <input :value="company" @input="$emit('update:company', $event.target.value)" type="text"
            placeholder="Filtrar por compañía..."
            class="col-span-1 md:col-span-1 bg-gray-800 border-gray-600 rounded-lg p-2 text-white placeholder-gray-400 focus:ring-blue-500 focus:border-blue-500">

        <button @click="$emit('add-external')"
            class="col-span-1 md:col-span-1 bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-300 flex items-center justify-center">
            <i class="fas fa-plus mr-2"></i> Añadir Correo Externo
        </button>
    </div>
</template>
