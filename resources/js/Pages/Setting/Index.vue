<template>
    <div>
        <AppLayout title="Configuraciones">
            <header class="mb-6 mt-4 mx-2 lg:mx-9">
                <h1 class="text-lg">Configuraciones</h1>
            </header>
            <main class="px-2 lg:px-14">
                <el-tabs v-model="activeTab" @tab-click="handleClick">
                    <el-tab-pane name="1">
                        <template #label>
                            <div class="flex items-center">
                                <i class="fa-solid fa-user-tag mr-1"></i>
                                <span>Roles</span>
                            </div>
                        </template>
                        <Roles :roles="roles" :permissions="permissions" />
                    </el-tab-pane>
                    <el-tab-pane name="2">
                        <template #label>
                            <div class="flex items-center">
                                <i class="fa-solid fa-user-gear mr-1"></i>
                                <span>Permisos</span>
                            </div>
                        </template>
                        <Permissions :permissions="permissions" />
                    </el-tab-pane>
                    <el-tab-pane name="3">
                        <template #label>
                            <div class="flex items-center">
                                <i class="fa-solid fa-list mr-1"></i>
                                <span>Categorías</span>
                            </div>
                        </template>
                        <Categories />
                    </el-tab-pane>
                    <el-tab-pane name="4">
                        <template #label>
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-5 mr-1">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0M3.124 7.5A8.969 8.969 0 0 1 5.292 3m13.416 0a8.969 8.969 0 0 1 2.168 4.5" />
                                </svg>
                                <span>Notificaciones</span>
                            </div>
                        </template>
                        <NotificationManager :notificationEvents="notificationEvents" :users="users"
                            :initialSubscriptions="initialSubscriptions" :filters="filters" />
                    </el-tab-pane>
                </el-tabs>
            </main>
        </AppLayout>
    </div>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import Roles from './Tabs/Roles.vue';
import Permissions from './Tabs/Permissions.vue';
import Categories from './Tabs/Categories.vue';
import NotificationManager from './Tabs/NotificationManager.vue';

export default {
    data() {
        return {
            activeTab: '1',
        }
    },
    components: {
        AppLayout,
        Roles,
        Permissions,
        Categories,
        NotificationManager,
    },
    props: {
        roles: Object,
        permissions: Object,
        notificationEvents: {
            type: Array,
            default: []
        },
        users: {
            type: Object,
            default: {}
        },
        initialSubscriptions: {
            type: Object,
            default: () => ({
                users: [],
                external: []
            })
        },
        filters: {
            type: Object,
            default: () => ({ search: '', department: '', company: '' })
        }
    },
    methods: {
        handleClick(tab) {
            // Agrega la variable currentTab=tab.props.name a la URL para mejorar la navegacion al actalizar o cambiar de pagina
            const currentURL = new URL(window.location.href);
            currentURL.searchParams.set('currentTab', tab.props.name);
            // Actualiza la URL
            window.history.replaceState({}, document.title, currentURL.href);
        }
    },
    mounted() {
        // Obtener la URL actual
        const currentURL = new URL(window.location.href);
        // Extraer el valor de 'currentTab' de los parámetros de búsqueda
        const currentTabFromURL = currentURL.searchParams.get('currentTab');

        if (currentTabFromURL) {
            this.activeTab = currentTabFromURL;
        }

    },
}
</script>