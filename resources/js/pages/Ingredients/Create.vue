<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import IngredientForm from '@/components/IngredientForm.vue';
import { useTrans } from '@/composables/useTrans';
import { type BreadcrumbItem } from '@/types';

const { t } = useTrans();

const props = defineProps<{
    suppliers: Array<{ id: number; name: string; code: string | null }>;
    parentIngredients: Array<{ id: number; name: string }>;
}>();

const form = useForm({
    name: '', brand: '', halal_risk_level: 'medium_risk', sh_number: '',
    type: 'simple', parent_id: '', supplier_id: '', category: 'bahan_baku',
    origin_country: '', manufacturer: '', notes: '',
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Ingredients', href: '/ingredients' },
    { title: 'Create', href: '#' },
];

function submit() {
    form.post('/ingredients');
}
</script>

<template>
    <Head :title="t('Add Ingredient')" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-xl p-4">
            <h2 class="mb-6 text-xl font-semibold text-gray-900 dark:text-gray-100">{{ t('Add Ingredient') }}</h2>
            <IngredientForm
                :form="form"
                :suppliers="suppliers"
                :parent-ingredients="parentIngredients"
                :submit-label="t('Create')"
                @submit="submit"
            />
        </div>
    </AppLayout>
</template>