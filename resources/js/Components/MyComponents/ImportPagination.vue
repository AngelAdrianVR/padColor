<template>
    <div v-if="pagination && pagination.last_page > 0" class="flex flex-wrap justify-end">
        <el-pagination
            background
            :current-page="Number(page)"
            :page-size="Number(perPage)"
            :page-sizes="[10, 20, 50, 100]"
            :total="pagination.total"
            layout="total, sizes, prev, pager, next"
            @current-change="$emit('update:page', $event)"
            @size-change="handleSizeChange"
        />
    </div>
</template>

<script>
export default {
    name: 'ImportPagination',
    props: {
        pagination: {
            type: Object,
            default: null,
        },
        page: {
            type: [Number, String],
            default: 1,
        },
        perPage: {
            type: [Number, String],
            default: 20,
        },
    },
    emits: ['update:page', 'update:perPage'],
    methods: {
        handleSizeChange(newSize) {
            // Al cambiar el número de registros por página regresamos a la primera página.
            this.$emit('update:perPage', newSize);
            this.$emit('update:page', 1);
        },
    },
};
</script>
