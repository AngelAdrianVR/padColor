<template>
    <AppLayout title="Editar usuario">
        <div class="lg:p-9 p-1">
            <Back />

            <form @submit.prevent="update"
                class="rounded-lg border border-grayD9 lg:p-5 p-3 lg:w-3/4 mx-auto mt-7 grid grid-cols-2 gap-x-3 gap-y-1">
                <h1 class="font-bold ml-2 col-span-full mb-8">Editar usuario</h1>
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
                    <InputLabel value="Sucursal*" class="ml-3 mb-1" />
                    <el-select class="w-full" v-model="form.employee_properties.branch" clearable placeholder="Seleccione"
                        no-data-text="No hay opciones registradas" no-match-text="No se encontraron coincidencias">
                        <el-option v-for="item in branches" :key="item" :label="item" :value="item" />
                    </el-select>
                    <InputError :message="form.errors['employee_properties.branch']" />
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
                <div>
                    <InputLabel value="Estado" class="ml-3 mb-1" />
                    <el-select class="w-full" v-model="form.is_active" placeholder="Selecciona el departamento"
                        no-data-text="No hay opciones registradas" no-match-text="No se encontraron coincidencias">
                        <el-option v-for="(item, index) in ['Inactivo', 'Activo']" :key="item" :label="item"
                            :value="index" />
                    </el-select>
                </div>
                <div class="col-span-full">
                    <InputLabel value="Agregar foto " class="ml-3 mb-1" />
                    <InputFilePreview ref="userImage" @imagen="saveImage" @cleared="clearImage" />
                    <InputError :message="form.errors.image" />
                </div>
                <!-- roles -->
                <el-divider content-position="left" class="col-span-full">Roles</el-divider>
                <br>
                <div class="col-span-full grid grid-cols-2 lg:grid-cols-3 gap-2">
                    <InputLabel v-for="role in roles" :key="role.id" class="flex items-center">
                        <input type="checkbox" v-model="form.roles" :value="role.id"
                            class="rounded text-primary shadow-sm focus:ring-primary bg-transparent" />
                        <span class="ml-2 text-sm">{{ role.name }}</span>
                    </InputLabel>
                </div>
                <InputError :message="form.errors.roles" />

                <div class="col-span-2 text-right">
                    <PrimaryButton type="submit" :disabled="form.processing">Guardar</PrimaryButton>
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
            name: this.user.name,
            email: this.user.email,
            phone: this.user.phone,
            is_active: this.user.is_active,
            image: null,
            employee_properties: {
                department: this.user.employee_properties?.department,
                job_position: this.user.employee_properties?.job_position,
                branch: this.user.employee_properties?.branch,
            },
            roles: this.user_roles,
            selectedImage: this.user.profile_photo_url
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
            branches: [
                'Alfajayucan',
                'Morelia',
                'San Luis Potosí',
                'Acapulco',
                'Av. del Tigre',
                'Calle C',
                'Calle 2',
                'Veracruz',
                'León',
                'Juárez',
                'Puebla',
                'Monterrey',
                'Federalismo',
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
        user: Object,
        roles: Array,
        user_roles: Array,
    },
    methods: {
        update() {
            if (this.form.image) {
                this.form.post(route("users.update-with-media", this.user.id), {
                    method: '_put',
                    onSuccess: () => {
                        this.$notify({
                            title: "Correcto",
                            message: "Usuario actualizado",
                            type: "success",
                        });

                    },
                });
            } else {
                this.form.put(route("users.update", this.user.id), {
                    onSuccess: () => {
                        this.$notify({
                            title: "Correcto",
                            message: "Usuario actualizado",
                            type: "success",
                        });

                    },
                });
            }
        },
        saveImage(image) {
            this.form.image = image;
        },
        clearImage() {
            this.form.image = null;

            // se eliminó la foto
            this.form.selectedImage = null;
        },
    },
    mounted() {
        // cargar imagen de usuario en componente
        this.$refs.userImage.image = this.user.profile_photo_url;
    }
}
</script>