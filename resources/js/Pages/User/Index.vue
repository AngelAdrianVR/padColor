<template>
    <AppLayout title="Usuarios">
        <div class="flex justify-between items-center mt-4 mx-10">
            <h1 class="text-lg font-bold">Todos los usuarios</h1>
            <PrimaryButton @click="$inertia.get(route('users.create'))">Agregar usuario</PrimaryButton>
        </div>

        <!-- Buscador -->
        <div class="flex items-center mt-4 mx-2 lg:mx-10">
            <div class="lg:w-1/4 relative lg:mr-12">
                <input v-model="searchTemp" @keyup.enter="search = searchTemp; searchTemp = null" class="input w-full pl-9" placeholder="Buscar usuario" type="search">
                <i class="fa-solid fa-magnifying-glass text-xs text-gray99 absolute top-[10px] left-4"></i>
            </div>
            <el-tag v-if="search" size="large" closable @close="handleTagClose">
                Estas buscando: <b>{{ search }}</b>
            </el-tag>
        </div>

        <!-- usuarios -->
        <div class="mt-7">
            <div class="flex items-center border-b border-grayD9 pb-2">
                <label class="flex items-center ml-7 lg:ml-24">
                    <Checkbox @change="handleAllUsersChecked" v-model:checked="allUsers" name="all" />
                    <span class="ms-2 text-sm font-bold">Todos los usuarios</span>
                </label>
                <div v-if="selectedItems.length" class="lg:ml-36">
                    <el-popconfirm confirm-button-text="Si" cancel-button-text="No" icon-color="#D72C8A"
                        title="¿Desea eliminar los elementos seleccionados?" @confirm="deleteItems()">
                        <template #reference>
                            <button class="bg-redpad text-white rounded-full px-2 py-px text-sm">Eliminar</button>
                        </template>
                    </el-popconfirm>
                </div>
            </div>
            <UserRow v-for="user in users.data" :key="user" :user="user" @checked="handleCheckedItem"
                :ref="'user' + user.id" />
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import UserRow from "@/Components/MyComponents/User/UserRow.vue";
import Checkbox from '@/Components/Checkbox.vue';

export default {
    data() {
        return {
            allUsers: false,
            searchTemp: null,
            search: null,
            selectedItems: [],
        }
    },
    components: {
        AppLayout,
        PrimaryButton,
        UserRow,
        Checkbox,
    },
    props: {
        users: Object
    },
    methods: {
        handleSearch() {
            this.search = this.searchTemp;
            console.log(this.search)
            // this.searchTemp = null;
        },
        handleTagClose() {
            this.search = null;
        },
        handleCheckedItem(evt) {
            if (evt.isActive) {
                this.selectedItems.push(evt.id);
            } else {
                const index = this.selectedItems.findIndex(item => item === evt.id);
                this.selectedItems.splice(index, 1);
            }

            if (this.selectedItems.length === this.users.data.length) {
                this.allUsers = true;
            } else if (this.selectedItems.length < this.users.data.length && this.allUsers) {
                this.allUsers = false;
            }
        },
        setSelectedPropFromUserRow(value) {
            this.users.data.forEach(element => {
                const ref = 'user' + element.id
                this.$refs[ref][0].selected = value;
            });
        },
        handleAllUsersChecked() {
            if (this.allUsers) {
                this.selectedItems = this.users.data.map(user => user.id);
            } else {
                this.selectedItems = [];
            }
            this.setSelectedPropFromUserRow(this.allUsers);
        },
        handleSearch() {

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
                    this.users.data = this.users.data.filter(user => !this.selectedItems.includes(user.id));
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