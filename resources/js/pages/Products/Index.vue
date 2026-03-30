<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { useTrans } from '@/composables/useTrans';
import FlashMessage from '@/components/FlashMessage.vue';
import HalalStatusBadge from '@/components/HalalStatusBadge.vue';
import ScoreRing from '@/components/ScoreRing.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';
import type { BreadcrumbItem } from '@/types';
import { ref } from 'vue';

const { t } = useTrans();

const props = defineProps<{
    products: Array<{
        id: number;
        name: string;
        code: string | null;
        brand: string | null;
        halal_status: string;
        halal_health_score: number;
        status: string;
        facility: { id: number; name: string };
    }>;
    summary: {
        total_products: number;
        compliant: number;
        at_risk: number;
        non_compliant: number;
        pending: number;
        average_score: number;
    };
    facilities: Array<{ id: number; name: string; code: string | null }>;
    filters: { facility_id?: string; halal_status?: string; status?: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Products', href: '/products' },
];

const deleteId = ref<number | null>(null);

function applyFilter(key: string, value: string) {
    router.get('/products', { ...props.filters, [key]: value || undefined }, { preserveState: true });
}

function handleDelete() {
    if (deleteId.value) {
        router.delete(`/products/${deleteId.value}`);
        deleteId.value = null;
    }
}
</script>

<template>
    <Head :title="t('Products')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <!-- Header -->
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ t('Products') }}</h2>
                <Link href="/products/create" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                    {{ t('Add Product') }}
                </Link>
            </div>

            <!-- Quick Stats -->
            <div class="mb-4 grid grid-cols-5 gap-3">
                <button @click="applyFilter('halal_status', '')" :class="[!filters.halal_status ? 'ring-2 ring-emerald-500' : '', 'rounded-lg border border-sidebar-border/70 bg-white p-3 text-center transition dark:border-sidebar-border dark:bg-gray-900']">
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ summary.total_products }}</p>
                    <p class="text-xs text-gray-500">{{ t('All') }}</p>
                </button>
                <button @click="applyFilter('halal_status', 'compliant')" :class="[filters.halal_status === 'compliant' ? 'ring-2 ring-emerald-500' : '', 'rounded-lg border border-sidebar-border/70 bg-white p-3 text-center transition dark:border-sidebar-border dark:bg-gray-900']">
                    <p class="text-2xl font-bold text-emerald-600">{{ summary.compliant }}</p>
                    <p class="text-xs text-gray-500">{{ t('Compliant') }}</p>
                </button>
                <button @click="applyFilter('halal_status', 'at_risk')" :class="[filters.halal_status === 'at_risk' ? 'ring-2 ring-amber-500' : '', 'rounded-lg border border-sidebar-border/70 bg-white p-3 text-center transition dark:border-sidebar-border dark:bg-gray-900']">
                    <p class="text-2xl font-bold text-amber-600">{{ summary.at_risk }}</p>
                    <p class="text-xs text-gray-500">{{ t('At Risk') }}</p>
                </button>
                <button @click="applyFilter('halal_status', 'non_compliant')" :class="[filters.halal_status === 'non_compliant' ? 'ring-2 ring-red-500' : '', 'rounded-lg border border-sidebar-border/70 bg-white p-3 text-center transition dark:border-sidebar-border dark:bg-gray-900']">
                    <p class="text-2xl font-bold text-red-600">{{ summary.non_compliant }}</p>
                    <p class="text-xs text-gray-500">{{ t('Non-Compliant') }}</p>
                </button>
                <button @click="applyFilter('halal_status', 'pending')" :class="[filters.halal_status === 'pending' ? 'ring-2 ring-gray-400' : '', 'rounded-lg border border-sidebar-border/70 bg-white p-3 text-center transition dark:border-sidebar-border dark:bg-gray-900']">
                    <p class="text-2xl font-bold text-gray-600">{{ summary.pending }}</p>
                    <p class="text-xs text-gray-500">{{ t('Pending') }}</p>
                </button>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-white dark:border-sidebar-border dark:bg-gray-900">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-100 bg-gray-50 dark:border-gray-800 dark:bg-gray-800/50">
                        <tr>
                            <th class="px-4 py-3 font-medium text-gray-500">{{ t('Product Name') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-500">{{ t('Code') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-500">{{ t('Facility') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-500">{{ t('Halal Health Score') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-500">{{ t('Status') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-500">{{ t('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-for="product in products" :key="product.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-4 py-3">
                                <Link :href="`/products/${product.id}`" class="font-medium text-gray-900 hover:text-emerald-600 dark:text-gray-100">{{ product.name }}</Link>
                                <p v-if="product.brand" class="text-xs text-gray-500">{{ product.brand }}</p>
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ product.code ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ product.facility.name }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <ScoreRing :score="product.halal_health_score" :size="32" />
                                    <HalalStatusBadge :status="product.halal_status" size="sm" />
                                </div>
                            </td>
                            <td class="px-4 py-3"><HalalStatusBadge :status="product.status" size="sm" /></td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <Link :href="`/products/${product.id}`" class="text-sm text-emerald-600 hover:text-emerald-800">{{ t('View') }}</Link>
                                    <Link :href="`/products/${product.id}/edit`" class="text-sm text-blue-600 hover:text-blue-800">{{ t('Edit') }}</Link>
                                    <button @click="deleteId = product.id" class="text-sm text-red-600 hover:text-red-800">{{ t('Delete') }}</button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="products.length === 0">
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">{{ t('No data available') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <ConfirmModal :show="deleteId !== null" @confirm="handleDelete" @cancel="deleteId = null" />
        <FlashMessage />
    </AppLayout>
</template>
