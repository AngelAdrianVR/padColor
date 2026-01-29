<template>
    <!-- Contenedor Principal -->
    <div class="h-screen bg-white border-r border-gray-200 flex flex-col transition-all duration-300 relative group"
         :class="small ? 'w-[64px]' : 'w-64'">
        
        <!-- Botón de Colapso (Visible al hacer hover en sidebar o siempre en móvil si se desea) -->
        <div class="absolute -right-3 top-16 z-50 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
            <button @click="updateSideNavSize(!small)" 
                class="bg-white border border-gray-200 text-gray-500 hover:text-primary hover:border-primary rounded-full p-1 shadow-sm transition-all flex items-center justify-center w-6 h-6">
                <i :class="small ? 'fa-solid fa-angle-right' : 'fa-solid fa-angle-left'" class="text-xs"></i>
            </button>
        </div>

        <!-- Logo -->
        <div class="h-16 flex items-center justify-center border-b border-gray-100 shrink-0">
            <Link :href="route('dashboard')" class="flex items-center justify-center w-full px-2">
                <ApplicationMark v-if="small" class="w-8 h-8" />
                <img v-else src="@/../../public/images/logo_name.png" alt="Logo" class="h-8 object-contain transition-all duration-300" />
            </Link>
        </div>

        <!-- Menú Scrollable -->
        <el-scrollbar class="flex-1">
            <el-menu
                :default-active="activeMenu"
                class="border-none !bg-transparent sidebar-menu"
                :collapse="small"
                :collapse-transition="false"
                text-color="#606266"
                active-text-color="#409EFF"
                unique-opened
            >
                <template v-for="(menu, index) in menus" :key="index">
                    <!-- Opción con Submenú -->
                    <el-sub-menu v-if="menu.show && menu.options.length > 0" :index="index.toString()">
                        <template #title>
                            <!-- Icono (Renderizado desde HTML string) -->
                            <div class="flex items-center justify-center text-lg mr-2" :class="small ? 'w-[8px]' : 'w-[24px]'" v-html="menu.icon"></div>
                            <span class="font-medium text-sm">{{ menu.label }}</span>
                        </template>
                        
                        <el-menu-item v-for="(option, optIndex) in menu.options" 
                            :key="optIndex" 
                            :index="option.route"
                            @click="goToRoute(option.route)"
                            v-show="option.show"
                            class="!pl-12"
                        >
                            <span>{{ option.label }}</span>
                        </el-menu-item>
                    </el-sub-menu>

                    <!-- Opción Simple -->
                    <el-menu-item v-else-if="menu.show" :index="menu.route" @click="goToRoute(menu.route)">
                        <div class="flex items-center justify-center text-lg mr-2" :class="small ? 'w-[8px]' : 'w-[24px]'" v-html="menu.icon"></div>
                        <template #title>
                            <span class="font-medium text-sm">{{ menu.label }}</span>
                        </template>
                    </el-menu-item>
                </template>

                <!-- Separador Visual -->
                <div class="my-2 border-t border-gray-100 mx-4" v-if="!small"></div>

                <!-- Botón Capacitación -->
                <el-menu-item index="capacitacion" @click="openNewTab('https://capacitacionpadcolor.my.canva.site/capacitacionpadcolor')">
                    <div class="flex items-center justify-center w-[24px] mr-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 0 0-.491 6.347A48.627 48.627 0 0 1 12 20.904a48.627 48.627 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.905 59.905 0 0 1 12 3.493a59.902 59.902 0 0 1 10.499 5.258 50.55 50.55 0 0 0-2.658.813m-15.482 0A50.55 50.55 0 0 1 12 13.489a50.55 50.55 0 0 1 10.499-3.342" />
                        </svg>
                    </div>
                    <template #title>
                        <span class="font-medium text-sm">Capacitación</span>
                    </template>
                </el-menu-item>

            </el-menu>
        </el-scrollbar>

        <!-- Perfil de Usuario (Footer) -->
        <div class="border-t border-gray-100 p-2 bg-gray-50/50">
            <el-popover
                placement="right-end"
                :width="280"
                trigger="click"
                popper-class="!p-0 !border-none !shadow-xl !rounded-xl"
                transition="el-zoom-in-bottom"
            >
                <template #reference>
                    <div class="flex items-center gap-3 p-2 rounded-lg cursor-pointer hover:bg-white hover:shadow-sm transition-all duration-200 group/profile" 
                         :class="{'justify-center': small}">
                        <div class="relative">
                            <el-avatar :size="36" :src="$page.props.auth.user.profile_photo_url" class="border border-white shadow-sm" />
                            <div class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-500 border-2 border-white rounded-full"></div>
                        </div>
                        
                        <div v-if="!small" class="flex-1 min-w-0 flex flex-col">
                            <p class="text-sm font-semibold text-gray-700 truncate leading-tight group-hover/profile:text-primary transition-colors">
                                {{ $page.props.auth.user.name }}
                            </p>
                            <p class="text-[10px] text-gray-400 truncate mt-0.5">Ver perfil</p>
                        </div>
                        
                        <i v-if="!small" class="fa-solid fa-chevron-right text-gray-300 text-xs group-hover/profile:text-primary transition-colors"></i>
                    </div>
                </template>
                
                <!-- Componente ProfileCard importado -->
                <ProfileCard />
            </el-popover>
        </div>
    </div>
</template>

<script>
import { Link } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import ProfileCard from './ProfileCard.vue';

export default {
    components: {
        ApplicationMark,
        Link,
        ProfileCard,
    },
    data() {
        return {
            small: true,
            menus: [
                {
                    label: 'Inicio',
                    icon: '<i class="fa-solid fa-house"></i>',
                    route: route('dashboard'),
                    active: route().current('dashboard'),
                    options: [],
                    show: this.$page.props.auth.user.permissions.includes('Ver dashboard'),
                },
                {
                    label: 'Tickets',
                    icon: '<i class="fa-solid fa-ticket"></i>',
                    route: route('tickets.index'),
                    active: route().current('tickets.*'),
                    options: [],
                    show: true,
                },
                {
                    label: 'Usuarios',
                    icon: '<i class="fa-solid fa-users"></i>',
                    route: route('users.index'),
                    active: route().current('users.*'),
                    options: [],
                    show: this.$page.props.auth.user.permissions.includes('Ver usuarios'),
                },
                {
                    label: 'Clientes',
                    icon: '<i class="fa-solid fa-handshake"></i>',
                    route: route('users.index'), // Nota: Originalmente apuntaba a users.index
                    active: route().current('users.*'), // Ajustar según lógica original
                    options: [],
                    show: this.$page.props.auth.user.permissions.includes('Ver clientes'),
                },
                {
                    label: 'Importaciones',
                    icon: '<i class="fa-solid fa-ship"></i>',
                    route: route('imports.index'),
                    active: route().current('imports.*') || route().current('suppliers.*') || route().current('customs-agents.*'),
                    options: [
                        {
                            label: 'Kanban',
                            route: route('imports.index'),
                            show: true,
                            active: route().current('imports.*'),
                        },
                        {
                            label: 'Proveedores',
                            route: route('suppliers.index'),
                            show: true,
                            active: route().current('suppliers.*'),
                        },
                        {
                            label: 'Agentes aduanales',
                            route: route('customs-agents.index'),
                            show: true,
                            active: route().current('customs-agents.*'),
                        },
                    ],
                    show: this.$page.props.auth.user.permissions.includes('Ver importaciones'),
                },
                {
                    label: 'SwAssistant',
                    icon: '<i class="fa-solid fa-robot"></i>',
                    route: route('productions.index'),
                    active:route().current('productions.*') || route().current('machines.*'),
                    options: [
                        {
                            label: 'Reportes',
                            route: route('productions.report'),
                            show: true,
                            active: route().current('productions.report'),
                        },
                        {
                            label: 'Inicio',
                            route: route('productions.dashboard'),
                            show: true,
                            active: route().current('productions.dashboard'),
                        },
                        {
                            label: 'Gestión de producción',
                            route: route('productions.index'),
                            show: true,
                            active: route().current('productions.index'),
                        },
                        {
                            label: 'Máquinas',
                            route: route('machines.index'),
                            show: true,
                            active: route().current('machines.*'),
                        },
                    ],
                    show: this.$page.props.auth.user.permissions.includes('Ver producciones'),
                },
                {
                    label: 'Catálogo',
                    icon: '<i class="fa-solid fa-box-open"></i>',
                    route: route('products.index'),
                    active: route().current('products.*') || route().current('raw-materials.*'),
                    options: [
                        {
                            label: 'Productos',
                            route: route('products.index'),
                            show: true,
                            active: route().current('products.*'),
                        },
                        {
                            label: 'Materia prima',
                            route: route('raw-materials.index'),
                            show: true,
                            active: route().current('raw-materials.*'),
                        },
                    ],
                    show: this.$page.props.auth.user.permissions.includes('Ver productos'),
                },
                {
                    label: 'Configuraciones',
                    icon: '<i class="fa-solid fa-gear"></i>',
                    route: route('settings.index'),
                    active: route().current('settings.*'),
                    options: [],
                    show: this.$page.props.auth.user.permissions.includes('Ver configuraciones'),
                },
            ],
        }
    },
    computed: {
        // Calcula qué menú debe estar activo basado en la ruta actual
        activeMenu() {
            // Revisar subopciones primero
            for (const menu of this.menus) {
                if (menu.options && menu.options.length > 0) {
                    const activeOp = menu.options.find(op => op.active);
                    if (activeOp) return activeOp.route;
                }
            }
            // Revisar menús principales
            const activeMain = this.menus.find(m => m.active);
            return activeMain ? activeMain.route : '';
        }
    },
    methods: {
        openNewTab(url) {
            window.open(url, '_blank');
        },
        goToRoute(routeUrl) {
            this.$inertia.get(routeUrl);
        },
        updateSideNavSize(is_small) {
            this.small = is_small;
            localStorage.setItem('is_sidenav_small', is_small);
        }
    },
    mounted() {
        const is_small = localStorage.getItem('is_sidenav_small');
        if (is_small !== null) {
            this.small = JSON.parse(is_small);
        }
        
        // Optimización: He simplificado los iconos SVG complejos por clases de FontAwesome donde fue posible para limpiar el código, 
        // pero puedes volver a pegar los SVGs largos en la propiedad 'icon' si los prefieres.
    }
}
</script>

<style scoped>
/* Ajustes finos para Element Plus Menu */
:deep(.el-menu) {
    border-right: none;
}
:deep(.el-menu-item), :deep(.el-sub-menu__title) {
    height: 48px;
    line-height: 48px;
    margin-bottom: 4px;
    border-radius: 8px;
    margin-left: 8px;
    margin-right: 8px;
}
:deep(.el-menu-item.is-active) {
    background-color: #ecf5ff; /* Un azul muy suave */
    color: #409EFF;
    font-weight: 600;
}
:deep(.el-menu-item:hover), :deep(.el-sub-menu__title:hover) {
    background-color: #f9fafb;
}

/* Ocultar elementos en colapso para evitar parpadeos */
:deep(.el-menu--collapse .el-sub-menu__title span) {
    display: none;
}
:deep(.el-menu--collapse .el-sub-menu__icon-arrow) {
    display: none;
}
</style>