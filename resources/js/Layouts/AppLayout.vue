<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import SideNav from '@/Components/MyComponents/SideNav.vue';
import NotificationsCenter from '@/Components/MyComponents/NotificationsCenter.vue';
import axios from 'axios';

defineProps({
    title: String,
});

const showingNavigationDropdown = ref(false);

const switchToTeam = (team) => {
    router.put(route('current-team.update'), {
        team_id: team.id,
    }, {
        preserveState: false,
    });
};

</script>

<template>
    <div>
        <Head :title="title" />

        <Banner />

        <div class="overflow-hidden h-screen md:flex bg-white">
            <!-- sidenav -->
            <aside class="col-span-2">
                <SideNav />
            </aside>

            <!-- resto de pagina -->
            <main class="w-full">
                <nav class="lg:bg-white bg-[#131313] border-b border-gray-100">
                    <!-- Primary Navigation Menu -->
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="flex justify-between h-16">
                            <div class="flex">
                                <!-- Logo -->
                                <div class="shrink-0 flex items-center md:hidden">
                                    <Link :href="route('dashboard')">
                                        <figure class="">
                                            <img class="w-32" src="@/../../public/images/authLogo_horizontal.png" alt="logo">
                                        </figure>
                                    </Link>
                                </div>
                            </div>

                            <div class="hidden sm:flex sm:items-center sm:ms-6">
                                <div class="ms-3 relative">
                                    <!-- Teams Dropdown -->
                                    <Dropdown v-if="$page.props.jetstream.hasTeamFeatures" align="right" width="60">
                                        <template #trigger>
                                            <span class="inline-flex rounded-md">
                                                <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150">
                                                    {{ $page.props.auth.user.current_team.name }}

                                                    <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                                                    </svg>
                                                </button>
                                            </span>
                                        </template>

                                        <template #content>
                                            <div class="w-60">
                                                <!-- Team Management -->
                                                <div class="block px-4 py-2 text-xs text-gray-400">
                                                    Manage Team
                                                </div>

                                                <!-- Team Settings -->
                                                <DropdownLink :href="route('teams.show', $page.props.auth.user.current_team)">
                                                    Team Settings
                                                </DropdownLink>

                                                <DropdownLink v-if="$page.props.jetstream.canCreateTeams" :href="route('teams.create')">
                                                    Create New Team
                                                </DropdownLink>

                                                <!-- Team Switcher -->
                                                <template v-if="$page.props.auth.user.all_teams.length > 1">
                                                    <div class="border-t border-gray-200" />

                                                    <div class="block px-4 py-2 text-xs text-gray-400">
                                                        Switch Teams
                                                    </div>

                                                    <template v-for="team in $page.props.auth.user.all_teams" :key="team.id">
                                                        <form @submit.prevent="switchToTeam(team)">
                                                            <DropdownLink as="button">
                                                                <div class="flex items-center">
                                                                    <svg v-if="team.id == $page.props.auth.user.current_team_id" class="me-2 h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                    </svg>

                                                                    <div>{{ team.name }}</div>
                                                                </div>
                                                            </DropdownLink>
                                                        </form>
                                                    </template>
                                                </template>
                                            </div>
                                        </template>
                                    </Dropdown>
                                </div>

                                <!-- notifications -->
                                <NotificationsCenter />
                            </div>
                        </div>
                    </div>
                </nav>

            
                <div class="overflow-y-auto h-[calc(100vh-9.1rem)] bg-white">
                    <slot />
                </div>

                 <!-- ---------------- footer nave mobile view --------------- -->
                <nav class="lg:hidden fixed bottom-0 w-full z-10">
                    <div class="w-full h-16 flex justify-center items-center px-1 bg-[#131313] space-x-5">
                        <div @click="$inertia.get(route('dashboard'))" :class="route().current('dashboard') ? 'bg-gray-800 text-primary' : 'text-[#999999]'" class="relative flex flex-col justify-center text-center px-3 rounded-[10px] my-2 py-1">
                            <p v-if="route().current('dashboard')" class="text-[13px] absolute -top-6 left-1">Inicio</p>
                            <div v-if="route().current('dashboard')" class="-z-10 absolute -top-7 -left-[34px] w-28 h-28 bg-[#131313] rounded-full"></div>
                            <i class="fa-solid fa-house text-xl"></i>
                        </div>
                        <div @click="$inertia.get(route('tickets.index'))" :class="route().current('tickets.*') ? 'bg-gray-800 text-primary' : 'text-[#999999]'" class="relative flex flex-col justify-center text-center px-3 rounded-[10px] my-2 py-1">
                            <p v-if="route().current('tickets.*')" class="text-[13px] absolute -top-6 left-0">Tickets</p>
                            <div v-if="route().current('tickets.*')" class="-z-10 absolute -top-7 -left-[34px] w-28 h-28 bg-[#131313] rounded-full"></div>
                            <i class="fa-regular fa-square-check text-xl"></i>
                        </div>
                        <div @click="$inertia.get(route('users.index'))" :class="route().current('users.*') ? 'bg-gray-800 text-primary' : 'text-[#999999]'" class="relative flex flex-col justify-center text-center px-3 rounded-[10px] my-2 py-1">
                            <p v-if="route().current('users.*')" class="text-[13px] absolute -top-6 -left-2">Usuarios</p>
                            <div v-if="route().current('users.*')" class="-z-10 absolute -top-7 -left-9 w-28 h-28 bg-[#131313] rounded-full"></div>
                            <i class="fa-regular fa-user text-xl"></i>
                        </div>
                        <div @click="$inertia.get(route('settings.index'))" :class="route().current('settings.*') ? 'bg-gray-800 text-primary' : 'text-[#999999]'" class="relative flex flex-col justify-center text-center px-3 rounded-[10px] my-2 py-1">
                            <p v-if="route().current('settings.*')" class="text-[13px] absolute -top-6 -left-2">Config</p>
                            <div v-if="route().current('settings.*')" class="-z-10 absolute -top-7 -left-9 w-28 h-28 bg-[#131313] rounded-full"></div>
                            <i class="fa-solid fa-gears text-xl"></i>
                        </div>
                        <button @click="$inertia.get(route('profile.show'))" :class="{'border-primary': route().current('profile.*')}" class="relative size-10 flex justify-center items-center text-sm border border-[#999999] rounded-full focus:outline-none focus:border-primary transition">
                            <img class="size-9 p-1 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                            <!-- <span class="animate-ping absolute -left-0 inline-flex size-8 rounded-full bg-primarylight opacity-50"></span> -->
                        </button>
                    </div>  
                </nav>
            </main>
        </div>
    </div>
</template>
