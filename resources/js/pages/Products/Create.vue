<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { useTrans } from '@/composables/useTrans';
import type { BreadcrumbItem } from '@/types';
import { computed, ref } from 'vue';

const { t } = useTrans();

const props = defineProps<{
    product?: {
        id: number;
        name: string;
        code: string | null;
        brand: string | null;
        description: string | null;
        category: string | null;
        facility_id: number;
        status: string;
        ingredients: Array<{
            id: number;
            name: string;
            pivot: { percentage: number | null; is_critical: boolean; usage_purpose: string | null; sort_order: number };
        }>;
    };
    facilities: Array<{ id: number; name: string; code: string | null }>;
    ingredients: Array<{ id: number; name: string; code: string | null; type: string }>;
}>();

const isEditing = computed(() => !!props.product);

const form = useForm({
    facility_id: props.product?.facility_id ?? (props.facilities[0]?.id ?? ''),
    name: props.product?.name ?? '',
    code: props.product?.code ?? '',
    brand: props.product?.brand ?? '',
    description: props.product?.description ?? '',
    category: props.product?.category ?? '',
    status: props.product?.status ?? 'active',
    ingredients: props.product?.ingredients?.map(i => ({
        ingredient_id: i.id,
        percentage: i.pivot.percentage,
        is_critical: i.pivot.is_critical,
        usage_purpose: i.pivot.usage_purpose ?? '',
        sort_order: i.pivot.sort_order,
    })) ?? [] as Array<{ ingredient_id: number; percentage: number | null; is_critical: boolean; usage_purpose: string; sort_order: number }>,
});

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Products', href: '/products' },
    { title: isEditing.value ? 'Edit' : 'Create', href: '#' },
]);

function addIngredient() {
    form.ingredients.push({ ingredient_id: 0, percentage: null, is_critical: false, usage_purpose: '', sort_order: form.ingredients.length });
}

function removeIngredient(index: number) {
    form.ingredients.splice(index, 1);
}

function submit() {
    if (isEditing.value) {
        form.put(`/products/${props.product!.id}`);
    } else {
        form.post('/products');
    }
}
</script>

<template>
    <Head :title="isEditing ? t('Edit Product') : t('Add Product')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="lg:mx-auto lg:max-w-3xl p-4">
            <h2 class="mb-6 text-xl font-semibold text-gray-900 dark:text-gray-100">
                {{ isEditing ? t('Edit Product') : t('Add Product') }}
            </h2>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Basic Info -->
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Product Name') }} *</label>
                        <input v-model="form.name" type="text" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Facility') }} *</label>
                        <select v-model="form.facility_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                            <option v-for="f in facilities" :key="f.id" :value="f.id">{{ f.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Product Code') }}</label>
                        <input v-model="form.code" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Brand') }}</label>
                        <input v-model="form.brand" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Category') }}</label>
                        <input v-model="form.category" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Description') }}</label>
                        <textarea v-model="form.description" rows="3" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                    </div>
                </div>

                <!-- Ingredients -->
                <div>
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ t('Ingredients') }}</h3>
                        <button type="button" @click="addIngredient" class="rounded-lg border border-emerald-600 px-3 py-1.5 text-xs font-medium text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950">
                            + {{ t('Add Ingredient to Product') }}
                        </button>
                    </div>

                    <div v-for="(item, index) in form.ingredients" :key="index" class="mb-3 rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                        <div class="grid gap-3 sm:grid-cols-4">
                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-xs text-gray-500">{{ t('Ingredient') }}</label>
                                <select v-model="item.ingredient_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                                    <option value="0" disabled>{{ t('Select...') }}</option>
                                    <option v-for="ing in ingredients" :key="ing.id" :value="ing.id">
                                        {{ ing.name }} {{ ing.code ? `(${ing.code})` : '' }} {{ ing.type === 'composite' ? '[C]' : '' }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs text-gray-500">{{ t('Composition (%)') }}</label>
                                <input v-model.number="item.percentage" type="number" step="0.01" min="0" max="100" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                            </div>
                            <div class="flex items-end gap-2">
                                <label class="flex items-center gap-2 text-sm">
                                    <input v-model="item.is_critical" type="checkbox" class="rounded border-gray-300 text-emerald-600" />
                                    {{ t('Critical Point') }}
                                </label>
                                <button type="button" @click="removeIngredient(index)" class="ml-auto text-red-500 hover:text-red-700">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </div>
                        </div>
                        <div class="mt-2">
                            <label class="mb-1 block text-xs text-gray-500">{{ t('Usage Purpose') }}</label>
                            <input v-model="item.usage_purpose" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                        </div>
                    </div>

                    <p v-if="form.ingredients.length === 0" class="rounded-lg border border-dashed border-gray-300 py-8 text-center text-sm text-gray-400 dark:border-gray-600">
                        {{ t('No data available') }}
                    </p>
                </div>

                <!-- Submit -->
                <div class="flex gap-3 border-t border-gray-200 pt-4 dark:border-gray-700">
                    <button type="submit" :disabled="form.processing" class="rounded-lg bg-emerald-600 px-6 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50">
                        {{ form.processing ? t('Loading...') : (isEditing ? t('Update') : t('Create')) }}
                    </button>
                    <a href="/products" class="rounded-lg border border-gray-300 px-6 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300">{{ t('Cancel') }}</a>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
