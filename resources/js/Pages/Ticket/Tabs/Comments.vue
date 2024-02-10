<template>
    <div class="lg:mx-7">
        <div v-if="!loading">
            <div class="flex space-x-3 mt-5">
                <div v-if="$page.props.jetstream.managesProfilePhotos" class="flex text-sm rounded-full w-10">
                    <img class="size-9 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url"
                        :alt="$page.props.auth.user.name" />
                </div>
                <RichText @submitComment="storeComment()" @content="updateConversationComment($event)" ref="commentEditor"
                    class="flex-1" withFooter :userList="users" :disabled="loading || !conversationComment" />
            </div>
            <div v-if="conversation?.length > 0" class="mt-10">
                <Comment class="mt-5 mx-9" v-for="comment in conversation" :key="comment" :comment="comment"
                    @comment-deleted="commentDeleted" />
            </div>
            <p v-else class="text-center text-gray-500 text-sm">No hay conversación en este ticket</p>
        </div>

        <!-- estado de carga -->
        <div v-if="loading" class="flex justify-center items-center py-10">
            <i class="fa-solid fa-spinner fa-spin text-4xl text-primary"></i>
        </div>
    </div>
</template>
<script>
export default {

}
</script>