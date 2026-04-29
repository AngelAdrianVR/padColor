<template>
    <div class="mt-8 mb-12">
        <h2 class="text-lg font-bold mb-2">Reglas de asignación de tickets</h2>
        <p class="text-sm text-gray-600 mb-8 max-w-4xl">
            Configura a qué departamentos puede asignar tickets un usuario dependiendo de su propio departamento de origen. 
            <br><i>Ejemplo: Si un usuario pertenece a "Comercial" y configuras que solo pueda asignar a "Sistemas" y "Embarques", en su formulario de creación de tickets no verá el resto de los departamentos.</i>
        </p>

        <div v-if="loading" class="text-center py-10">
            <i class="fa-solid fa-circle-notch fa-spin text-primary text-2xl"></i>
            <p class="text-sm mt-2 text-gray-500">Cargando reglas...</p>
        </div>

        <div v-else>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-6">
                <div v-for="dept in departments" :key="dept" class="border border-grayD9 rounded-xl p-4 shadow-md bg-white">
                    <label class="block font-semibold mb-3 text-primary"><i class="fa-solid fa-users mr-2 text-gray-400"></i>{{ dept }}</label>
                    <el-select
                        v-model="rules[dept]"
                        multiple
                        clearable
                        collapse-tags
                        collapse-tags-tooltip
                        placeholder="Selecciona a qué departamentos puede asignar..."
                        class="w-full"
                        no-data-text="No hay departamentos registrados">
                        <el-option v-for="opt in departments" :key="opt" :label="opt" :value="opt" />
                    </el-select>
                </div>
            </div>

            <div class="mt-8 flex justify-end">
                <PrimaryButton :disabled="saving" @click="saveRules">
                    <span v-if="saving"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i>Guardando</span>
                    <span v-else>Guardar configuraciones</span>
                </PrimaryButton>
            </div>
        </div>
    </div>
</template>

<script>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import axios from "axios";

export default {
    components: {
        PrimaryButton,
    },
    data() {
        return {
            loading: true,
            saving: false,
            departments: [
                'Administración', 'Almacén', 'Comercial', 'Compras', 'Contabilidad',
                'Contraloría', 'Crédito y cobranza', 'Dirección', 'Empaques',
                'Inspección', 'Mantenimiento', 'Producción', 'Recursos Humanos',
                'Sistemas', 'Tesorería',
            ],
            rules: {}, // Guardará las reglas dinámicamente ej: { 'Comercial': ['Sistemas', 'Almacén'] }
        };
    },
    methods: {
        async fetchRules() {
            this.loading = true;
            try {
                const response = await axios.get(route('settings.ticket-assignment-rules.get'));
                
                // Inicializamos todos los departamentos con arreglos vacíos por defecto
                this.departments.forEach(dept => {
                    this.rules[dept] = [];
                });

                // Llenamos con lo que viene de la base de datos
                if (response.data.items && response.data.items.length > 0) {
                    response.data.items.forEach(item => {
                        // Si el departamento devuelto aún existe en nuestro catálogo fijo
                        if (this.rules.hasOwnProperty(item.department)) {
                            this.rules[item.department] = item.allowed_departments || [];
                        }
                    });
                }
            } catch (error) {
                console.error("Error obteniendo reglas:", error);
                this.$notify({
                    title: "Error",
                    message: "No se pudieron cargar las configuraciones de asignación.",
                    type: "error",
                });
            } finally {
                this.loading = false;
            }
        },
        async saveRules() {
            this.saving = true;
            try {
                const response = await axios.post(route('settings.ticket-assignment-rules.update'), {
                    rules: this.rules
                });
                
                this.$notify({
                    title: "Éxito",
                    message: response.data.message,
                    type: "success",
                });
            } catch (error) {
                console.error("Error guardando reglas:", error);
                this.$notify({
                    title: "Error",
                    message: "Ocurrió un problema al intentar guardar las configuraciones.",
                    type: "error",
                });
            } finally {
                this.saving = false;
            }
        }
    },
    mounted() {
        this.fetchRules();
    }
};
</script>