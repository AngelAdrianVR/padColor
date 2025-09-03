<template>
    <div class="p-1">
        <!-- ==================================================== -->
        <!-- MODO EDICIÓN                                         -->
        <!-- ==================================================== -->
        <div v-if="isEditing">
            <div class="space-y-6">
                <div v-for="(fields, sectionName) in fieldsBySection" :key="sectionName"
                    class="bg-white rounded-xl border border-gray-200 p-5">
                    <h3 class="font-semibold text-gray-700 mb-4 capitalize flex items-center">
                        <component :is="getSectionIcon(fields)" class="w-5 h-5 mr-2 text-gray-500" />
                        {{ formatSectionName(sectionName) }}
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div v-for="field in fields" :key="field.slug"
                            :class="field.type === 'textarea' ? 'md:col-span-2' : ''">
                            <label class="el-form-item__label">{{ field.label }}</label>
                            <el-input v-if="field.type === 'text'" v-model="form.sheet_data[field.slug]" />
                            <el-input v-if="field.type === 'textarea'" v-model="form.sheet_data[field.slug]"
                                type="textarea" :rows="3" />
                            <el-select v-if="field.type === 'select'" v-model="form.sheet_data[field.slug]"
                                class="w-full" clearable>
                                <el-option v-for="option in field.options" :key="option.value" :label="option.label"
                                    :value="option.value" />
                            </el-select>
                            <el-radio-group v-if="field.type === 'radio'" v-model="form.sheet_data[field.slug]">
                                <el-radio v-for="option in field.options" :key="option.value" :label="option.value">{{
                                    option.label }}</el-radio>
                            </el-radio-group>
                            <el-checkbox-group v-if="field.type === 'multicheckbox' || field.type === 'checklist'"
                                v-model="form.sheet_data[field.slug]">
                                <el-checkbox v-for="option in field.options" :key="option.value" :label="option.value"
                                    :value="option.value">{{ option.label }}</el-checkbox>
                            </el-checkbox-group>
                            <CustomUploader v-if="field.type === 'file'" v-model:files="form.sheet_data[field.slug]" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- ==================================================== -->
        <!-- MODO VISTA                                           -->
        <!-- ==================================================== -->
        <div v-else>
            <p class="text-gray-600 text-sm mb-6">{{ description }}</p>
            <div class="space-y-6">
                <div v-for="(fields, sectionName) in fieldsBySection" :key="sectionName"
                    class="bg-white rounded-xl border border-gray-200 p-5">
                    <h3 class="font-semibold text-gray-700 mb-4 capitalize flex items-center">
                        <component :is="getSectionIcon(fields)" class="w-5 h-5 mr-2 text-gray-500" />
                        {{ formatSectionName(sectionName) }}
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3">
                        <div v-for="field in fields" :key="field.slug"
                            :class="field.type === 'textarea' ? 'md:col-span-2 border-b-0' : 'border-b pb-2'">

                            <div v-if="field.type === 'textarea'" class="text-sm">
                                <span class="text-gray-500 block mb-1">{{ field.label }}</span>
                                <p class="text-gray-800 whitespace-pre-wrap">{{ getDisplayValue(field) }}</p>
                            </div>
                            <div v-if="field.type === 'file'" class="text-sm">
                                <span class="text-gray-500 block mb-1">{{ field.label }}</span>
                                <div v-if="product.media.filter(m => m.collection_name === field.slug).length > 0"
                                    class="space-y-2">
                                    <FileView
                                        v-for="media in product.media.filter(m => m.collection_name === field.slug)"
                                        :key="media.id" :file="media" />
                                </div>
                                <span v-else class="text-gray-800 font-medium">-</span>
                            </div>
                            <div v-else class="flex justify-between items-start text-sm">
                                <span class="text-gray-500 mr-2">{{ field.label }}</span>
                                <span class="text-gray-800 font-medium text-right">{{ getDisplayValue(field) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import CustomUploader from '@/Components/MyComponents/CustomUploader.vue';
import FileView from '@/Components/MyComponents/Ticket/FileView.vue';
import * as HeroIcons from '@heroicons/vue/24/outline';

const { QuestionMarkCircleIcon } = HeroIcons;

export default {
    name: 'DynamicTab',
    props: {
        product: Object,
        fieldsBySection: Object,
        description: String,
        isEditing: Boolean,
        form: Object,
    },
    components: {
        CustomUploader,
        FileView,
    },
    created() {
        if (this.isEditing && this.fieldsBySection) {
            Object.values(this.fieldsBySection).flat().forEach(field => {
                const types = ['multicheckbox', 'checklist'];
                if (types.includes(field.type) && !Array.isArray(this.form.sheet_data[field.slug])) {
                    this.form.sheet_data[field.slug] = [];
                }
            });
        }
    },
    methods: {
        formatSectionName(slug) {
            if (!slug) return '';
            return slug.replace(/_/g, ' ');
        },
        getSectionIcon(fields) {
            const iconName = fields[0]?.icon;
            return HeroIcons[iconName] || QuestionMarkCircleIcon;
        },
        getDisplayValue(field) {
            // Comprobar si sheet_data existe antes de intentar acceder a él.
            const value = this.product.sheet_data ? this.product.sheet_data[field.slug] : undefined;

            if (value === null || value === undefined || value === '') {
                return '-';
            }

            const optionTypes = ['select', 'radio'];
            const multiOptionTypes = ['multicheckbox', 'checklist'];

            if ((optionTypes.includes(field.type)) && field.options) {
                const option = field.options.find(o => o.value === value);
                return option ? option.label : value;
            }

            if ((multiOptionTypes.includes(field.type)) && Array.isArray(value) && field.options) {
                if (value.length === 0) return '-';
                return value.map(val => {
                    const option = field.options.find(o => o.value === val);
                    return option ? option.label : val;
                }).join(', ');
            }

            return value;
        }
    }
}
</script>