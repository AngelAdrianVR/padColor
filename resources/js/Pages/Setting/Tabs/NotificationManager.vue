<template>
    <div class="container mx-auto p-4 sm:p-6 lg:p-8">
        <h1 class="text-lg font-bold">Gestión de Suscripciones a Notificaciones</h1>

        <!-- Selector de Eventos -->
        <div class="mt-3">
            <InputLabel value="Selecciona un evento de Notificación" />
            <el-select class="w-full" v-model="selectedEventId" placeholder="Selecciona el departamento"
                no-data-text="No hay opciones registradas" no-match-text="No se encontraron coincidencias">
                <el-option v-for="event in notificationEvents" :key="event.id" :label="event.name" :value="event.id" />
            </el-select>
        </div>

        <h2 v-if="selectedEvent" class="text-xl font-semibold mt-6 mb-2">
            Asignar usuarios a: <span class="text-blue-400">{{ selectedEvent.name }}</span>
        </h2>

        <UserFilters v-model:search="searchFilters.search" v-model:department="searchFilters.department"
            v-model:company="searchFilters.company" @add-external="isModalOpen = true" />
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