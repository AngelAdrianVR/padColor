<script>
export default {
    props: {
        show: {
            type: Boolean,
            default: false,
        },
        eventName: {
            type: String,
            default: ''
        }
    },
    emits: ['close', 'save'],
    data() {
        return {
            email: '',
            error: '',
        }
    },
    watch: {
        // Limpiar el estado cuando el modal se abre
        show(newValue) {
            if (newValue) {
                this.email = '';
                this.error = '';
                // Enfocar el input al abrir
                this.$nextTick(() => {
                    this.$refs.emailInput.focus();
                });
            }
        }
    },
    methods: {
        closeModal() {
            this.$emit('close');
        },
        saveEmail() {
            this.error = '';
            // Validación
            if (!this.email) {
                this.error = 'El correo no puede estar vacío.';
                return;
            }
            if (!/^\S+@\S+\.\S+$/.test(this.email)) {
                this.error = 'Por favor, introduce un correo válido.';
                return;
            }
            this.$emit('save', this.email);
        }
    }
}
</script>

<template>
    <!-- Usamos <Transition> para animar la entrada y salida del modal -->
    <Transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0"
        enter-to-class="opacity-100" leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100"
        leave-to-class="opacity-0">
        <div v-if="show" class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50">
            <Transition enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0 transform scale-95" enter-to-class="opacity-100 transform scale-100"
                leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100 transform scale-100"
                leave-to-class="opacity-0 transform scale-95">
                <div v-if="show" class="bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-md" @click.stop>
                    <h3 class="text-xl font-bold mb-4">Añadir Correo Externo</h3>
                    <p class="text-gray-400 mb-4">
                        Este correo recibirá notificaciones para el evento
                        <strong class="text-blue-400">{{ eventName }}</strong>.
                    </p>
                    <div>
                        <input ref="emailInput" v-model="email" @keyup.enter="saveEmail" type="email"
                            placeholder="ejemplo@correo.com"
                            class="w-full bg-gray-700 border-gray-600 rounded-lg p-2 text-white placeholder-gray-400 focus:ring-blue-500 focus:border-blue-500">
                        <p v-if="error" class="text-red-500 text-sm mt-1 h-4">{{ error }}</p>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button @click="closeModal"
                            class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">Cancelar</button>
                        <button @click="saveEmail"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">Guardar</button>
                    </div>
                </div>
            </Transition>
        </div>
    </Transition>
</template>
