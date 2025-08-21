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
                <Link v-if="small" :href="route('dashboard')">
                <ApplicationMark />
                </Link>
                <Link v-else :href="route('dashboard')">
                <figure class="">
                    <img class="w-32" src="@/../../public/images/logo_name.png" alt="logo">
                </figure>
                </Link>
            </div>
            <nav class="px-2 flex flex-col justify-between h-[calc(100vh-5rem)]" :class="small ? 'pt-11' : 'pt-7'">
                <section>
                    <template v-if="small">
                        <div v-for="(menu, index) in menus" :key="index">
                            <SideNavLink v-if="menu.show" :href="menu.route" :active="menu.active"
                                :dropdown="menu.options.length > 0" class="mb-px">
                                <template #trigger>
                                    <button v-if="menu.show" :active="menu.active" :title="menu.label"
                                        class="w-full text-center py-1 justify-between rounded-[10px] mt-1 transition ease-linear duration-150"
                                        :class="menu.active ? 'bg-grayD9 text-primary' : 'hover:text-primary hover:bg-grayD9 text-gray66'">
                                        <span v-html="menu.icon"></span>
                                    </button>
                                    <i v-if="menu.notifications"
                                        class="fa-solid fa-circle fa-flip text-primary text-[10px] absolute bottom-7 right-1"></i>
                                </template>
                                <template #content>
                                    <template v-for="option in menu.options" :key="option">
                                        <DropdownNavLink v-if="option.show" :href="option.route"
                                            :active="option.active">
                                            {{ option.label }}
                                        </DropdownNavLink>
                                    </template>
                                </template>
                            </SideNavLink>
                        </div>
                    </template>
                    <template v-else v-for="(menu, index) in menus" :key="index">
                        <!-- Con submenues -->
                        <div v-if="menu.show">
                            <Accordion v-if="menu.options.length" :icon="menu.icon" :active="menu.active"
                                :title="menu.label" :id="index">
                                <!-- Opciones del menu -->
                                <div class="relative" v-for="(option, index2) in menu.options" :key="index2">
                                    <Link :href="option.route" class="flex items-center">
                                    <button v-if="option.show" :active="option.active" :title="option.label"
                                        class="w-full text-start ml-6 px-3 pr-2 mt-2 flex justify-between text-xs rounded-md py-1 transition ease-linear duration-150"
                                        :class="option.active ? 'bg-grayD9 text-primary' : 'hover:text-primary hover:bg-grayD9 text-gray66'">
                                        <p class="w-full truncate">{{ option.label }}</p>
                                    </button>
                                    <!-- Adorno lateral de subcategorias-->
                                    <i class="absolute left-[8px] top-[14px] fa-solid fa-circle text-[7px] z-10 p-1"
                                        :class="option.active ? 'text-primary' : 'text-grayD9'"></i>
                                    <div class="border-l border-grayD9 absolute left-[15px] h-full"></div>
                                    </Link>
                                </div>
                            </Accordion>
                            <!-- Sin submenues -->
                            <button v-else-if="menu.show" @click="goToRoute(menu.route)" :title="menu.label"
                                class="w-full text-start px-2 mt-2 flex justify-between text-xs rounded-md py-1 transition ease-linear duration-150"
                                :class="menu.active ? 'bg-grayD9 text-primary' : 'hover:text-primary hover:bg-grayD9 text-gray66'">
                                <p class="w-full text-sm truncate"><span class="mr-2" v-html="menu.icon"></span>
                                    {{ menu.label }}
                                </p>
                            </button>
                        </div>
                    </template>
                    <!-- Capacitacion visible para todos -->
                    <button v-if="small"
                        @click='openNewTab("https://padcolor.sharepoint.com/:l:/s/PapelDiseoyColorS.AdeC.V/FGCXpm_1M_1JmmAPw0DWjPMBPJI7JTLnd2Fykxg1aLDHcg?e=9CvpF6")'
                        title="Capacitación"
                        class="w-full text-center py-1 justify-between rounded-[10px] mt-1 transition ease-linear duration-150 text-gray66 hover:text-primary hover:bg-grayD9">
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                class="size-[22px] inline">
                                <path
                                    d="M11.25 4.533A9.707 9.707 0 0 0 6 3a9.735 9.735 0 0 0-3.25.555.75.75 0 0 0-.5.707v14.25a.75.75 0 0 0 1 .707A8.237 8.237 0 0 1 6 18.75c1.995 0 3.823.707 5.25 1.886V4.533ZM12.75 20.636A8.214 8.214 0 0 1 18 18.75c.966 0 1.89.166 2.75.47a.75.75 0 0 0 1-.708V4.262a.75.75 0 0 0-.5-.707A9.735 9.735 0 0 0 18 3a9.707 9.707 0 0 0-5.25 1.533v16.103Z" />
                            </svg>
                        </span>
                    </button>
                    <button v-else
                        @click='openNewTab("https://padcolor.sharepoint.com/:l:/s/PapelDiseoyColorS.AdeC.V/FGCXpm_1M_1JmmAPw0DWjPMBPJI7JTLnd2Fykxg1aLDHcg?e=9CvpF6")'
                        title="Capacitación"
                        class="w-full text-start px-2 mt-2 flex justify-between text-xs rounded-md py-1 transition ease-linear duration-150 text-gray66 hover:text-primary hover:bg-grayD9">
                        <p class="w-full text-sm truncate">
                            <span class="mr-2">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                    class="size-[22px] inline">
                                    <path
                                        d="M11.25 4.533A9.707 9.707 0 0 0 6 3a9.735 9.735 0 0 0-3.25.555.75.75 0 0 0-.5.707v14.25a.75.75 0 0 0 1 .707A8.237 8.237 0 0 1 6 18.75c1.995 0 3.823.707 5.25 1.886V4.533ZM12.75 20.636A8.214 8.214 0 0 1 18 18.75c.966 0 1.89.166 2.75.47a.75.75 0 0 0 1-.708V4.262a.75.75 0 0 0-.5-.707A9.735 9.735 0 0 0 18 3a9.707 9.707 0 0 0-5.25 1.533v16.103Z" />
                                </svg>
                            </span>
                            <span>Capacitación</span>
                        </p>
                    </button>
                </section>
                <!-- Avatar de usuario -->
                <div class="mt-24 text-center">
                    <button v-if="$page.props.jetstream.managesProfilePhotos"
                        @click="showProfileCard = !showProfileCard"
                        class="flex items-center space-x-2 text-sm border rounded-full focus:outline-none transition mt-4"
                        :class="{ 'border-primary': showProfileCard, 'border-[#999999]': !showProfileCard, 'size-12 justify-center': small, 'h-12 w-full px-2 justify-between': !small }">
                        <div class="flex items-center">
                            <img class="size-9 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url"
                                :alt="$page.props.auth.user.name">
                            <p v-if="!small" class="text-[11px] text-gray99 leading-snug text-start mt-1 ml-2">{{
                                $page.props.auth.user.name }}</p>
                        </div>
                        <i v-if="!small" class="fa-solid fa-angle-right text-center text-xs text-[#999999]"></i>
                    </button>
                    <ProfileCard v-if="showProfileCard" @close="showProfileCard = false"
                        class="bottom-3 left-[calc(100%+0.75rem)]" />
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
import SideNavLink from './SideNavLink.vue';
import DropdownNavLink from './DropdownNavLink.vue';

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
                    show: this.$page.props.auth.user.permissions.includes('Ver dashboard'),
                },
                {
                    label: 'Tickets',
                    icon: '<svg width="20" height="20" class="inline" viewBox="0 0 18 17" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M0.231489 10.7042L10.694 0.241731C10.9644 -0.0287082 11.3207 -0.105215 11.5996 0.183928L12.6593 1.2822C12.8526 1.48253 12.8935 1.60511 12.8135 1.7061C11.4846 3.38241 13.7004 5.40687 15.2412 4.19166C15.383 4.07988 15.4744 4.11459 15.6073 4.24947L16.9175 5.57895C17.0916 5.75555 17.0916 6.00254 16.9175 6.17626L6.3009 16.7736C6.04996 17.0241 5.77824 17.0819 5.54944 16.87C5.41811 16.7483 5.29996 16.6426 5.18915 16.5434L5.18912 16.5434L5.18907 16.5434L5.18903 16.5433L5.18902 16.5433C4.88327 16.2697 4.63334 16.046 4.31629 15.6754C4.13501 15.4634 4.16234 15.3671 4.35483 15.0973C5.54944 13.4788 3.25194 11.5404 1.92707 12.6696C1.71876 12.8471 1.56689 12.8682 1.40683 12.7081L0.115881 11.4171C-0.115003 11.1859 0.0389857 10.8969 0.231489 10.7042ZM10.9634 5.43846C10.8369 5.31603 10.6351 5.31933 10.5127 5.44582C10.3903 5.57231 10.3936 5.77409 10.5201 5.89652L10.9781 6.33985C11.1046 6.46227 11.3064 6.45897 11.4288 6.33248C11.5513 6.20599 11.548 6.00421 11.4215 5.88179L10.9634 5.43846ZM8.22365 3.22902C8.34607 3.10253 8.54785 3.09924 8.67434 3.22166L9.1324 3.66499C9.25889 3.78741 9.26219 3.9892 9.13977 4.11569C9.01734 4.24218 8.81556 4.24547 8.68907 4.12305L8.23101 3.67972C8.10452 3.5573 8.10122 3.35551 8.22365 3.22902ZM12.1118 6.54783C11.9854 6.42541 11.7836 6.42871 11.6611 6.5552C11.5387 6.68168 11.542 6.88347 11.6685 7.00589L12.1266 7.44922C12.2531 7.57164 12.4548 7.56835 12.5773 7.44186C12.6997 7.31537 12.6964 7.11359 12.5699 6.99116L12.1118 6.54783ZM9.36818 4.3384C9.4906 4.21191 9.69238 4.20861 9.81887 4.33103L10.2769 4.77437C10.4034 4.89679 10.4067 5.09857 10.2843 5.22506C10.1619 5.35155 9.96009 5.35485 9.8336 5.23243L9.37554 4.78909C9.24905 4.66667 9.24576 4.46489 9.36818 4.3384ZM13.2525 7.65525C13.126 7.53283 12.9242 7.53613 12.8018 7.66262C12.6793 7.78911 12.6826 7.99089 12.8091 8.11331L13.2672 8.55664C13.3937 8.67907 13.5955 8.67577 13.7179 8.54928C13.8403 8.42279 13.837 8.22101 13.7105 8.09858L13.2525 7.65525Z" fill="currentColor"/></svg>',
                    route: route('tickets.index'),
                    active: route().current('tickets.*'),
                    options: [],
                    show: true,
                },
                {
                    label: 'Usuarios',
                    icon: '<svg width="16" height="19" class="inline" viewBox="0 0 13 16" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="6.5051" cy="4.41526" r="4.41526" fill="currentColor"/><path d="M9.53815 8.60156C7.13688 9.95182 5.82103 10.0154 3.51316 8.60156C2.08731 9.38343 0.642262 10.5793 0.109733 12.6029C-0.0718203 13.2928 -0.0589851 14.1479 0.385687 14.6266C2.07018 16.4398 11.3454 16.1032 12.5276 14.6266C13.08 13.9367 13.0666 13.2704 12.8956 12.6029C12.4361 10.8092 11.3318 9.61339 9.53815 8.60156Z" fill="currentColor"/></svg>',
                    route: route('users.index'),
                    active: route().current('users.*'),
                    options: [],
                    show: this.$page.props.auth.user.permissions.includes('Ver usuarios'),
                },
                {
                    label: 'Clientes',
                    icon: '<svg width="19" height="21" class="inline" viewBox="0 0 12 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8.46882 7.73438C9.94246 8.56585 10.85 9.54855 11.2276 11.0225C11.3681 11.571 11.3787 12.1186 10.9249 12.6855C9.95344 13.8991 2.33277 14.1756 0.948315 12.6855C0.582923 12.2922 0.571591 11.5894 0.720776 11.0225C1.15838 9.3596 2.34607 8.3769 3.51765 7.73438C5.41405 8.89632 6.49568 8.84403 8.46882 7.73438ZM7.99714 10.0127L7.00593 10.2666L7.69148 11.0029L7.6407 12.0195L8.53035 11.6641L9.4698 12.0195L9.41902 11.0029L10.0802 10.2666L9.11433 10.0127L8.55574 9.14844L7.99714 10.0127Z" fill="currentColor"/><path d="M5.97754 0.667969C7.98126 0.667969 9.60547 2.29291 9.60547 4.29688C9.60535 5.36909 9.13982 6.33191 8.40039 6.99609C8.61597 6.70826 8.7881 6.36847 8.8916 5.97168C9.11864 5.10086 8.59641 4.21054 7.72559 3.9834C7.02605 3.80114 6.315 4.10281 5.94434 4.67969C5.53851 4.04709 4.73417 3.75966 4.00195 4.03418C3.2119 4.33042 2.78768 5.17489 2.99805 5.97363L3.04785 6.13281L3.1543 6.38379C3.24872 6.58441 3.36142 6.76691 3.48633 6.93359C2.78665 6.27221 2.34973 5.3357 2.34961 4.29688C2.34961 2.29296 3.97388 0.668042 5.97754 0.667969Z" fill="currentColor"/></svg>',
                    route: route('users.index'),
                    active: route().current('users.*'),
                    options: [],
                    show: this.$page.props.auth.user.permissions.includes('Ver clientes'),
                },
                {
                    label: 'Importaciones',
                    icon: '<svg width="20" height="22" class="inline" viewBox="0 0 16 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12.7532 16.7774C12.7964 16.7774 12.9038 16.9183 12.9038 16.9615C12.9038 17.0047 12.7964 17.1571 12.7532 17.1571H3.42057C3.37738 17.1571 3.26923 17.0047 3.26923 16.9615C3.26923 16.9183 3.37738 16.7774 3.42057 16.7774H12.7532Z" fill="currentColor"/><path d="M15.6208 9.80189C15.2654 12.6342 14.5842 14.0034 12.4168 15.9802H15.8293C15.8725 15.9802 16 16.0948 16 16.1538C16 16.2129 15.8725 16.36 15.8293 16.36H7.98262H0.135899C0.0927066 16.36 0 16.197 0 16.1538C0 16.1107 0.0927066 15.9802 0.135899 15.9802H3.85967C1.18647 13.6772 0.531832 12.315 0.605138 9.80189L7.85356 7.06771V9.51057L2.14177 11.6814C2 11.7115 2.00159 11.8953 2.01923 11.9423C2.03441 11.9827 2.11538 12.1154 2.19676 12.0897L7.85356 9.9395V15.9802H8.23334V9.93879L13.9253 12.0899C13.9795 12.1119 14.0962 12.0192 14.1154 11.9231C14.1346 11.8269 14.0962 11.7308 13.9795 11.6812L8.23334 9.51016V7.0669L15.6208 9.80189Z" fill="currentColor"/><path fill-rule="evenodd" clip-rule="evenodd" d="M14.0567 5.05736V8.68231L8.00869 6.54467L2.16927 8.68231V5.05736C2.16927 4.72715 2.28223 4.37957 2.74278 4.37957H13.2746C13.683 4.37957 14.0567 4.58812 14.0567 5.05736ZM4.04623 6.04797H4.86162V5.36534H4.04623V6.04797ZM5.81891 6.04797H6.6343V5.36534H5.81891V6.04797ZM7.80014 6.04797H8.61553V5.36534H7.80014V6.04797ZM9.6771 6.04797H10.4925V5.36534H9.6771V6.04797ZM11.5541 6.04797H12.3694V5.36534H11.5541V6.04797Z" fill="currentColor"/><path d="M9.10358 0C9.46854 0 9.62496 0.156413 9.62496 0.573515V4.01429H6.54883V0.573515C6.54883 0.156413 6.75739 0 7.17449 0H9.10358Z" fill="currentColor"/></svg>',
                    route: route('imports.index'),
                    active: route().current('imports.*') || route().current('suppliers.*') || route().current('customs-agents.*'),
                    options: [
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
                    icon: '<svg width="20" height="18" class="inline" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M0.154379 14.9829L14.2487 15C14.3858 15 14.3858 14.9829 14.3858 14.6754C14.3858 14.5007 14.3246 14.1993 14.2509 13.8367L14.2509 13.8366L14.2509 13.8366L14.2509 13.8366L14.2509 13.8366C14.1396 13.2889 14 12.6016 14 12V10.5V8.5C14 7.28943 14.0618 6.89676 14.3858 5.3475C14.4208 5.18027 14.3858 5.03103 14.1637 5.17665L9.4996 8.23481C9.36292 8.32443 9.36292 8.26898 9.36292 8.1323V5.3475C9.36292 5.08147 9.29458 5.02941 9.07248 5.17665L4.45963 8.23481C4.35654 8.30315 4.27169 8.30315 4.27169 8.1323L4 6.5V4L4.24064 2.46974H0.044804L0.5 4.5V6.5V9V12L0.000527196 14.4533L0.000244263 14.544C-0.00114732 14.8409 -0.00181277 14.9829 0.154379 14.9829ZM0.0514674 2.06974H4.22845L4 1C4 0.5 4 0 3.5 0H1C0.342221 0 0.342221 0.375586 0.342221 0.913825L0.0514674 2.06974ZM4.27127 2.2703V2.27493L4.27169 2.27226L4.27127 2.2703ZM4.56205 10.6584C4.35704 10.6584 4.33995 10.7609 4.33995 10.8634V11.7347C4.33995 11.8885 4.3912 11.991 4.56205 11.991H6.86848C7.03933 11.991 7.07077 11.8891 7.0735 11.7347V10.8634C7.0735 10.7268 7.03933 10.6584 6.86848 10.6584H4.56205ZM9.36353 10.8636C9.36353 10.7611 9.38062 10.6585 9.58563 10.6585H11.8921C12.0629 10.6585 12.0971 10.7269 12.0971 10.8636V11.7349C12.0943 11.8892 12.0629 11.9911 11.8921 11.9911H9.58563C9.41479 11.9911 9.36353 11.8886 9.36353 11.7349V10.8636Z" fill="currentColor"/></svg>',
                    route: route('productions.index'),
                    active: route().current('productions.*'),
                    options: [],
                    show: this.$page.props.auth.user.permissions.includes('Ver producciones'),
                },
                {
                    label: 'Catálogo',
                    icon: '<svg width="19" height="19" class="inline" viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2.98136 4.49467C0.954921 2.88619 4.70988 -2.07101 7.62927 4.42519C11.4445 -2.4881 15.3186 2.94497 12.1003 4.49464C14.1193 2.76468 11.5041 -1.05771 7.62927 4.42519C5.36546 -0.402134 1.76655 2.41175 2.98136 4.49467Z" fill="currentColor"/><path d="M7.62927 4.42519C4.70988 -2.07101 0.954921 2.88619 2.98136 4.49467C1.76655 2.41175 5.36546 -0.402134 7.62927 4.42519ZM7.62927 4.42519C11.4445 -2.4881 15.3186 2.94497 12.1003 4.49464C14.1193 2.76468 11.5041 -1.05771 7.62927 4.42519Z" stroke="currentColor" stroke-width="0.119196"/><path d="M0 7.76328V5.31975C-0.000220968 4.60458 0.0595985 4.42578 0.65558 4.42578H14.9591C15.6147 4.42578 15.6147 4.78337 15.6147 5.31975V7.76328C15.6147 8.35926 15.4359 8.59765 14.9591 8.59765H0.65558C0.0595987 8.59765 0 8.29966 0 7.76328Z" fill="currentColor"/><path d="M0.65625 16.4031V8.71495L14.9002 8.71484V16.4031C14.9002 16.8798 14.6618 16.9991 14.185 16.9991H1.37143C0.835044 16.9991 0.65625 16.8202 0.65625 16.4031Z" fill="currentColor"/><path d="M6.07812 16.999V8.71484H9.35602V16.999H6.07812Z" fill="#EDEDED"/><path d="M5.83984 8.59765V4.42578H9.71372V8.59765H5.83984Z" fill="#EDEDED"/></svg>',
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
                    icon: '<svg width="20" height="20" class="inline" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7.21973 0.0478516C7.94732 -0.0418853 8.42134 0.0143806 9.00684 0.0664062L9.09961 0.0791016C9.18688 0.0962526 9.2619 0.126104 9.32324 0.169922C9.40531 0.228698 9.46298 0.312233 9.49609 0.418945L9.76367 1.28125L9.76465 1.28418L9.80469 1.3877C9.8196 1.41139 9.83951 1.42705 9.87207 1.43457L10.0957 1.49121C10.5862 1.6255 10.8765 1.78638 11.3193 2.04688L11.3691 2.06836C11.3845 2.07248 11.3993 2.07388 11.4131 2.07324C11.4413 2.07179 11.4694 2.06139 11.498 2.0459L12.2412 1.64453L12.4004 1.5752C12.5595 1.52213 12.7163 1.52281 12.8379 1.61621L13.2959 1.9834C13.7038 2.3293 13.9866 2.63882 14.3574 3.13672L14.4062 3.20996C14.4473 3.28332 14.4681 3.35679 14.4717 3.43066C14.4763 3.52897 14.4506 3.62834 14.4043 3.73145L14.4033 3.73242L14.002 4.49121C13.955 4.58008 13.9417 4.63279 13.9717 4.68457L14.166 5.03516C14.3359 5.36295 14.4435 5.64899 14.5684 6.10156L14.5986 6.19922C14.6038 6.20956 14.6099 6.21845 14.6172 6.22461C14.6244 6.23063 14.6345 6.23524 14.6475 6.23926L15.5703 6.52246L15.6475 6.55078C15.722 6.58311 15.7889 6.62808 15.8418 6.68652C15.9125 6.76477 15.9583 6.86773 15.9668 6.99707L15.9961 7.52832C16.0148 8.01604 16.0035 8.40753 15.9668 8.99219C15.9662 9.17899 15.8612 9.36166 15.6914 9.44922L15.6143 9.48145L14.707 9.76367C14.6784 9.77259 14.6568 9.78302 14.6396 9.79883C14.6312 9.80669 14.623 9.81623 14.6162 9.82812L14.5977 9.87305C14.4654 10.3243 14.3579 10.6146 14.1914 10.9541L14.002 11.3184C13.9825 11.3543 13.9743 11.382 13.9746 11.4082C13.975 11.4343 13.9836 11.462 14.002 11.498L14.4033 12.2861L14.4395 12.3643C14.47 12.4419 14.4863 12.5204 14.4834 12.5986C14.4804 12.6772 14.4583 12.7558 14.4121 12.834L14.3574 12.9121C13.9857 13.3766 13.7163 13.6735 13.3301 14.0059L12.8975 14.3574C12.8291 14.4104 12.7441 14.4551 12.6406 14.4697C12.5373 14.4842 12.4153 14.4687 12.2725 14.4043L12.2715 14.4033L11.498 13.9873C11.4622 13.968 11.437 13.9619 11.416 13.9629C11.4056 13.9635 11.3955 13.9657 11.3848 13.9697L11.3486 13.9873C10.9367 14.2124 10.6516 14.3425 10.2803 14.4629L9.87207 14.583C9.84548 14.5903 9.82801 14.603 9.81445 14.6201C9.8005 14.6379 9.78986 14.6621 9.77832 14.6934L9.48145 15.5996C9.44346 15.7153 9.38568 15.7977 9.29883 15.8535C9.23408 15.895 9.15334 15.9217 9.05371 15.9385L8.94824 15.9521C8.47899 15.9968 8.04378 16.0141 7.51562 15.9902L6.95215 15.9521C6.76507 15.9358 6.60402 15.8194 6.53223 15.667L6.50781 15.5986L6.25488 14.6914C6.25057 14.6759 6.2474 14.6641 6.24414 14.6533C6.24106 14.6432 6.23749 14.635 6.2334 14.6289C6.22958 14.6234 6.22408 14.6182 6.21582 14.6133L6.17578 14.5977C5.73103 14.4771 5.43943 14.3754 5.09082 14.2021L4.71484 14.002C4.68239 13.9837 4.6512 13.9745 4.62012 13.9746C4.60465 13.9747 4.58858 13.9771 4.57227 13.9814L4.52051 14.002L3.74707 14.3887C3.61114 14.4566 3.49226 14.4775 3.37695 14.457C3.2907 14.4416 3.20703 14.4027 3.12012 14.3457L3.03223 14.2832C2.54132 13.9061 2.26232 13.6367 1.97461 13.2842L1.67578 12.8965C1.60861 12.8058 1.56445 12.7254 1.5498 12.6426C1.5352 12.5591 1.5505 12.4744 1.59961 12.376L2.03125 11.5127L2.05664 11.457C2.06224 11.4415 2.06509 11.428 2.06543 11.415C2.06572 11.4024 2.06289 11.3892 2.05762 11.374L2.03125 11.3184C1.80622 10.9178 1.68069 10.6431 1.56543 10.2959L1.4502 9.91699C1.43766 9.8726 1.42702 9.84179 1.41309 9.82031C1.40649 9.81023 1.39866 9.80171 1.38965 9.79492L1.35547 9.77832L0.433594 9.48145C0.319952 9.44479 0.240057 9.3902 0.18457 9.3125C0.143357 9.25459 0.116253 9.18372 0.0976562 9.09863L0.0810547 9.00781C-0.028582 8.22578 -0.0241658 7.77648 0.0810547 6.96582L0.105469 6.83887C0.11658 6.79921 0.131607 6.76249 0.151367 6.72949C0.191317 6.66292 0.252266 6.61239 0.344727 6.58203L1.34082 6.25488L1.40723 6.23047C1.42052 6.22265 1.43005 6.21137 1.43555 6.19043L1.55664 5.77148C1.68072 5.39173 1.8224 5.1039 2.06152 4.68457L2.07812 4.6377C2.08027 4.62257 2.07943 4.60754 2.07617 4.59277L2.0332 4.49414L2.03125 4.49121L1.64453 3.76172C1.5967 3.66138 1.56245 3.55439 1.5498 3.45996C1.53736 3.36616 1.54621 3.28034 1.58691 3.22559L1.9541 2.75293C2.2995 2.33446 2.60217 2.05384 3.07715 1.69043L3.16797 1.62793C3.25518 1.57487 3.3307 1.55048 3.4043 1.54688C3.50166 1.54217 3.59396 1.57359 3.70117 1.61426H3.70215L4.52051 2.03125L4.57617 2.05566C4.5912 2.06061 4.60438 2.06263 4.61621 2.0625C4.63975 2.0621 4.66351 2.05246 4.7002 2.03125L4.89941 1.91992C5.34258 1.68403 5.6366 1.60295 6.11523 1.43555L6.17773 1.41211C6.19342 1.40529 6.20477 1.39838 6.21387 1.39062C6.231 1.37595 6.24165 1.35555 6.25488 1.31152L6.52246 0.418945L6.5498 0.352539C6.58217 0.289608 6.62963 0.236279 6.68164 0.195312C6.75086 0.14087 6.82933 0.106062 6.8916 0.0957031L7.21973 0.0478516ZM7.98242 4.20215C5.89377 4.20215 4.2002 5.89573 4.2002 7.98438C4.20044 10.0728 5.89393 11.7656 7.98242 11.7656C10.0708 11.7655 11.7634 10.0728 11.7637 7.98438C11.7637 5.89578 10.071 4.20224 7.98242 4.20215Z" fill="currentColor"/></svg>',
                    route: route('settings.index'),
                    active: route().current('settings.*'),
                    options: [],
                    show: this.$page.props.auth.user.permissions.includes('Ver configuraciones'),
                },
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
        SideNavLink,
        DropdownNavLink,
    },
    methods: {
        openNewTab(url) {
            window.open(url, 'blank');
        },
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
        updateSideNavSize(is_small) {
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