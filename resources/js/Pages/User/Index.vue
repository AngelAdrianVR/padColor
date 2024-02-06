<template>
    <AppLayout title="Usuarios">
        <div class="flex justify-between items-center mt-4 mx-10">
            <h1 class="text-lg font-bold">Todos los usuarios</h1>
            <PrimaryButton @click="$inertia.get(route('users.create'))">Crear ticket</PrimaryButton>
        </div>

        <!-- Buscador -->
        <div class="flex justify-between items-center mt-4 mx-2 lg:mx-10">

            <div class="lg:w-1/4 relative">
                <input class="input w-full pl-9" placeholder="Buscar usuario" type="text">
                <i class="fa-solid fa-magnifying-glass text-xs text-gray99 absolute top-[10px] left-4"></i>
            </div>
        </div>

        <!-- usuarios -->
        <div class="mt-7">
            <div class="flex items-center space-x-3 border-b border-grayD9 pb-2">
                <label class="flex items-center ml-7 lg:ml-24">
                    <Checkbox v-model:checked="all_users" name="all" />
                    <span class="ms-2 text-sm font-bold">Todos los usuarios</span>
                </label>
            </div>
            <UserRow v-for="user in users.data" :key="user" :user="user" />
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import UserRow from "@/Components/MyComponents/User/UserRow.vue";
import Checkbox from '@/Components/Checkbox.vue';

export default {
    data() {
        return {
            all_users: false,
        }
    },
    components: {
        AppLayout,
        PrimaryButton,
        UserRow,
        Checkbox
    },
    props: {
        users: Object
    },
    methods: {

    }
}
</script>