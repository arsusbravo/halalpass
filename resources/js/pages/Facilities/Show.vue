<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { useTrans } from '@/composables/useTrans';
import HalalStatusBadge from '@/components/HalalStatusBadge.vue';
import type { BreadcrumbItem } from '@/types';

const { t } = useTrans();

const props = defineProps<{
    facility: {
        id: number;
        name: string;
        code: string | null;
        address: string;
        city: string;
        province: string;
        postal_code: string | null;
        phone: string | null;
        pic_name: string | null;
        production_capacity: string | null;
        sjph_status: string;
        status: string;
        products: Array<{ id: number; name: string; code: string | null; halal_status: string; halal_health_score: number }>;
        sjph_documents: Array<{ id: number; version: string; status: string; }>;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Facilities', href: '/facilities' },
    { title: props.facility.name, href: '#' },
];
</script>

<template>
    <Head :title="facility.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="lg:mx-auto lg:max-w-4xl p-4">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ facility.name }}</h2>
                <div class="flex gap-2">
                    <Link :href="`/sjph/${facility.id}`" class="rounded-lg border border-emerald-600 px-4 py-2 text-sm font-medium text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950">
                        {{ t('SJPH Document') }}
                    </Link>
                    <Link :href="`/facilities/${facility.id}/edit`" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                        {{ t('Edit') }}
                    </Link>
                </div>
            </div>

            <!-- Info Grid -->
            <div class="mb-6 grid gap-4 rounded-xl border border-sidebar-border/70 bg-white p-5 sm:grid-cols-2 dark:border-sidebar-border dark:bg-gray-900">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ t('Facility Code') }}</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ facility.code ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ t('Status') }}</p>
                    <HalalStatusBadge :status="facility.status" />
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ t('Address') }}</p>
                    <p class="text-sm text-gray-900 dark:text-gray-100">{{ facility.address }}, {{ facility.city }}, {{ facility.province }} {{ facility.postal_code }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ t('Phone') }}</p>
                    <p class="text-sm text-gray-900 dark:text-gray-100">{{ facility.phone ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ t('Person In Charge') }}</p>
                    <p class="text-sm text-gray-900 dark:text-gray-100">{{ facility.pic_name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ t('Production Capacity') }}</p>
                    <p class="text-sm text-gray-900 dark:text-gray-100">{{ facility.production_capacity ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ t('SJPH Status') }}</p>
                    <HalalStatusBadge :status="facility.sjph_status" />
                </div>
            </div>

            <!-- Products in this facility -->
            <h3 class="mb-3 font-semibold text-gray-900 dark:text-gray-100">{{ t('Products') }} ({{ facility.products.length }})</h3>
            <div class="overflow-x-auto rounded-xl border border-sidebar-border/70 bg-white dark:border-sidebar-border dark:bg-gray-900">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-100 bg-gray-50 dark:border-gray-800 dark:bg-gray-800/50">
                        <tr>
                            <th class="px-4 py-3 font-medium text-gray-500">{{ t('Product Name') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-500">{{ t('Code') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-500">{{ t('Halal Status') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-500">{{ t('Halal Health Score') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-for="product in facility.products" :key="product.id">
                            <td class="px-4 py-3">
                                <Link :href="`/products/${product.id}`" class="font-medium text-emerald-600 hover:text-emerald-800">{{ product.name }}</Link>
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ product.code ?? '-' }}</td>
                            <td class="px-4 py-3"><HalalStatusBadge :status="product.halal_status" size="sm" /></td>
                            <td class="px-4 py-3 font-medium">{{ product.halal_health_score }}/100</td>
                        </tr>
                        <tr v-if="facility.products.length === 0">
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500">{{ t('No data available') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
