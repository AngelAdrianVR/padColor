<template>
    <div class="grid grid-cols-2 gap-2">
        <div class="w-full">
            <InputLabel value="Buscar usuario" />
            <input :value="search" @input="$emit('update:search', $event.target.value)" type="text"
                placeholder="Buscar por nombre o correo"
                class="w-full bg-transparent border-grayD9 rounded-[3px] py-1 px-2 text-[#3f3f3f] placeholder-gray99 focus:ring-0 focus:border-primary">
        </div>
        <div class="w-full">
            <InputLabel value="Departamento" />
            <el-select
                :value="department" v-model="cDepartment" @change="handleChancheDepartment" placeholder="Selecciona el departamento" clearable>
                <el-option v-for="item in departments" :key="item" :label="item" :value="item" />
            </el-select>
        </div>
        <div class="w-full">
            <InputLabel value="Compañía" />
            <el-select
                :value="company" v-model="cCompany" @change="handleChancheCompany" placeholder="Selecciona la compañía" clearable>
                <el-option v-for="item in companies" :key="item" :label="item" :value="item" />
            </el-select>
        </div>
        <PrimaryButton @click="$emit('add-external')" class="rounded-lg self-end">
            <i class="fa-solid fa-circle-plus mr-2"></i>
            Añadir externo
        </PrimaryButton>
    </div>
</template>
<script>
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

export default {
    data() {
        return {
            cDepartment: '',
            cCompany: '',
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
        }
    },
    props: {
        search: String,
        department: String,
        company: String,
    },
    components: {
        InputLabel,
        PrimaryButton,
    },
    emits: ['update:search', 'update:department', 'update:company', 'add-external'],
    methods: {
        handleChancheCompany(value) {
            this.$emit('update:company', value);
        },
        handleChancheDepartment(value) {
            this.$emit('update:department', value);
        },
    }
}
</script>