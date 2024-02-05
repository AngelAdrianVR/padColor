<template>
    <AppLayout title="Tickets">
        <div class="flex justify-between items-center mt-4 mx-10">
            <h1 class="text-lg font-bold">Todos los tickets</h1>
            <PrimaryButton @click="$inertia.get(route('tickets.create'))">Crear ticket</PrimaryButton>
        </div>

        <!-- Buscador y filtros -->
        <div class="flex justify-between items-center mt-4 mx-2 lg:mx-10">
            
            <div class="lg:w-1/4 relative">
                <input class="input w-full pl-9" placeholder="Buscar tickets" type="text">
                <i class="fa-solid fa-magnifying-glass text-xs text-gray99 absolute top-[10px] left-4"></i>
            </div>

            <div class="flex items-center space-x-3 lg:w-1/4">
                <select value="Filtrar" class="input">
                    <option disabled value="Filtrar"> <i class="fa-solid fa-arrow-down-short-wide"></i> Filtro </option>
                </select>
                <select value="Ordenar" class="input">
                    <option disabled value="Ordenar"> <i class="fa-solid fa-arrow-down-short-wide"></i> Ordenar </option>
                </select>
            </div>
        </div>

        <!-- Tickets -->
        <div class="mt-9">
            <div class="flex items-center space-x-3 border-b border-grayD9 pb-2">
                <label class="flex items-center ml-7 lg:ml-20">
                    <Checkbox v-model:checked="all_tickets" name="remember" />
                    <span class="ms-2 text-sm font-bold">Todos los tickets</span>
                </label>
            </div>


        </div>
    </AppLayout>
  
</template>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import Checkbox from '@/Components/Checkbox.vue';

export default {
data() {
    return {
        all_tickets: false,
    }
},
components:{
AppLayout,
PrimaryButton,
Checkbox
},
props:{
tickets: Object
},
methods:{

}
}
</script>