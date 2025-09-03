<template>
    <div>
        <!-- El componente el-upload ahora es visible y usa la funcionalidad de arrastrar -->
        <el-upload ref="uploadRef" drag action="#" :auto-upload="false" :on-change="handleFileChange"
            :show-file-list="false" multiple class="custom-uploader">
            <div class="el-upload__text text-center">
                <p class="text-gray-600">
                    Arrastra o haz clic aquí para subir
                </p>
                <div class="el-upload__tip text-xs text-gray-500 mt-2">
                    Puedes seleccionar múltiples archivos (Max 10MB c/u)
                </div>
            </div>
        </el-upload>

        <!-- Lista personalizada de archivos pendientes de subir -->
        <div v-if="localFiles.length > 0" class="mt-4 space-y-2">
            <p class="text-sm font-semibold text-gray-600">Archivos nuevos que se guardarán:</p>
            <div v-for="(file, index) in localFiles" :key="file.uid"
                class="flex items-center justify-between p-2 border rounded-md bg-gray-50 hover:bg-red-50 transition-colors group">
                <div class="flex items-center min-w-0">
                    <PaperClipIcon class="w-5 h-5 mr-3 text-gray-400 flex-shrink-0" />
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-800 truncate">{{ file.name }}</p>
                        <p class="text-xs text-gray-500">{{ (file.size / 1024).toFixed(2) }} KB</p>
                    </div>
                </div>
                <el-button type="danger" :icon="Close" text circle @click="removeFile(index)"
                    class="opacity-50 group-hover:opacity-100" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { ElUpload, ElButton } from 'element-plus';
import { Close } from '@element-plus/icons-vue';
import { PaperClipIcon } from '@heroicons/vue/24/outline';

// --- Props y Emits para v-model ---
const props = defineProps({
    files: {
        type: Array,
        default: () => [],
    },
});
const emit = defineEmits(['update:files']);

// --- Referencias y Estado Interno ---
const uploadRef = ref(null);
const localFiles = ref([]);

// --- Sincronización con el Padre ---
watch(() => props.files, (newFiles) => {
    if (newFiles.length === 0 && localFiles.value.length > 0) {
        localFiles.value = [];
        if (uploadRef.value) {
            uploadRef.value.clearFiles();
        }
    }
});

// --- Métodos ---
const handleFileChange = (uploadFile, uploadFiles) => {
    localFiles.value = uploadFiles;
    emit('update:files', uploadFiles.map(f => f.raw));
};

const removeFile = (indexToRemove) => {
    const fileToRemove = localFiles.value[indexToRemove];
    localFiles.value.splice(indexToRemove, 1);
    if (uploadRef.value) {
        uploadRef.value.handleRemove(fileToRemove);
    }
    emit('update:files', localFiles.value.map(f => f.raw));
};

// Exponemos un método para que el padre pueda limpiar la lista si es necesario.
const clear = () => {
    localFiles.value = [];
    if (uploadRef.value) {
        uploadRef.value.clearFiles();
    }
    emit('update:files', []);
}
defineExpose({ clear });
</script>

