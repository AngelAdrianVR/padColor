<template>
    <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mx-6 my-6">
        <article v-for="(station, index) in filteredProductions" :key="index"
            class="relative hover:scale-105 transition-all duration-300">
            <div @click="$inertia.visit(route('productions.index', { currentTab: 2, filter: station.name }))"
                class="cursor-pointer">
                <svg viewBox="0 0 536 283" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M0 30.3214C0 13.5754 13.5754 0 30.3214 0H404.286C421.032 0 434.214 13.6296 435.534 30.3236C438.899 72.9049 453.179 86.9777 505.367 90.1843C522.082 91.2113 535.679 104.54 535.679 121.286V252.679C535.679 269.425 522.103 283 505.357 283H30.3215C13.5754 283 0 269.425 0 252.679V30.3214Z"
                        :fill="station.dark" />
                </svg>
                <svg class="absolute top-0 right-[15%]" width="200" height="134" viewBox="0 0 200 134" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M41 130C-36 155.507 171 14.0059 0 0C120 -0.000654822 153 30.5067 51 110.5C12.107 140.119 113.289 91.7396 200 55.0001C137.907 84.2894 104.5 108.965 41 130Z"
                        :fill="station.light" fill-opacity="0.19" />
                </svg>
                <div class="absolute top-0 right-1 rounded-full size-[46px] flex items-center justify-center"
                    :style="{ backgroundColor: station.light, color: station.dark }">
                    <component :is="station.icon" class="size-6" />
                </div>
                <div class="text-white absolute top-[16%] left-5">
                    <h3 class="text-5xl font-bold">{{ station.productions.length }}</h3>
                    <p class="mt-3 text-[26px] font-semibold">{{ station.name }}</p>
                </div>
            </div>
        </article>
    </section>
</template>

<script>
import { stations } from '@/Data/stations';

export default {
    name: 'Home',
    data() {
        return {
            stations: stations,
        }
    },
    computed: {
        // filtrar producciones por estación
        filteredProductions() {
            return this.stations
                .filter(station => this.$page.props.auth.user.permissions.includes(station.permission))
                .map(station => {
                    return {
                        ...station,
                        productions: this.productions.filter(production => production.station == station.name),
                    }
                })
        },
    },
    components: {
    },
    props: {
        productions: Array,
    },
    methods: {
    },
}
</script>
