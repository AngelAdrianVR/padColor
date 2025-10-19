<template>
    <div>
        <el-collapse v-model="activeCollapse">
            <el-collapse-item v-for="(station, index) in stationTimes" :key="index" :name="index">
                <template #title>
                    <div class="flex justify-between items-center w-full pr-4 font-semibold text-sm">
                        <span>{{ station.station_name }}</span>
                        <span>Tiempo total: {{ formatDuration(totalStationTime(station)) }}</span>
                    </div>
                </template>
                <div class="px-4 pb-2">
                    <!-- History Timeline -->
                    <ul class="space-y-2 border-l-2 border-gray-200 ml-2">
                        <li v-for="(event, eventIdx) in station.history" :key="eventIdx" class="ml-4">
                            <div class="absolute w-4 h-4 bg-gray-300 rounded-full -left-2 border-2 border-white"></div>
                            <p class="text-xs text-gray-500">{{ formatDateTime(event.timestamp) }}</p>
                            <p class="font-semibold text-xs">
                                {{ event.event }} por {{ users[event.user_id] ?? 'Usuario desconocido' }}
                            </p>
                            <p v-if="event.details" class="text-xs text-gray-600 mt-1">
                                Motivo: <span class="italic">"{{ event.details }}"</span>
                            </p>
                        </li>
                    </ul>
                    <!-- Time Summary for the Station -->
                    <div class="grid grid-cols-3 gap-2 mt-4 text-center">
                         <div class="bg-green-50 border border-green-200 rounded-lg p-2">
                            <p class="text-xs text-green-700">Tiempo efectivo</p>
                            <p class="font-bold text-green-800">{{ formatDuration(station.times.effective_seconds) }}</p>
                        </div>
                        <div class="bg-orange-50 border border-orange-200 rounded-lg p-2">
                            <p class="text-xs text-orange-700">Tiempo en pausa</p>
                            <p class="font-bold text-orange-800">{{ formatDuration(station.times.paused_seconds) }}</p>
                        </div>
                        <div class="bg-gray-100 border border-gray-200 rounded-lg p-2">
                            <p class="text-xs text-gray-700">Tiempo en espera</p>
                            <p class="font-bold text-gray-800">{{ formatDuration(station.times.waiting_seconds) }}</p>
                        </div>
                    </div>
                </div>
            </el-collapse-item>
        </el-collapse>
    </div>
</template>

<script>
import { format, parseISO } from 'date-fns';
import es from 'date-fns/locale/es';

export default {
    props: {
        stationTimes: Array,
        users: Object, // Pass users from parent to map IDs to names
    },
    data() {
        return {
            activeCollapse: [],
        };
    },
    methods: {
        formatDateTime(dateString) {
            if (!dateString) return '-';
            return format(parseISO(dateString), 'dd MMMM yyyy, hh:mm a', { locale: es });
        },
        formatDuration(totalSeconds) {
            if (totalSeconds === null || totalSeconds < 0) return '00:00:00';
            
            const days = Math.floor(totalSeconds / 86400);
            totalSeconds %= 86400;
            const hours = Math.floor(totalSeconds / 3600);
            totalSeconds %= 3600;
            const minutes = Math.floor(totalSeconds / 60);
            const seconds = Math.floor(totalSeconds % 60);

            let result = '';
            if (days > 0) result += `${days}d `;
            result += `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            return result;
        },
        totalStationTime(station) {
            return (station.times.effective_seconds ?? 0) + (station.times.paused_seconds ?? 0) + (station.times.waiting_seconds ?? 0);
        }
    }
}
</script>