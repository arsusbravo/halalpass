<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { useTrans } from '@/composables/useTrans';
import FlashMessage from '@/components/FlashMessage.vue';
import HalalStatusBadge from '@/components/HalalStatusBadge.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';
import { type BreadcrumbItem } from '@/types';
import { ref } from 'vue';

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

// Deactivate / Activate
function toggleStatus(companyId: number) {
    router.patch(`/companies/${companyId}/toggle-status`);
}

// Delete
const deleteTarget = ref<{ id: number; name: string } | null>(null);

function confirmDelete(company: { id: number; name: string }) {
    deleteTarget.value = company;
}

function executeDelete() {
    if (!deleteTarget.value) return;
    router.delete(`/companies/${deleteTarget.value.id}`, {
        onFinish: () => { deleteTarget.value = null; },
    });
}
</script>

<template>
    <Head :title="t('Companies')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ t('Companies') }}</h2>
                <div class="flex gap-2">
                    <button
                        v-if="activeCompanyId"
                        @click="leaveCompany"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800"
                    >
                        {{ t('Leave Company Context') }}
                    </button>
                    <Link href="/companies/create" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                        + {{ t('Add Company') }}
                    </Link>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="company in companies"
                    :key="company.id"
                    :class="[
                        'group rounded-xl border bg-white p-5 transition dark:bg-gray-900',
                        company.status === 'inactive'
                            ? 'border-gray-200 opacity-60 dark:border-gray-700'
                            : activeCompanyId === company.id
                                ? 'border-emerald-500 ring-2 ring-emerald-500/20'
                                : 'border-sidebar-border/70 dark:border-sidebar-border'
                    ]"
                >
                    <!-- Header -->
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ company.name }}</h3>
                        <HalalStatusBadge :status="company.status" size="sm" />
                    </div>

                    <p class="mb-3 text-xs text-gray-500">
                        {{ [company.city, company.province].filter(Boolean).join(', ') || '-' }}
                    </p>

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

                    <!-- Actions -->
                    <div class="mt-4 flex items-center gap-2 border-t border-gray-100 pt-3 dark:border-gray-800">
                        <button
                            v-if="company.status === 'active'"
                            @click="enterCompany(company.id)"
                            class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-emerald-700"
                        >
                            {{ t('Enter') }}
                        </button>

                        <button
                            @click="toggleStatus(company.id)"
                            :class="[
                                'rounded-lg border px-3 py-1.5 text-xs font-medium',
                                company.status === 'active'
                                    ? 'border-amber-300 text-amber-700 hover:bg-amber-50 dark:border-amber-700 dark:text-amber-400 dark:hover:bg-amber-950'
                                    : 'border-emerald-300 text-emerald-700 hover:bg-emerald-50 dark:border-emerald-700 dark:text-emerald-400 dark:hover:bg-emerald-950'
                            ]"
                        >
                            {{ company.status === 'active' ? t('Deactivate') : t('Activate') }}
                        </button>

                        <button
                            @click="confirmDelete(company)"
                            class="ml-auto rounded-lg border border-red-300 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 dark:border-red-700 dark:text-red-400 dark:hover:bg-red-950"
                        >
                            {{ t('Delete') }}
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="!companies.length" class="rounded-xl border border-dashed border-gray-300 py-12 text-center dark:border-gray-600">
                <p class="mb-4 text-gray-500">{{ t('No companies yet') }}</p>
                <Link href="/companies/create" class="rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-medium text-white hover:bg-emerald-700">
                    + {{ t('Add Company') }}
                </Link>
            </div>
        </div>

        <!-- Delete confirmation modal -->
        <ConfirmModal
            :show="!!deleteTarget"
            :title="t('Delete Company')"
            :message="t('Are you sure you want to delete :name? This cannot be undone. Companies with users cannot be deleted.', { name: deleteTarget?.name ?? '' })"
            :confirm-text="t('Delete')"
            :cancel-text="t('Cancel')"
            variant="danger"
            @confirm="executeDelete"
            @cancel="deleteTarget = null"
        />

        <FlashMessage />
    </AppLayout>
</template>