<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
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
</script>

<template>

    <Head title="Inicio de sesión" />

    <AuthenticationCard>
        <h1 class="text-gray66 font-bold text-sm ml-5 mt-2">SISTEMA DE TICKETS</h1>
        <div class="flex justify-center mt-2">
            <AuthenticationCardLogo />
        </div>

        <!-- <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div> -->

        <form class="w-2/3 mx-auto" @submit.prevent="submit">
            <div>
                <InputLabel class="ml-5 text-gray66" for="name" value="Nombre" />
                <el-input v-model="form.name" id="name" type="text" placeholder="Escribe tu nombre" required autofocus
                    autocomplete="username">
                    <template #prefix>
                        <i class="fa-solid fa-user text-sm text-primary"></i>
                    </template>
                </el-input>
                <InputError :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <InputLabel class="ml-5 text-gray66" for="password" value="Contraseña" />
                <el-input v-model="form.password" required class="w-2/3" type="password"
                    placeholder="Ingresa tu contraseña " show-password>

                    <template #prefix>
                        <i class="fa-solid fa-lock text-sm text-primary"></i>
                    </template>
                </el-input>
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
