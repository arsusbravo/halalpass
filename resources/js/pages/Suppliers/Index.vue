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
const props = defineProps<{ suppliers: Array<{ id: number; name: string; code: string | null; city: string | null; country: string; email: string | null; status: string; ingredients_count: number }> }>();
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: '/dashboard' }, { title: 'Suppliers', href: '/suppliers' }];
const deleteId = ref<number | null>(null);
function handleDelete() { if (deleteId.value) { router.delete(`/suppliers/${deleteId.value}`); deleteId.value = null; } }
</script>

<template>
    <Head :title="t('Suppliers')" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ t('Suppliers') }}</h2>
                <Link href="/suppliers/create" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">{{ t('Add Supplier') }}</Link>
            </div>
            <div class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-white dark:border-sidebar-border dark:bg-gray-900">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-100 bg-gray-50 dark:border-gray-800 dark:bg-gray-800/50">
                        <tr>
                            <th class="px-4 py-3 font-medium text-gray-500">{{ t('Name') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-500">{{ t('Code') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-500">{{ t('City') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-500">{{ t('Country') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-500">{{ t('Ingredients') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-500">{{ t('Status') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-500">{{ t('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-for="s in suppliers" :key="s.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-4 py-3"><Link :href="`/suppliers/${s.id}`" class="font-medium text-gray-900 hover:text-emerald-600 dark:text-gray-100">{{ s.name }}</Link></td>
                            <td class="px-4 py-3 text-gray-500">{{ s.code ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ s.city ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ s.country }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ s.ingredients_count }}</td>
                            <td class="px-4 py-3"><HalalStatusBadge :status="s.status" size="sm" /></td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <Link :href="`/suppliers/${s.id}/edit`" class="text-sm text-blue-600 hover:text-blue-800">{{ t('Edit') }}</Link>
                                    <button @click="deleteId = s.id" class="text-sm text-red-600 hover:text-red-800">{{ t('Delete') }}</button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="suppliers.length === 0"><td colspan="7" class="px-4 py-8 text-center text-gray-500">{{ t('No data available') }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <ConfirmModal :show="deleteId !== null" @confirm="handleDelete" @cancel="deleteId = null" />
        <FlashMessage />
    </AppLayout>
</template>
