<template>
    <div class="lg:mx-7">
        <div v-if="!loading">
            <div v-if="ticketHistory?.length > 0">
                <el-timeline>
                    <el-timeline-item v-for="(activity, index) in ticketHistory" :key="index"
                        :timestamp="activity.created_at">
                        <p class="font-bold text-secondary text-sm">{{ activity.user.name + ' ' }}<span
                                class="font-normal">{{ activity.description }}</span></p>
                    </el-timeline-item>
                </el-timeline>
            </div>
            <p v-else class="text-gray-500 text-center text-sm">No hay actividad registrada a este ticket</p>
            <RichText @submitComment="storeComment(taskComponentLocal)" @content="updateConversationComment($event)"
                ref="commentEditor" class="flex-1" withFooter :userList="users"
                :disabled="loading || !conversationComment" />
        </div>
        <div class="mt-10">
            <Comment class="mt-5 mx-9" v-for="comment in conversation" :key="comment" :comment="comment"
                @comment-deleted="commentDeleted" />
        </div>
    </div>
</template>
<script>
export default {

}
</script>