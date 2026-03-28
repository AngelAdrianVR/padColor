<template>
    <AppLayout title="Editar ticket">
        <div class="lg:p-9 p-1">
            <Back />

            <TicketForm 
                :ticket="ticket"
                :categories="categories" 
                :users="users" 
                :media="media"
                is-edit
            />

        </div>
    </AppLayout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";
import Back from "@/Components/MyComponents/Back.vue";
import TicketForm from "./Partials/TicketForm.vue";

export default {
    components: {
        AppLayout,
        Back,
        TicketForm
    },
    props: {
        ticket: Object,
        categories: Array,
        users: Array,
        media: Array,
    },
}
</script>