<template>
    <AppLayout title="Detalles usuario">
        <header class="lg:px-9 px-1 mt-3">

            <section class="flex items-center justify-between mt-2">
                <h1 class="font-bold text-base">Detalles de usuario</h1>
                <div class="flex items-center space-x-2">
                    <PrimaryButton @click="$inertia.get(route('users.edit', user.id))">Editar</PrimaryButton>
                    <SecondaryButton @click="$inertia.get(route('users.create'))" class="!rounded-[10px]"><i
                            class="fa-solid fa-plus"></i></SecondaryButton>
                </div>
            </section>
            <!-- buscador -->
            <el-select @change="$inertia.get(route('users.show', selectedItem))" v-model="selectedItem"
                class="w-full lg:!w-1/4 mt-5" placeholder="Buscar usuario" filterable
                no-data-text="No hay más usuarios registrados" no-match-text="No se encontraron coincidencias">
                <el-option v-for="item in users" :key="item.id" :label="item.name" :value="item.id" />
            </el-select>
            <!-- <div class="lg:w-1/4 relative mt-5">
                <input class="input w-full pl-9" placeholder="Buscar usuario" type="text">
                <i class="fa-solid fa-magnifying-glass text-xs text-gray99 absolute top-[10px] left-4"></i>
            </div> -->
        </header>
        <main class="relative mt-5">
            <div class="bg-gradient-to-r from-gray-200 from-5% via-gray99 via-50% to-gray-200 to-95% h-32 pt-px">
                <button @click="$inertia.get(route('users.index'))"
                    class="flex justify-center items-center rounded-full py-[9px] px-3 focus:outline-none bg-grayD9 ml-3 mt-3">
                    <i class="fa-solid fa-angle-left"></i>
                </button>
            </div>
            <figure class="size-40 rounded-[5px] bg-gray-500 absolute top-8 left-[calc(50%-5rem)]">
                <img :src="user.profile_photo_url" :alt="user.name" class="size-40 object-cover rounded-[5px]">
            </figure>
            <section class="mt-20">
                <h1 class="font-bold text-center">{{ user.name }}</h1>
                <article class="flex mt-12 mx-36 text-sm">
                    <div class="w-1/2 border-r border-grayD9 grid grid-cols-2 gap-x-3 gap-y-2 pr-16">
                        <p class="flex items-center space-x-2 text-gray99">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0M12 12.75h.008v.008H12v-.008Z" />
                            </svg>
                            <span>Departamento:</span>
                        </p>
                        <p>{{ user.employee_properties?.department }}</p>
                        <p class="flex items-center space-x-2 text-gray99">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16.5 3.75V16.5L12 14.25 7.5 16.5V3.75m9 0H18A2.25 2.25 0 0 1 20.25 6v12A2.25 2.25 0 0 1 18 20.25H6A2.25 2.25 0 0 1 3.75 18V6A2.25 2.25 0 0 1 6 3.75h1.5m9 0h-9" />
                            </svg>
                            <span>Puesto:</span>
                        </p>
                        <p>{{ user.employee_properties?.job_position }}</p>
                        <p class="flex items-center space-x-2 text-gray99">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                            </svg>
                            <span>Correo electrónico:</span>
                        </p>
                        <p>{{ user.email }}</p>
                    </div>
                    <div class="w-1/2 grid grid-cols-2 gap-x-3 gap-y-2 pl-16">
                        <p class="flex items-start space-x-2 text-gray99">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                            </svg>
                            <span>Teléfono:</span>
                        </p>
                        <p>{{ user.phone }}</p>
                        <p class="flex items-start space-x-2 text-gray99">
                            <svg v-if="user.is_active" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <span>Estado:</span>
                        </p>
                        <p :class="user.is_active ? 'text-greenpad' : 'text-redpad'">{{ user.is_active ? 'Activo' :
                            'Inactivo' }}</p>
                    </div>
                </article>
            </section>
        </main>
    </AppLayout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import FileUploader from "@/Components/MyComponents/FileUploader.vue";
import Back from "@/Components/MyComponents/Back.vue";
import InputFilePreview from "@/Components/MyComponents/InputFilePreview.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import { useForm } from "@inertiajs/vue3";

export default {
    data() {
        const form = useForm({
            name: null,
            email: null,
            phone: null,
            image: null,
            employee_properties: {
                department: null,
                job_position: null,
            },
        });

        return {
            form,
            selectedItem: null,
            departments: [
                'Administración',
                'Almacén',
                'Comercial',
                'Compras',
                'Contabilidad',
                'Contraloría',
                'Crédito y cobranza',
                'Dirección',
                'Empaques',
                'Inspección',
                'Mantenimiento',
                'Producción',
                'Recursos Humanos',
                'Sistemas',
                'Tesorería',
            ],
        }
    },
    components: {
        AppLayout,
        PrimaryButton,
        FileUploader,
        InputLabel,
        InputError,
        Back,
        InputFilePreview,
        SecondaryButton,
    },
    props: {
        user: Object,
        users: Array,
    },
    methods: {
        store() {
            this.form.post(route("users.store"), {
                onSuccess: () => {
                    this.$notify({
                        title: "Correcto",
                        message: "Se ha agregado el usuario",
                        type: "success",
                    });
                },
            });
        },
        saveImage(image) {
            this.form.image = image;
        },
        clearImage() {
            this.form.image = null;
        },
    }
}
</script>