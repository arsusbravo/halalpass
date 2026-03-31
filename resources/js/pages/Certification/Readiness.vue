<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { useTrans } from '@/composables/useTrans';
import FlashMessage from '@/components/FlashMessage.vue';
import HalalStatusBadge from '@/components/HalalStatusBadge.vue';
import DeadlineBanner from '@/components/DeadlineBanner.vue';
import { type BreadcrumbItem } from '@/types';

const { t, d } = useTrans();

const props = defineProps<{
    isReady: boolean;
    readinessPercentage: number;
    checks: {
        has_facilities: boolean;
        has_suppliers: boolean;
        has_ingredients: boolean;
        has_products: boolean;
        all_certs_valid: boolean;
        all_products_compliant: boolean;
        no_expiring_certs: boolean;
        all_sjph_approved: boolean;
        any_sjph_approved: boolean;
    };
    stats: {
        facilities: number;
        suppliers: number;
        ingredients: number;
        products: number;
        certificates: number;
        products_compliant: number;
        products_total: number;
    };
    ingredientIssues: Array<{
        id: number;
        name: string;
        code: string | null;
        risk_level: string;
        supplier: string | null;
        cert_status: string;
        cert_expiry: string | null;
    }>;
    productIssues: Array<{
        id: number;
        name: string;
        code: string | null;
        halal_status: string;
        halal_health_score: number;
    }>;
    expiringSoon: Array<{
        id: number;
        sh_number: string;
        expiry_date: string;
        days_until_expiry: number;
        ingredient_name: string | null;
    }>;
    sjphStatuses: Array<{
        facility_id: number;
        facility_name: string;
        sjph_exists: boolean;
        sjph_status: string;
        sjph_completion: number;
        sjph_approved: boolean;
    }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Certification Readiness', href: '/certification' },
];

interface CheckItem {
    key: string;
    label: string;
    passed: boolean;
    action: string;
    href: string;
}

const checkItems: CheckItem[] = [
    { key: 'has_facilities', label: t('Production facilities registered'), passed: props.checks.has_facilities, action: t('Add Facility'), href: '/facilities/create' },
    { key: 'has_suppliers', label: t('Suppliers registered'), passed: props.checks.has_suppliers, action: t('Add Supplier'), href: '/suppliers/create' },
    { key: 'has_ingredients', label: t('Ingredients inventoried'), passed: props.checks.has_ingredients, action: t('Add Ingredient'), href: '/ingredients/create' },
    { key: 'has_products', label: t('Products created'), passed: props.checks.has_products, action: t('Add Product'), href: '/products/create' },
    { key: 'all_certs_valid', label: t('All required certificates are valid'), passed: props.checks.all_certs_valid, action: t('Fix certificates'), href: '/certificates' },
    { key: 'all_products_compliant', label: t('All products are halal compliant'), passed: props.checks.all_products_compliant, action: t('View products'), href: '/products' },
    { key: 'no_expiring_certs', label: t('No certificates expiring within 90 days'), passed: props.checks.no_expiring_certs, action: t('View certificates'), href: '/certificates' },
    { key: 'any_sjph_approved', label: t('At least one SJPH document approved'), passed: props.checks.any_sjph_approved, action: t('Open SJPH'), href: '/facilities' },
];

const passedCount = checkItems.filter(c => c.passed).length;
</script>

<template>
    <Head :title="t('Certification Readiness')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-4xl p-4">
            <DeadlineBanner />

            <!-- Ready / Not Ready Banner -->
            <div :class="[
                'mt-4 rounded-xl p-8 text-center',
                isReady
                    ? 'border-2 border-emerald-300 bg-emerald-50 dark:border-emerald-700 dark:bg-emerald-950'
                    : 'border-2 border-amber-300 bg-amber-50 dark:border-amber-700 dark:bg-amber-950'
            ]">
                <div :class="[
                    'mx-auto mb-4 inline-flex h-20 w-20 items-center justify-center rounded-full',
                    isReady ? 'bg-emerald-200 dark:bg-emerald-800' : 'bg-amber-200 dark:bg-amber-800'
                ]">
                    <svg v-if="isReady" class="h-10 w-10 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                    <svg v-else class="h-10 w-10 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>

                <h2 :class="['text-2xl font-bold', isReady ? 'text-emerald-800 dark:text-emerald-200' : 'text-amber-800 dark:text-amber-200']">
                    <template v-if="isReady">{{ t('Ready to Submit to SIHALAL!') }}</template>
                    <template v-else>{{ t('Not Ready Yet') }}</template>
                </h2>

                <p :class="['mt-2 text-sm', isReady ? 'text-emerald-700 dark:text-emerald-300' : 'text-amber-700 dark:text-amber-300']">
                    <template v-if="isReady">{{ t('All requirements met. Download your export and submit to sihalal.halal.go.id') }}</template>
                    <template v-else>{{ t('Complete the items below before submitting to SIHALAL.') }}</template>
                </p>

                <div class="mt-4 flex items-center justify-center gap-4">
                    <span class="text-3xl font-bold" :class="isReady ? 'text-emerald-600' : 'text-amber-600'">{{ readinessPercentage }}%</span>
                    <span class="text-sm text-gray-500">{{ passedCount }}/{{ checkItems.length }} {{ t('checks passed') }}</span>
                </div>

                <Link v-if="isReady" href="/export" class="mt-6 inline-block rounded-lg bg-emerald-600 px-8 py-3 text-sm font-semibold text-white hover:bg-emerald-700">
                    {{ t('Download Export for SIHALAL') }}
                </Link>
            </div>

            <!-- Readiness Checklist -->
            <div class="mt-6 rounded-xl border border-sidebar-border/70 bg-white dark:border-sidebar-border dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ t('Readiness Checklist') }}</h3>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    <div v-for="check in checkItems" :key="check.key" class="flex items-center justify-between px-5 py-3">
                        <div class="flex items-center gap-3">
                            <div :class="[
                                'flex h-6 w-6 shrink-0 items-center justify-center rounded-full',
                                check.passed ? 'bg-emerald-100 dark:bg-emerald-900' : 'bg-red-100 dark:bg-red-900'
                            ]">
                                <svg v-if="check.passed" class="h-3.5 w-3.5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                <svg v-else class="h-3.5 w-3.5 text-red-500 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </div>
                            <span :class="check.passed ? 'text-gray-600 dark:text-gray-400' : 'font-medium text-gray-900 dark:text-gray-100'">{{ check.label }}</span>
                        </div>
                        <Link v-if="!check.passed" :href="check.href" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-emerald-700">
                            {{ check.action }}
                        </Link>
                        <span v-else class="text-xs text-emerald-600">✓</span>
                    </div>
                </div>
            </div>

            <!-- Blocking Issues: Missing/Expired Certificates -->
            <div v-if="ingredientIssues.length > 0" class="mt-6 rounded-xl border border-red-200 bg-white dark:border-red-800 dark:bg-gray-900">
                <div class="border-b border-red-100 px-5 py-4 dark:border-red-800">
                    <h3 class="font-semibold text-red-800 dark:text-red-200">{{ t('Certificates to Fix') }} ({{ ingredientIssues.length }})</h3>
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ t('These ingredients need valid halal certificates before you can submit.') }}</p>
                </div>
                <div class="divide-y divide-red-100 dark:divide-red-800/50">
                    <div v-for="issue in ingredientIssues" :key="issue.id" class="flex items-center justify-between px-5 py-3">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ issue.name }} <span class="text-xs text-gray-400">({{ issue.code }})</span></p>
                            <p class="text-xs text-gray-500">
                                {{ issue.supplier ?? '-' }} &middot;
                                <span :class="issue.risk_level === 'high_risk' ? 'text-red-600 font-semibold' : 'text-amber-600'">
                                    {{ issue.risk_level === 'high_risk' ? t('High Risk') : t('Medium Risk') }}
                                </span>
                                &middot;
                                <span class="text-red-600">{{ issue.cert_status === 'expired' ? t('Expired') + (issue.cert_expiry ? ` (${d(issue.cert_expiry)})` : '') : t('Missing') }}</span>
                            </p>
                        </div>
                        <Link :href="`/certificates/create?ingredient_id=${issue.id}`" class="rounded-lg border border-red-300 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-50 dark:border-red-700 dark:text-red-300 dark:hover:bg-red-950">
                            {{ issue.cert_status === 'expired' ? t('Renew') : t('Upload') }}
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Non-Compliant Products -->
            <div v-if="productIssues.length > 0" class="mt-6 rounded-xl border border-amber-200 bg-white dark:border-amber-800 dark:bg-gray-900">
                <div class="border-b border-amber-100 px-5 py-4 dark:border-amber-800">
                    <h3 class="font-semibold text-amber-800 dark:text-amber-200">{{ t('Products Not Compliant') }} ({{ productIssues.length }})</h3>
                </div>
                <div class="divide-y divide-amber-100 dark:divide-amber-800/50">
                    <div v-for="product in productIssues" :key="product.id" class="flex items-center justify-between px-5 py-3">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ product.name }} <span class="text-xs text-gray-400">({{ product.code }})</span></p>
                            <p class="text-xs text-gray-500">{{ t('Score') }}: {{ product.halal_health_score }}/100</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <HalalStatusBadge :status="product.halal_status" size="sm" />
                            <Link :href="`/products/${product.id}`" class="text-xs text-emerald-600 hover:text-emerald-800">{{ t('View') }}</Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Expiring Soon -->
            <div v-if="expiringSoon.length > 0" class="mt-6 rounded-xl border border-amber-200 bg-white dark:border-amber-800 dark:bg-gray-900">
                <div class="border-b border-amber-100 px-5 py-4 dark:border-amber-800">
                    <h3 class="font-semibold text-amber-800 dark:text-amber-200">{{ t('Certificates Expiring Soon') }} ({{ expiringSoon.length }})</h3>
                    <p class="mt-1 text-xs text-amber-600">{{ t('Renew these before submitting to avoid rejection.') }}</p>
                </div>
                <div class="divide-y divide-amber-100 dark:divide-amber-800/50">
                    <div v-for="cert in expiringSoon" :key="cert.id" class="flex items-center justify-between px-5 py-3">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ cert.ingredient_name }}</p>
                            <p class="text-xs text-gray-500">{{ cert.sh_number }} &middot; {{ d(cert.expiry_date) }}</p>
                        </div>
                        <span class="text-sm font-semibold tabular-nums" :class="cert.days_until_expiry <= 30 ? 'text-red-600' : 'text-amber-600'">
                            {{ cert.days_until_expiry }} {{ t('days left') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- SJPH Status per Facility -->
            <div class="mt-6 rounded-xl border border-sidebar-border/70 bg-white dark:border-sidebar-border dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ t('SJPH Status') }}</h3>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    <Link v-for="sjph in sjphStatuses" :key="sjph.facility_id" :href="`/sjph/${sjph.facility_id}`" class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ sjph.facility_name }}</p>
                            <p class="text-xs text-gray-500">{{ t('Completion') }}: {{ sjph.sjph_completion }}%</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="h-1.5 w-20 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                <div :class="sjph.sjph_completion === 100 ? 'bg-emerald-500' : 'bg-amber-500'" class="h-full rounded-full" :style="{ width: `${sjph.sjph_completion}%` }" />
                            </div>
                            <HalalStatusBadge :status="sjph.sjph_status" size="sm" />
                        </div>
                    </Link>
                </div>
            </div>

            <!-- Next Steps -->
            <div class="mt-6 rounded-xl border border-blue-200 bg-blue-50 p-6 dark:border-blue-800 dark:bg-blue-950">
                <h3 class="mb-3 font-semibold text-blue-800 dark:text-blue-200">{{ t('After Getting Ready') }}</h3>
                <ol class="list-inside list-decimal space-y-2 text-sm text-blue-700 dark:text-blue-300">
                    <li><strong>{{ t('Download Export') }}</strong> — {{ t('Go to Export page, generate ZIP with Daftar Bahan + Matriks Bahan') }}</li>
                    <li><strong>{{ t('Upload to SIHALAL') }}</strong> — {{ t('Visit sihalal.halal.go.id, log in, upload the ZIP files') }}</li>
                    <li><strong>{{ t('LPH Review') }}</strong> — {{ t('Halal Inspection Body reviews your documents (5-10 working days)') }}</li>
                    <li><strong>{{ t('Facility Audit') }}</strong> — {{ t('LPH may visit your facility to verify SJPH compliance') }}</li>
                    <li><strong>{{ t('MUI Fatwa') }}</strong> — {{ t('Indonesian Ulema Council issues halal fatwa') }}</li>
                    <li><strong>{{ t('Certificate Issued') }}</strong> — {{ t('BPJPH issues your Halal Certificate (valid 4 years)') }}</li>
                </ol>
            </div>

        </div>
        <FlashMessage />
    </AppLayout>
</template>