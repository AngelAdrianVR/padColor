<template>
    <div class="container mx-auto px-2 sm:p-4 lg:px-20">
        <h1 class="text-lg font-bold">Gestión de Notificaciones por correo</h1>
        <h3 class="text-sm text-gray99 mt-1">
            Activa o desactiva las notificaciones para usuarios internos y externos.
        </h3>

        <section class="mt-5 border border-grayD9 rounded-xl py-5 px-3 space-y-4">
            <div class="flex space-x-4 items-center">
                <span
                    class="bg-[#666666] text-white font-bold size-7 text-lg rounded-full flex items-center justify-center">1</span>
                <span class="font-bold">Selecciona una notificación</span>
            </div>
            <el-select class="w-full" v-model="selectedEventId" placeholder="Selecciona el departamento"
                no-data-text="No hay opciones registradas" no-match-text="No se encontraron coincidencias">
                <el-option v-for="event in notificationEvents" :key="event.id" :label="event.name" :value="event.id" />
            </el-select>
            <div class="bg-[#EDFDEE] text-[#076C22] p-3 rounded-lg">
                <p class="font-bold flex items-center space-x-2">
                    <svg width="20" height="21" viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g clip-path="url(#clip0_18919_1708)">
                            <path
                                d="M12.7724 13.0406C13.9191 12.5409 15.0028 11.9077 16.0011 11.154C14.7317 10.3873 13.7926 9.17607 13.3662 7.75568L13.2217 7.277C12.9152 6.26138 12.2178 5.40911 11.283 4.90768C10.3481 4.40625 9.25229 4.29675 8.23666 4.60325C7.22104 4.90975 6.36877 5.60715 5.86734 6.54203C5.36591 7.47692 5.2564 8.5727 5.56291 9.58833L5.70736 10.067C6.13778 11.4863 6.02525 13.0148 5.39166 14.3558C6.621 14.4305 7.87276 14.3626 9.12556 14.1412M12.7724 13.0406C11.6044 13.5499 10.3803 13.9193 9.12556 14.1412M12.7724 13.0406C12.9511 13.3 13.0659 13.5978 13.1076 13.91C13.1494 14.2221 13.1168 14.5397 13.0126 14.8369C12.9084 15.1341 12.7354 15.4024 12.5079 15.6201C12.2803 15.8378 12.0046 15.9987 11.7031 16.0897C11.4016 16.1807 11.0829 16.1992 10.7729 16.1437C10.4629 16.0882 10.1704 15.9603 9.9192 15.7704C9.66798 15.5805 9.46517 15.334 9.32725 15.0508C9.18934 14.7677 9.12024 14.4561 9.12556 14.1412"
                                stroke="#076C22" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </g>
                        <defs>
                            <clipPath id="clip0_18919_1708">
                                <rect width="16" height="16" fill="white" transform="translate(0 5) rotate(-16.793)" />
                            </clipPath>
                        </defs>
                    </svg>
                    <span>{{ selectedEvent?.name }}</span>
                </p>
                <p class="mt-2">
                    <span class="font-semibold">Internos: </span><span>{{ subscriptions?.users?.length }}
                        usuario(s)</span> |
                    <span class="font-semibold">Externos: </span><span>{{ subscriptions?.external?.length }}
                        usuario(s)</span>
                </p>
            </div>
        </section>
        <section class="mt-5 border border-grayD9 rounded-xl py-5 px-3 space-y-4">
            <div class="flex space-x-4 items-center">
                <span
                    class="bg-[#666666] text-white font-bold size-7 text-lg rounded-full flex items-center justify-center">2</span>
                <span class="font-bold">Gestionar destinatarios</span>
            </div>
            <UserFilters v-model:search="searchFilters.search" v-model:department="searchFilters.department"
                v-model:company="searchFilters.company" @add-external="isModalOpen = true" />
        </section>

        <!-- Selector de Eventos -->
        <!-- <div class="mt-3">
            <InputLabel value="Selecciona un evento de Notificación" />
            <el-select class="w-full" v-model="selectedEventId" placeholder="Selecciona el departamento"
                no-data-text="No hay opciones registradas" no-match-text="No se encontraron coincidencias">
                <el-option v-for="event in notificationEvents" :key="event.id" :label="event.name" :value="event.id" />
            </el-select>
        </div> -->

        <!-- <h2 v-if="selectedEvent" class="text-xl font-semibold mt-6 mb-2">
            Asignar usuarios a: <span class="text-blue-400">{{ selectedEvent.name }}</span>
        </h2> -->

        <ExternalEmails :emails="subscriptions.external" @remove="removeExternalEmail" />
        <UsersTable :users="users.data" :subscriptions="subscriptions.users" @toggle="toggleSubscription" />
        <Pagination :links="users.links" />
        <AddExternalEmailModal :show="isModalOpen" @close="isModalOpen = false"
            :event-name="selectedEvent ? selectedEvent.name : ''" @save="addExternalEmail" />

        <!-- <div class="mt-8 p-4 bg-gray-700 rounded-lg">
            <h3 class="font-semibold text-lg mb-2">Estado Actual (para depuración):</h3>
            <pre
                class="text-xs text-gray-300 overflow-auto"><code>{{ { selectedEventId, filters: searchFilters, subscriptions } }}</code></pre>
        </div> -->
    </div>
</template>
<script>
import { debounce } from 'lodash';
import InputLabel from '@/Components/InputLabel.vue';
import UserFilters from '@/Components/MyComponents/Setting/Notifications/UserFilters.vue';
import ExternalEmails from '@/Components/MyComponents/Setting/Notifications/ExternalEmails.vue';
import UsersTable from '@/Components/MyComponents/Setting/Notifications/UsersTable.vue';
import Pagination from '@/Components/MyComponents/Setting/Notifications/Pagination.vue';
import AddExternalEmailModal from '@/Components/MyComponents/Setting/Notifications/AddExternalEmailModal.vue';

export default {
    data() {
        return {
            selectedEventId: null,
            subscriptions: this.initialSubscriptions,
            searchFilters: {
                search: this.filters.search,
                department: this.filters.department,
                company: this.filters.company,
            },
            isModalOpen: false,
        };
    },
    components: {
        UserFilters,
        ExternalEmails,
        UsersTable,
        Pagination,
        AddExternalEmailModal,
        InputLabel,
    },
    props: {
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
    computed: {
        selectedEvent() {
            return this.notificationEvents.find(e => e.id === this.selectedEventId);
        }
    },
    watch: {
        searchFilters: {
            deep: true, // Necesario para observar cambios dentro del objeto
            handler: debounce(function () {
                this.fetchData();
            }, 100)
        },
        selectedEventId() {
            this.fetchData();
        }
    },
    methods: {
        fetchData() {
            this.$inertia.get(route('notifications.management'), {
                event_id: this.selectedEventId,
                ...this.searchFilters
            }, {
                preserveState: true,
                replace: true,
                onSuccess: () => {
                    this.subscriptions = this.initialSubscriptions;
                },
                onError: (error) => {
                    console.error('Error al cargar los datos:', error);
                }
            });
        },
        toggleSubscription(userId) {
            this.$inertia.post(route('subscriptions.user.toggle'), {
                event_id: this.selectedEventId,
                user_id: userId,
            }, {
                preserveScroll: true,
                // Inertia actualizará las props automáticamente al recibir la respuesta
                onSuccess: () => {
                },
                onError: (error) => {
                    console.error('Error al registrar suscripción:', error);
                }
            });
        },
        addExternalEmail(email) {
            this.$inertia.post(route('subscriptions.external.add'), {
                event_id: this.selectedEventId,
                email: email,
            }, {
                preserveScroll: true,
                onSuccess: () => {
                    this.isModalOpen = false; // Cierra el modal solo si la petición fue exitosa
                    this.subscriptions.external.push(email); // Actualiza la lista de correos externos
                },
                onError: (errors) => {
                    // Puedes manejar los errores aquí, por ejemplo, pasándolos al modal
                    console.error("Error al añadir correo:", errors);
                }
            });
        },
        removeExternalEmail(email) {
            this.$inertia.delete(route('subscriptions.external.remove'), {
                data: {
                    event_id: this.selectedEventId,
                    email: email,
                },
                preserveScroll: true,
                onSuccess: () => {
                    this.subscriptions.external = this.subscriptions.external.filter(e => e !== email);
                },
                onError: (errors) => {
                    console.error("Error al remover correo:", errors);
                }
            });
        }
    },
    mounted() {
        // cargar el evento seleccionado desde la URL si existe
        const currentURL = new URL(window.location.href);
        const currentEventId = currentURL.searchParams.get('event_id');
        if (currentEventId) {
            this.selectedEventId = parseInt(currentEventId, 10);
        } else if (this.notificationEvents.length > 0) {
            this.selectedEventId = this.notificationEvents[0].id; // Selecciona el primer evento si no hay uno en la URL
        }
    }
}
</script>