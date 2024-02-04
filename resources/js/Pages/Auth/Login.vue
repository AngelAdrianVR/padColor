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
    email: '',
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

        <div class="flex justify-center mt-14">
            <AuthenticationCardLogo />
        </div>

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>

        <form class="w-2/3 mx-auto mt-12" @submit.prevent="submit">
            <div>
                <InputLabel class="ml-5 text-white" for="email" value="Correo electrónico" />
                <el-input v-model="form.email"
                    id="email"
                    class="w-2/3" 
                    type="email"
                    placeholder="Escribe tu correo electrónico"
                    required
                    autofocus
                    autocomplete="username"
                    >
                    <template #prefix>
                        <i class="fa-solid fa-envelope text-primary text-sm"></i>
                    </template>
                </el-input>
                <!-- <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full"
                    required
                    autofocus
                    autocomplete="username"
                /> -->
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel class="ml-5 text-white" for="password" value="Contraseña" />
                <el-input
                    v-model="form.password"
                    required
                    class="w-2/3"
                    type="password"
                    placeholder="Please input password"
                    show-password
                >
                    <template #prefix>
                        <i class="fa-solid fa-lock text-sm"></i>
                    </template>
                </el-input>
                <!-- <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full"
                    required
                    autocomplete="current-password"
                /> -->
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="block mt-3 ml-5">
                <label class="flex items-center">
                    <Checkbox v-model:checked="form.remember" name="remember" />
                    <span class="ms-2 text-sm text-[#999999]">Mantener sesión</span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-4">
                <!-- <Link v-if="canResetPassword" :href="route('password.request')" class="underline text-sm text-[#999999] hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Forgot your password?
                </Link> -->

                <PrimaryButton class="mx-auto px-12 mt-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing || !form.email || ! form.password">
                    Iniciar sesión
                </PrimaryButton>
            </div>
        </form>
    </AuthenticationCard>
</template>
