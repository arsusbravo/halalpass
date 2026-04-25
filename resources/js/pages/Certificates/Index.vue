<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { useTrans } from '@/composables/useTrans';
import FlashMessage from '@/components/FlashMessage.vue';
import HalalStatusBadge from '@/components/HalalStatusBadge.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';
import type { BreadcrumbItem } from '@/types';
import { ref } from 'vue';

const { t, d } = useTrans();
const props = defineProps<{
    certificates: Array<{ id: number; sh_number: string; issuing_body: string; issuing_body_name: string | null; expiry_date: string; days_until_expiry: number; status: string; ingredient: { id: number; name: string }; uploader: { name: string } | null }>;
    summary: { total: number; valid: number; expiring_soon: number; expired: number };
    filters: { status?: string; issuing_body?: string };
}>();
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: '/dashboard' }, { title: 'Certificates', href: '/certificates' }];
const deleteId = ref<number | null>(null);
function filterByStatus(status: string) { router.get('/certificates', { ...props.filters, status: status || undefined }, { preserveState: true }); }
function handleDelete() { if (deleteId.value) { router.delete(`/certificates/${deleteId.value}`); deleteId.value = null; } }
</script>

<template>
    <Head :title="t('Halal Certificates')" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ t('Certificate Vault') }}</h2>
                <Link href="/certificates/create" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">{{ t('Add Certificate') }}</Link>
            </div>

            <!-- Filter tabs -->
            <div class="mb-4 flex gap-2">
                <button @click="filterByStatus('')" :class="[!filters.status ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300', 'rounded-lg px-3 py-1.5 text-sm font-medium transition']">{{ t('All') }} ({{ summary.total }})</button>
                <button @click="filterByStatus('valid')" :class="[filters.status === 'valid' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300', 'rounded-lg px-3 py-1.5 text-sm font-medium transition']">{{ t('Valid') }} ({{ summary.valid }})</button>
                <button @click="filterByStatus('expiring_soon')" :class="[filters.status === 'expiring_soon' ? 'bg-amber-600 text-white' : 'bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300', 'rounded-lg px-3 py-1.5 text-sm font-medium transition']">{{ t('Expiring Soon') }} ({{ summary.expiring_soon }})</button>
                <button @click="filterByStatus('expired')" :class="[filters.status === 'expired' ? 'bg-red-600 text-white' : 'bg-red-50 text-red-700 dark:bg-red-950 dark:text-red-300', 'rounded-lg px-3 py-1.5 text-sm font-medium transition']">{{ t('Expired') }} ({{ summary.expired }})</button>
            </div>

            <div class="overflow-x-auto rounded-xl border border-sidebar-border/70 bg-white dark:border-sidebar-border dark:bg-gray-900">
                <table class="w-full text-left text-sm">
                    <thead class="border-b bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <th class="px-4 py-3 font-medium text-gray-500">{{ t('Ingredient') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-500">{{ t('SH Number') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-500">{{ t('Issuing Body') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-500">{{ t('Expiry Date') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-500">{{ t('Status') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-500">{{ t('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-for="cert in certificates" :key="cert.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-4 py-3"><Link :href="`/ingredients/${cert.ingredient.id}`" class="font-medium text-gray-900 hover:text-emerald-600 dark:text-gray-100">{{ cert.ingredient.name }}</Link></td>
                            <td class="px-4 py-3"><Link :href="`/certificates/${cert.id}`" class="font-mono text-sm text-emerald-600 hover:text-emerald-800">{{ cert.sh_number }}</Link></td>
                            <td class="px-4 py-3 text-gray-500">{{ cert.issuing_body_name ?? cert.issuing_body }}</td>
                            <td class="px-4 py-3 text-gray-500">
                                {{ d(cert.expiry_date) }}
                                <span class="ml-1 text-xs" :class="cert.days_until_expiry <= 0 ? 'text-red-600 font-bold' : cert.days_until_expiry <= 30 ? 'text-red-600 font-semibold' : cert.days_until_expiry <= 90 ? 'text-amber-600' : 'text-gray-400'">
                                    ({{ cert.days_until_expiry <= 0 ? t('Expired') : cert.days_until_expiry + 'd' }})
                                </span>
                            </td>
                            <td class="px-4 py-3"><HalalStatusBadge :status="cert.status" size="sm" /></td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <Link :href="`/certificates/${cert.id}/edit`" class="text-sm text-blue-600 hover:text-blue-800">{{ t('Edit') }}</Link>
                                    <button @click="deleteId = cert.id" class="text-sm text-red-600 hover:text-red-800">{{ t('Delete') }}</button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!certificates.length"><td colspan="6" class="px-4 py-8 text-center text-gray-500">{{ t('No data available') }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <ConfirmModal :show="deleteId !== null" @confirm="handleDelete" @cancel="deleteId = null" />
        <FlashMessage />
    </AppLayout>
</template>
