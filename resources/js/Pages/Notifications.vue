<template>
    <AppLayout title="Notificaciones">
        <Loading v-if="loading" class="my-20" />
        <div v-else class="w-[90%] border rounded-t-[10px] border-grayD9 mx-auto absolute bottom-20 left-[calc(5%)]">
            <h1 class="text-gray66 text-center text-sm my-2">Notificaciones <span class="text-primary">({{
                notifications.length }})</span></h1>
            <main class="h-[60vh] overflow-y-auto">
                <el-empty v-if="!notifications.length" description="No hay notificaciones" :image-size="90" />
                <div @click="handleClickNotification(item.id)" v-for="item in notifications" :key="item.id" :href="item.data.url"
                    class="block px-4 py-2 text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out"
                    :class="{ 'bg-primarylight': item.read_at === null }">
                    <div class="relative">
                        <div v-if="item.read_at === null"
                            class="size-1 bg-primary rounded-full absolute top-[6px] -right-2">
                        </div>
                        <div class="flex">
                            <label class="w-[8%]">
                                <input type="checkbox" @change="handleItemChecked()" @click.stop v-model="selectedItems"
                                    :value="item.id"
                                    class="size-3 rounded-sm border-[#999999] text-primary shadow-sm focus:ring-primary bg-transparent disabled:border-gray-300 disabled:bg-gray-200 disabled:cursor-not-allowed" />
                            </label>
                            <figure class="w-[14%] rounded-full">
                                <img class="size-7 rounded-full object-cover" :src="item.data.user_photo"
                                    :alt="item.data.user_name">
                            </figure>
                            <section class="w-[78%] text-xs">
                                <p :class="{ 'text-primary': item.read_at === null }">
                                    <span class="text-gray66 mr-1">{{ item.data.user_name }}</span>
                                    {{ item.data.description }}
                                </p>
                            </section>
                        </div>
                        <small class="mt-1 text-gray2 text-[10px]">{{ item.created_at_for_humans }}</small>
                    </div>
                </div>
            </main>
            <footer v-if="notifications.length" class="py-1 px-2 flex justify-between items-center border-t mt-3">
                <label class="text-gray99 text-xs has-[:checked]:text-primary">
                    <Checkbox v-model:checked="allItems" @change="handleChange" class="size-3 mr-2" />
                    <span>Seleccionar todos</span>
                </label>
                <div class="flex space-x-2">
                    <el-popconfirm confirm-button-text="Si" cancel-button-text="No" icon-color="#D72C8A"
                        :title="'¿Desea marcar como leidas las notificaciones seleccionadas (' + selectedItems.length + ')?'"
                        @confirm="readNotifications()">
                        <template #reference>
                            <button
                                class="flex justify-center items-center size-6 text-xs rounded-[5px] bg-primarylight text-primary disabled:cursor-not-allowed disabled:bg-grayED disabled:text-gray66"
                                :disabled="!selectedItems.length">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </button>
                        </template>
                    </el-popconfirm>
                    <el-popconfirm confirm-button-text="Si" cancel-button-text="No" icon-color="#D72C8A"
                        :title="'¿Desea eliminar las notificaciones seleccionadas (' + selectedItems.length + ')?'"
                        @confirm="deleteNotifications()">
                        <template #reference>
                            <button
                                class="flex justify-center items-center size-6 text-xs rounded-[5px] bg-primarylight text-primary disabled:cursor-not-allowed disabled:bg-grayED disabled:text-gray66"
                                :disabled="!selectedItems.length">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                            </button>
                        </template>
                    </el-popconfirm>
                </div>
            </footer>
        </div>
    </AppLayout>
</template>
<script>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Loading from '@/Components/MyComponents/Loading.vue';
import Checkbox from '@/Components/Checkbox.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from "axios";

export default {
    data() {
        return {
            allItems: false,
            loading: true,
            notifications: [],
            selectedItems: [],
        }
    },
    props: {
    },
    components: {
        AppLayout,
        PrimaryButton,
        Checkbox,
        Loading,
    },
    methods: {
        handleClickNotification(itemId) {
            this.selectedItems = [itemId];
            this.readNotifications();
            this.$inertia.get(this.notifications.find(item => item.id == itemId)?.data.url);
        },
        handleChange() {
            if (this.allItems) {
                this.selectedItems = this.notifications.map(notification => notification.id);
            } else {
                this.selectedItems = [];
            }
        },
        handleItemChecked() {
            if (this.selectedItems.length == this.notifications.length) {
                this.allItems = true;
            } else if (this.selectedItems.length < this.notifications.length && this.allItems) {
                this.allItems = false;
            }
        },
        async deleteNotifications() {
            try {
                const response = await axios.post(route('users.delete-user-notifications'), { notifications_ids: this.selectedItems });

                if (response.status === 200) {
                    // Filtrar el arreglo excluyendo los elementos con IDs en 'selectedItems'
                    this.notifications = this.notifications.filter(item => !this.selectedItems.includes(item.id));
                    this.$notify({
                        title: "Éxito",
                        message: response.data.message,
                        type: "success",
                    });
                }
            } catch (error) {
                console.log(error);
                this.$notify({
                    title: "No se pudo completar la solicitud",
                    message: "El servidor no pudo procesar la petición, inténtalo más tarde",
                    type: "error",
                });
            }
        },
        async fetchNotifications() {
            try {
                this.loading = true;
                const response = await axios.get(route('users.get-notifications'));

                if (response.status === 200) {
                    this.notifications = response.data.items;
                }
            } catch (error) {
                console.log(error);
                this.$notify({
                    title: "No se pudo completar la solicitud",
                    message: "El servidor no pudo procesar la petición para obtener las notificaciones. Inténtalo más tarde",
                    type: "error",
                });
            } finally {
                this.loading = false;
            }
        },
        async readNotifications() {
            try {
                const response = await axios.post(route("users.read-user-notifications"), { notifications_ids: this.selectedItems });

                if (response.data.unread) {
                    this.fetchNotifications();
                }
            } catch (error) {
                console.log(error);
            }
        },
    },
    computed: {
        getUnreadMessages() {
            return this.notifications?.filter(item => item.read_at === null);
        }
    },
    mounted() {
        this.fetchNotifications();
    },
}
</script>
