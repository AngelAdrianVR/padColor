<template>
    <div class="border border-grayD9 rounded-[10px] p-4">
        <section class="flex space-x-3">
            <span class="flex items-center justify-center text-secondary text-xs bg-secondarylight size-7 rounded-lg">
                <i :class="icon"></i>
            </span>
            <div class="flex flex-col w-4/5">
                <h2 class="text-xs text-gray66">{{ title }}</h2>
                <span class="text-lg font-semibold">{{ value }}</span>
            </div>
        </section>
    </div>
</template>
<script>
export default {
    data() {
        return {
            
        }
    },
    props: {
        icon: String,
        title: String,
        value: String,
    }
}
</script>