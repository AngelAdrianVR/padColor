<template>
    <div class="mt-8">
        <Loading v-if="loading" class="mt-10" />
        <div v-else>
            <div class="flex space-x-3 mt-5">
                <div v-if="$page.props.jetstream.managesProfilePhotos" class="flex text-sm rounded-full w-10">
                    <img class="size-9 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url"
                        :alt="$page.props.auth.user.name" />
                </div>
                <RichText @submitComment="storeComment()" @content="updateComment($event)" ref="commentEditor"
                    class="flex-1" withFooter :userList="users" :disabled="loading || !comment" />
            </div>
            <Comment class="mt-5 mx-9" v-for="comment in conversation" :key="comment" :comment="comment"
                @comment-deleted="commentDeleted" />
        </div>
    </div>
</template>
<script>
import Comment from "@/Components/MyComponents/Ticket/Comment.vue";
import RichText from "@/Components/MyComponents/RichText.vue";
import Loading from "@/Components/MyComponents/Loading.vue";

export default {
    data() {
        return {
            conversation: [],
            loading: true,
            comment: null,
            users: [],
        }
    },
    components: {
        Comment,
        RichText,
        Loading,
    },
    props: {
        ticketId: String,
    },
    methods: {
        updateComment(content) {
            this.comment = content;
        },
        commentDeleted(commentId) {
            const indexToDelete = this.conversation.findIndex(item => item.id === commentId);

            if (indexToDelete !== -1) {
                this.conversation.splice(indexToDelete, 1);
            }
        },
        async storeComment() {
            const editor = this.$refs.commentEditor;
            this.loading = true;
            try {
                const response = await axios.post(route("tickets.comment", this.ticketId), {
                    comment: this.comment,
                    mentions: editor.mentions,
                });
                if (response.status === 200) {
                    // this.taskComponentLocal?.comments.push(response.data.item);
                }
            } catch (error) {
                console.log(error);
            } finally {
                this.loading = false;
                this.comment = null;
                // editor.clearContent();
                this.fetchConversation(); //recupera los comentarios de nuevo
            }
        },
        async fetchUsers() {
            this.loading = true;
            try {
                const response = await axios.get(route("users.get-all"));
                if (response.status === 200) {
                    this.users = response.data.items;
                }
            } catch (error) {
                console.log(error);
            } finally {
                this.loading = false;
            }
        },
        async fetchConversation() {
            this.loading = true;
            try {
                const response = await axios.get(route("tickets.fetch-conversation", this.ticketId));
                if (response.status === 200) {
                    this.conversation = response.data.items;
                }
            } catch (error) {
                console.log(error);
                this.$notify({
                    title: "Error de servidor",
                    message: "Hubo un problema al publicar tu solución. Inténta más tarde",
                    type: "error",
                });

            } finally {
                this.loading = false;
            }
        },
    },
    mounted() {
        this.fetchConversation();
        this.fetchUsers();
    }
}
</script>