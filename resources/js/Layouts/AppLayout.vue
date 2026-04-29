<script setup>
import { ref, onMounted, computed, onBeforeUnmount, watch } from 'vue';
// import Echo from 'laravel-echo';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import Banner from '@/Components/Banner.vue';
import SideNav from '@/Components/MyComponents/SideNav.vue';
import ProfileCard from '@/Components/MyComponents/ProfileCard.vue';
import NotificationsCenter from '@/Components/MyComponents/NotificationsCenter.vue';
import axios from 'axios';
import { ElMessage } from 'element-plus';

defineProps({
    title: String,
});

const showingNavigationDropdown = ref(false);
const notifications = ref([]);
const showProfileCard = ref(false);
const showMenu = ref(false);
const openMenus = ref({}); // Estado para controlar qué menús están desplegados

// Menú unificado idéntico al SideNav
const options = [
    {
        label: 'Inicio',
        route: 'dashboard',
        active: 'dashboard',
        showPermission: 'Ver dashboard',
        icon: '<i class="fa-solid fa-house text-lg text-primary"></i>',
        options: []
    },
    {
        label: 'Tickets',
        route: 'tickets.index',
        active: 'tickets.*',
        showPermission: 'Ver tickets',
        icon: '<i class="fa-solid fa-ticket text-lg text-primary"></i>',
        options: []
    },
    {
        label: 'Usuarios',
        route: 'users.index',
        active: 'users.*',
        showPermission: 'Ver usuarios',
        icon: '<i class="fa-solid fa-users text-lg text-primary"></i>',
        options: []
    },
    {
        label: 'Clientes',
        route: 'users.index',
        active: 'users.*',
        showPermission: 'Ver clientes',
        icon: '<i class="fa-solid fa-handshake text-lg text-primary"></i>',
        options: []
    },
    {
        label: 'Importaciones',
        route: 'imports.index',
        active: 'imports.*',
        showPermission: 'Ver importaciones',
        icon: '<i class="fa-solid fa-ship text-lg text-primary"></i>',
        options: [
            { label: 'Kanban', route: 'imports.index', active: 'imports.*', showPermission: 'Ver importaciones' },
            { label: 'Proveedores', route: 'suppliers.index', active: 'suppliers.*', showPermission: 'Ver importaciones' },
            { label: 'Agentes aduanales', route: 'customs-agents.index', active: 'customs-agents.*', showPermission: 'Ver importaciones' },
        ]
    },
    {
        label: 'SwAssistant',
        route: 'productions.index',
        active: 'productions.*',
        showPermission: 'Ver producciones',
        icon: '<i class="fa-solid fa-robot text-lg text-primary"></i>',
        options: [
            { label: 'Reportes', route: 'productions.report', active: 'productions.report', showPermission: 'Ver producciones' },
            { label: 'Inicio', route: 'productions.dashboard', active: 'productions.dashboard', showPermission: 'Ver producciones' },
            { label: 'Gestión de producción', route: 'productions.index', active: 'productions.index', showPermission: 'Ver producciones' },
            { label: 'Máquinas', route: 'machines.index', active: 'machines.*', showPermission: 'Ver máquinas' },
        ]
    },
    {
        label: 'Catálogo',
        route: 'products.index',
        active: 'products.*',
        showPermission: 'Ver productos',
        icon: '<i class="fa-solid fa-box-open text-lg text-primary"></i>',
        options: [
            { label: 'Productos', route: 'products.index', active: 'products.*', showPermission: 'Ver productos' },
            { label: 'Materia prima', route: 'raw-materials.index', active: 'raw-materials.*', showPermission: 'Ver productos' },
        ]
    },
    {
        label: 'Configuraciones',
        route: 'settings.index',
        active: 'settings.*',
        showPermission: 'Ver configuraciones',
        icon: '<i class="fa-solid fa-gear text-lg text-primary"></i>',
        options: []
    },
];

// Lógica para desplegar submenús
const toggleSubMenu = (index) => {
    openMenus.value[index] = !openMenus.value[index];
};

// Verificar si un grupo está activo (si tiene hijos activos)
const isGroupActive = (option) => {
    if (option.active && route().current(option.active)) return true;
    if (option.options && option.options.length > 0) {
        return option.options.some(opt => route().current(opt.active));
    }
    return false;
};

const openNewTab = (url) => {
    window.open(url, '_blank');
};

// Observa los cambios en las props de la página
watch(() => usePage().props.flash, (flash) => {
    if (flash.success) {
        ElMessage({
            type: 'success',
            message: flash.success,
        });
    }
    if (flash.error) {
        ElMessage({
            type: 'error',
            message: flash.error,
        });
    }
    if (flash.info) {
        ElMessage({
            type: 'info',
            message: flash.info,
        });
    }
}, {
    // deep: true es importante para que observe cambios dentro del objeto flash
    deep: true
});

const fetchNotifications = async () => {
    try {
        const response = await axios.get(route('users.get-notifications'))

        if (response.status === 200) {
            notifications.value = response.data.items;
        }
    } catch (error) {
        console.log(error)
    }
};

const getUnreadNotifications = computed(() => {
    return notifications.value?.filter(item => item.read_at === null).length;
});

// Escuchar el evento de broadcast al montar el componente
const handleNotificationsMarkedAsRead = () => {
    console.log('ejecutado broadcasting');
    fetchNotifications();
};

onMounted(() => {
    fetchNotifications();
    Echo.channel('notifications') // Reemplaza 'notifications' con el nombre de tu canal
        .listen('NotificationsMarkedAsRead', handleNotificationsMarkedAsRead);

    // Abrir automáticamente el grupo activo en el menú móvil
    options.forEach((opt, index) => {
        if (isGroupActive(opt)) {
            openMenus.value[index] = true;
        }
    });
});

// Desmontar el componente y limpiar el evento de broadcast
onBeforeUnmount(() => {
    Echo.leave('notifications');
});

</script>

<template>
    <div>

        <Head :title="title" />

        <Banner />

        <div class="overflow-hidden h-screen md:flex bg-white">
            <!-- sidenav -->
            <!-- SE AGREGO 'hidden lg:block' PARA OCULTAR EN MOVIL/TABLET -->
            <aside class="hidden lg:block col-span-2">
                <SideNav />
            </aside>

            <!-- resto de pagina -->
            <main class="flex-1 min-w-0">
                <nav class="lg:bg-white bg-grayED border-b border-gray-100">
                    <!-- Primary Navigation Menu -->
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="flex justify-between h-10">
                            <div class="flex">
                                <!-- Logo -->
                                <div class="shrink-0 flex items-center lg:hidden">
                                    <Link :href="route('dashboard')">
                                    <figure class="">
                                        <img class="w-32" src="@/../../public/images/logo_name.png" alt="logo">
                                    </figure>
                                    </Link>
                                </div>
                            </div>

                            <!-- mensaje para cambiar contrasena -->
                            <div v-if="!$page.props.auth.user.password_changed"
                                class="z-20 flex items-center w-1/2 md:w-3/4">
                                <article
                                    class="flex items-center space-x-3 bg-red-200 text-red-600 rounded-md px-3 py-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="hidden md:inline size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 9v3.75m0-10.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.25-8.25-3.286Zm0 13.036h.008v.008H12v-.008Z" />
                                    </svg>
                                    <p class="text-[7px] md:text-xs">
                                        Para garantizar la seguridad de tu cuenta, te recomendamos
                                        cambiar tu contraseña por defecto. Esto ayudará a proteger tu información
                                        personal y garantizará que solo tú tengas acceso.
                                        <Link :href="route('profile.show')" class="text-blue-600 underline">
                                        Click aqui
                                        </Link>
                                    </p>
                                </article>
                            </div>

                            <div class="hidden sm:flex sm:items-center sm:ms-6">
                                <!-- notifications -->
                                <NotificationsCenter class="hidden lg:block" />
                            </div>
                        </div>
                    </div>
                </nav>
                <div class="overflow-y-auto h-[calc(100vh-5rem)] lg:h-[calc(100vh-2.6rem)] bg-white">
                    <slot />
                </div>
                <!-- ---------------- footer nave mobile view --------------- -->
                <nav class="lg:hidden fixed bottom-0 w-full z-10">
                    <div class="w-full h-10 flex justify-between items-center px-4 bg-grayED">
                        <button @click="showMenu = !showMenu"
                            :class="{ 'border-primary bg-primary text-white': showMenu }"
                            class="flex items-center space-x-1 px-3 py-1 text-sm border border-[#999999] text-[#999999] rounded-full transition-all duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="size-5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3.75 6.75h16.5M3.75 12H12m-8.25 5.25h16.5" />
                            </svg>
                            <span>Menú</span>
                        </button>
                        <div v-if="showMenu" class="absolute bottom-12 left-2 bg-grayED rounded-2xl w-2/3 py-3 overflow-y-auto max-h-[70vh] shadow-lg border border-gray-200">
                            <ul class="space-y-1 px-2">
                                <template v-for="(option, index) in options" :key="index">
                                    <!-- Verificar permiso -->
                                    <li v-if="$page.props.auth.user.permissions.includes(option.showPermission)">
                                        <!-- Opción con Submenú -->
                                        <div v-if="option.options && option.options.length > 0">
                                            <button @click="toggleSubMenu(index)"
                                                class="w-full px-4 flex items-center justify-between py-2 rounded-lg transition-colors duration-200"
                                                :class="isGroupActive(option) ? 'text-primary font-medium' : 'text-gray-600 hover:bg-gray-100'">
                                                <div class="flex items-center space-x-3">
                                                    <div class="w-6 flex justify-center text-lg" v-html="option.icon"></div>
                                                    <span class="text-sm">{{ option.label }}</span>
                                                </div>
                                                <i class="fa-solid fa-chevron-down text-xs transition-transform duration-200" :class="{'rotate-180': openMenus[index]}"></i>
                                            </button>
                                            
                                            <!-- Lista de Subopciones -->
                                            <ul v-show="openMenus[index]" class="pl-4 mt-1 space-y-1">
                                                <li v-for="(subOption, subIndex) in option.options" :key="subIndex">
                                                    <button v-if="!subOption.showPermission || $page.props.auth.user.permissions.includes(subOption.showPermission)"
                                                        @click="$inertia.get(route(subOption.route))"
                                                        class="w-full px-4 py-2 rounded-lg text-left text-sm transition-colors duration-200 flex items-center space-x-2"
                                                        :class="route().current(subOption.active) ? 'bg-white text-primary font-medium shadow-sm' : 'text-gray-500 hover:bg-gray-100'">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                                        <span>{{ subOption.label }}</span>
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                        
                                        <!-- Opción Simple -->
                                        <button v-else
                                            @click="$inertia.get(route(option.route))"
                                            class="w-full px-4 flex items-center space-x-3 py-2 rounded-lg transition-colors duration-200"
                                            :class="route().current(option.active) ? 'bg-white text-primary font-medium shadow-sm' : 'text-gray-600 hover:bg-gray-100'">
                                            <div class="w-6 flex justify-center text-lg" v-html="option.icon"></div>
                                            <span class="text-sm">{{ option.label }}</span>
                                        </button>
                                    </li>
                                </template>
                                
                                <!-- Separador -->
                                <div class="border-t border-gray-300 my-2 mx-2"></div>
                                
                                <!-- Capacitación -->
                                <li>
                                    <button @click="openNewTab('https://capacitacionpadcolor.my.canva.site/capacitacionpadcolor')"
                                        class="w-full px-4 flex items-center space-x-3 py-2 rounded-lg text-gray-600 hover:bg-gray-100 transition-colors duration-200">
                                        <div class="w-6 flex justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 text-primary">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 0 0-.491 6.347A48.627 48.627 0 0 1 12 20.904a48.627 48.627 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.905 59.905 0 0 1 12 3.493a59.902 59.902 0 0 1 10.499 5.258 50.55 50.55 0 0 0-2.658.813m-15.482 0A50.55 50.55 0 0 1 12 13.489a50.55 50.55 0 0 1 10.499-3.342" />
                                            </svg>
                                        </div>
                                        <span class="text-sm">Capacitación</span>
                                    </button>
                                </li>
                            </ul>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div @click="$inertia.get(route('notifications'))"
                                :class="route().current('notifications') ? 'bg-[#c8c8c8] text-primary' : 'text-[#999999]'"
                                class="relative flex flex-col justify-center text-center px-3 rounded-[10px] my-2 py-1">
                                <p v-if="route().current('notifications')"
                                    class="text-center text-[13px] absolute -top-6 -left-2">Notifica...</p>
                                <div v-if="route().current('notifications')"
                                    class="-z-10 absolute -top-7 -left-9 w-24 h-24 bg-grayED rounded-full"></div>
                                <i class="fa-solid fa-bell text-xl"></i>
                                <span v-if="getUnreadNotifications"
                                    class="size-4 rounded-full bg-primary text-white text-[10px] absolute top-0 right-0">{{
                                        getUnreadNotifications }}</span>
                            </div>
                            <button @click="showProfileCard = !showProfileCard"
                                :class="{ 'border-primary': route().current('profile.*') }"
                                class="relative size-8 flex justify-center items-center text-sm border border-[#999999] rounded-full focus:outline-none focus:border-primary transition">
                                <img class="size-9 p-1 rounded-full object-cover"
                                    :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                            </button>
                        </div>
                        <ProfileCard @close="showProfileCard = false" v-if="showProfileCard"
                            class="bottom-12 right-2" />
                    </div>
                </nav>
            </main>
        </div>
    </div>
</template>