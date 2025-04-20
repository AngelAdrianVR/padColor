<script setup>
import { ref, onMounted, computed, onBeforeUnmount } from 'vue';
// import Echo from 'laravel-echo';
import { Head, Link, router } from '@inertiajs/vue3';
import Banner from '@/Components/Banner.vue';
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
const showMenu = ref(false);
const options = [
    {
        label: 'Inicio',
        route: 'dashboard',
        active: 'dashboard',
        showPermission: 'Ver dashboard',
        icon: '<i class="fa-solid fa-house text-lg"></i>',
    },
    {
        label: 'Tickets',
        route: 'tickets.index',
        active: 'tickets.*',
        showPermission: 'Ver tickets',
        icon: '<svg width="20" height="20" class="inline" viewBox="0 0 18 17" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M0.231489 10.7042L10.694 0.241731C10.9644 -0.0287082 11.3207 -0.105215 11.5996 0.183928L12.6593 1.2822C12.8526 1.48253 12.8935 1.60511 12.8135 1.7061C11.4846 3.38241 13.7004 5.40687 15.2412 4.19166C15.383 4.07988 15.4744 4.11459 15.6073 4.24947L16.9175 5.57895C17.0916 5.75555 17.0916 6.00254 16.9175 6.17626L6.3009 16.7736C6.04996 17.0241 5.77824 17.0819 5.54944 16.87C5.41811 16.7483 5.29996 16.6426 5.18915 16.5434L5.18912 16.5434L5.18907 16.5434L5.18903 16.5433L5.18902 16.5433C4.88327 16.2697 4.63334 16.046 4.31629 15.6754C4.13501 15.4634 4.16234 15.3671 4.35483 15.0973C5.54944 13.4788 3.25194 11.5404 1.92707 12.6696C1.71876 12.8471 1.56689 12.8682 1.40683 12.7081L0.115881 11.4171C-0.115003 11.1859 0.0389857 10.8969 0.231489 10.7042ZM10.9634 5.43846C10.8369 5.31603 10.6351 5.31933 10.5127 5.44582C10.3903 5.57231 10.3936 5.77409 10.5201 5.89652L10.9781 6.33985C11.1046 6.46227 11.3064 6.45897 11.4288 6.33248C11.5513 6.20599 11.548 6.00421 11.4215 5.88179L10.9634 5.43846ZM8.22365 3.22902C8.34607 3.10253 8.54785 3.09924 8.67434 3.22166L9.1324 3.66499C9.25889 3.78741 9.26219 3.9892 9.13977 4.11569C9.01734 4.24218 8.81556 4.24547 8.68907 4.12305L8.23101 3.67972C8.10452 3.5573 8.10122 3.35551 8.22365 3.22902ZM12.1118 6.54783C11.9854 6.42541 11.7836 6.42871 11.6611 6.5552C11.5387 6.68168 11.542 6.88347 11.6685 7.00589L12.1266 7.44922C12.2531 7.57164 12.4548 7.56835 12.5773 7.44186C12.6997 7.31537 12.6964 7.11359 12.5699 6.99116L12.1118 6.54783ZM9.36818 4.3384C9.4906 4.21191 9.69238 4.20861 9.81887 4.33103L10.2769 4.77437C10.4034 4.89679 10.4067 5.09857 10.2843 5.22506C10.1619 5.35155 9.96009 5.35485 9.8336 5.23243L9.37554 4.78909C9.24905 4.66667 9.24576 4.46489 9.36818 4.3384ZM13.2525 7.65525C13.126 7.53283 12.9242 7.53613 12.8018 7.66262C12.6793 7.78911 12.6826 7.99089 12.8091 8.11331L13.2672 8.55664C13.3937 8.67907 13.5955 8.67577 13.7179 8.54928C13.8403 8.42279 13.837 8.22101 13.7105 8.09858L13.2525 7.65525Z" fill="currentColor"/></svg>',
    },
    {
        label: 'SwAssistant',
        route: 'productions.index',
        active: 'productions.*',
        showPermission: 'Ver producciones',
        icon: '<svg width="20" height="18" class="inline" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M0.154379 14.9829L14.2487 15C14.3858 15 14.3858 14.9829 14.3858 14.6754C14.3858 14.5007 14.3246 14.1993 14.2509 13.8367L14.2509 13.8366L14.2509 13.8366L14.2509 13.8366L14.2509 13.8366C14.1396 13.2889 14 12.6016 14 12V10.5V8.5C14 7.28943 14.0618 6.89676 14.3858 5.3475C14.4208 5.18027 14.3858 5.03103 14.1637 5.17665L9.4996 8.23481C9.36292 8.32443 9.36292 8.26898 9.36292 8.1323V5.3475C9.36292 5.08147 9.29458 5.02941 9.07248 5.17665L4.45963 8.23481C4.35654 8.30315 4.27169 8.30315 4.27169 8.1323L4 6.5V4L4.24064 2.46974H0.044804L0.5 4.5V6.5V9V12L0.000527196 14.4533L0.000244263 14.544C-0.00114732 14.8409 -0.00181277 14.9829 0.154379 14.9829ZM0.0514674 2.06974H4.22845L4 1C4 0.5 4 0 3.5 0H1C0.342221 0 0.342221 0.375586 0.342221 0.913825L0.0514674 2.06974ZM4.27127 2.2703V2.27493L4.27169 2.27226L4.27127 2.2703ZM4.56205 10.6584C4.35704 10.6584 4.33995 10.7609 4.33995 10.8634V11.7347C4.33995 11.8885 4.3912 11.991 4.56205 11.991H6.86848C7.03933 11.991 7.07077 11.8891 7.0735 11.7347V10.8634C7.0735 10.7268 7.03933 10.6584 6.86848 10.6584H4.56205ZM9.36353 10.8636C9.36353 10.7611 9.38062 10.6585 9.58563 10.6585H11.8921C12.0629 10.6585 12.0971 10.7269 12.0971 10.8636V11.7349C12.0943 11.8892 12.0629 11.9911 11.8921 11.9911H9.58563C9.41479 11.9911 9.36353 11.8886 9.36353 11.7349V10.8636Z" fill="currentColor"/></svg>',
    },
    {
        label: 'Productos',
        route: 'products.index',
        active: 'products.*',
        showPermission: 'Ver productos',
        icon: '<svg width="19" height="19" class="inline" viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2.98136 4.49467C0.954921 2.88619 4.70988 -2.07101 7.62927 4.42519C11.4445 -2.4881 15.3186 2.94497 12.1003 4.49464C14.1193 2.76468 11.5041 -1.05771 7.62927 4.42519C5.36546 -0.402134 1.76655 2.41175 2.98136 4.49467Z" fill="currentColor"/><path d="M7.62927 4.42519C4.70988 -2.07101 0.954921 2.88619 2.98136 4.49467C1.76655 2.41175 5.36546 -0.402134 7.62927 4.42519ZM7.62927 4.42519C11.4445 -2.4881 15.3186 2.94497 12.1003 4.49464C14.1193 2.76468 11.5041 -1.05771 7.62927 4.42519Z" stroke="currentColor" stroke-width="0.119196"/><path d="M0 7.76328V5.31975C-0.000220968 4.60458 0.0595985 4.42578 0.65558 4.42578H14.9591C15.6147 4.42578 15.6147 4.78337 15.6147 5.31975V7.76328C15.6147 8.35926 15.4359 8.59765 14.9591 8.59765H0.65558C0.0595987 8.59765 0 8.29966 0 7.76328Z" fill="currentColor"/><path d="M0.65625 16.4031V8.71495L14.9002 8.71484V16.4031C14.9002 16.8798 14.6618 16.9991 14.185 16.9991H1.37143C0.835044 16.9991 0.65625 16.8202 0.65625 16.4031Z" fill="currentColor"/><path d="M6.07812 16.999V8.71484H9.35602V16.999H6.07812Z" fill="#EDEDED"/><path d="M5.83984 8.59765V4.42578H9.71372V8.59765H5.83984Z" fill="#EDEDED"/></svg>',
    },
    {
        label: 'Máquinas',
        route: 'machines.index',
        active: 'machines.*',
        showPermission: 'Ver máquinas',
        icon: '<svg width="20" height="20" class="inline" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7.62556 10.668L7.62362 9.87727C7.27364 8.36406 4.84289 8.44244 4.64844 10.2347V4.15422L4.95246 3.8502C5.35782 4.66092 6.83395 4.86868 7.60835 3.66406L7.62362 9.87727C7.67628 10.105 7.68183 10.3687 7.62556 10.668Z" fill="currentColor"/><path d="M7.47852 1.51437L5.8813 1.72088C7.68822 1.72088 7.6812 3.14154 7.47852 3.74958L8.18791 4.25628L11.3295 2.83752C10.9107 2.02197 10.6137 0.912066 12.5456 0L7.47852 1.51437Z" fill="currentColor"/><path d="M12.0389 5.26969V3.34422C12.9509 3.85092 14.167 3.24288 14.471 2.22947L14.3697 5.26969C14.3697 4.86433 12.1402 4.76299 12.0389 5.26969Z" fill="currentColor"/><path d="M13.7616 5.84714C13.5392 5.24868 12.7992 5.17985 12.5456 5.84714L13.1536 7.56993L13.7616 5.84714Z" fill="currentColor"/><path d="M9.30266 11.2488V13.1743H3.01953V11.2488C3.01953 10.9448 3.12087 10.6407 3.52623 10.6407H4.74232C5.45171 12.0595 6.97182 11.9582 7.57986 10.6407H8.69461C9.09997 10.6407 9.30266 10.8434 9.30266 11.2488Z" fill="currentColor"/><circle cx="6.16855" cy="3.1412" r="1.41877" stroke="currentColor" stroke-width="0.202681"/><path d="M1.30402 13.1758C1.10134 13.1761 1 13.2768 1 13.4798V14.6959C1 14.8986 1.20268 14.9999 1.40536 14.9999H15.289C15.4917 14.9999 15.5931 14.8986 15.5931 14.6959V13.3785C15.5931 13.1758 15.4917 13.1758 15.289 13.1758H1.30402Z" fill="currentColor" stroke="currentColor" stroke-width="0.486435"/><circle cx="6.16855" cy="10.2564" r="1.41877" stroke="currentColor" stroke-width="0.202681"/><circle cx="6.188" cy="10.2993" r="0.570817" fill="currentColor"/><circle cx="6.188" cy="3.15285" r="0.570817" fill="currentColor"/><circle cx="12.768" cy="1.83829" r="0.744544" fill="currentColor"/><circle cx="12.7577" cy="1.82413" r="1.70252" stroke="currentColor" stroke-width="0.243218"/><path d="M11.4413 5.90082C11.9746 5.0795 11.9746 4.91992 13.1449 4.91992C14.1926 4.91992 14.5173 5.02317 14.8658 5.90082C14.9683 6.15895 14.2905 7.01212 13.7474 7.58728C14.0362 6.98387 14.3178 6.26221 14.3496 6.02128C14.4098 5.56375 13.7474 5.24689 13.1449 4.91992C12.4395 5.2641 12.0627 5.71153 12.0091 6.09012C11.9652 6.39988 12.1148 6.95886 12.4395 7.58728C11.8885 6.97044 11.2625 6.17616 11.4413 5.90082Z" fill="currentColor"/></svg>',
    },
    {
        label: 'Usuarios',
        route: 'users.index',
        active: 'users.*',
        showPermission: 'Ver usuarios',
        icon: '<svg width="16" height="19" class="inline" viewBox="0 0 13 16" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="6.5051" cy="4.41526" r="4.41526" fill="currentColor"/><path d="M9.53815 8.60156C7.13688 9.95182 5.82103 10.0154 3.51316 8.60156C2.08731 9.38343 0.642262 10.5793 0.109733 12.6029C-0.0718203 13.2928 -0.0589851 14.1479 0.385687 14.6266C2.07018 16.4398 11.3454 16.1032 12.5276 14.6266C13.08 13.9367 13.0666 13.2704 12.8956 12.6029C12.4361 10.8092 11.3318 9.61339 9.53815 8.60156Z" fill="currentColor"/></svg>',
    },
    {
        label: 'Clientes',
        route: 'users.index',
        active: 'users.*',
        showPermission: 'Ver clientes',
        icon: `<svg width="19" height="21" class="inline" viewBox="0 0 12 14" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M8.46882 7.73438C9.94246 8.56585 10.85 9.54855 11.2276 11.0225C11.3681 11.571 11.3787 12.1186 10.9249 12.6855C9.95344 13.8991 2.33277 14.1756 0.948315 12.6855C0.582923 12.2922 0.571591 11.5894 0.720776 11.0225C1.15838 9.3596 2.34607 8.3769 3.51765 7.73438C5.41405 8.89632 6.49568 8.84403 8.46882 7.73438ZM7.99714 10.0127L7.00593 10.2666L7.69148 11.0029L7.6407 12.0195L8.53035 11.6641L9.4698 12.0195L9.41902 11.0029L10.0802 10.2666L9.11433 10.0127L8.55574 9.14844L7.99714 10.0127Z" fill="currentColor"/>
        <path d="M5.97754 0.667969C7.98126 0.667969 9.60547 2.29291 9.60547 4.29688C9.60535 5.36909 9.13982 6.33191 8.40039 6.99609C8.61597 6.70826 8.7881 6.36847 8.8916 5.97168C9.11864 5.10086 8.59641 4.21054 7.72559 3.9834C7.02605 3.80114 6.315 4.10281 5.94434 4.67969C5.53851 4.04709 4.73417 3.75966 4.00195 4.03418C3.2119 4.33042 2.78768 5.17489 2.99805 5.97363L3.04785 6.13281L3.1543 6.38379C3.24872 6.58441 3.36142 6.76691 3.48633 6.93359C2.78665 6.27221 2.34973 5.3357 2.34961 4.29688C2.34961 2.29296 3.97388 0.668042 5.97754 0.667969Z" fill="currentColor"/>
        </svg>
        `,
    },
    {
        label: 'Configuraciones',
        route: 'settings.index',
        active: 'settings.*',
        showPermission: 'Ver configuraciones',
        icon: '<svg width="20" height="20" class="inline" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7.21973 0.0478516C7.94732 -0.0418853 8.42134 0.0143806 9.00684 0.0664062L9.09961 0.0791016C9.18688 0.0962526 9.2619 0.126104 9.32324 0.169922C9.40531 0.228698 9.46298 0.312233 9.49609 0.418945L9.76367 1.28125L9.76465 1.28418L9.80469 1.3877C9.8196 1.41139 9.83951 1.42705 9.87207 1.43457L10.0957 1.49121C10.5862 1.6255 10.8765 1.78638 11.3193 2.04688L11.3691 2.06836C11.3845 2.07248 11.3993 2.07388 11.4131 2.07324C11.4413 2.07179 11.4694 2.06139 11.498 2.0459L12.2412 1.64453L12.4004 1.5752C12.5595 1.52213 12.7163 1.52281 12.8379 1.61621L13.2959 1.9834C13.7038 2.3293 13.9866 2.63882 14.3574 3.13672L14.4062 3.20996C14.4473 3.28332 14.4681 3.35679 14.4717 3.43066C14.4763 3.52897 14.4506 3.62834 14.4043 3.73145L14.4033 3.73242L14.002 4.49121C13.955 4.58008 13.9417 4.63279 13.9717 4.68457L14.166 5.03516C14.3359 5.36295 14.4435 5.64899 14.5684 6.10156L14.5986 6.19922C14.6038 6.20956 14.6099 6.21845 14.6172 6.22461C14.6244 6.23063 14.6345 6.23524 14.6475 6.23926L15.5703 6.52246L15.6475 6.55078C15.722 6.58311 15.7889 6.62808 15.8418 6.68652C15.9125 6.76477 15.9583 6.86773 15.9668 6.99707L15.9961 7.52832C16.0148 8.01604 16.0035 8.40753 15.9668 8.99219C15.9662 9.17899 15.8612 9.36166 15.6914 9.44922L15.6143 9.48145L14.707 9.76367C14.6784 9.77259 14.6568 9.78302 14.6396 9.79883C14.6312 9.80669 14.623 9.81623 14.6162 9.82812L14.5977 9.87305C14.4654 10.3243 14.3579 10.6146 14.1914 10.9541L14.002 11.3184C13.9825 11.3543 13.9743 11.382 13.9746 11.4082C13.975 11.4343 13.9836 11.462 14.002 11.498L14.4033 12.2861L14.4395 12.3643C14.47 12.4419 14.4863 12.5204 14.4834 12.5986C14.4804 12.6772 14.4583 12.7558 14.4121 12.834L14.3574 12.9121C13.9857 13.3766 13.7163 13.6735 13.3301 14.0059L12.8975 14.3574C12.8291 14.4104 12.7441 14.4551 12.6406 14.4697C12.5373 14.4842 12.4153 14.4687 12.2725 14.4043L12.2715 14.4033L11.498 13.9873C11.4622 13.968 11.437 13.9619 11.416 13.9629C11.4056 13.9635 11.3955 13.9657 11.3848 13.9697L11.3486 13.9873C10.9367 14.2124 10.6516 14.3425 10.2803 14.4629L9.87207 14.583C9.84548 14.5903 9.82801 14.603 9.81445 14.6201C9.8005 14.6379 9.78986 14.6621 9.77832 14.6934L9.48145 15.5996C9.44346 15.7153 9.38568 15.7977 9.29883 15.8535C9.23408 15.895 9.15334 15.9217 9.05371 15.9385L8.94824 15.9521C8.47899 15.9968 8.04378 16.0141 7.51562 15.9902L6.95215 15.9521C6.76507 15.9358 6.60402 15.8194 6.53223 15.667L6.50781 15.5986L6.25488 14.6914C6.25057 14.6759 6.2474 14.6641 6.24414 14.6533C6.24106 14.6432 6.23749 14.635 6.2334 14.6289C6.22958 14.6234 6.22408 14.6182 6.21582 14.6133L6.17578 14.5977C5.73103 14.4771 5.43943 14.3754 5.09082 14.2021L4.71484 14.002C4.68239 13.9837 4.6512 13.9745 4.62012 13.9746C4.60465 13.9747 4.58858 13.9771 4.57227 13.9814L4.52051 14.002L3.74707 14.3887C3.61114 14.4566 3.49226 14.4775 3.37695 14.457C3.2907 14.4416 3.20703 14.4027 3.12012 14.3457L3.03223 14.2832C2.54132 13.9061 2.26232 13.6367 1.97461 13.2842L1.67578 12.8965C1.60861 12.8058 1.56445 12.7254 1.5498 12.6426C1.5352 12.5591 1.5505 12.4744 1.59961 12.376L2.03125 11.5127L2.05664 11.457C2.06224 11.4415 2.06509 11.428 2.06543 11.415C2.06572 11.4024 2.06289 11.3892 2.05762 11.374L2.03125 11.3184C1.80622 10.9178 1.68069 10.6431 1.56543 10.2959L1.4502 9.91699C1.43766 9.8726 1.42702 9.84179 1.41309 9.82031C1.40649 9.81023 1.39866 9.80171 1.38965 9.79492L1.35547 9.77832L0.433594 9.48145C0.319952 9.44479 0.240057 9.3902 0.18457 9.3125C0.143357 9.25459 0.116253 9.18372 0.0976562 9.09863L0.0810547 9.00781C-0.028582 8.22578 -0.0241658 7.77648 0.0810547 6.96582L0.105469 6.83887C0.11658 6.79921 0.131607 6.76249 0.151367 6.72949C0.191317 6.66292 0.252266 6.61239 0.344727 6.58203L1.34082 6.25488L1.40723 6.23047C1.42052 6.22265 1.43005 6.21137 1.43555 6.19043L1.55664 5.77148C1.68072 5.39173 1.8224 5.1039 2.06152 4.68457L2.07812 4.6377C2.08027 4.62257 2.07943 4.60754 2.07617 4.59277L2.0332 4.49414L2.03125 4.49121L1.64453 3.76172C1.5967 3.66138 1.56245 3.55439 1.5498 3.45996C1.53736 3.36616 1.54621 3.28034 1.58691 3.22559L1.9541 2.75293C2.2995 2.33446 2.60217 2.05384 3.07715 1.69043L3.16797 1.62793C3.25518 1.57487 3.3307 1.55048 3.4043 1.54688C3.50166 1.54217 3.59396 1.57359 3.70117 1.61426H3.70215L4.52051 2.03125L4.57617 2.05566C4.5912 2.06061 4.60438 2.06263 4.61621 2.0625C4.63975 2.0621 4.66351 2.05246 4.7002 2.03125L4.89941 1.91992C5.34258 1.68403 5.6366 1.60295 6.11523 1.43555L6.17773 1.41211C6.19342 1.40529 6.20477 1.39838 6.21387 1.39062C6.231 1.37595 6.24165 1.35555 6.25488 1.31152L6.52246 0.418945L6.5498 0.352539C6.58217 0.289608 6.62963 0.236279 6.68164 0.195312C6.75086 0.14087 6.82933 0.106062 6.8916 0.0957031L7.21973 0.0478516ZM7.98242 4.20215C5.89377 4.20215 4.2002 5.89573 4.2002 7.98438C4.20044 10.0728 5.89393 11.7656 7.98242 11.7656C10.0708 11.7655 11.7634 10.0728 11.7637 7.98438C11.7637 5.89578 10.071 4.20224 7.98242 4.20215Z" fill="currentColor"/></svg>',
    },
];

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
                        <div class="flex justify-between h-10">
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

                            <!-- mensaje para cambiar contrasena -->
                            <div v-if="!$page.props.auth.user.password_changed"
                                class="z-20 flex items-center w-1/2 md:w-3/4">
                                <article
                                    class="flex items-center space-x-3 bg-red-200 text-red-600 rounded-md px-3 py-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="hidden md:inline size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 9v3.75m0-10.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.25-8.25-3.286Zm0 13.036h.008v.008H12v-.008Z" />
                                    </svg>
                                    <p class="text-[7px] md:text-xs">
                                        Para garantizar la seguridad de tu cuenta, te recomendamos
                                        cambiar tu contraseña por defecto. Esto ayudará a proteger tu información
                                        personal y garantizará que solo tú tengas acceso.
                                        <Link :href="route('profile.show')" class="text-blue-600 underline">
                                        Click aqui
                                        </Link>
                                    </p>
                                </article>
                            </div>

                            <div class="hidden sm:flex sm:items-center sm:ms-6">
                                <!-- notifications -->
                                <NotificationsCenter class="hidden lg:block" />
                            </div>
                        </div>
                    </div>
                </nav>
                <div class="overflow-y-auto h-[calc(100vh-5rem)] lg:h-[calc(100vh-2.6rem)] bg-white">
                    <slot />
                </div>
                <!-- ---------------- footer nave mobile view --------------- -->
                <nav class="lg:hidden fixed bottom-0 w-full z-10">
                    <div class="w-full h-10 flex justify-between items-center px-4 bg-grayED">
                        <button @click="showMenu = !showMenu"
                            :class="{ 'border-primary bg-primary text-white': showMenu }"
                            class="flex items-center space-x-1 px-3 py-1 text-sm border border-[#999999] text-[#999999] rounded-full transition-all duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="size-5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3.75 6.75h16.5M3.75 12H12m-8.25 5.25h16.5" />
                            </svg>
                            <span>Menú</span>
                        </button>
                        <div v-if="showMenu" class="absolute bottom-12 left-2 bg-grayED rounded-2xl w-2/3 py-3">
                            <ul>
                                <li v-for="(option, index) in options" :key="index">
                                    <button v-if="$page.props.auth.user.permissions.includes(option.showPermission)"
                                        type="button" @click="$inertia.get(route(option.route))"
                                        class="w-full px-4 flex items-center space-x-2 py-1 focus:bg-[#D7D7D7] focus:text-primary"
                                        :class="route().current(option.active) ? 'bg-[#D7D7D7] text-primary' : 'text-[#666666]'">
                                        <i v-html="option.icon"></i>
                                        <span>{{ option.label }}</span>
                                    </button>
                                </li>
                            </ul>
                        </div>
                        <!-- <div @click="$inertia.get(route('dashboard'))"
                            :class="route().current('dashboard') ? 'bg-[#c8c8c8] text-primary' : 'text-[#999999]'"
                            class="relative flex flex-col justify-center text-center px-3 rounded-[10px] my-2 py-1">
                            <p v-if="route().current('dashboard')" class="text-[13px] absolute -top-6 left-1">Inicio</p>
                            <div v-if="route().current('dashboard')"
                                class="-z-10 absolute -top-7 -left-[34px] w-24 h-24 bg-grayED rounded-full"></div>
                            <i class="fa-solid fa-house text-xl"></i>
                        </div> -->
                        <!-- <div @click="$inertia.get(route('tickets.index'))"
                            :class="route().current('tickets.*') ? 'bg-[#c8c8c8] text-primary' : 'text-[#999999]'"
                            class="relative flex flex-col justify-center text-center px-3 rounded-[10px] my-2 py-1">
                            <p v-if="route().current('tickets.*')" class="text-[13px] absolute -top-6 left-0">Tickets</p>
                            <div v-if="route().current('tickets.*')"
                                class="-z-10 absolute -top-7 -left-[34px] w-24 h-24 bg-grayED rounded-full"></div>
                            <i class="fa-regular fa-square-check text-xl"></i>
                        </div>
                        <div @click="$inertia.get(route('users.index'))"
                            :class="route().current('users.*') ? 'bg-[#c8c8c8] text-primary' : 'text-[#999999]'"
                            class="relative flex flex-col justify-center text-center px-3 rounded-[10px] my-2 py-1">
                            <p v-if="route().current('users.*')" class="text-[13px] absolute -top-6 -left-2">Usuarios</p>
                            <div v-if="route().current('users.*')"
                                class="-z-10 absolute -top-7 -left-9 w-24 h-24 bg-grayED rounded-full"></div>
                            <i class="fa-regular fa-user text-xl"></i>
                        </div>
                        <div @click="$inertia.get(route('settings.index'))"
                            :class="route().current('settings.*') ? 'bg-[#c8c8c8] text-primary' : 'text-[#999999]'"
                            class="relative flex flex-col justify-center text-center px-3 rounded-[10px] my-2 py-1">
                            <p v-if="route().current('settings.*')" class="text-[13px] absolute -top-6 -left-2">Config...
                            </p>
                            <div v-if="route().current('settings.*')"
                                class="-z-10 absolute -top-7 -left-9 w-24 h-24 bg-grayED rounded-full"></div>
                            <i class="fa-solid fa-gears text-xl"></i>
                        </div> -->
                        <div class="flex items-center space-x-2">
                            <div @click="$inertia.get(route('notifications'))"
                                :class="route().current('notifications') ? 'bg-[#c8c8c8] text-primary' : 'text-[#999999]'"
                                class="relative flex flex-col justify-center text-center px-3 rounded-[10px] my-2 py-1">
                                <p v-if="route().current('notifications')"
                                    class="text-center text-[13px] absolute -top-6 -left-2">Notifica...</p>
                                <div v-if="route().current('notifications')"
                                    class="-z-10 absolute -top-7 -left-9 w-24 h-24 bg-grayED rounded-full"></div>
                                <i class="fa-solid fa-bell text-xl"></i>
                                <span v-if="getUnreadNotifications"
                                    class="size-4 rounded-full bg-primary text-white text-[10px] absolute top-0 right-0">{{
                                        getUnreadNotifications }}</span>
                            </div>
                            <button @click="showProfileCard = !showProfileCard"
                                :class="{ 'border-primary': route().current('profile.*') }"
                                class="relative size-8 flex justify-center items-center text-sm border border-[#999999] rounded-full focus:outline-none focus:border-primary transition">
                                <img class="size-9 p-1 rounded-full object-cover"
                                    :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                            </button>
                        </div>
                        <ProfileCard @close="showProfileCard = false" v-if="showProfileCard"
                            class="bottom-12 right-2" />
                    </div>
                </nav>
            </main>
        </div>
    </div>
</template>
