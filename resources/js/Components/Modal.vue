<script setup>
import { computed, onMounted, onUnmounted, watch, ref } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: '2xl',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['close']);

watch(() => props.show, () => {
    if (props.show) {
        document.body.style.overflow = 'hidden';
        resetPosition();
    } else {
        document.body.style.overflow = null;
    }
});

const close = () => {
    if (props.closeable) {
        emit('close');
    }
};

const closeOnEscape = (e) => {
    if (e.key === 'Escape' && props.show) {
        close();
    }
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));

onUnmounted(() => {
    document.removeEventListener('keydown', closeOnEscape);
    document.body.style.overflow = null;
});

const maxWidthClass = computed(() => {
    return {
        'sm': 'sm:max-w-sm',
        'md': 'sm:max-w-md',
        'lg': 'sm:max-w-lg',
        'xl': 'sm:max-w-xl',
        '2xl': 'sm:max-w-2xl',
        '3xl': 'sm:max-w-3xl',
    }[props.maxWidth];
});

// --- Draggable Modal Logic ---
const modalContent = ref(null);
const dragging = ref(false);
const position = ref({ x: 0, y: 0 });
const startPosition = ref({ x: 0, y: 0 });

const startDrag = (event) => {
    // Evitar que el arrastre inicie si se hace clic en inputs, botones, etc.
    if (event.target.closest('button, input, select, textarea, a')) {
        return;
    }
    event.preventDefault(); // Evita la selección de texto mientras se arrastra
    dragging.value = true;
    startPosition.value.x = event.clientX - position.value.x;
    startPosition.value.y = event.clientY - position.value.y;
    window.addEventListener('mousemove', onDrag);
    window.addEventListener('mouseup', stopDrag);
};

const onDrag = (event) => {
    if (dragging.value) {
        // Usar requestAnimationFrame para un rendimiento más suave
        requestAnimationFrame(() => {
            position.value.x = event.clientX - startPosition.value.x;
            position.value.y = event.clientY - startPosition.value.y;
        });
    }
};

const stopDrag = () => {
    dragging.value = false;
    window.removeEventListener('mousemove', onDrag);
    window.removeEventListener('mouseup', stopDrag);
};

const resetPosition = () => {
    position.value = { x: 0, y: 0 };
};

const modalStyle = computed(() => ({
    transform: `translate(${position.value.x}px, ${position.value.y}px)`,
}));

</script>

<template>
    <teleport to="body">
        <transition leave-active-class="duration-200">
            <div v-show="show" class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50" scroll-region>
                <transition
                    enter-active-class="ease-out duration-300"
                    enter-from-class="opacity-0"
                    enter-to-class="opacity-100"
                    leave-active-class="ease-in duration-200"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div v-show="show" class="fixed inset-0 transform transition-all" @click="close">
                        <div class="absolute inset-0 bg-gray-500 opacity-75" />
                    </div>
                </transition>

                <transition
                    enter-active-class="ease-out duration-300"
                    enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-active-class="ease-in duration-200"
                    leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                >
                    <!-- Eliminamos 'transition-all' para que no interfiera con el drag -->
                    <div v-show="show" ref="modalContent" :style="modalStyle" class="relative mb-6 bg-white rounded-2xl overflow-hidden shadow-xl transform sm:w-full sm:mx-auto" :class="maxWidthClass">
                        <!-- Draggable Handle -->
                        <div @mousedown="startDrag" class="absolute top-0 left-0 w-full h-5 cursor-move z-10"></div>
                        <div class="relative z-0">
                           <slot v-if="show" />
                        </div>
                    </div>
                </transition>
            </div>
        </transition>
    </teleport>
</template>