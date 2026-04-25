<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { useTrans } from '@/composables/useTrans';
import HalalStatusBadge from '@/components/HalalStatusBadge.vue';
import type { BreadcrumbItem } from '@/types';

const { t, d } = useTrans();
const props = defineProps<{
    certificate: { id: number; sh_number: string; issuing_body: string; issuing_body_name: string | null; issue_date: string | null; expiry_date: string; days_until_expiry: number; status: string; document_path: string | null; original_filename: string | null; notes: string | null; ingredient: { id: number; name: string; supplier: { name: string } | null }; uploader: { name: string } | null };
}>();
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: '/dashboard' }, { title: 'Certificates', href: '/certificates' }, { title: props.certificate.sh_number, href: '#' }];
</script>

<template>
    <Head :title="certificate.sh_number" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="lg:mx-auto lg:max-w-3xl p-4">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ t('Certificate Details') }}</h2>
                <div class="flex gap-2">
                    <a v-if="certificate.document_path" :href="`/certificates/${certificate.id}/download`" class="rounded-lg border border-emerald-600 px-4 py-2 text-sm font-medium text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950">{{ t('Download Document') }}</a>
                    <Link :href="`/certificates/${certificate.id}/edit`" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">{{ t('Edit') }}</Link>
                </div>
            </div>
            <div class="grid gap-4 rounded-xl border border-sidebar-border/70 bg-white p-5 sm:grid-cols-2 dark:border-sidebar-border dark:bg-gray-900">
                <div><p class="text-xs text-gray-500">{{ t('SH Number') }}</p><p class="font-mono text-sm font-medium text-gray-900 dark:text-gray-100">{{ certificate.sh_number }}</p></div>
                <div><p class="text-xs text-gray-500">{{ t('Status') }}</p><HalalStatusBadge :status="certificate.status" /></div>
                <div><p class="text-xs text-gray-500">{{ t('Ingredient') }}</p><Link :href="`/ingredients/${certificate.ingredient.id}`" class="text-sm text-emerald-600 hover:text-emerald-800">{{ certificate.ingredient.name }}</Link></div>
                <div><p class="text-xs text-gray-500">{{ t('Supplier') }}</p><p class="text-sm text-gray-900 dark:text-gray-100">{{ certificate.ingredient.supplier?.name ?? '-' }}</p></div>
                <div><p class="text-xs text-gray-500">{{ t('Issuing Body') }}</p><p class="text-sm text-gray-900 dark:text-gray-100">{{ certificate.issuing_body }} — {{ certificate.issuing_body_name ?? '-' }}</p></div>
                <div><p class="text-xs text-gray-500">{{ t('Issue Date') }}</p><p class="text-sm text-gray-900 dark:text-gray-100">{{ d(certificate.issue_date, true) ?? '-' }}</p></div>
                <div><p class="text-xs text-gray-500">{{ t('Expiry Date') }}</p><p class="text-sm text-gray-900 dark:text-gray-100">{{ certificate.expiry_date }} <span class="ml-1 text-xs" :class="certificate.days_until_expiry <= 0 ? 'text-red-600 font-bold' : certificate.days_until_expiry <= 90 ? 'text-amber-600' : 'text-gray-400'">({{ certificate.days_until_expiry }}{{ t('days left') }})</span></p></div>
                <div><p class="text-xs text-gray-500">{{ t('Uploaded By') }}</p><p class="text-sm text-gray-900 dark:text-gray-100">{{ certificate.uploader?.name ?? '-' }}</p></div>
                <div><p class="text-xs text-gray-500">{{ t('Document') }}</p><p class="text-sm text-gray-900 dark:text-gray-100">{{ certificate.original_filename ?? t('No data available') }}</p></div>
                <div v-if="certificate.notes" class="sm:col-span-2"><p class="text-xs text-gray-500">{{ t('Notes') }}</p><p class="text-sm text-gray-900 dark:text-gray-100">{{ certificate.notes }}</p></div>
            </div>
        </div>
    </AppLayout>
</template>
