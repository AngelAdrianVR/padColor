<template>
    <div>
        <h1 class="font-bold">Ordenes de producción</h1>
        <!-- Buscador y botones -->
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center mt-4 space-y-2 lg:space-y-0">
            <div class="lg:w-1/3 relative lg:mr-12">
                <input :value="modelValue" @input="$emit('update:modelValue', $event.target.value)" @keyup.enter="$emit('search')" class="input w-full pl-9"
                    placeholder="Buscar por folio, progreso, producto, cliente, temporada" type="search">
                <i class="fa-solid fa-magnifying-glass text-xs text-gray99 absolute top-[10px] left-4"></i>
            </div>
            <el-dropdown split-button type="primary" @click="$inertia.visit(route('productions.create'))" trigger="click">
                <PlusIcon class="size-4 mr-1" /> Nueva producción
                <template #dropdown>
                    <el-dropdown-menu>
                        <el-dropdown-item @click="$emit('show-import')">Importar</el-dropdown-item>
                        <el-dropdown-item @click="$emit('show-export')">Exportar</el-dropdown-item>
                    </el-dropdown-menu>
                </template>
            </el-dropdown>
        </div>
    </div>
</template>

<script>
import { PlusIcon } from '@heroicons/vue/24/outline';

export default {
    name: 'ActionHeader',
    components: {
        PlusIcon,
    },
    props: {
        modelValue: String, // para v-model en el input de búsqueda
    },
    emits: ['update:modelValue', 'search', 'show-import', 'show-export'],
};
</script>