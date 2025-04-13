<template>
    <!-- sidebar -->
    <div class="h-screen hidden lg:block shadow-lg relative">
        <i @click="updateSideNavSize(false)" v-if="small"
            class="fa-solid fa-angle-right text-center text-xs pt-[2px] text-white rounded-full size-5 bg-primary absolute top-24 -right-3 cursor-pointer hover:scale-125 transition-transform ease-linear duration-150"></i>
        <i @click="updateSideNavSize(true)" v-else
            class="fa-solid fa-angle-left text-center text-xs pt-[2px] text-white rounded-full size-5 bg-primary absolute top-24 -right-3 cursor-pointer hover:scale-125 transition-transform ease-linear duration-150"></i>
        <div class="bg-grayED h-full overflow-auto px-1">
            <!-- Logo -->
            <div class="flex items-center justify-center mt-7">
                <Link v-if="small" :href="route('tickets.index')">
                <ApplicationMark />
                </Link>
                <Link v-else :href="route('tickets.index')">
                <figure class="">
                    <img class="w-32" src="@/../../public/images/logo_name.png" alt="logo">
                </figure>
                </Link>
            </div>
            <nav class="px-2 pt-20 flex flex-col justify-between h-[calc(100vh-5rem)]">
                <section>
                    <template v-if="small">
                        <div v-for="(menu, index) in menus" :key="index">
                            <button v-if="menu.show" @click="goToRoute(menu.route)" :active="menu.active" :title="menu.label"
                                class="w-full text-center py-2 justify-between rounded-[10px] mt-2 transition ease-linear duration-150"
                                :class="menu.active ? 'bg-[#c8c8c8] text-primary' : 'hover:text-primary hover:bg-[#c8c8c8] text-gray66'">
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
                                        :class="option.active ? 'bg-[#c8c8c8] text-primary' : 'hover:text-primary hover:bg-[#c8c8c8] text-gray66'">
                                        <p class="w-full truncate"> {{ option.label }}</p>
                                    </button>
                                </div>
                            </Accordion>
                            <button v-else-if="menu.show" @click="goToRoute(menu.route)" :active="menu.active"
                                :title="menu.label"
                                class="w-full text-start px-2 mt-2 flex justify-between text-xs rounded-md py-1 transition ease-linear duration-150"
                                :class="menu.active ? 'bg-[#c8c8c8] text-primary' : 'hover:text-primary hover:bg-[#c8c8c8] text-gray66'">
                                <p class="w-full text-sm truncate"><span class="mr-2" v-html="menu.icon"></span> {{ menu.label
                                }}</p>
                            </button>
                        </div>
                    </template>
                </section>

                <!-- Avatar de usuario -->
                <div class="mt-24 text-center">
                    <button v-if="$page.props.jetstream.managesProfilePhotos" @click="showProfileCard = !showProfileCard"
                        class="flex items-center space-x-2 text-sm border rounded-full focus:outline-none transition"
                        :class="{ 'border-primary': showProfileCard, 'border-[#999999]': !showProfileCard, 'size-12 justify-center': small, 'h-12 w-full px-2 justify-between': !small }">
                        <div class="flex items-center">
                            <img class="size-9 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url"
                                :alt="$page.props.auth.user.name">
                            <p v-if="!small" class="text-[11px] text-gray99 leading-snug text-start mt-1 ml-2">{{ $page.props.auth.user.name }}</p>
                        </div>
                        <i v-if="!small" class="fa-solid fa-angle-right text-center text-xs text-[#999999]"></i>
                    </button>
                    <ProfileCard v-if="showProfileCard" @close="showProfileCard = false" class="bottom-3 left-[calc(100%+0.75rem)]" />
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
import ProfileCard from './ProfileCard.vue';

export default {
    data() {
        return {
            small: true,
            showProfileCard: false,
            collapsedMenu: null,
            menus: [
                {
                    label: 'Inicio',
                    icon: '<i class="fa-solid fa-house text-lg"></i>',
                    route: route('dashboard'),
                    active: route().current('dashboard'),
                    options: [],
                    dropdown: false,
                    show: this.$page.props.auth.user.permissions.includes('Ver dashboard'),
                },
                {
                    label: 'Tickets',
                    icon: '<i class="fa-regular fa-square-check text-lg"></i>',
                    route: route('tickets.index'),
                    active: route().current('tickets.*'),
                    options: [],
                    dropdown: false,
                    show: true,
                },
                {
                    label: 'Usuarios',
                    icon: '<i class="fa-regular fa-user text-lg"></i>',
                    route: route('users.index'),
                    active: route().current('users.*'),
                    options: [],
                    dropdown: false,
                    show: this.$page.props.auth.user.permissions.includes('Ver usuarios'),
                },
                {
                    label: 'Configuraciones',
                    icon: '<i class="fa-solid fa-gears text-lg"></i>',
                    route: route('settings.index'),
                    active: route().current('settings.*'),
                    options: [],
                    dropdown: false,
                    show: this.$page.props.auth.user.permissions.includes('Ver configuraciones'),
                },
                {
                    label: 'SwAssistant',
                    icon: '<i class="fa-solid fa-gears text-lg"></i>',
                    route: route('productions.index'),
                    active: route().current('productions.*'),
                    options: [],
                    dropdown: false,
                    show: this.$page.props.auth.user.permissions.includes('Ver producción'),
                },
                {
                    label: 'Productos',
                    icon: '<i class="fa-solid fa-boxes-stacked"></i>',
                    route: route('products.index'),
                    active: route().current('products.*'),
                    options: [],
                    dropdown: false,
                    show: true,
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
        Link,
        ProfileCard,
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
        },
        updateSideNavSize(is_small){
            this.small = is_small;
            localStorage.setItem('is_sidenav_small', is_small);
        }
    },
    mounted() {
        const is_small = localStorage.getItem('is_sidenav_small');
        if (is_small !== null) {
            this.small = JSON.parse(is_small); // Convertirlo a booleano si es necesario
        }
    }
}
</script>