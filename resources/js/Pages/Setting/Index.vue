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
    },
    props: {
        roles: Object,
        permissions: Object,
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
    