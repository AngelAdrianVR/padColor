<template>
    <AppLayout title="Portal de Clientes">
        <div class="lg:p-9 p-1">

            <UploadPortalFiles :portal-pages="localPages" />

            <el-divider />

            <PortalPagesManager :portal-pages="localPages" @refresh="loadPages" />

        </div>
    </AppLayout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";
import Back from "@/Components/MyComponents/Back.vue";
import UploadPortalFiles from "./Partials/UploadPortalFiles.vue";
import PortalPagesManager from "./Partials/PortalPagesManager.vue";
import axios from "axios";

export default {
    components: {
        AppLayout,
        Back,
        UploadPortalFiles,
        PortalPagesManager,
    },
    props: {
        portalPages: {
            type: Array,
            required: true,
        },
    },
    data() {
        return {
            localPages: [...this.portalPages],
        }
    },
    watch: {
        portalPages: {
            immediate: true,
            handler(val) {
                this.localPages = [...val];
            },
        },
    },
    methods: {
        async loadPages() {
            try {
                const response = await axios.get(route('settings.portal-pages.get'));
                this.localPages = response.data.items;
            } catch {
                // Silently fail — the manager handles its own errors
            }
        },
    },
}
</script>
