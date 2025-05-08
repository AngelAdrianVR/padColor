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
                    <InputLabel value="Empresa*" class="ml-3 mb-1" />
                    <el-select class="w-full" v-model="form.employee_properties.company" @change="handleChangeCompany"
                        clearable placeholder="Seleccione" no-data-text="No hay opciones registradas"
                        no-match-text="No se encontraron coincidencias">
                        <el-option v-for="item in companies" :key="item" :label="item" :value="item" />
                    </el-select>
                    <InputError :message="form.errors['employee_properties.company']" />
                </div>
                <div>
                    <InputLabel value="Sucursal*" class="ml-3 mb-1" />
                    <el-select class="w-full" v-model="form.employee_properties.branch" clearable
                        placeholder="Seleccione" no-data-text="Primero selecciona la empresa"
                        no-match-text="No se encontraron coincidencias">
                        <el-option v-for="item in branches" :key="item" :label="item" :value="item" />
                    </el-select>
                    <InputError :message="form.errors['employee_properties.branch']" />
                </div>
                <div>
                    <InputLabel value="Correo electrónico" class="ml-3 mb-1" />
                    <el-input v-model="form.email" placeholder="Escribe el correo electrónico" maxlength="255"
                        type="email" clearable />
                    <InputError :message="form.errors.email" />
                </div>
                <div>
                    <InputLabel value="Número de télefono*" class="ml-3 mb-1" />
                    <el-input v-model="form.phone" placeholder="Escribe aqui tu número" clearable />
                    <InputError :message="form.errors.phone" />
                </div>
                <div class="col-span-full">
                    <InputLabel value="Agregar foto " class="ml-3 mb-1" />
                    <InputFilePreview ref="petImage" @imagen="saveImage" @cleared="clearImage" />
                    <InputError :message="form.errors.image" />
                </div>
                <el-divider content-position="left" class="col-span-full">Roles</el-divider>
                <div class="col-span-full grid grid-cols-2 lg:grid-cols-3 gap-2">
                    <InputLabel v-for="role in roles" :key="role.id" class="flex items-center">
                        <input type="checkbox" v-model="form.roles" :value="role.id"
                            class="rounded text-primary shadow-sm focus:ring-primary bg-transparent" />
                        <span class="ml-2 text-sm">{{ role.name }}</span>
                    </InputLabel>
                </div>
                <InputError :message="form.errors.roles" />

                <div class="col-span-2 text-right mt-4">
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
                company: null,
                branch: null,
                job_position: null,
            },
            roles: [],
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
            companies: [
                'Papel, diseño y color',
                'Padcolor insumos gráficos',
            ],
            branches: [],
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
        roles: Array,
    },
    methods: {
        handleChangeCompany() {
            if (this.form.employee_properties.company == 'Papel, diseño y color')
                this.branches = [
                    'Alfajayucan',
                    'Morelia',
                    'San Luis Potosí',
                    'Acapulco',
                    'Av. del Tigre',
                    'Calle C',
                    'Calle 2'
                ];
            else {
                this.branches = [
                    'Veracruz',
                    'León',
                    'Juárez',
                    'Puebla',
                    'Monterrey',
                    'Federalismo'
                ];
            }
        },
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