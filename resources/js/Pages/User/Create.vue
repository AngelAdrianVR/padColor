<template>
    <AppLayout title="Agregar usuario">
        <div class="lg:p-9 p-1">
            <Back />

            <form @submit.prevent="store"
                class="rounded-lg border border-grayD9 lg:p-5 p-3 lg:w-3/4 mx-auto mt-7 grid grid-cols-2 gap-x-3 gap-y-1">
                <h1 class="font-bold ml-2 col-span-full mb-8">Agregar usuario</h1>
                <div>
                    <InputLabel value="Nombre de usuario*" class="ml-3 mb-1" />
                    <el-input v-model="form.name" placeholder="Escribe el nombre del colaborador " :maxlength="100"
                        clearable />
                    <InputError :message="form.errors.name" />
                </div>
                <div>
                    <InputLabel value="Departamento*" class="ml-3 mb-1" />
                    <el-select class="w-full" v-model="form.employee_properties.department"
                        placeholder="Selecciona el departamento" no-data-text="No hay opciones registradas"
                        no-match-text="No se encontraron coincidencias">
                        <el-option v-for="item in departments" :key="item" :label="item" :value="item" />
                    </el-select>
                    <InputError :message="form.errors['employee_properties.department']" />
                </div>
                <div>
                    <InputLabel value="Puesto*" class="ml-3 mb-1" />
                    <el-input v-model="form.employee_properties.job_position" placeholder="Menciona el puesto"
                        :maxlength="100" clearable />
                    <InputError :message="form.errors['employee_properties.job_position']" />
                </div>
                <div>
                    <InputLabel value="Correo electrónico*" class="ml-3 mb-1" />
                    <el-input v-model="form.email" placeholder="Escribe el correo electrónico" maxlength="255" type="email"
                        clearable />
                    <InputError :message="form.errors.email" />
                </div>
                <div>
                    <InputLabel value="Número de télefono" class="ml-3 mb-1" />
                    <el-input v-model="form.phone" placeholder="Escribe aqui tu número"
                        :formatter="(value) => `${value}`.replace(/(\d{2})(\d{4})(\d{4})/, '$1 $2 $3')"
                        :parser="(value) => value.replace(/\D/g, '')" maxlength="10" clearable />
                    <InputError :message="form.errors.phone" />
                </div>
                <div class="col-span-full">
                    <InputLabel value="Agregar foto " class="ml-3 mb-1" />
                    <InputFilePreview ref="petImage" @imagen="saveImage" @cleared="clearImage" />
                    <InputError :message="form.errors.image" />
                </div>

                <div class="col-span-2 text-right">
                    <PrimaryButton :disabled="form.processing">Guardar</PrimaryButton>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import FileUploader from "@/Components/MyComponents/FileUploader.vue";
import Back from "@/Components/MyComponents/Back.vue";
import InputFilePreview from "@/Components/MyComponents/InputFilePreview.vue";
import { useForm } from "@inertiajs/vue3";

export default {
    data() {
        const form = useForm({
            name: null,
            email: null,
            phone: null,
            image: null,
            employee_properties: {
                department: null,
                job_position: null,
            },
        });

        return {
            form,
            departments: [
                'Administración',
                'Almacén',
                'Comercial',
                'Compras',
                'Contabilidad',
                'Contraloría',
                'Crédito y cobranza',
                'Dirección',
                'Empaques',
                'Inspección',
                'Mantenimiento',
                'Producción',
                'Recursos Humanos',
                'Sistemas',
                'Tesorería',
            ],
        }
    },
    components: {
        AppLayout,
        PrimaryButton,
        FileUploader,
        InputLabel,
        InputError,
        Back,
        InputFilePreview,
    },
    props: {
    },
    methods: {
        store() {
            this.form.post(route("users.store"), {
                onSuccess: () => {
                    this.$notify({
                        title: "Correcto",
                        message: "Se ha agregado el usuario",
                        type: "success",
                    });
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