<template>
    <AppLayout title="Agregar producto">
        <main class="px-2 lg:px-14">
            <form @submit.prevent="store"
                class="rounded-lg border border-grayD9 lg:p-5 p-3 lg:w-3/4 mx-auto mt-7 lg:grid grid-cols-2 gap-x-3 gap-y-2">
                <div class="flex items-center space-x-2">
                    <Back />
                    <h1 class="font-semibold ml-2 col-span-full">Crear producto</h1>
                </div>
                <h2 class="text-gray-500 font-semibold ml-2 col-span-full my-4">Información del producto</h2>
                <div>
                    <InputLabel value="Código" />
                    <el-input v-model="form.code" placeholder="Escribe el código del producto" type="text" />
                    <InputError :message="form.errors.code" />
                </div>

                <div>
                    <InputLabel value="Fecha de creación" />
                    <el-date-picker class="!w-full" v-model="form.created_at" type="date" placeholder="dd/mm/aa"
                        value-format="YYYY-MM-DD" format="DD/MM/YYYY" />
                    <InputError :message="form.errors.created_at" />
                </div>

                <!-- <div>
                    <InputLabel value="Sucursal" />
                    <el-select v-model="form.branch" placeholder="" class="!w-full">
                        <el-option label="PadColor" value="PadColor" />
                        <el-option label="Papel diseño y color" value="Papel diseño y color" />
                    </el-select>
                    <InputError :message="form.errors.branch" />
                </div> -->

                <div class="col-span-full">
                    <InputLabel value="Nombre del producto*" />
                    <el-input v-model="form.name" placeholder="Escribe el nombre del producto" type="text" />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="col-span-full">
                    <InputLabel value="Descripción" />
                    <el-input v-model="form.description" :autosize="{ minRows: 3, maxRows: 5 }" type="textarea"
                        :maxlength="500" placeholder="Agrega una descripción al producto" show-word-limit clearable />
                    <InputError :message="form.errors.description" />
                </div>

                <div>
                    <InputLabel value="Temporada*" />
                    <el-select v-model="form.season" placeholder="Selecciona la temporada" class="!w-full">
                        <el-option v-for="item in seasons" :key="item" :label="item" :value="item" />
                    </el-select>
                    <InputError :message="form.errors.season" />
                </div>

                <div>
                    <InputLabel value="Material" />
                    <el-input v-model="form.material" placeholder="Escribe el material" type="text" />
                    <!-- <el-select v-model="form.material" placeholder="Selecciona" class="!w-full">
                        <el-option v-for="material in materials" :key="material" :label="material" :value="material" />
                    </el-select> -->
                    <InputError :message="form.errors.material" />
                </div>

                <div class="mt-4 col-span-full">
                    <InputLabel value="Archivo adjunto" />
                    <FileUploader @files-selected="this.form.media = $event" :multiple="true" />
                </div>

                <div class="mt-4">
                    <InputLabel value="Imagen del producto" class="ml-3 mb-1" />
                    <InputFilePreview @imagen="saveImage" @cleared="clearImage" />
                </div>

                <!-- <div>
                    <InputLabel value="Unidad*" />
                    <el-select v-model="form.measure_unit" placeholder="Selecciona unidad de medida" class="!w-full">
                        <el-option v-for="item in measure_units" :key="item" :label="item" :value="item" />
                    </el-select>
                    <InputError :message="form.errors.measure_unit" />
                </div> -->

                <!-- <div>
                    <InputLabel value="Alto*" />
                    <el-input v-model="form.height" placeholder="Ej.25" type="text"
                        :formatter="(value) => `${value}`.replace(/\B(?=(\d{3})+(?!\d))/g, ',')"
                        :parser="(value) => value.replace(/\$\s?|(,*)/g, '')">
                        <template #append>cm</template>
                    </el-input>
                    <InputError :message="form.errors.height" />
                </div>
                <div>
                    <InputLabel value="Ancho*" />
                    <el-input v-model="form.width" placeholder="Ej.15" type="text"
                        :formatter="(value) => `${value}`.replace(/\B(?=(\d{3})+(?!\d))/g, ',')"
                        :parser="(value) => value.replace(/\$\s?|(,*)/g, '')">
                        <template #append>cm</template>
                    </el-input>
                    <InputError :message="form.errors.width" />
                </div>
                <div>
                    <InputLabel value="Largo*" />
                    <el-input v-model="form.large" placeholder="Ej.40" type="text"
                        :formatter="(value) => `${value}`.replace(/\B(?=(\d{3})+(?!\d))/g, ',')"
                        :parser="(value) => value.replace(/\$\s?|(,*)/g, '')">
                        <template #append>cm</template>
                    </el-input>
                    <InputError :message="form.errors.large" />
                </div> -->

                <div class="col-span-2 text-right mt-4">
                    <PrimaryButton :disabled="form.processing">
                        <i v-if="form.processing" class="fa-solid fa-circle-notch fa-spin mr-2"></i>
                        <span>Crear producto</span>
                    </PrimaryButton>
                </div>
            </form>
        </main>
    </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import Back from '@/Components/MyComponents/Back.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import FileUploader from "@/Components/MyComponents/FileUploader.vue";
import InputFilePreview from "@/Components/MyComponents/InputFilePreview.vue";
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { format } from "date-fns";

export default {
    data() {
        const form = useForm({
            name: null,
            code: null,
            description: null,
            season: null,
            // branch: 'PadColor',
            // measure_unit: null,
            // width: null,
            // large: null,
            // height: null,
            material: null,
            stock: null,
            min_stock: null,
            max_stock: null,
            price: null,
            created_at: format(new Date(), "yyyy-MM-dd"), // Establece la fecha de hoy por defecto
            media: null, //archivos adjuntos al producto    
            image: null, //imagen de portada del producto
        });

        return {
            form,
            seasons: [
                'Amistad',
                'Escolar',
                'Fiestas patrias',
                'Monarca',
                'Muestos',
                'Navidad',
                'Servicios',
                'Toda ocasión',
                'UUPZ',
            ],
            measure_units: [
                'Pieza',
                'Paquete',
            ],
            materials: [
                'Material 1',
                'Material 2',
                'Material 3',
                'Material 4',
            ],
        }
    },
    components: {
        Back,
        AppLayout,
        InputLabel,
        InputError,
        FileUploader,
        PrimaryButton,
        InputFilePreview,
    },
    props: {
    },
    methods: {
        store() {
            this.form.post(route('products.store'), {
                onSuccess: () => {
                    this.form.reset();
                    this.$notify({
                        title: 'Producto agregado',
                        message: '',
                        type: 'success',
                    });
                },
                onError: () => {
                    console.log(this.form.errors);
                },
            });
        },
        saveImage(image) {
            this.form.image = image;
        },
        clearImage() {
            this.form.image = null;
        },
    }
}
</script>
