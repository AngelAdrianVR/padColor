<template>
    <div class="flex items-center border-b border-grayD9 hover:border-primary pb-2 mt-3 mx-3">
        <label class="flex items-center py-1">
            <Checkbox v-model:checked="selected" @change="$emit('checked', { id: item.id, isActive: selected })" />
        </label>
        <section @click="$emit('open')" class="flex flex-col pl-6 cursor-pointer">
            <h2 class="font-bold">{{ item.name }}</h2>
            <div class="flex items-center space-x-3 text-[11px] text-gray66">
                <span>#{{ item.id }}</span>
                <span>•</span>
                <span>{{ formatDate(item.created_at) }}</span>
            </div>
        </section>
    </div>
</template>

<script>
import Checkbox from '@/Components/Checkbox.vue';
import { format, parseISO } from 'date-fns';
import es from 'date-fns/locale/es';

export default {
    data() {
        return {
            selected: false,
        }
    },
    emits: ['checked', 'open'],
    components: {
        Checkbox
    },
    props: {
        item: Object
    },
    methods: {
        formatDate(dateString) {
            return format(parseISO(dateString), 'dd MMM yyyy', { locale: es });
        },
    }
}
</script>
