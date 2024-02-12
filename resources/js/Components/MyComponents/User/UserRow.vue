<template>
    <div class="flex items-center border-b border-grayD9 hover:border-primary pb-2 mt-3 lg:pl-[95px] px-2">
        <label class="flex items-center py-1">
            <Checkbox v-model:checked="selected" @change="$emit('checked', { id: user.id, isActive: selected })"
                :disabled="$page.props.auth.user.id === user.id" />
        </label>
        <figure class="flex justify-center items-center text-sm rounded-full ml-2">
            <img class="size-9 md:size-14 rounded-full object-cover" :src="user.profile_photo_url" :alt="user.name">
        </figure>
        <section @click="$inertia.get(route('users.show', user.id))" class="flex flex-col pl-4 lg:pl-6 cursor-pointer">
            <div class="flex items-center space-x-1 lg:space-x-3 text-gray66 text-xs font-bold">
                <span>Dpto. {{ user.employee_properties?.department }}</span>
                <span>•</span>
                <span>{{ user.employee_properties?.job_position }}</span>
            </div>
            <h2 class="font-bold">{{ user.name }}</h2>
            <div class="flex items-center space-x-1 lg:space-x-3 text-xs text-gray66">
                <span>#{{ user.id }}</span>
                <span>•</span>
                <span>{{ user.email }}</span>
                <span>•</span>
                <span>{{ user.phone }}</span>
                <span>•</span>
                <span :class="user.is_active ? 'text-greenpad' : 'text-redpad'">{{ user.is_active ? 'Activo' : 'Inactivo'
                }}</span>
            </div>
        </section>
    </div>
</template>

<script>
import Checkbox from '@/Components/Checkbox.vue';
import axios from 'axios';

export default {
    data() {
        return {
            selected: false,
            showResponsibleModal: false,
        }
    },
    emits: ['checked'],
    components: {
        Checkbox
    },
    props: {
        user: Object
    },
    methods: {

    }
}
</script>
