<template>
    <div class="bg-white p-5 rounded-xl border border-grayD9 flex flex-col justify-between">
        <div>
            <div class="p-1 bg-[#f0e8f8] rounded-full w-fit mb-3">
                 <component :is="icon" class="size-5 text-[#9F53FD]" />
            </div>
            <p class="text-sm text-gray-500">{{ title }}</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ currentValue }}</p>
        </div>
        <div class="text-xs flex items-center mt-2" v-if="percentage !== null">
            <span class="flex items-center px-2 py-0.5 rounded-full text-xs font-medium" :class="percentageChange.class">
                <svg v-if="percentage >= 0" class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                <svg v-else class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                {{ Math.abs(percentage).toFixed(1) }}%
            </span>
            <span class="ml-2 text-gray-500">vs. {{ prevValue }}</span>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    icon: [Object, Function],
    title: String,
    currentValue: [String, Number],
    prevValue: [String, Number],
    percentage: {
        type: Number,
        default: 0
    },
    invertColors: {
        type: Boolean,
        default: false
    }
});

const percentageChange = computed(() => {
    const isPositive = props.percentage >= 0;
    
    // Green is good, Red is bad
    const goodColor = 'bg-green-100 text-green-800';
    const badColor = 'bg-red-100 text-red-800';

    if (props.invertColors) {
        // Inverted: higher is bad (e.g., pause time)
        return { class: isPositive ? badColor : goodColor };
    }
    
    // Normal: higher is good (e.g., efficiency)
    return { class: isPositive ? goodColor : badColor };
});
</script>
