<template>
    <AppLayout title="Crear ticket">
        <div class="lg:p-9 p-1">
            <Back />
            
            <TicketForm 
                :categories="categories" 
                :users="users" 
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
        categories: Array,
        users: Array,
    },
}
</script>