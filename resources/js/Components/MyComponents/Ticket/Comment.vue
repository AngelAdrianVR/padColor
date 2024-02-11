<template>
    <div>
        <div class="flex items-center justify-between mx-3">
            <div class="flex text-xs md:text-sm rounded-full items-center space-x-3">
                <img class="size-7 rounded-full object-cover" :src="comment.user.profile_photo_url"
                :alt="comment.user.name" />
                <p class="text-secondary font-bold text-sm">{{ comment.user.name }}</p>
                <p v-if="!in_edition" class="text-gray99 pl-5">{{ comment.created_at }}</p>
            </div>
            <div v-if="$page.props.auth.user.id === comment.user.id" class="flex items-center space-x-2">
                <PrimaryButton @click="update" class="!py-1" v-if="in_edition">Actualizar</PrimaryButton>
                <i v-if="!in_edition" @click="in_edition = true" class="fa-solid fa-pencil text-xs text-primary rounded-md bg-pink-200 py-1 px-[5px] cursor-pointer"></i>
                <el-tag v-else closable :type="primary" @close="in_edition = false">
                    En edición
                </el-tag>
                <el-popconfirm confirm-button-text="Si" cancel-button-text="No" icon-color="#ff4d4d"
                    title="¿Continuar?" @confirm="deleteItem">
                    <template #reference>
                        <i class="fa-regular fa-trash-can text-xs text-primary rounded-md bg-pink-200 py-1 px-[6px] cursor-pointer"></i>
                    </template>
                </el-popconfirm>
            </div>
        </div>
        <div class="border border-grayD9 rounded-b-xl rounded-r-lg px-3 pt-3 pb-5 mt-1">
            <input v-model="form.body" class="input !border-transparent" type="text" v-if="in_edition" autofocus />
            <p v-else v-html="comment.body"></p>
        </div>
    </div>
</template>

<script>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { useForm } from "@inertiajs/vue3";

export default {
data() {
    const form = useForm({
      body: this.comment.body,
    });
    return {
        form,
        in_edition: false
    }
},
components:{
PrimaryButton
},
props:{
comment: Object
},
emits:['comment-deleted'],
methods:{
    update() {
        this.form.put(route("comments.update", this.comment.id), {
            onSuccess: () => {
                this.$notify({
                title: "Correcto",
                message: "Se ha editado tu comentario",
                type: "success",
                });
                this.in_edition = false;
                this.comment.body = this.form.body;
                this.form.reset();
            },
        });
    },
    async deleteItem() {
    try {
        const response = await axios.delete(route('comments.destroy', this.comment.id));
        if (response.status === 200) {
            this.$emit('comment-deleted', this.comment.id);
            this.$notify({
                title: "Correcto",
                message: "Se ha eliminado tu comentario",
                type: "success",
            });
        }
    } catch (error) {
        console.log(error);
        this.$notify({
            title: "Error de servidor",
            message: "No se pudo eliminar tu comentario. Inténtalo de nuevo más tarde",
            type: "error",
        });
    }
},
}
}
</script>
