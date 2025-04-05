<template>
    <section class="grid grid-cols-3 gap-5">
        <div v-for="(station, index) in filteredProductions" :key="index" class="border p-2 mb-2">
            <h3 class="text-lg font-bold text-gray-700">{{ station.name }}</h3>
            <span>{{ station.productions.length }}</span>
        </div>
    </section>
</template>

<script>

export default {
    name: 'Home',
    data() {
        return {
            stations: [
                {
                    id: 1,
                    name: 'Inspección',
                },
                {
                    id: 2,
                    name: 'Suaje',
                },
                {
                    id: 3,
                    name: 'Corte',
                },
            ],
            productions: [
                {
                    id: 1,
                    name: 'Producción 1',
                    station: 'Inspección',
                },
                {
                    id: 2,
                    name: 'Producción 2',
                    station: 'Inspección',
                },
                {
                    id: 3,
                    name: 'Producción 3',
                    station: 'Suaje',
                },
                {
                    id: 3,
                    name: 'Producción 4',
                    station: 'Corte',
                },
            ],
        }
    },
    computed: {
        // filtrar producciones por estación
        filteredProductions() {
            return this.stations.map(station => {
                return {
                    ...station,
                    productions: this.productions.filter(production => production.station === station.name),
                }
            })
        },
    },
    components: {
    },
    props: {
    },
    methods: {

    },
}
</script>