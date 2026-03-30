<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { useTrans } from '@/composables/useTrans';
import FlashMessage from '@/components/FlashMessage.vue';
import HalalStatusBadge from '@/components/HalalStatusBadge.vue';
import type { BreadcrumbItem } from '@/types';

const { t } = useTrans();

const props = defineProps<{
    companies: Array<{
        id: number;
        name: string;
        npwp: string | null;
        city: string | null;
        province: string | null;
        status: string;
        facilities_count: number;
        products_count: number;
        users_count: number;
        ingredients_count: number;
        cert_summary: { valid: number; expiring_soon: number; expired: number };
    }>;
    activeCompanyId: number | null;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Companies', href: '/companies' },
];

function enterCompany(companyId: number) {
    router.post(`/companies/${companyId}/enter`);
}

function leaveCompany() {
    router.post('/companies/leave');
}
</script>

<template>
    <Head :title="t('Companies')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ t('Companies') }}</h2>
                <button
                    v-if="activeCompanyId"
                    @click="leaveCompany"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800"
                >
                    {{ t('Leave Company Context') }}
                </button>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <button
                    v-for="company in companies"
                    :key="company.id"
                    @click="enterCompany(company.id)"
                    :class="[
                        'group rounded-xl border bg-white p-5 text-left transition hover:shadow-md dark:bg-gray-900',
                        activeCompanyId === company.id
                            ? 'border-emerald-500 ring-2 ring-emerald-500/20'
                            : 'border-sidebar-border/70 hover:border-emerald-300 dark:border-sidebar-border dark:hover:border-emerald-700'
                    ]"
                >
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="font-semibold text-gray-900 group-hover:text-emerald-600 dark:text-gray-100">{{ company.name }}</h3>
                        <HalalStatusBadge :status="company.status" size="sm" />
                    </div>

                    <p class="mb-3 text-xs text-gray-500">{{ company.city }}, {{ company.province }}</p>

                    <!-- Stats -->
                    <div class="mb-3 grid grid-cols-2 gap-2 text-xs">
                        <div class="rounded bg-gray-50 px-2 py-1.5 dark:bg-gray-800">
                            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ company.facilities_count }}</span>
                            <span class="ml-1 text-gray-500">{{ t('Facilities') }}</span>
                        </div>
                        <div class="rounded bg-gray-50 px-2 py-1.5 dark:bg-gray-800">
                            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ company.products_count }}</span>
                            <span class="ml-1 text-gray-500">{{ t('Products') }}</span>
                        </div>
                        <div class="rounded bg-gray-50 px-2 py-1.5 dark:bg-gray-800">
                            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ company.ingredients_count }}</span>
                            <span class="ml-1 text-gray-500">{{ t('Ingredients') }}</span>
                        </div>
                        <div class="rounded bg-gray-50 px-2 py-1.5 dark:bg-gray-800">
                            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ company.users_count }}</span>
                            <span class="ml-1 text-gray-500">{{ t('Users') }}</span>
                        </div>
                    </div>

                    <!-- Cert health bar -->
                    <div class="flex gap-1 overflow-hidden rounded-full">
                        <div v-if="company.cert_summary.valid" class="h-1.5 bg-emerald-500" :style="{ flex: company.cert_summary.valid }" />
                        <div v-if="company.cert_summary.expiring_soon" class="h-1.5 bg-amber-500" :style="{ flex: company.cert_summary.expiring_soon }" />
                        <div v-if="company.cert_summary.expired" class="h-1.5 bg-red-500" :style="{ flex: company.cert_summary.expired }" />
                        <div v-if="!company.cert_summary.valid && !company.cert_summary.expiring_soon && !company.cert_summary.expired" class="h-1.5 flex-1 bg-gray-200 dark:bg-gray-700" />
                    </div>
                    <div class="mt-1 flex gap-3 text-xs text-gray-400">
                        <span class="text-emerald-600">{{ company.cert_summary.valid }} {{ t('Valid') }}</span>
                        <span class="text-amber-600">{{ company.cert_summary.expiring_soon }} {{ t('Expiring Soon') }}</span>
                        <span class="text-red-600">{{ company.cert_summary.expired }} {{ t('Expired') }}</span>
                    </div>

                    <!-- Active indicator -->
                    <div v-if="activeCompanyId === company.id" class="mt-3 text-xs font-medium text-emerald-600">
                        ● {{ t('Active') }}
                    </div>
                </button>
            </div>

            <div v-if="!companies.length" class="rounded-xl border border-dashed border-gray-300 py-12 text-center dark:border-gray-600">
                <p class="text-gray-500">{{ t('No data available') }}</p>
            </div>
        </div>
        <FlashMessage />
    </AppLayout>
</template>