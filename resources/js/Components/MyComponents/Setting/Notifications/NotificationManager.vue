<script>
import { debounce } from 'lodash';

export default {
    data() {
        return {
            selectedEventId: this.notificationEvents[0]?.id || null,
            subscriptions: this.initialSubscriptions,
            searchFilters: {
                search: this.filters.search,
                department: this.filters.department,
                company: this.filters.company,
            },
            isModalOpen: false,
        };
    },
    props: {
        notificationEvents: {
            type: Array,
            default: () => [
                { id: 1, name: 'Producción Liberada a Calidad' },
                { id: 2, name: 'Ficha Técnica Aprobada' },
                { id: 3, name: 'Ticket de Soporte Cerrado' },
                { id: 4, name: 'Nuevo Cliente Registrado' },
            ]
        },
        users: {
            type: Object,
            default: () => ({
                data: [
                    { id: 101, name: 'Ana Torres', email: 'ana.torres@example.com', department: 'Calidad', company: 'PadColor' },
                    { id: 102, name: 'Carlos Gomez', email: 'carlos.gomez@example.com', department: 'Producción', company: 'PadColor' },
                    { id: 103, name: 'Luisa Fernandez', email: 'luisa.f@example.com', department: 'Diseño', company: 'PrintCo' },
                    { id: 104, name: 'Jorge Diaz', email: 'jorge.d@example.com', department: 'Ventas', company: 'PadColor' },
                    { id: 105, name: 'Sofia Ramirez', email: 'sofia.r@example.com', department: 'Calidad', company: 'PrintCo' },
                    { id: 106, name: 'Miguel Angel', email: 'miguel.a@example.com', department: 'Producción', company: 'PadColor' },
                    { id: 107, name: 'Valeria Castillo', email: 'valeria.c@example.com', department: 'Soporte', company: 'PadColor' },
                ],
                links: { first: 'url', prev: null, next: 'url', last: 'url' },
                meta: { current_page: 1, from: 1, last_page: 2, total: 12 }
            })
        },
        initialSubscriptions: {
            type: Object,
            default: () => ({
                users: [101, 105],
                external: ['proveedor.calidad@externo.com']
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
            }, 300)
        },
        selectedEventId() {
            this.fetchData();
        }
    },
    methods: {
        fetchData() {
            this.$inertia.get(route('notifications.management'), { // Asume que tienes una ruta con este nombre
                event_id: this.selectedEventId,
                ...this.searchFilters
            }, {
                preserveState: true,
                replace: true,
                onSuccess: () => {
                    console.log(`Cargando suscripciones para el evento ${this.selectedEventId}...`);
                    // Simulación para el ejemplo
                    const newSubs = { 1: { users: [101, 105], external: ['proveedor.calidad@externo.com'] }, 2: { users: [103], external: [] }, 3: { users: [107], external: ['cliente.final@externo.com'] }, 4: { users: [], external: [] } };
                    this.subscriptions.users = newSubs[this.selectedEventId]?.users || [];
                    this.subscriptions.external = newSubs[this.selectedEventId]?.external || [];
                }
            });
        },
        toggleSubscription(userId) {
            const index = this.subscriptions.users.indexOf(userId);
            if (index > -1) {
                this.subscriptions.users.splice(index, 1);
            } else {
                this.subscriptions.users.push(userId);
            }
            // TODO: Llamar a la API para persistir el cambio
            console.log(`Suscripción para usuario ${userId} cambiada.`);
        },
        addExternalEmail(email) {
            if (email && !this.subscriptions.external.includes(email)) {
                this.subscriptions.external.push(email);
                // TODO: Llamar a la API para persistir el cambio
                console.log(`Correo externo ${email} añadido.`);
            }
            this.isModalOpen = false;
        },
        removeExternalEmail(email) {
            const index = this.subscriptions.external.indexOf(email);
            if (index > -1) {
                this.subscriptions.external.splice(index, 1);
                // TODO: Llamar a la API para persistir el cambio
                console.log(`Correo externo ${email} eliminado.`);
            }
        }
    }
}
</script>

<template>
    <div class="container mx-auto p-4 sm:p-6 lg:p-8">
        <h1 class="text-3xl font-bold mb-6">Gestión de Suscripciones a Notificaciones</h1>

        <!-- Selector de Eventos -->
        <div class="mb-6">
            <label for="event-selector" class="block text-sm font-medium text-gray-300 mb-2">Selecciona un Evento de
                Notificación:</label>
            <select id="event-selector" v-model="selectedEventId"
                class="w-full bg-gray-700 border-gray-600 text-white rounded-lg p-2.5 focus:ring-blue-500 focus:border-blue-500">
                <option v-for="event in notificationEvents" :key="event.id" :value="event.id">
                    {{ event.name }}
                </option>
            </select>
        </div>

        <h2 v-if="selectedEvent" class="text-2xl font-semibold mb-4">
            Asignar usuarios a: <span class="text-blue-400">{{ selectedEvent.name }}</span>
        </h2>

        <!-- Aquí irán los componentes hijos -->
        <!-- <UserFilters v-model:search="searchFilters.search" ... /> -->
        <!-- <ExternalEmails :emails="subscriptions.external" @remove="removeExternalEmail" /> -->
        <!-- <UsersTable :users="users.data" :subscriptions="subscriptions.users" @toggle="toggleSubscription" /> -->
        <!-- <Pagination :links="users.links" /> -->
        <!-- <AddExternalEmailModal :show="isModalOpen" @close="isModalOpen = false" @save="addExternalEmail" /> -->

        <div class="mt-8 p-4 bg-gray-900 rounded-lg">
            <h3 class="font-semibold text-lg mb-2">Estado Actual (para depuración):</h3>
            <pre
                class="text-xs text-gray-300 overflow-auto"><code>{{ { selectedEventId, filters: searchFilters, subscriptions } }}</code></pre>
        </div>

    </div>
</template>
