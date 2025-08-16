<template>
    <div v-if="links.length > 3" class="mt-6 flex justify-center items-center">
        <div class="flex flex-wrap -mb-1">
            <template v-for="(link, key) in links" :key="key">
                <div v-if="link.url === null"
                    class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-[#666666] border rounded bg-white"
                    v-html="link.label" />
                <Link v-else
                    class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-[#666666] border rounded hover:bg-primarylight focus:border-primary focus:text-primary"
                    :class="{ 'bg-primary text-white': link.active }" :href="link.url" v-html="link.label"
                    preserve-scroll />
            </template>
        </div>
    </div>
</template>
<script>
import { Link } from '@inertiajs/vue3';

export default {
    components: {
        Link,
    },
    props: {
        links: {
            type: Object,
            required: true,
        }
    }
}
</script>