<template>
    <div class="bg-white overflow-hidden w-full font-sans">
        <!-- Encabezado con degradado sutil -->
        <div class="h-20 bg-gradient-to-br from-gray-50 to-gray-200 relative">
             <div class="absolute top-2 right-2">
                 <Link :href="route('profile.show')" class="flex items-center justify-center w-8 h-8 rounded-full bg-white/50 hover:bg-white text-primary transition-all shadow-sm" title="Editar Perfil">
                    <i class="fa-solid fa-pen text-xs"></i>
                 </Link>
             </div>
        </div>

        <!-- Contenido Principal -->
        <div class="px-5 pb-5 relative">
            <!-- Avatar Superpuesto -->
            <div class="-mt-10 mb-3 flex justify-between items-end">
                 <img :src="$page.props.auth.user.profile_photo_url" 
                      class="w-20 h-20 rounded-full border-4 border-white shadow-md object-cover bg-white">
                 
                 <!-- Badge o Estado (Opcional) -->
                 <span class="mb-1 text-[10px] px-2 py-0.5 rounded-full bg-green-100 text-green-700 font-medium border border-green-200">
                    Activo
                 </span>
            </div>

            <!-- Información del Usuario -->
            <div class="text-left">
                <h3 class="text-lg font-bold text-gray-800 leading-tight truncate">
                    {{ $page.props.auth.user.name }}
                </h3>
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide mt-1">
                    {{ $page.props.auth.user.employee_properties?.job_position || 'Colaborador' }}
                </p>

                <div class="mt-4 space-y-2.5">
                    <div class="flex items-center gap-3 text-sm text-gray-600 bg-gray-50 p-2 rounded-lg border border-gray-100">
                        <i class="fa-regular fa-envelope text-gray-400"></i>
                        <span class="truncate text-xs">{{ $page.props.auth.user.email }}</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm text-gray-600 px-2">
                        <i class="fa-solid fa-phone text-gray-400"></i>
                        <span class="text-xs">{{ $page.props.auth.user.phone || 'Sin teléfono' }}</span>
                    </div>
                </div>
            </div>

            <!-- Botón de Cerrar Sesión -->
            <div class="mt-6 pt-4 border-t border-gray-100">
                <form @submit.prevent="logout" class="w-full">
                    <button type="submit" class="w-full group flex items-center justify-center gap-2 px-4 py-2 text-sm text-red-600 hover:text-white hover:bg-red-500 rounded-lg transition-all duration-200 border border-transparent hover:shadow-md">
                        <i class="fa-solid fa-arrow-right-from-bracket transition-transform group-hover:-translate-x-1"></i>
                        <span>Cerrar sesión</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import { Link } from "@inertiajs/vue3"

export default {
    components: {
        Link,
    },
    methods: {
        logout() {
            this.$inertia.post(route('logout'));
        },
    }
}
</script>