<template>
    <AppLayout title="Usuarios">
        <div class="flex justify-between items-center mt-4 mx-2 lg:mx-10">
            <h1 class="text-sm lg:text-lg font-bold">Todos los usuarios</h1>
            <PrimaryButton v-if="$page.props.auth.user.permissions.includes('Crear usuarios')"
                @click="$inertia.get(route('users.create'))">Agregar usuario</PrimaryButton>
        </div>

        <!-- Buscador -->
        <div class="flex flex-col lg:flex-row space-y-2 lg:items-center mt-4 mx-2 lg:mx-10">
            <div class="lg:w-1/4 relative lg:mr-12">
                <input v-model="searchTemp" @keyup.enter="handleSearch" class="input w-full pl-9"
                    placeholder="Buscar usuario" type="search">
                <i class="fa-solid fa-magnifying-glass text-xs text-gray99 absolute top-[10px] left-4"></i>
            </div>
            <el-tag v-if="search" size="large" closable @close="handleTagClose">
                Estas buscando: <b>{{ search }}</b>
            </el-tag>
        </div>

        <!-- usuarios -->
        <div v-if="loading" class="mt-32">
            <Loading />
        </div>
        <div v-else class="mt-7">
            <div v-if="localUsers.length" class="flex items-center border-b border-grayD9 pb-2">
                <label class="flex items-center ml-2 lg:ml-24">
                    <Checkbox @change="handleAllUsersChecked" v-model:checked="allUsers" name="all"
                        :disabled="!localUsers.length" />
                    <span class="ms-2 text-sm font-bold">Todos los usuarios</span>
                </label>
                <div v-if="selectedItems.length && $page.props.auth.user.permissions.includes('Eliminar usuarios')"
                    class="lg:ml-36">
                    <el-popconfirm confirm-button-text="Si" cancel-button-text="No" icon-color="#D72C8A"
                        :title="'¿Desea eliminar los elementos seleccionados (' + selectedItems.length + ')?'"
                        @confirm="deleteItems()">
                        <template #reference>
                            <button class="bg-redpad text-white rounded-full px-2 py-px text-sm">Eliminar</button>
                        </template>
                    </el-popconfirm>
                </div>
            </div>
            <UserRow v-for="user in localUsers" :key="user" :user="user" @checked="handleCheckedItem"
                :ref="'user' + user.id" />
            <p v-if="loadingItems" class="text-xs my-4 text-center">
                Cargando <i class="fa-sharp fa-solid fa-circle-notch fa-spin ml-2 text-primary"></i>
            </p>
            <button v-else-if="total_users > 15 && localUsers.length < total_users" @click="fetchItemsByPage"
                class="w-full text-secondary my-4 text-xs mx-auto underline ml-6">Cargar más elementos</button>
            <el-empty v-if="!localUsers.length" description="No hay usuarios para mostrar" />
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import UserRow from "@/Components/MyComponents/User/UserRow.vue";
import Loading from "@/Components/MyComponents/Loading.vue";
import Checkbox from '@/Components/Checkbox.vue';

export default {
    data() {
        return {
            allUsers: false,
            loading: false,
            searchTemp: null,
            search: null,
            selectedItems: [],
            usersBuffer: [], //guarda temporalmente todos los usuarios cargados para solo mostrar las coincidencias cuando se hace busqueda
            localUsers: this.users.data,
            // paginacion dinamica
            currentPage: 1,
            loadingItems: false,
        }
    },
    components: {
        AppLayout,
        PrimaryButton,
        UserRow,
        Checkbox,
        Loading,
    },
    props: {
        users: Object,
        total_users: Number,
    },
    methods: {
        handleSearch() {
            this.search = this.searchTemp;
            this.searchTemp = null;
            if (this.search) {
                this.fetchMatches();
            } else {
                this.showAllUsers();
            }
        },
        showAllUsers() {
            if (this.usersBuffer.length) {
                this.localUsers = this.usersBuffer;
                this.usersBuffer = [];
            }
        },
        handleTagClose() {
            this.search = null;
            this.showAllUsers();
        },
        handleCheckedItem(evt) {
            if (evt.isActive) {
                this.selectedItems.push(evt.id);
            } else {
                const index = this.selectedItems.findIndex(item => item === evt.id);
                this.selectedItems.splice(index, 1);
            }

            if (this.selectedItems.length === this.localUsers.length) {
                this.allUsers = true;
            } else if (this.selectedItems.length < this.localUsers.length && this.allUsers) {
                this.allUsers = false;
            }
        },
        setSelectedPropFromUserRow(value) {
            this.localUsers.forEach(element => {
                if (element.id !== this.$page.props.auth.user.id) {
                    const ref = 'user' + element.id;
                    this.$refs[ref][0].selected = value;
                }
            });
        },
        handleAllUsersChecked() {
            if (this.allUsers) {
                this.selectedItems = this.localUsers.map(user => user.id);
                const index = this.selectedItems.findIndex(item => item === this.$page.props.auth.user.id);
                this.selectedItems.splice(index, 1);
            } else {
                this.selectedItems = [];
            }
            this.setSelectedPropFromUserRow(this.allUsers);
        },
        async fetchItemsByPage() {
            try {
                this.loadingItems = true;
                const response = await axios.get(route('users.get-by-page', this.currentPage));

                if (response.status === 200) {
                    this.localUsers = [...this.localUsers, ...response.data.items];
                    this.currentPage++;
                }
            } catch (error) {
                console.log(error)
            } finally {
                this.loadingItems = false;
            }
        },
        async fetchMatches() {
            try {
                this.loading = true;
                const response = await axios.get(route('users.get-matches', { query: this.search }));

                if (response.status === 200) {
                    if (!this.usersBuffer.length) {
                        this.usersBuffer = this.localUsers;
                    }
                    this.localUsers = response.data.items;
                }
            } catch (error) {
                console.log(error);
            } finally {
                this.loading = false;
            }
        },
        async deleteItems() {
            try {
                const response = await axios.post(route('users.massive-delete', {
                    items_ids: this.selectedItems
                }));

                if (response.status == 200) {
                    this.$notify({
                        title: 'Éxito',
                        message: response.data.message,
                        type: 'success'
                    });

                    // Filtrar el arreglo 'users' excluyendo los elementos con IDs en 'selectedItems'
                    this.localUsers = this.localUsers.filter(user => !this.selectedItems.includes(user.id));
                }
            } catch (err) {
                this.$notify({
                    title: 'Algo salió mal',
                    message: err.message,
                    type: 'error'
                });
                console.log(err);
            }
        },
    },
}
</script>