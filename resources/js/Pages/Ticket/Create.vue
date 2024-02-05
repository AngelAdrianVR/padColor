<template>
    <AppLayout title="Crear ticket">
        <div class="lg:p-9 p-1">
            <Back />

            <form @submit.prevent="store" class="rounded-lg border border-grayD9 lg:p-5 p-3 lg:w-1/2 mx-auto mt-7 grid grid-cols-2 gap-x-3">
                <h1 class="font-bold ml-2 col-span-2">Crear ticket</h1>
                <div class="relative mt-5">
                    <InputLabel value="Categoría*" class="ml-3 mb-1" />
                    <p @click="showCategoryModal = true"
                        class="text-primary text-xs cursor-pointer absolute right-2 top-[2px]">Agregar categoría</p>
                    <el-select class="w-full" v-model="form.category_id" clearable
                        placeholder="Seleccione" no-data-text="No hay opciones registradas"
                        no-match-text="No se encontraron coincidencias">
                        <el-option v-for="item in categories" :key="item" :label="item.name" :value="item.id" />
                    </el-select>
                    <InputError :message="form.errors.category_id" />
                </div>
                <div class="mt-5">
                    <InputLabel value="Responsable del Ticket" class="ml-3 mb-1" />
                    <el-select class="w-full" v-model="form.responsible_id" clearable
                        placeholder="Seleccione" no-data-text="No hay opciones registradas"
                        no-match-text="No se encontraron coincidencias">
                        <el-option v-for="user in users" :key="user" :label="user.name" :value="user.id">
                            <figure style="float: left">
                                <img class="object-contain bg-no-repeat size-7 rounded-full mt-1" :src="user.profile_photo_url"
                                    alt="" />
                            </figure>
                            <span class="ml-2">{{ user.name }}</span>
                        </el-option>
                    </el-select>
                    <InputError :message="form.errors.responsible_id" />
                </div>
                <div class="mt-3 col-span-2">
                    <InputLabel value="Título del ticket*" class="ml-3 mb-1" />
                    <el-input v-model="form.title" placeholder="Escribe el nombre del ticket" :maxlength="100" clearable />
                    <InputError :message="form.errors.title" />
                </div>
                <div class="mt-3 col-span-2">
                    <InputLabel value="Descripción" class="ml-3 mb-1" />
                    <el-input v-model="form.description" :autosize="{ minRows: 3, maxRows: 5 }" type="textarea"
                    :maxlength="500" show-word-limit clearable />
                    <InputError :message="form.errors.description" />
                </div>
                <div class="mt-3">
                    <div class="flex items-center space-x-3 ml-3 mb-1">
                        <InputLabel value="Estatus" class="" />
                        <i v-if="form.status" :class="getStatusColor()" class="fa-solid fa-circle text-[8px]"></i>
                    </div>
                    <el-select class="w-full" v-model="form.status" clearable
                        placeholder="Seleccione" no-data-text="No hay opciones registradas"
                        no-match-text="No se encontraron coincidencias">
                        <el-option v-for="item in statuses" :key="item" :label="item.label" :value="item.label">
                            <p style="float: left">
                                <i :class="item.color" class="fa-solid fa-circle text-[8px]"></i>
                            </p>
                            <span class="ml-2">{{ item.label }}</span>
                        </el-option>
                    </el-select>
                    <InputError :message="form.errors.status" />
                </div>
                <div class="mt-3">
                    <div class="flex items-center space-x-3 ml-3 mb-1">
                        <InputLabel value="Prioridad" class="" />
                        <i v-if="form.priority" :class="getPriorityColor()" class="fa-solid fa-circle text-[8px]"></i>
                    </div>
                    <el-select class="w-full" v-model="form.priority" clearable
                        placeholder="Seleccione" no-data-text="No hay opciones registradas"
                        no-match-text="No se encontraron coincidencias">
                        <el-option v-for="item in priorities" :key="item" :label="item.label" :value="item.label">
                            <p style="float: left">
                                <i :class="item.color" class="fa-solid fa-circle text-[8px]"></i>
                            </p>
                            <span class="ml-2">{{ item.label }}</span>
                        </el-option>
                    </el-select>
                    <InputError :message="form.errors.priority" />
                </div>
                <div class="mt-3">
                    <InputLabel value="Fecha de vencimiento" class="ml-3 mb-1" />
                    <el-date-picker v-model="form.expired_date" type="date" placeholder="Seleccione"
                        :disabled-date="disabledDate" />
                    <InputError :message="form.errors.expired_date" />
                </div>
                <div class="ml-2 mt-3 col-span-full">
                    <FileUploader @files-selected="this.form.media = $event" />
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
import Modal from "@/Components/Modal.vue";
import Back from "@/Components/MyComponents/Back.vue";
import { useForm } from "@inertiajs/vue3";

export default {
data() {
     const form = useForm({
      category_id: null,
      responsible_id: null,
      title: null,
      description: null,
      status: null,
      priority: null,
      expired_date: null,
      media: null
    });
    return {
        form,
        showCategoryModal: false,
        statuses: [
            {
                label: 'Abierto',
                color: 'text-[#0355B5]',

            },
            {
                label:'En espera',
                color:'text-[#FD8827]',
            },
            {
                label:'En espera de 3ro',
                color:'text-[#B927FD]',
            },
            {
                label:'Completado',
                color:'text-[#3F9C30]',
            },
            {
                label:'Re-abierto',
                color:'text-[#FD4646]',
            },
            {
                label:'En proceso',
                color:'text-[#3D3D3D]',
            },
        ],
        priorities: [
            {
                label: 'Baja',
                color: 'text-[#A49C9D]',

            },
            {
                label:'Media',
                color:'text-[#D68D1F]',
            },
            {
                label:'Alta',
                color:'text-[#C1202A]',
            },
        ],
    }
},
components:{
AppLayout,
PrimaryButton,
FileUploader,
InputLabel,
InputError,
Modal,
Back
},
props:{
categories: Array,
users: Array,
},
methods:{
    store() {
        this.form.post(route("tickets.store"), {
            onSuccess: () => {
            this.$notify({
                title: "Correcto",
                message: "Se ha agregado el ticket",
                type: "success",
            });
            },
        });
    },
    getStatusColor() {
        if (this.form.status === 'Abierto') {
            return 'text-[#0355B5]';
        } else if (this.form.status === 'En espera') {
            return 'text-[#FD8827]';
        } else if (this.form.status === 'En espera de 3ro') {
            return 'text-[#B927FD]';
        } else if (this.form.status === 'Completado') {
            return 'text-[#3F9C30]';
        } else if (this.form.status === 'Re-abierto') {
            return 'text-[#FD4646]';
        } else if (this.form.status === 'En proceso') {
            return 'text-[#3D3D3D]';
        }
    },
    getPriorityColor() {
        if (this.form.priority === 'Baja') {
            return 'text-[#A49C9D]';
        } else if (this.form.priority === 'Media') {
            return 'text-[#D68D1F]';
        } else if (this.form.priority === 'Alta') {
            return 'text-[#C1202A]';
        }
    },
    disabledDate(time) {
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      return time.getTime() < today.getTime();
    },
}
}
</script>