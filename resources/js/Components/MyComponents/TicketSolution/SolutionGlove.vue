<template>
    <div @click="optionsDropdown = false" class="flex space-x-5 my-4 relative">
        <!-- foto -->
        <div class="flex text-sm rounded-full w-10">
            <img class="size-9 rounded-full object-cover" :src="solution.user.profile_photo_url"
                :alt="solution.user.name" />
        </div>

        <i @click.stop="optionsDropdown = !optionsDropdown" v-if="$page.props.auth.user.id === solution.user.id"
            class="fa-solid fa-ellipsis-vertical text-xs text-primary py-1 px-3 rounded-full cursor-pointer hover:bg-gray-100 absolute right-5 top-5"></i>

        <!-- Opciones -->
        <div v-if="optionsDropdown"
            class="border border-gray5 flex flex-col space-y-1 rounded-md py-2 absolute w-28 h-auto top-11 right-8 text-xs z-50 bg-white">
            <!-- <p @click="editPublication = true" class="hover:bg-pink-100 cursor-pointer px-2">
                Editar
            </p> -->
            <el-popconfirm confirm-button-text="Si" cancel-button-text="No" icon-color="#ff4d4d" title="¿Continuar?"
                @confirm="deleteItem">
                <template #reference>
                    <p @click.stop class="hover:bg-primary hover:text-white cursor-pointer px-2">Eliminar</p>
                </template>
            </el-popconfirm>
        </div>
        <!-- cuerpo de la solucion -->
        <div class="w-full border border-grayD9 rounded-[30px] lg:rounded-[50px] rounded-tl-none lg:rounded-tl-none py-3 px-3 lg:px-9 mt-2">
            <h1 class="font-bold mb-2">Resolución {{ (index + 1) }}</h1>
            <div class="flex justify-between items-center">
                <p class="text-secondary font-semibold">{{ solution.user?.name }}</p>
                <p class="hidden lg:block text-gray66 text-xs">{{ solution.created_at['isoFormat'] }}<span class="ml-2">({{
                    solution?.created_at['diffForHumans'] }})</span></p>
            </div>
            <p v-html="solution.description"></p>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 mt-4">
                <FileView v-for="file in solution.media" :key="file" :file="file" />
            </div>
            <p class="lg:hidden text-gray66 text-xs text-right mt-3">{{ solution.created_at['isoFormat'] }}<span class="ml-2">({{
                solution?.created_at['diffForHumans'] }})</span></p>
        </div>
    </div>
</template>

<script>
import FileView from "@/Components/MyComponents/Ticket/FileView.vue";

export default {
    data() {
        return {
            optionsDropdown: false
        }
    },
    props: {
        solution: Object,
        index: Number
    },
    components: {
        FileView,
    },
    emits: ['solution-deleted'],
    methods: {
        async deleteItem() {
            try {
                const response = await axios.delete(route('ticket-solutions.destroy', this.solution.id));
                if (response.status === 200) {
                    this.$emit('solution-deleted', this.solution.id);
                    this.$notify({
                        title: "Correcto",
                        message: "Se ha eliminado tu solución",
                        type: "success",
                    });
                }
            } catch (error) {
                console.log(error);
                this.$notify({
                    title: "Error de servidor",
                    message: "No se pudo eliminar tu solución. Inténtalo de nuevo más tarde",
                    type: "error",
                });
            } finally {
                this.optionsDropdown = false
            }
        }
    }
}
</script>