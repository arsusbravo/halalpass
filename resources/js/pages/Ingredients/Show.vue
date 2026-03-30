<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { useTrans } from '@/composables/useTrans';
import HalalStatusBadge from '@/components/HalalStatusBadge.vue';
import type { BreadcrumbItem } from '@/types';

const { t, d } = useTrans();
const props = defineProps<{
    ingredient: {
        id: number; name: string; code: string | null; type: string; category: string; origin_country: string | null; brand: string | null; manufacturer: string | null; notes: string | null;
        supplier: { id: number; name: string } | null;
        parent: { id: number; name: string } | null;
        halal_certificates: Array<{ id: number; sh_number: string; issuing_body: string; issuing_body_name: string | null; expiry_date: string; status: string; days_until_expiry: number }>;
        children_recursive: Array<{ id: number; name: string; code: string | null; halal_certificates: Array<{ sh_number: string; status: string; expiry_date: string }> }>;
    };
}>();
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: '/dashboard' }, { title: 'Ingredients', href: '/ingredients' }, { title: props.ingredient.name, href: '#' }];
const categoryLabels: Record<string, string> = { bahan_baku: 'Raw Material', bahan_tambahan: 'Additive', bahan_penolong: 'Processing Aid' };
</script>

<template>
    <Head :title="ingredient.name" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-4xl p-4">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ ingredient.name }}</h2>
                    <p v-if="ingredient.parent" class="text-sm text-gray-500">{{ t('Sub-Ingredients') }} {{ t('of') }} <Link :href="`/ingredients/${ingredient.parent.id}`" class="text-emerald-600 hover:text-emerald-800">{{ ingredient.parent.name }}</Link></p>
                </div>
                <div class="flex gap-2">
                    <Link :href="`/certificates/create?ingredient_id=${ingredient.id}`" class="rounded-lg border border-emerald-600 px-4 py-2 text-sm font-medium text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950">{{ t('Add Certificate') }}</Link>
                    <Link :href="`/ingredients/${ingredient.id}/edit`" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">{{ t('Edit') }}</Link>
                </div>
            </div>

            <!-- Info -->
            <div class="mb-6 grid gap-4 rounded-xl border border-sidebar-border/70 bg-white p-5 sm:grid-cols-3 dark:border-sidebar-border dark:bg-gray-900">
                <div><p class="text-xs text-gray-500">{{ t('Code') }}</p><p class="text-sm text-gray-900 dark:text-gray-100">{{ ingredient.code ?? '-' }}</p></div>
                <div><p class="text-xs text-gray-500">{{ t('Ingredient Type') }}</p><p class="text-sm text-gray-900 dark:text-gray-100">{{ ingredient.type === 'simple' ? t('Simple Material') : t('Composite Material') }}</p></div>
                <div><p class="text-xs text-gray-500">{{ t('Category') }}</p><p class="text-sm text-gray-900 dark:text-gray-100">{{ t(categoryLabels[ingredient.category] ?? ingredient.category) }}</p></div>
                <div><p class="text-xs text-gray-500">{{ t('Origin Country') }}</p><p class="text-sm text-gray-900 dark:text-gray-100">{{ ingredient.origin_country ?? '-' }}</p></div>
                <div><p class="text-xs text-gray-500">{{ t('Brand') }}</p><p class="text-sm text-gray-900 dark:text-gray-100">{{ ingredient.brand ?? '-' }}</p></div>
                <div><p class="text-xs text-gray-500">{{ t('Manufacturer') }}</p><p class="text-sm text-gray-900 dark:text-gray-100">{{ ingredient.manufacturer ?? '-' }}</p></div>
                <div><p class="text-xs text-gray-500">{{ t('Supplier') }}</p><p class="text-sm"><Link v-if="ingredient.supplier" :href="`/suppliers/${ingredient.supplier.id}`" class="text-emerald-600 hover:text-emerald-800">{{ ingredient.supplier.name }}</Link><span v-else class="text-gray-500">-</span></p></div>
            </div>

            <!-- Certificates -->
            <h3 class="mb-3 font-semibold text-gray-900 dark:text-gray-100">{{ t('Halal Certificates') }}</h3>
            <div class="mb-6 overflow-hidden rounded-xl border border-sidebar-border/70 bg-white dark:border-sidebar-border dark:bg-gray-900">
                <table class="w-full text-left text-sm">
                    <thead class="border-b bg-gray-50 dark:bg-gray-800/50"><tr>
                        <th class="px-4 py-3 font-medium text-gray-500">{{ t('SH Number') }}</th>
                        <th class="px-4 py-3 font-medium text-gray-500">{{ t('Issuing Body') }}</th>
                        <th class="px-4 py-3 font-medium text-gray-500">{{ t('Expiry Date') }}</th>
                        <th class="px-4 py-3 font-medium text-gray-500">{{ t('Status') }}</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-for="cert in ingredient.halal_certificates" :key="cert.id">
                            <td class="px-4 py-3"><Link :href="`/certificates/${cert.id}`" class="font-mono text-sm text-emerald-600 hover:text-emerald-800">{{ cert.sh_number }}</Link></td>
                            <td class="px-4 py-3 text-gray-500">{{ cert.issuing_body_name ?? cert.issuing_body }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ d(cert.expiry_date, true) }} <span class="text-xs" :class="cert.days_until_expiry <= 30 ? 'text-red-600 font-semibold' : cert.days_until_expiry <= 90 ? 'text-amber-600' : 'text-gray-400'">({{ cert.days_until_expiry }}d)</span></td>
                            <td class="px-4 py-3"><HalalStatusBadge :status="cert.status" size="sm" /></td>
                        </tr>
                        <tr v-if="!ingredient.halal_certificates.length"><td colspan="4" class="px-4 py-6 text-center text-red-500">{{ t('Missing') }} — <Link :href="`/certificates/create?ingredient_id=${ingredient.id}`" class="text-emerald-600 underline">{{ t('Add Certificate') }}</Link></td></tr>
                    </tbody>
                </table>
            </div>

            <!-- Children -->
            <div v-if="ingredient.children_recursive.length">
                <h3 class="mb-3 font-semibold text-gray-900 dark:text-gray-100">{{ t('Sub-Ingredients') }} ({{ ingredient.children_recursive.length }})</h3>
                <div class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-white dark:border-sidebar-border dark:bg-gray-900">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b bg-gray-50 dark:bg-gray-800/50"><tr><th class="px-4 py-3 font-medium text-gray-500">{{ t('Name') }}</th><th class="px-4 py-3 font-medium text-gray-500">{{ t('Code') }}</th><th class="px-4 py-3 font-medium text-gray-500">{{ t('Certificate Status') }}</th></tr></thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <tr v-for="child in ingredient.children_recursive" :key="child.id">
                                <td class="px-4 py-3"><Link :href="`/ingredients/${child.id}`" class="font-medium text-emerald-600 hover:text-emerald-800">{{ child.name }}</Link></td>
                                <td class="px-4 py-3 text-gray-500">{{ child.code ?? '-' }}</td>
                                <td class="px-4 py-3"><HalalStatusBadge :status="child.halal_certificates.length ? child.halal_certificates[0].status : 'missing'" size="sm" /></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
