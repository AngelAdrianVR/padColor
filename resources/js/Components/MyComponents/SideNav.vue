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
                            :class="menu.active ? 'bg-gray-800 text-primary' : 'hover:text-primary bg-gray-800 text-[#999999]'">
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
                                    class="w-full text-start pl-6 pr-2 mb-1 flex justify-between text-xs rounded-md py-1 transition ease-linear duration-150"
                                    :class="option.active ? 'bg-gray-800 text-primary' : 'hover:text-primary bg-gray-800 text-[#999999]'">
                                    <p class="w-full truncate"> {{ option.label }}</p>
                                </button>
                            </div>
                        </Accordion>
                        <button v-else-if="menu.show" @click="goToRoute(menu.route)" :active="menu.active" :title="menu.label"
                            class="w-full text-start px-2 mb-1 flex justify-between text-xs rounded-md py-1 transition ease-linear duration-150"
                            :class="menu.active ? 'bg-gray-800 text-primary' : 'hover:text-primary hover:bg-gray-800 text-[#999999]'">
                            <p class="w-full text-sm truncate"><span class="mr-2" v-html="menu.icon"></span> {{ menu.label }}</p>
                        </button>
                    </div>
                </template>
            </nav>
        </div>
    </div>
</template>

<script>
import Accordion from './Accordion.vue';
import { Link } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';

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
                // {
                //     label: 'Gestion de pagos',
                //     icon: '<i class="fa-solid fa-dollar-sign mr-2"></i>',
                //     route: route('payments.index'),
                //     active: route().current('payments.*'),
                //     options: [],
                //     dropdown: false,
                //     show: true
                // },
                // {
                //     label: 'Visitas',
                //     icon: '<i class="fa-solid fa-users text-sm mr-2"></i>',
                //     route: route('guests.index'),
                //     active: route().current('guests.*'),
                //     options: [],
                //     dropdown: false,
                //     show: true
                // },
                // {
                //     label: 'Reservación de áreas',
                //     icon: '<i class="fa-solid fa-leaf text-sm mr-2"></i>',
                //     route: route('common-areas-users.index'),
                //     active: route().current('common-areas-users.*'),
                //     options: [],
                //     dropdown: false,
                //     show: true
                // },
                // {
                //     label: 'Mantenimiento',
                //     icon: '<i class="fa-solid fa-screwdriver-wrench text-sm mr-2"></i>',
                //     route: route('maintenances.index'),
                //     active: route().current('maintenances.*'),
                //     options: [],
                //     dropdown: false,
                //     show: true
                // },
                // {
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
                // {
                //     label: 'Normativas',
                //     icon: '<i class="fa-solid fa-sheet-plastic text-sm mr-2"></i>',
                //     route: route('norms.index'),
                //     active: route().current('norms.*'),
                //     options: [],
                //     dropdown: false,
                //     show: true
                // },
                // {
                //     label: 'Servicios',
                //     icon: '<i class="fa-solid fa-briefcase text-sm mr-2"></i>',
                //     route: route('services.index'),
                //     active: route().current('services.*'),
                //     options: [],
                //     dropdown: false,
                //     show: true
                // },
                // {
                //     label: 'Soporte técnico',
                //     icon: '<i class="fa-solid fa-headset text-sm mr-2"></i>',
                //     route: route('supports.create'), //si es residente manda a create, si es administrador manda a index
                //     active: route().current('supports.*'),
                //     options: [],
                //     dropdown: false,
                //     show: true
                // },
                
            ],
        }
    },
    components: {
        ApplicationMark,
        Accordion,
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
        }
    },
    mounted() {
    }
}
</script>