<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { useTrans } from '@/composables/useTrans';
import type { BreadcrumbItem } from '@/types';
import { computed } from 'vue';

const { t } = useTrans();
const props = defineProps<{
    ingredient?: { id: number; name: string; code: string | null; type: string; supplier_id: number | null; origin_country: string | null; brand: string | null; manufacturer: string | null; category: string; halal_risk_level: string; notes: string | null };
    suppliers: Array<{ id: number; name: string; code: string | null }>;
}>();

const isEditing = computed(() => !!props.ingredient);
const form = useForm({
    name: props.ingredient?.name ?? '', code: props.ingredient?.code ?? '', type: props.ingredient?.type ?? 'simple',
    supplier_id: props.ingredient?.supplier_id ?? '', origin_country: props.ingredient?.origin_country ?? '', brand: props.ingredient?.brand ?? '',
    manufacturer: props.ingredient?.manufacturer ?? '', category: props.ingredient?.category ?? 'bahan_baku', notes: props.ingredient?.notes ?? '',
    specifications: {} as Record<string, string>, children: [] as Array<{ name: string; code: string; supplier_id: number | string; category: string }>,
    halal_risk_level: props.ingredient?.halal_risk_level ?? 'medium_risk',
});
const breadcrumbs = computed<BreadcrumbItem[]>(() => [{ title: 'Dashboard', href: '/dashboard' }, { title: 'Ingredients', href: '/ingredients' }, { title: isEditing.value ? 'Edit' : 'Create', href: '#' }]);
function addChild() { form.children.push({ name: '', code: '', supplier_id: '', category: 'bahan_baku' }); }
function removeChild(i: number) { form.children.splice(i, 1); }
function submit() { isEditing.value ? form.put(`/ingredients/${props.ingredient!.id}`) : form.post('/ingredients'); }
</script>

<template>
    <Head :title="isEditing ? t('Edit Ingredient') : t('Add Ingredient')" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-2xl p-4">
            <h2 class="mb-6 text-xl font-semibold text-gray-900 dark:text-gray-100">{{ isEditing ? t('Edit Ingredient') : t('Add Ingredient') }}</h2>
            <form @submit.prevent="submit" class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2"><label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Ingredient Name') }} *</label><input v-model="form.name" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" /><p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p></div>
                    <div><label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Code') }}</label><input v-model="form.code" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" /></div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Ingredient Type') }} *</label>
                        <select v-model="form.type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                            <option value="simple">{{ t('Simple Material') }}</option>
                            <option value="composite">{{ t('Composite Material') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Supplier') }}</label>
                        <select v-model="form.supplier_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                            <option value="">{{ t('Select...') }}</option>
                            <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Category') }} *</label>
                        <select v-model="form.category" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                            <option value="bahan_baku">{{ t('Raw Material') }}</option>
                            <option value="bahan_tambahan">{{ t('Additive') }}</option>
                            <option value="bahan_penolong">{{ t('Processing Aid') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Halal Risk Level') }}</label>
                        <select v-model="form.halal_risk_level" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                            <option value="no_risk">{{ t('No Risk — Naturally Halal') }}</option>
                            <option value="low_risk">{{ t('Low Risk — Certificate Recommended') }}</option>
                            <option value="medium_risk">{{ t('Medium Risk — Certificate Required') }}</option>
                            <option value="high_risk">{{ t('High Risk — Certificate Mandatory') }}</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-400">
                            <template v-if="form.halal_risk_level === 'no_risk'">{{ t('E.g. eggs, water, fresh vegetables, rice') }}</template>
                            <template v-else-if="form.halal_risk_level === 'low_risk'">{{ t('E.g. salt, sugar, wheat flour') }}</template>
                            <template v-else-if="form.halal_risk_level === 'medium_risk'">{{ t('E.g. MSG, coloring, preservatives, cooking oil') }}</template>
                            <template v-else>{{ t('E.g. gelatin, enzymes, emulsifiers, animal-derived ingredients') }}</template>
                        </p>
                    </div>
                    <div><label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Origin Country') }}</label><input v-model="form.origin_country" maxlength="2" placeholder="ID" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" /></div>
                    <div><label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Brand') }}</label><input v-model="form.brand" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" /></div>
                    <div><label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Manufacturer') }}</label><input v-model="form.manufacturer" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" /></div>
                    <div class="sm:col-span-2"><label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Notes') }}</label><textarea v-model="form.notes" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" /></div>
                </div>

                <!-- Sub-ingredients for composite -->
                <div v-if="form.type === 'composite' && !isEditing">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ t('Sub-Ingredients') }}</h3>
                        <button type="button" @click="addChild" class="rounded-lg border border-emerald-600 px-3 py-1.5 text-xs font-medium text-emerald-600 hover:bg-emerald-50">+ {{ t('Add Sub-Ingredient') }}</button>
                    </div>
                    <div v-for="(child, i) in form.children" :key="i" class="mb-3 rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                        <div class="grid gap-3 sm:grid-cols-3">
                            <div><label class="mb-1 block text-xs text-gray-500">{{ t('Name') }} *</label><input v-model="child.name" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" /></div>
                            <div><label class="mb-1 block text-xs text-gray-500">{{ t('Code') }}</label><input v-model="child.code" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" /></div>
                            <div class="flex items-end gap-2">
                                <div class="flex-1"><label class="mb-1 block text-xs text-gray-500">{{ t('Supplier') }}</label>
                                    <select v-model="child.supplier_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                                        <option value="">-</option><option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
                                    </select>
                                </div>
                                <button type="button" @click="removeChild(i)" class="mb-1 text-red-500 hover:text-red-700"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" :disabled="form.processing" class="rounded-lg bg-emerald-600 px-6 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50">{{ form.processing ? t('Loading...') : (isEditing ? t('Update') : t('Create')) }}</button>
                    <a href="/ingredients" class="rounded-lg border border-gray-300 px-6 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300">{{ t('Cancel') }}</a>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
