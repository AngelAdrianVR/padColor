<template>
    <div>
        <h2 class="text-lg font-semibold mb-4">Portal de Clientes - Subir Archivos</h2>

        <!-- Informativo -->
        <el-alert type="info" show-icon :closable="false" class="mb-5">
            <template #title>
                Los archivos deben estar en formato <b>.blade.php o .html</b>. Al subir un archivo, se reemplazará el archivo existente en el servidor para la ruta seleccionada.
            </template>
        </el-alert>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Selector de ruta -->
            <div>
                <InputLabel value="Ruta a actualizar *" class="ml-1 mb-1" />
                <el-select v-model="selectedRoute" placeholder="Seleccione la ruta a actualizar" class="w-full"
                    no-data-text="No hay rutas disponibles">
                    <el-option v-for="route in routes" :key="route.key" :label="route.label" :value="route.key">
                        <div class="flex flex-col">
                            <span class="font-medium">{{ route.label }}</span>
                            <span class="text-gray-400 text-xs">{{ route.url }}</span>
                        </div>
                    </el-option>
                </el-select>
                <p class="text-xs text-gray-500 mt-1 ml-1">
                    <i class="fa-solid fa-circle-info text-secondary mr-1"></i>
                    Selecciona qué página del portal quieres actualizar.
                </p>
            </div>

            <!-- Upload de archivo -->
            <div>
                <InputLabel value="Archivo .blade.php o .html *" class="ml-1 mb-1" />
                <label
                    class="flex items-center justify-center w-full h-12 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition-colors"
                    :class="{ 'border-green-400 bg-green-50': selectedFile, 'border-red-300': fileError }">
                    <input type="file" @change="handleFileChange" ref="fileInput"
                        accept=".php,.html"
                        class="hidden" />
                    <div v-if="!selectedFile" class="text-gray-500 text-sm">
                        <i class="fa-solid fa-cloud-arrow-up mr-2"></i> Haz clic para seleccionar un archivo .blade.php o .html
                    </div>
                    <div v-else class="text-green-700 text-sm flex items-center gap-2">
                        <i class="fa-solid fa-file-code text-green-600"></i>
                        <span class="font-medium">{{ selectedFile.name }}</span>
                        <i class="fa-solid fa-xmark text-red-400 hover:text-red-600 cursor-pointer ml-2"
                            @click.stop="removeFile" title="Quitar archivo"></i>
                    </div>
                </label>
                <p class="text-xs text-gray-400 mt-1 ml-1">
                    <i class="fa-solid fa-circle-info mr-1"></i>
                    Solo se permiten archivos <b>.blade.php o .html</b> (extensiones .php, .html)
                </p>
                <p v-if="fileError" class="text-xs text-red-500 mt-1 ml-1">
                    <i class="fa-solid fa-triangle-exclamation mr-1"></i> {{ fileError }}
                </p>
            </div>
        </div>

        <!-- Botón de subida -->
        <div class="mt-6 flex items-center gap-4">
            <PrimaryButton type="button" @click.prevent="uploadFile" :disabled="!canUpload || uploading">
                <i v-if="uploading" class="fa-solid fa-spinner fa-spin mr-1"></i>
                <i v-else class="fa-solid fa-upload mr-1"></i>
                {{ uploading ? 'Subiendo...' : 'Subir archivo al servidor' }}
            </PrimaryButton>
            <p v-if="successMessage" class="text-green-600 text-sm font-medium">
                <i class="fa-solid fa-circle-check mr-1"></i> {{ successMessage }}
            </p>
            <p v-if="errorMessage" class="text-red-600 text-sm font-medium">
                <i class="fa-solid fa-circle-xmark mr-1"></i> {{ errorMessage }}
            </p>
        </div>

        <p class="text-xs text-gray-400 mt-2">
            <i class="fa-solid fa-circle-info mr-1"></i>
            Tamaño máximo por archivo: <b>15MB</b>. La subida puede demorar algunos minutos dependiendo del tamaño del archivo.
        </p>

        <p v-if="uploading" class="text-xs text-orange-500 mt-1 flex items-center gap-1">
            <i class="fa-solid fa-spinner fa-spin"></i>
            Subiendo archivo, esto puede demorar unos minutos dependiendo del tamaño...
        </p>

        <!-- Vista previa de lo que se va a subir -->
        <div v-if="selectedRoute && selectedFile" class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">Resumen de la operación:</h3>
            <ul class="text-sm text-gray-600 space-y-1">
                <li><b>Ruta pública:</b> <code class="bg-gray-200 px-1 rounded">{{ getRouteUrl(selectedRoute) }}</code></li>
                <li><b>Archivo en servidor:</b> <code class="bg-gray-200 px-1 rounded">resources/views/external/{{ getFileName(selectedRoute) }}</code></li>
                <li><b>Archivo a subir:</b> {{ selectedFile.name }} ({{ formatFileSize(selectedFile.size) }})</li>
                <li class="text-orange-600">
                    <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                    <b>El archivo existente será reemplazado.</b>
                </li>
            </ul>
        </div>

        <!-- Acceso rápido al portal (visible al seleccionar ruta o tras subir) -->
        <div v-if="selectedRoute" class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
            <h3 class="text-sm font-semibold text-blue-800 mb-2">
                <i class="fa-solid fa-globe mr-1"></i> Revisar cambios en el portal
            </h3>
            <p class="text-sm text-blue-700 mb-2">
                {{ uploadSuccess
                    ? 'El archivo se ha subido correctamente. Revisa las actualizaciones haciendo clic en el enlace:'
                    : 'Una vez subido el archivo, puedes revisar los cambios en el portal haciendo clic en el enlace:' }}
            </p>
            <button @click="openPortalLink"
                class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 hover:underline font-medium text-sm bg-transparent border-none cursor-pointer p-0">
                <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                {{ getRouteUrl(selectedRoute) }}
            </button>
        </div>
    </div>
</template>

<script>
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import axios from "axios";

export default {
    components: {
        InputLabel,
        PrimaryButton,
    },
    data() {
        return {
            selectedRoute: null,
            selectedFile: null,
            fileError: null,
            uploading: false,
            successMessage: null,
            errorMessage: null,
            uploadSuccess: false,
            routes: [
                {
                    key: 'pedidos',
                    label: 'Generador de Pedidos',
                    url: '/generador-pedidos',
                    filename: 'pedidos.blade.php',
                },
                {
                    key: 'tutorial',
                    label: 'Tutorial de Pedidos',
                    url: '/tutorial-pedidos',
                    filename: 'tutorial.blade.php',
                },
                {
                    key: 'catalogo',
                    label: 'Catálogo Toda Ocasión 2026',
                    url: '/catalogo-toda-ocasion-2026-san-felipe',
                    filename: 'catalogo.blade.php',
                },
                {
                    key: 'buscador',
                    label: 'Buscador de Clientes',
                    url: '/buscador-clientes',
                    filename: 'buscador.blade.php',
                },
                {
                    key: 'credito',
                    label: 'Solicitud de Crédito',
                    url: '/solicitud-credito',
                    filename: 'credito.blade.php',
                },
                {
                    key: 'guias',
                    label: 'Buscador de Guías',
                    url: '/buscador-guias',
                    filename: 'guias.blade.php',
                },
            ],
        }
    },
    computed: {
        canUpload() {
            return this.selectedRoute && this.selectedFile && !this.fileError;
        },
    },
    watch: {
        selectedRoute() {
            this.uploadSuccess = false;
        },
    },
    methods: {
        handleFileChange(event) {
            this.fileError = null;
            this.successMessage = null;
            this.errorMessage = null;
            this.uploadSuccess = false;

            const file = event.target.files[0];
            if (!file) {
                this.selectedFile = null;
                return;
            }

            // Validar que sea un archivo .php o .html
            const extension = file.name.split('.').pop().toLowerCase();
            if (extension !== 'php' && extension !== 'html') {
                this.fileError = 'Solo se permiten archivos .blade.php o .html (extensiones .php, .html).';
                this.selectedFile = null;
                event.target.value = '';
                return;
            }

            // Validar tamaño máximo 15MB
            if (file.size > 15 * 1024 * 1024) {
                this.fileError = 'El archivo supera el tamaño máximo permitido de 15MB.';
                this.selectedFile = null;
                event.target.value = '';
                return;
            }

            this.selectedFile = file;
        },
        removeFile(preserveSuccess = false) {
            this.selectedFile = null;
            this.fileError = null;
            if (!preserveSuccess) {
                this.uploadSuccess = false;
            }
            this.$refs.fileInput.value = '';
        },
        async uploadFile() {
            if (!this.canUpload || this.uploading) return;

            this.uploading = true;
            this.successMessage = null;
            this.errorMessage = null;

            const formData = new FormData();
            formData.append('route_key', this.selectedRoute);
            formData.append('file', this.selectedFile);

            try {
                const response = await axios.post(route('settings.upload-portal-file'), formData, {
                    headers: { 'Content-Type': 'multipart/form-data' },
                });
                this.successMessage = response.data.message;
                this.removeFile(true);
                this.uploadSuccess = true;
                this.$notify({
                    title: "Correcto",
                    message: response.data.message,
                    type: "success",
                });
            } catch (error) {
                const msg = error.response?.data?.message || 'Error al subir el archivo.';
                this.errorMessage = msg;
                this.$notify({
                    title: "Error",
                    message: msg,
                    type: "error",
                });
            } finally {
                this.uploading = false;
            }
        },
        getRouteUrl(routeKey) {
            const route = this.routes.find(r => r.key === routeKey);
            return route ? route.url : '';
        },
        openPortalLink() {
            const url = this.getRouteUrl(this.selectedRoute);
            if (url) {
                window.open(url, '_blank');
            }
        },
        getFileName(routeKey) {
            const route = this.routes.find(r => r.key === routeKey);
            return route ? route.filename : '';
        },
        formatFileSize(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / 1048576).toFixed(1) + ' MB';
        },
    },
}
</script>