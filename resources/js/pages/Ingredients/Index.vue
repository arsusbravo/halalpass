<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { useTrans } from '@/composables/useTrans';
import FlashMessage from '@/components/FlashMessage.vue';
import HalalStatusBadge from '@/components/HalalStatusBadge.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';
import type { BreadcrumbItem } from '@/types';
import { ref } from 'vue';

const { t } = useTrans();
const props = defineProps<{
    ingredients: Array<{
        id: number; name: string; code: string | null; type: string; category: string;
        supplier: { name: string } | null;
        halal_certificates: Array<{ status: string }>;
        children_recursive: Array<{ id: number; name: string; code: string | null; type: string; halal_certificates: Array<{ status: string }> }>;
    }>;
}>();
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: '/dashboard' }, { title: 'Ingredients', href: '/ingredients' }];
const deleteId = ref<number | null>(null);
function handleDelete() { if (deleteId.value) { router.delete(`/ingredients/${deleteId.value}`); deleteId.value = null; } }
function certStatus(certs?: Array<{ status: string }> | null): string { return certs?.length ? certs[0].status : 'missing'; }
const categoryLabels: Record<string, string> = { bahan_baku: 'Raw Material', bahan_tambahan: 'Additive', bahan_penolong: 'Processing Aid' };
</script>

<template>
    <Head :title="t('Ingredients')" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ t('Ingredients') }}</h2>
                <Link href="/ingredients/create" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">{{ t('Add Ingredient') }}</Link>
            </div>

            <div class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-white dark:border-sidebar-border dark:bg-gray-900">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-100 bg-gray-50 dark:border-gray-800 dark:bg-gray-800/50">
                        <tr>
                            <th class="px-4 py-3 font-medium text-gray-500">{{ t('Ingredient Name') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-500">{{ t('Code') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-500">{{ t('Ingredient Type') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-500">{{ t('Category') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-500">{{ t('Supplier') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-500">{{ t('Certificate Status') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-500">{{ t('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <template v-for="ing in ingredients" :key="ing.id">
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-4 py-3">
                                    <Link :href="`/ingredients/${ing.id}`" class="font-medium text-gray-900 hover:text-emerald-600 dark:text-gray-100">
                                        {{ ing.name }}
                                    </Link>
                                    <span v-if="ing.type === 'composite'" class="ml-1.5 rounded bg-blue-100 px-1.5 py-0.5 text-xs text-blue-700 dark:bg-blue-900 dark:text-blue-200">{{ t('Composite Material') }}</span>
                                </td>
                                <td class="px-4 py-3 text-gray-500">{{ ing.code ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ ing.type === 'simple' ? t('Simple Material') : t('Composite Material') }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ t(categoryLabels[ing.category] ?? ing.category) }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ ing.supplier?.name ?? '-' }}</td>
                                <td class="px-4 py-3"><HalalStatusBadge :status="certStatus(ing.halal_certificates)" size="sm" /></td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-2">
                                        <Link :href="`/ingredients/${ing.id}/edit`" class="text-sm text-blue-600 hover:text-blue-800">{{ t('Edit') }}</Link>
                                        <button @click="deleteId = ing.id" class="text-sm text-red-600 hover:text-red-800">{{ t('Delete') }}</button>
                                    </div>
                                </td>
                            </tr>
                            <!-- Children (sub-ingredients) -->
                            <tr v-for="child in ing.children_recursive" :key="child.id" class="bg-gray-50/50 dark:bg-gray-800/25">
                                <td class="py-2 pl-10 pr-4">
                                    <span class="mr-2 text-gray-300 dark:text-gray-600">└</span>
                                    <Link :href="`/ingredients/${child.id}`" class="text-gray-700 hover:text-emerald-600 dark:text-gray-300">{{ child.name }}</Link>
                                </td>
                                <td class="px-4 py-2 text-gray-400">{{ child.code ?? '-' }}</td>
                                <td class="px-4 py-2 text-gray-400">{{ t('Simple Material') }}</td>
                                <td class="px-4 py-2"></td>
                                <td class="px-4 py-2"></td>
                                <td class="px-4 py-2"><HalalStatusBadge :status="certStatus(child.halal_certificates)" size="sm" /></td>
                                <td class="px-4 py-2"></td>
                            </tr>
                        </template>
                        <tr v-if="!ingredients.length"><td colspan="7" class="px-4 py-8 text-center text-gray-500">{{ t('No data available') }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <ConfirmModal :show="deleteId !== null" @confirm="handleDelete" @cancel="deleteId = null" />
        <FlashMessage />
    </AppLayout>
</template>
