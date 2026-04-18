<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import IngredientForm from '@/components/IngredientForm.vue';
import { useTrans } from '@/composables/useTrans';
import { type BreadcrumbItem } from '@/types';

const { t } = useTrans();

const props = defineProps<{
    ingredient: {
        id: number; name: string; code: string | null; type: string;
        supplier_id: number | null; origin_country: string | null;
        brand: string | null; manufacturer: string | null;
        category: string; halal_risk_level: string;
        sh_number: string | null; parent_id: number | null; notes: string | null;
    };
    suppliers: Array<{ id: number; name: string; code: string | null }>;
    parentIngredients: Array<{ id: number; name: string }>;
}>();

const form = useForm({
    name: props.ingredient.name,
    brand: props.ingredient.brand ?? '',
    halal_risk_level: props.ingredient.halal_risk_level ?? 'medium_risk',
    sh_number: props.ingredient.sh_number ?? '',
    type: props.ingredient.type,
    parent_id: props.ingredient.parent_id ?? '',
    supplier_id: props.ingredient.supplier_id ?? '',
    category: props.ingredient.category,
    origin_country: props.ingredient.origin_country ?? '',
    manufacturer: props.ingredient.manufacturer ?? '',
    notes: props.ingredient.notes ?? '',
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Ingredients', href: '/ingredients' },
    { title: 'Edit', href: '#' },
];

function submit() {
    form.put(`/ingredients/${props.ingredient.id}`);
}
</script>

<template>
    <Head :title="t('Edit Ingredient')" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-xl p-4">
            <div class="mb-6 flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ t('Edit Ingredient') }}</h2>
                <span class="rounded bg-gray-100 px-2 py-1 font-mono text-xs text-gray-500 dark:bg-gray-800">{{ ingredient.code }}</span>
            </div>
            <IngredientForm
                :form="form"
                :suppliers="suppliers"
                :parent-ingredients="parentIngredients"
                :submit-label="t('Update')"
                @submit="submit"
            />
        </div>
    </AppLayout>
</template>