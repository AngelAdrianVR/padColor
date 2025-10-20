<template>
    <div class="bg-white p-5 rounded-xl shadow">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-gray-100 rounded-full p-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-gray-500">
                    <path stroke-linecap="round" stroke-linejoin="round" :d="iconPath" />
                </svg>
            </div>
        </div>
        <div class="mt-4">
            <p class="text-sm text-gray-500 truncate">{{ title }}</p>
            <p class="text-2xl font-bold text-gray-800">{{ currentValue }}</p>
            <div class="flex items-center text-xs mt-1" :class="percentage >= 0 ? (invertColors ? 'text-red-500' : 'text-green-500') : (invertColors ? 'text-green-500' : 'text-red-500')">
                <svg v-if="percentage >= 0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4 mr-1">
                    <path fill-rule="evenodd" d="M10 17a.75.75 0 01-.75-.75V5.612L5.28 9.68a.75.75 0 01-1.06-1.06l5.25-5.25a.75.75 0 011.06 0l5.25 5.25a.75.75 0 11-1.06 1.06L10.75 5.612V16.25A.75.75 0 0110 17z" clip-rule="evenodd" />
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4 mr-1">
                     <path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.638l3.97-4.07a.75.75 0 111.06 1.06l-5.25 5.25a.75.75 0 01-1.06 0l-5.25-5.25a.75.75 0 111.06-1.06l3.97 4.07V3.75A.75.75 0 0110 3z" clip-rule="evenodd" />
                </svg>
                <span>{{ percentage.toFixed(1) }}%</span>
                <span class="text-gray-400 ml-2">vs. {{ prevValue }}</span>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    iconPath: String,
    title: String,
    currentValue: [String, Number],
    prevValue: [String, Number],
    percentage: Number,
    invertColors: {
        type: Boolean,
        default: false
    }
});
</script>