<template>
    <div class="p-1 mt-2 space-y-4">
        <div v-if="loading" class="text-center py-8">
            <Loading />
        </div>
        
        <div v-else-if="children.length === 0" class="text-center text-gray-500 py-12 bg-gray-50 rounded-lg">
             <div class="flex justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-12 text-gray-300">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.39 4.221a.75.75 0 0 1 .13.13l3.418 3.418a.75.75 0 0 1 0 1.06l-2.12 2.122a.75.75 0 0 1-1.06 0L8.34 7.52a.75.75 0 0 1 0-1.06l3.418-3.418a.75.75 0 0 1 .208-.13Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.39 4.221a.75.75 0 0 1 .13.13l3.418 3.418a.75.75 0 0 1 0 1.06l-2.12 2.122a.75.75 0 0 1-1.06 0L8.34 7.52a.75.75 0 0 1 0-1.06l3.418-3.418a.75.75 0 0 1 .208-.13Zm0 9.117a.75.75 0 0 1 .13.13l3.418 3.418a.75.75 0 0 1 0 1.06l-2.12 2.122a.75.75 0 0 1-1.06 0L8.34 16.637a.75.75 0 0 1 0-1.06l3.418-3.418a.75.75 0 0 1 .208-.13Z" />
                </svg>
             </div>
            <p class="font-semibold mt-3">Esta orden aún no ha sido dividida</p>
            <p class="text-xs mt-1">Usa el botón "Dividir Orden" para crear componentes.</p>
        </div>

        <div v-else>
            <!-- Formulario de Movimiento Masivo -->
            <div class="p-4 bg-gray-50 border rounded-lg space-y-3">
                <h3 class="font-semibold text-gray-800">Mover componentes seleccionados</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel value="Siguiente estación*" />
                        <el-select v-model="moveForm.next_station" class="!w-full" placeholder="Selecciona estación" :disabled="selectedChildren.length === 0">
                            <el-option v-for="station in stations" :key="station.name" :label="station.name"
                                :value="station.name" />
                        </el-select>
                        <InputError :message="moveForm.errors.next_station" />
                    </div>
                    <div>
                        <InputLabel value="Máquina (Opcional)" />
                        <el-select v-model="moveForm.machine_id" class="!w-full" placeholder="Selecciona máquina" clearable :disabled="selectedChildren.length === 0">
                            <el-option v-for="machine in machines" :key="machine.id" :label="machine.name"
                                :value="machine.id" />
                        </el-select>
                        <InputError :message="moveForm.errors.machine_id" />
                    </div>
                </div>
                <div>
                    <InputLabel value="Notas (Opcional)" />
                    <el-input v-model="moveForm.notes" type="textarea" :rows="2" placeholder="Notas para el movimiento masivo" :disabled="selectedChildren.length === 0" />
                    <InputError :message="moveForm.errors.notes" />
                </div>
                 <InputError :message="moveForm.errors.general" />
                <PrimaryButton @click="submitMoveAll" :disabled="!moveForm.next_station || selectedChildren.length === 0 || moveForm.processing">
                    <span v-if="moveForm.processing">Moviendo...</span>
                    <span v-else>Mover {{ selectedChildren.length }} componente(s)</span>
                </PrimaryButton>
            </div>

            <!-- Lista de Componentes -->
            <div class="mt-4 space-y-2">
                <div v-for="child in children" :key="child.id" 
                     class="flex items-center space-x-3 p-3 border rounded-lg hover:bg-gray-50 transition-all">
                    <el-checkbox v-model="selectedChildren" :label="child.id" size="large" />
                    <div class="flex-1">
                        <p class="font-semibold">{{ child.part_identifier }}</p>
                        <p class="text-xs text-gray-600">
                            Cantidad: <span class="font-medium">{{ child.part_quantity?.toLocaleString('es-MX') }}</span>
                        </p>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs text-gray-600">Estación Actual:</p>
                        <p class="font-medium">{{ child.station }}</p>
                    </div>
                     <div class="flex-1">
                        <p class="text-xs text-gray-600">Máquina Actual:</p>
                        <p class="font-medium">{{ child.machine?.name ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { useForm } from '@inertiajs/vue3';
import Loading from '@/Components/MyComponents/Loading.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

export default {
    name: 'ProductionChildrenList',
    components: {
        Loading, InputLabel, InputError, PrimaryButton
    },
    props: {
        parentProduction: Object,
        stations: Array,
        machines: Array,
    },
    emits: ['children-updated'],
    data() {
        return {
            loading: false,
            children: [],
            selectedChildren: [],
            moveForm: useForm({
                child_ids: [],
                next_station: null,
                machine_id: null,
                notes: '',
            }),
        };
    },
    methods: {
        async fetchChildren() {
            if (!this.parentProduction) return;
            this.loading = true;
            try {
                const response = await axios.get(route('productions.get-children', this.parentProduction.id));
                this.children = response.data;
            } catch (error) {
                console.error("Error fetching children:", error);
                this.$notify({ title: "Error", message: "No se pudieron cargar los componentes.", type: "error" });
            } finally {
                this.loading = false;
            }
        },
        submitMoveAll() {
            this.moveForm.child_ids = this.selectedChildren;
            this.moveForm.post(route('productions.move-all-children'), {
                preserveScroll: true,
                onSuccess: () => {
                    this.$notify({ title: "Movimiento exitoso", message: "Los componentes han sido movidos.", type: "success" });
                    this.selectedChildren = [];
                    this.moveForm.reset('next_station', 'machine_id', 'notes');
                    this.fetchChildren(); // Recarga la lista
                    this.$emit('children-updated'); // Avisa al modal padre
                },
                onError: (errors) => {
                    this.$notify({ title: "Error", message: "No se pudo completar el movimiento.", type: "error" });
                }
            });
        }
    },
    watch: {
        // Recargar si cambia la prop (aunque el modal usualmente se destruye)
        parentProduction: {
            handler(newVal) {
                if (newVal) {
                    this.fetchChildren();
                }
            },
            immediate: true
        }
    }
}
</script>