<template>
    <div class="mt-8 px-2">
        <Loading v-if="loading" class="mt-10" />
        <el-timeline v-else>
            <el-timeline-item v-for="(activity, index) in historical" :key="index" :timestamp="activity.created_at">
                <p class="font-bold text-sm">
                    {{ activity.user.name + ' ' }}
                    <span v-html="activity.description" class="font-normal"></span>
                </p>
            </el-timeline-item>
        </el-timeline>
    </div>
</template>
<script>
import RichText from "@/Components/MyComponents/RichText.vue";
import Loading from "@/Components/MyComponents/Loading.vue";

export default {
    data() {
        return {
            loading: true,
            historical: [],
            users: [],
        }
    },
    components: {
        RichText,
        Loading,
    },
    props: {
        ticketId: String,
    },
    methods: {
        async fetchHistorical() {
            this.loading = true;
            try {
                const response = await axios.get(route("tickets.fetch-history", this.ticketId));
                if (response.status === 200) {
                    this.historical = response.data.items;
                }
            } catch (error) {
                console.log(error);

            } finally {
                this.loading = false;
            }
        },
    },
    mounted() {
        this.fetchHistorical();
    }
}
</script>