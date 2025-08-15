<template>
    <div>
        <AppLayout title="Gestión de producción">
            <main class="px-2 lg:px-14">
                <el-tabs v-model="activeTab" @tab-click="handleClick">
                    <el-tab-pane name="1">
                        <template #label>
                            <div class="flex items-center">
                                <span>Home</span>
                            </div>
                        </template>
                        <Home :productions="productions"/>
                    </el-tab-pane>
                    <el-tab-pane name="2">
                        <template #label>
                            <div class="flex items-center">
                                <span>Gestión de producción</span>
                            </div>
                        </template>
                        <Productions ref="productions"/>
                    </el-tab-pane>
                    <el-tab-pane name="3">
                        <template #label>
                            <div class="flex items-center">
                                <span>Nueva producción</span>
                            </div>
                        </template>
                        <Create :nextProduction="next_production" @created="refreshProductionsList" />
                    </el-tab-pane>
                    <el-tab-pane name="4">
                        <template #label>
                            <div class="flex items-center">
                                <span>Máquinas</span>
                            </div>
                        </template>
                        <Machines />
                    </el-tab-pane>
                </el-tabs>
            </main>
        </AppLayout>
    </div>
</template>
    
<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import Create from './Tabs/Create.vue';
import Productions from './Tabs/Productions.vue';
import Home from './Tabs/Home.vue';
import Machines from './Tabs/Machines.vue';

export default {
    data() {
        return {
            activeTab: '1',
        }
    },
    components: {
        AppLayout,
        Home,
        Productions,
        Create,
        Machines,
    },
    props: {
        productions: Array,
        machines: Object,
        next_production: Number,
    },
    methods: {
        handleClick(tab) {
            // Agrega la variable currentTab=tab.props.name a la URL para mejorar la navegacion al actalizar o cambiar de pagina
            const currentURL = new URL(window.location.href);
            currentURL.searchParams.set('currentTab', tab.props.name);
            // Actualiza la URL
            window.history.replaceState({}, document.title, currentURL.href);
        },
        refreshProductionsList() {
            // Llama al método fetchProductions del componente Productions
            this.$refs.productions.fetchProductions();
        },
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
    