<script setup>
import { ref, onMounted, computed, onBeforeUnmount } from 'vue';
// import Echo from 'laravel-echo';
import { Head, Link, router } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import SideNav from '@/Components/MyComponents/SideNav.vue';
import ProfileCard from '@/Components/MyComponents/ProfileCard.vue';
import NotificationsCenter from '@/Components/MyComponents/NotificationsCenter.vue';
import axios from 'axios';

defineProps({
    title: String,
});

const showingNavigationDropdown = ref(false);
const notifications = ref([]);
const showProfileCard = ref(false);

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
            <aside class="col-span-2">
                <SideNav />
            </aside>

            <!-- resto de pagina -->
            <main class="w-full">
                <nav class="lg:bg-white bg-grayED border-b border-gray-100">
                    <!-- Primary Navigation Menu -->
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="flex justify-between h-16">
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

                            <div class="hidden sm:flex sm:items-center sm:ms-6">
                                <!-- notifications -->
                                <NotificationsCenter class="hidden lg:block"/>
                            </div>
                        </div>
                    </div>
                </nav>


                <div class="overflow-y-auto h-[calc(100vh-9.1rem)] lg:h-[calc(100vh-4.1rem)] bg-white">
                    <slot />
                </div>

                <!-- ---------------- footer nave mobile view --------------- -->
                <nav class="lg:hidden fixed bottom-0 w-full z-10">
                    <div class="w-full h-16 flex justify-center items-center px-1 bg-grayED space-x-5">
                        <div @click="$inertia.get(route('dashboard'))"
                            :class="route().current('dashboard') ? 'bg-[#c8c8c8] text-primary' : 'text-[#999999]'"
                            class="relative flex flex-col justify-center text-center px-3 rounded-[10px] my-2 py-1">
                            <p v-if="route().current('dashboard')" class="text-[13px] absolute -top-6 left-1">Inicio</p>
                            <div v-if="route().current('dashboard')"
                                class="-z-10 absolute -top-7 -left-[34px] w-28 h-28 bg-grayED rounded-full"></div>
                            <i class="fa-solid fa-house text-xl"></i>
                        </div>
                        <div @click="$inertia.get(route('tickets.index'))"
                            :class="route().current('tickets.*') ? 'bg-[#c8c8c8] text-primary' : 'text-[#999999]'"
                            class="relative flex flex-col justify-center text-center px-3 rounded-[10px] my-2 py-1">
                            <p v-if="route().current('tickets.*')" class="text-[13px] absolute -top-6 left-0">Tickets</p>
                            <div v-if="route().current('tickets.*')"
                                class="-z-10 absolute -top-7 -left-[34px] w-28 h-28 bg-grayED rounded-full"></div>
                            <i class="fa-regular fa-square-check text-xl"></i>
                        </div>
                        <div @click="$inertia.get(route('users.index'))"
                            :class="route().current('users.*') ? 'bg-[#c8c8c8] text-primary' : 'text-[#999999]'"
                            class="relative flex flex-col justify-center text-center px-3 rounded-[10px] my-2 py-1">
                            <p v-if="route().current('users.*')" class="text-[13px] absolute -top-6 -left-2">Usuarios</p>
                            <div v-if="route().current('users.*')"
                                class="-z-10 absolute -top-7 -left-9 w-28 h-28 bg-grayED rounded-full"></div>
                            <i class="fa-regular fa-user text-xl"></i>
                        </div>
                        <div @click="$inertia.get(route('settings.index'))"
                            :class="route().current('settings.*') ? 'bg-[#c8c8c8] text-primary' : 'text-[#999999]'"
                            class="relative flex flex-col justify-center text-center px-3 rounded-[10px] my-2 py-1">
                            <p v-if="route().current('settings.*')" class="text-[13px] absolute -top-6 -left-2">Config...
                            </p>
                            <div v-if="route().current('settings.*')"
                                class="-z-10 absolute -top-7 -left-9 w-28 h-28 bg-grayED rounded-full"></div>
                            <i class="fa-solid fa-gears text-xl"></i>
                        </div>
                        <div @click="$inertia.get(route('notifications'))"
                            :class="route().current('notifications') ? 'bg-[#c8c8c8] text-primary' : 'text-[#999999]'"
                            class="relative flex flex-col justify-center text-center px-3 rounded-[10px] my-2 py-1">
                            <p v-if="route().current('notifications')"
                                class="text-center text-[13px] absolute -top-6 -left-2">Notifica...</p>
                            <div v-if="route().current('notifications')"
                                class="-z-10 absolute -top-7 -left-9 w-28 h-28 bg-grayED rounded-full"></div>
                            <i class="fa-solid fa-bell text-xl"></i>
                            <span v-if="getUnreadNotifications"
                                class="size-4 rounded-full bg-primary text-white text-[10px] absolute top-0 right-0">{{
                                    getUnreadNotifications }}</span>
                        </div>
                        <button @click="showProfileCard = !showProfileCard"
                            :class="{ 'border-primary': route().current('profile.*') }"
                            class="relative size-10 flex justify-center items-center text-sm border border-[#999999] rounded-full focus:outline-none focus:border-primary transition">
                            <img class="size-9 p-1 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url"
                                :alt="$page.props.auth.user.name">
                        </button>
                        <ProfileCard @close="showProfileCard = false" v-if="showProfileCard" />
                    </div>
                </nav>
            </main>
        </div>
    </div>
</template>
