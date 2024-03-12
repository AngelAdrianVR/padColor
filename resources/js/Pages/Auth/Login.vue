<script setup>
import { ref, onMounted } from "vue";
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const users = ref(null);

const form = useForm({
    name: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

const fetchUsers = async () => {
    try {
        const response = await axios.get(route("users.get-all"));

        if (response.status === 200) {
            users.value = response.data.items;
        }
    } catch (error) {
        console.error(error);
    }
};

onMounted(() => {
    fetchUsers();
});
</script>

<template>

    <Head title="Inicio de sesión" />

    <AuthenticationCard>
        <h1 class="text-gray66 font-bold text-sm ml-5 mt-2">SISTEMA DE TICKETS</h1>
        <div class="flex justify-center mt-2">
            <AuthenticationCardLogo />
        </div>
        <form class="w-2/3 mx-auto" @submit.prevent="submit">
            <div>
                <InputLabel class="ml-5 text-gray66" for="name" value="Nombre" />
                <el-select v-model="form.name" id="name" class="w-full" filterable
                    no-data-text="No hay más usuarios registrados" no-match-text="No se encontraron coincidencias">
                    <el-option v-for="item in users" :key="item.id" :label="item.name" :value="item.name" />
                </el-select>
                <InputError :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <InputLabel class="ml-5 text-gray66" for="password" value="Contraseña" />
                <el-input v-model="form.password" required class="w-2/3" type="password" show-password />
                <InputError :message="form.errors.password" />
            </div>

            <div class="block mt-3 ml-2">
                <label class="flex items-center">
                    <Checkbox v-model:checked="form.remember" name="remember" />
                    <span class="ms-2 text-xs text-gray66">Mantener sesión</span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-4">
                <PrimaryButton class="mx-auto px-16 mt-4" :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing || !form.name || !form.password">
                    Iniciar sesión
                </PrimaryButton>
            </div>
        </form>
    </AuthenticationCard>
</template>
