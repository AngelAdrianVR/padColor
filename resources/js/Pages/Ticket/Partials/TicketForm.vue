<template>
    <div>
        <form @submit.prevent="submit"
            class="rounded-lg border border-grayD9 lg:p-5 p-3 lg:w-1/2 mx-auto mt-7 lg:grid grid-cols-2 gap-x-3">
            <h1 class="font-bold ml-2 col-span-2">{{ isEdit ? 'Editar ticket' : 'Crear ticket' }}</h1>
            
            <div class="relative mt-5 col-span-2">
                <InputLabel value="Categoría*" class="ml-3 mb-1" />
                <p @click="showCategoryModal = true"
                    v-if="$page.props.auth.user.permissions.includes('Crear categoria')"
                    class="text-secondary text-xs cursor-pointer absolute md:right-2 right-0 top-[2px]">Agregar
                    categoría</p>
                <el-select class="w-full" v-model="form.category_id" clearable placeholder="Seleccione"
                    no-data-text="No hay opciones registradas" no-match-text="No se encontraron coincidencias">
                    <el-option v-for="item in categories" :key="item.id" :label="item.name" :value="item.id" />
                </el-select>
                <InputError :message="form.errors.category_id" />
            </div>

            <!-- TIPO DE ASIGNACIÓN -->
            <div class="mt-5 col-span-2">
                <InputLabel value="Tipo de Asignación" class="ml-3 mb-1" />
                <el-radio-group v-model="assignmentType" @change="handleAssignmentChange" class="ml-3">
                    <el-radio label="user">Usuario específico</el-radio>
                    <el-radio label="department">Departamento completo</el-radio>
                </el-radio-group>
            </div>

            <!-- SELECT USUARIO (AGRUPADO POR DEPARTAMENTO) -->
            <div class="mt-3 col-span-2" v-if="assignmentType === 'user'">
                <InputLabel value="Responsable del Ticket (Usuario)" class="ml-3 mb-1" />
                <el-select class="w-full" v-model="form.responsible_id" clearable filterable placeholder="Seleccione un usuario"
                    no-data-text="No hay opciones registradas" no-match-text="No se encontraron coincidencias">
                    <el-option-group
                        v-for="group in groupedUsers"
                        :key="group.label"
                        :label="group.label">
                        <el-option v-for="user in group.options" :key="user.id" :label="user.name" :value="user.id">
                            <figure style="float: left">
                                <img class="object-cover bg-no-repeat size-7 rounded-full mt-1"
                                    :src="user.profile_photo_url" alt="" />
                            </figure>
                            <span class="ml-2">{{ user.name }}</span>
                        </el-option>
                    </el-option-group>
                </el-select>
                <InputError :message="form.errors.responsible_id" />
                <p class="text-xs text-gray-500 mt-2 ml-3">
                    <i class="fa-solid fa-circle-info text-secondary mr-1"></i> 
                    Se notificará por correo y sistema al usuario seleccionado.
                </p>
            </div>

            <!-- SELECT DEPARTAMENTO -->
            <div class="mt-3 col-span-2" v-if="assignmentType === 'department'">
                <InputLabel value="Departamento asignado" class="ml-3 mb-1" />
                <el-select class="w-full" v-model="form.department" clearable filterable placeholder="Seleccione un departamento"
                    no-data-text="No hay opciones registradas" no-match-text="No se encontraron coincidencias">
                    <el-option v-for="dept in departments" :key="dept" :label="dept" :value="dept">
                    </el-option>
                </el-select>
                <InputError :message="form.errors.department" />
                <p class="text-xs text-gray-500 mt-2 ml-3">
                    <i class="fa-solid fa-circle-info text-secondary mr-1"></i> 
                    Se notificará por correo y sistema a todos los integrantes de este departamento.
                </p>
            </div>

            <div class="mt-3">
                <InputLabel value="Sucursal *" class="ml-3 mb-1" />
                <el-select class="w-full" v-model="form.branch" clearable placeholder="Seleccione"
                    no-data-text="No hay opciones registradas" no-match-text="No se encontraron coincidencias">
                    <el-option v-for="item in branches" :key="item" :label="item" :value="item" />
                </el-select>
                <InputError :message="form.errors.branch" />
            </div>
            
            <div class="mt-3">
                <InputLabel value="Tipo de ticket *" class="ml-3 mb-1" />
                <el-select class="w-full" v-model="form.ticket_type" clearable placeholder="Seleccione"
                    no-data-text="No hay opciones registradas" no-match-text="No se encontraron coincidencias">
                    <el-option v-for="item in ['Solicitud o servicio', 'Soporte o incidencia']" :key="item" :label="item" :value="item" />
                </el-select>
                <InputError :message="form.errors.ticket_type" />
            </div>

            <div class="mt-3 col-span-2">
                <InputLabel value="Título del ticket*" class="ml-3 mb-1" />
                <el-input v-model="form.title" placeholder="Escribe el nombre del ticket" :maxlength="100"
                    clearable />
                <InputError :message="form.errors.title" />
            </div>
            
            <div class="mt-3 col-span-2">
                <InputLabel value="Descripción" class="ml-3 mb-1" />
                <el-input v-model="form.description" :autosize="{ minRows: 3, maxRows: 5 }" type="textarea"
                    :maxlength="500" show-word-limit clearable />
                <InputError :message="form.errors.description" />
            </div>
            
            <!-- Campos Status -->
            <div class="mt-3">
                <div class="flex items-center space-x-3 ml-3 mb-1">
                    <InputLabel value="Estatus" class="" />
                    <i v-if="form.status" :class="getStatusColor()" class="fa-solid fa-circle text-[8px]"></i>
                </div>
                <!-- El status se bloquea si se está creando, o si se edita pero no tiene permiso -->
                <el-select v-model="form.status" placeholder="Seleccione"
                    :disabled="!isEdit || !$page.props.auth.user.permissions.includes('Editar status de tickets')"
                    no-data-text="No hay opciones registradas" no-match-text="No se encontraron coincidencias">
                    <el-option v-for="item in statuses" :key="item.label" :label="item.label" :value="item.label">
                        <p class="w-4" style="float: left">
                            <span v-html="item.icon"></span>
                        </p>
                        <span class="ml-2">{{ item.label }}</span>
                    </el-option>
                </el-select>
                <InputError :message="form.errors.status" />
            </div>
            
            <!-- Prioridad -->
            <div class="mt-3">
                <div class="flex items-center space-x-3 ml-3 mb-1">
                    <InputLabel value="Prioridad" class="" />
                    <i v-if="form.priority" :class="getPriorityColor()" class="fa-solid fa-circle text-[8px]"></i>
                </div>
                <el-select class="w-full" v-model="form.priority" clearable placeholder="Seleccione"
                    no-data-text="No hay opciones registradas" no-match-text="No se encontraron coincidencias">
                    <el-option v-for="item in priorities" :key="item.label" :label="item.label" :value="item.label">
                        <p style="float: left">
                            <i :class="item.color" class="fa-solid fa-circle text-[8px]"></i>
                        </p>
                        <span class="ml-2">{{ item.label }}</span>
                    </el-option>
                </el-select>
                <InputError :message="form.errors.priority" />
            </div>
            
            <!-- Vencimiento -->
            <div class="mt-3">
                <InputLabel value="Fecha de vencimiento" class="ml-3 mb-1" />
                <el-date-picker class="!w-full" v-model="form.expired_date" type="date" placeholder="Seleccione"
                    :disabled-date="disabledDate" 
                    :disabled="!isEdit || !$page.props.auth.user.permissions.includes('Editar fecha de expiracion de tickets')" />
                <InputError :message="form.errors.expired_date" />
            </div>
            
            <div class="ml-2 mt-3 col-span-full">
                <FileUploader @files-selected="this.form.media = $event" />
            </div>

            <!-- Archivos actuales (Solo al Editar) -->
            <div class="col-span-full mt-2" v-if="isEdit && media?.length">
                <li v-for="file in media" :key="file.id" class="flex items-center justify-between text-sm">
                    <a :href="file.original_url" target="_blank" class="flex items-center text-blue-600 hover:underline">
                        <i :class="getFileTypeIcon(file.file_name)"></i>
                        <span class="ml-2">{{ file.file_name }}</span>
                    </a>
                </li>
            </div>
            
            <div class="col-span-2 text-right mt-5">
                <PrimaryButton :disabled="form.processing">{{ isEdit ? 'Guardar cambios' : 'Guardar' }}</PrimaryButton>
            </div>
        </form>

        <!-- agregar categoria modal -->
        <Modal :show="showCategoryModal" @close="showCategoryModal = false">
            <div class="mx-5 my-4 space-y-4 relative">
                <div @click="showCategoryModal = false"
                    class="cursor-pointer w-5 h-5 rounded-full border-2 border-black flex items-center justify-center absolute top-0 -right-2">
                    <i class="fa-solid fa-xmark"></i>
                </div>

                <h1 class="font-bold">Agregar nueva categoría</h1>

                <div class="mt-3 col-span-2">
                    <InputLabel value="Nombre de la categoría*" class="ml-3 mb-1" />
                    <el-input v-model="categoryForm.name" placeholder="Escribe el nombre de la categoría"
                        :maxlength="100" clearable />
                    <InputError :message="categoryForm.errors.name" />
                </div>

                <div class="flex justify-end space-x-1 pt-5 pb-1">
                    <CancelButton @click="categoryForm.reset(); showCategoryModal = false">Cancelar</CancelButton>
                    <PrimaryButton :disabled="categoryForm.processing" @click="storeCategory">Guardar</PrimaryButton>
                </div>
            </div>
        </Modal>
    </div>
</template>

<script>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import CancelButton from "@/Components/MyComponents/CancelButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import FileUploader from "@/Components/MyComponents/FileUploader.vue";
import Modal from "@/Components/Modal.vue";
import { addDays, format, isWeekend } from 'date-fns';
import { useForm } from "@inertiajs/vue3";

export default {
    components: {
        PrimaryButton, CancelButton, FileUploader,
        InputLabel, InputError, Modal
    },
    props: {
        ticket: {
            type: Object,
            default: null,
        },
        isEdit: {
            type: Boolean,
            default: false,
        },
        categories: Array,
        users: Array,
        media: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        const form = useForm({
            category_id: this.ticket?.category_id || null,
            responsible_id: this.ticket?.responsible_id || null,
            department: this.ticket?.department || null,
            title: this.ticket?.title || null,
            ticket_type: this.ticket?.ticket_type || null,
            description: this.ticket?.description || null,
            status: this.ticket?.status || 'Abierto',
            priority: this.ticket?.priority || null,
            expired_date: this.ticket?.expired_date || null,
            branch: this.ticket?.branch || null,
            media: null
        });

        const categoryForm = useForm({
            name: null,
        });

        // Determinar el tipo de asignación según los datos existentes
        let initialAssignmentType = 'user';
        if (this.ticket?.department) {
            initialAssignmentType = 'department';
        }

        return {
            form,
            categoryForm,
            assignmentType: initialAssignmentType, 
            showCategoryModal: false,
            departments: [
                'Administración', 'Almacén', 'Comercial', 'Compras', 'Contabilidad', 
                'Contraloría', 'Crédito y cobranza', 'Dirección', 'Empaques', 
                'Inspección', 'Mantenimiento', 'Producción', 'Recursos Humanos', 
                'Sistemas', 'Tesorería',
            ],
            statuses: [
                { label: 'Abierto', color: 'text-[#0355B5]', icon: '<i class="fa-solid fa-arrow-up text-[#0355B5]"></i>' },
                { label: 'En espera', color: 'text-[#FD8827]', icon: '<i class="fa-regular fa-clock text-[#FD8827]"></i>' },
                { label: 'En espera de 3ro', color: 'text-[#B927FD]', icon: '<i class="fa-regular fa-hourglass-half text-[#B927FD]"></i>' },
                { label: 'Completado', color: 'text-[#3F9C30]', icon: '<i class="fa-solid fa-check text-[#3F9C30]"></i>' },
                { label: 'Re-abierto', color: 'text-[#FD4646]', icon: '<i class="fa-solid fa-rotate-right text-[#FD4646]"></i>' },
                { label: 'En proceso', color: 'text-[#3D3D3D]', icon: '<i class="fa-solid fa-arrow-right-to-bracket text-[#3D3D3D]"></i>' },
            ],
            priorities: [
                { label: 'Baja', color: 'text-[#A49C9D]' },
                { label: 'Media', color: 'text-[#D68D1F]' },
                { label: 'Alta', color: 'text-[#C1202A]' },
            ],
            branches: [
                'General', 'Alfajayucan', 'Morelia', 'San Luis Potosí', 'Acapulco', 'Av. del Tigre',
                'Calle C', 'Calle 2', 'Veracruz', 'León', 'Juárez', 'Puebla', 'Monterrey', 'Federalismo',
            ],
        }
    },
    computed: {
        groupedUsers() {
            const groups = {};
            this.users.forEach(user => {
                const dept = user.employee_properties?.department || 'Sin departamento asignado';
                if (!groups[dept]) groups[dept] = [];
                groups[dept].push(user);
            });

            const sortedKeys = Object.keys(groups).sort((a, b) => {
                if (a === 'Sistemas') return -1;
                if (b === 'Sistemas') return 1;
                return a.localeCompare(b);
            });

            return sortedKeys.map(key => ({
                label: key,
                options: groups[key]
            }));
        }
    },
    methods: {
        handleAssignmentChange(value) {
            if (value === 'user') {
                this.form.department = null;
            } else if (value === 'department') {
                this.form.responsible_id = null;
            }
        },
        calculateBusinessDays(days) {
            let count = 0;
            let currentDate = new Date();
            while (count < days) {
                currentDate = addDays(currentDate, 1);
                if (!isWeekend(currentDate)) count++;
            }
            return this.formatDate(currentDate);
        },
        formatDate(date) {
            return format(date, 'yyyy-MM-dd');
        },
        submit() {
            if (this.isEdit) {
                if (this.form.media) {
                    this.form.post(route("tickets.update-with-media", this.ticket.id), {
                        onSuccess: () => {
                            this.$notify({ title: "Correcto", message: "Se ha editado el ticket", type: "success" });
                        },
                    });
                } else {
                    this.form.put(route("tickets.update", this.ticket.id), {
                        onSuccess: () => {
                            this.$notify({ title: "Correcto", message: "Se ha editado el ticket", type: "success" });
                        },
                    });
                }
            } else {
                this.form.post(route("tickets.store"), {
                    onSuccess: () => {
                        this.$notify({ title: "Correcto", message: "Se ha agregado el ticket", type: "success" });
                    },
                });
            }
        },
        storeCategory() {
            this.categoryForm.post(route("categories.store"), {
                onSuccess: () => {
                    this.$notify({ title: "Correcto", message: "Se ha agregado una nueva categoría", type: "success" });
                    this.showCategoryModal = false;
                    this.categoryForm.reset();
                },
            });
        },
        getStatusColor() {
            const currentObj = this.statuses.find(item => item.label === this.form.status);
            return currentObj ? currentObj.color : '';
        },
        getPriorityColor() {
            const currentObj = this.priorities.find(item => item.label === this.form.priority);
            return currentObj ? currentObj.color : '';
        },
        disabledDate(time) {
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            return time.getTime() < today.getTime();
        },
        getFileTypeIcon(fileName) {
            const fileExtension = fileName?.split('.').pop().toLowerCase();
            switch (fileExtension) {
                case 'pdf': return 'fa-regular fa-file-pdf text-red-700';
                case 'jpg': case 'jpeg': case 'png': case 'gif': return 'fa-regular fa-image text-blue-300';
                case 'mp4': case 'avi': case 'mkv': case 'mov': return 'fa-regular fa-file-video text-sky-400';
                default: return 'fa-regular fa-file-lines';
            }
        },
    },
    mounted() {
        if (!this.isEdit) {
            this.form.expired_date = this.calculateBusinessDays(5);
        }
    }
}
</script>