<template>
    <div class="lg:mx-7">
        <Loading v-if="loading" />
        <div v-else>
            <div class="flex space-x-3 mt-5">
                <div v-if="$page.props.jetstream.managesProfilePhotos" class="flex text-sm rounded-full w-10">
                    <img class="size-9 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url"
                        :alt="$page.props.auth.user.name" />
                </div>
                <RichText @submitComment="storeSolution($event)" @content="updatedescription($event)" class="flex-1"
                    hasMedia :userList="users" :disabled="loading || !description" />
            </div>

            <SolutionGlove v-for="(solution, index) in solutions" :key="solution" :solution="solution" :index="index"
                @solution-deleted="solutionDeleted" />
        </div>
    </div>
</template>
<script>
import SolutionGlove from "@/Components/MyComponents/TicketSolution/SolutionGlove.vue";
import RichText from "@/Components/MyComponents/RichText.vue";
import Loading from "@/Components/MyComponents/Loading.vue";

export default {
    data() {
        return {
            solutions: [],
            loading: true,
            description: null,
            users: [],
        }
    },
    components: {
        SolutionGlove,
        RichText,
        Loading,
    },
    methods: {
        solutionDeleted(solutionId) {
            const indexToDelete = this.solutions.findIndex(item => item.id === solutionId);

            if (indexToDelete !== -1) {
                this.solutions.splice(indexToDelete, 1);
            }
        },
        updatedescription(content) {
            this.description = content;
        },
        async storeSolution(solutionMedia) {
            this.loading = true;
            try {
                const response = await axios.post(route("ticket-solutions.store"), {
                    ticketId: this.ticket.data.id,
                    description: this.description,
                    solution_media: solutionMedia,
                });
                if (response.status === 200) {
                    console.log(response);
                    this.$notify({
                        title: "Correcto",
                        message: "Se ha publicado tu solución",
                        type: "success",
                    });
                    this.description = null;
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
                this.fetchSolutions(); //recupera las soluciones de nuevo
            }
        },
        async fetchSolutions() {
            this.loading = true;
            try {
                const response = await axios.get(route("ticket-solutions.fetch-solutions", this.ticket.data.id));
                if (response.status === 200) {
                    this.solutions = response.data.items;
                }
            } catch (error) {
                console.log(error);
            } finally {
                this.loading = false;
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
    },
    mounted() {
        this.fetchSolutions();
        this.fetchUsers();
    }
}
</script>