<template>
    <!-- sidebar -->
    <div class="h-screen hidden md:block shadow-lg relative">
        <i @click="small = false" v-if="small" class="fa-solid fa-angle-right text-center text-xs pt-[2px] text-white rounded-full size-5 bg-primary absolute top-24 -right-3 cursor-pointer hover:scale-125 transition-transform ease-linear duration-150"></i>
        <i @click="small = true" v-else class="fa-solid fa-angle-left text-center text-xs pt-[2px] text-white rounded-full size-5 bg-primary absolute top-24 -right-3 cursor-pointer hover:scale-125 transition-transform ease-linear duration-150"></i>
        <div class="bg-black1 h-full overflow-auto px-1">
            <!-- Logo -->
            <div class="flex items-center justify-center mt-7">
                <Link v-if="small" :href="route('dashboard')">
                    <ApplicationMark />
                </Link>
                <Link v-else :href="route('dashboard')">
                    <figure class="">
                        <img class="w-20" src="@/../../public/images/authLogo.png" alt="logo">
                    </figure>
                </Link>
            </div>
            <nav class="px-2 pt-20 text-white">
                <template v-if="small">
                    <div v-for="(menu, index) in menus" :key="index">
                        <button v-if="menu.show" @click="goToRoute(menu.route)" :active="menu.active" :title="menu.label"
                            class="w-full text-center py-2 px-3 justify-between rounded-[10px] mt-2 transition ease-linear duration-150"
                            :class="menu.active ? 'bg-gray-800 text-primary' : 'hover:text-primary hover:bg-gray-800 text-[#999999]'">
                            <span v-html="menu.icon"></span>
                        </button>
                    </div>
                </template>
                <template v-else v-for="(menu, index) in menus" :key="index">
                    <div v-if="menu.show">
                        <Accordion v-if="menu.options.length" :icon="menu.icon" :active="menu.active" :title="menu.label"
                            :id="index">
                            <div v-for="(option, index2) in menu.options" :key="index2">
                                <button @click="goToRoute(option.route)" v-if="option.show" :active="option.active"
                                    :title="option.label"
                                    class="w-full text-start pl-6 pr-2 mt-2 flex justify-between text-xs rounded-md py-1 transition ease-linear duration-150"
                                    :class="option.active ? 'bg-gray-800 text-primary' : 'hover:text-primary hover:bg-gray-800 text-[#999999]'">
                                    <p class="w-full truncate"> {{ option.label }}</p>
                                </button>
                            </div>
                        </Accordion>
                        <button v-else-if="menu.show" @click="goToRoute(menu.route)" :active="menu.active" :title="menu.label"
                            class="w-full text-start px-2 mt-2 flex justify-between text-xs rounded-md py-1 transition ease-linear duration-150"
                            :class="menu.active ? 'bg-gray-800 text-primary' : 'hover:text-primary hover:bg-gray-800 text-[#999999]'">
                            <p class="w-full text-sm truncate"><span class="mr-2" v-html="menu.icon"></span> {{ menu.label }}</p>
                        </button>
                    </div>
                </template>

                <!-- Avatar de usuario -->
                <div class="mt-24 text-center">
                    <Dropdown align="left" width="48">
                        <template #trigger>
                            <button v-if="$page.props.jetstream.managesProfilePhotos" class="p-2 flex justify-center items-center space-x-2 text-sm border border-[#999999] rounded-full focus:outline-none focus:border-primary transition">
                                <img class="size-9 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                                <p v-if="!small" class="text-sm w-32">{{ $page.props.auth.user.name }}</p>
                                <i v-if="!small" class="fa-solid fa-angle-right text-center text-sm text-[#999999]"></i>
                            </button>

                            <span v-else class="inline-flex rounded-md">
                                <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-[#999999] hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150">
                                    {{ $page.props.auth.user.name }}

                                    <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </button>
                            </span>
                        </template>
                        <template #content>
                            <!-- Account Management -->
                            <div class="block px-4 py-2 text-xs text-gray-400">
                                Opciones de usuario
                            </div>

                            <DropdownLink :href="route('profile.show')">
                                Perfil
                            </DropdownLink>

                            <div class="border-t border-gray-200" />

                            <!-- Authentication -->
                            <form method="POST" @submit.prevent="logout" class="text-red-500 text-sm text-center px-2">
                                <button>
                                <i class="fa-solid fa-arrow-right-from-bracket mr-[7px]"></i> Cerrar sesión
                                </button>
                            </form>
                        </template>
                    </Dropdown>
                </div>
            </nav>
        </div>
    </div>
</template>

<script>
import Accordion from './Accordion.vue';
import { Link } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

export default {
    data() {
        return {
            small: true,
            collapsedMenu: null,
            menus: [
                {
                    label: 'Inicio',
                    icon: '<i class="fa-solid fa-house text-lg"></i>',
                    route: route('dashboard'),
                    active: route().current('dashboard'),
                    options: [],
                    dropdown: false,
                    show: true
                },
                {
                    label: 'Tickets',
                    icon: '<i class="fa-regular fa-square-check text-lg"></i>',
                    route: route('tickets.index'),
                    active: route().current('tickets.*'),
                    options: [],
                    dropdown: false,
                    show: true
                },
                {
                    label: 'Usuarios',
                    icon: '<i class="fa-regular fa-user text-lg"></i>',
                    route: route('users.index'),
                    active: route().current('users.*'),
                    options: [],
                    dropdown: false,
                    show: true
                },
                {
                    label: 'Configuraciones',
                    icon: '<i class="fa-solid fa-gears text-lg"></i>',
                    route: route('settings.index'),
                    active: route().current('settings.*'),
                    options: [],
                    dropdown: false,
                    show: true
                },
               
                //     label: 'Comunidad',
                //     icon: '<i class="fa-solid fa-people-roof text-sm mr-2"></i>',
                //     // route: route('posts.index'),
                //     active: route().current('posts.*') || route().current('community-events.*')|| route().current('neighbors.*'),
                //     options: [
                //         {
                //             label: 'Muro de noticias',
                //             route: route('posts.index'),
                //             show: true,
                //         },
                //         {
                //             label: 'Eventos',
                //             route: route('community-events.index'),
                //             show: true,
                //         },
                //         {
                //             label: 'Directorio de vecinos',
                //             route: route('neighbors.index'),
                //             show: true,
                //         },
                //     ],
                //     dropdown: true,
                //     show: true
                // },
            ],
        }
    },
    components: {
        ApplicationMark,
        Accordion,
        DropdownLink,
        Dropdown,
        Link
    },
    methods: {
        handleClickInMenu(index) {
            if (this.menus[index].options.length) {
                if (this.collapsedMenu === index) {
                    this.collapsedMenu = null;
                } else {
                    this.collapsedMenu = index;
                }
            } else {
                this.goToRoute(this.menus[index].route)
            }
            },
            goToRoute(route) {
                this.$inertia.get(route);
            },
            logout() {
                this.$inertia.post(route('logout'));
            }
    },
    mounted() {
    }
}
</script>