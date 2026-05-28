<template>
    <AppLayout title="Portal de Clientes">
        <div class="lg:p-9 p-1">
            
            <UploadPortalFiles />

        </div>
    </AppLayout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";
import Back from "@/Components/MyComponents/Back.vue";
import UploadPortalFiles from "./Partials/UploadPortalFiles.vue";

export default {
    components: {
        AppLayout,
        Back,
        UploadPortalFiles,
    },
}
</script>
