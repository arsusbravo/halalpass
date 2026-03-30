<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { useTrans } from '@/composables/useTrans';
import { useAuth } from '@/composables/useAuth';
import FlashMessage from '@/components/FlashMessage.vue';
import HalalStatusBadge from '@/components/HalalStatusBadge.vue';
import ScoreRing from '@/components/ScoreRing.vue';
import DeadlineBanner from '@/components/DeadlineBanner.vue';
import type { BreadcrumbItem } from '@/types';
import { computed } from 'vue';

const { t } = useTrans();
const { isOwner, hasCompanyContext, user } = useAuth();

const props = defineProps<{
    productSummary: {
        total_products: number;
        compliant: number;
        at_risk: number;
        non_compliant: number;
        pending: number;
        average_score: number;
    } | null;
    certSummary: {
        total: number;
        valid: number;
        expiring_soon: number;
        expired: number;
    } | null;
    expiringSoon: Array<{
        id: number;
        sh_number: string;
        expiry_date: string;
        days_until_expiry: number;
        status: string;
        ingredient: {
            id: number;
            name: string;
            supplier?: { name: string };
        };
    }>;
    onboarding: {
        company_profile: boolean;
        facilities: number;
        suppliers: number;
        ingredients: number;
        certificates: number;
        products: number;
    } | null;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
];

interface OnboardingStep {
    done: boolean;
    href: string;
    title: string;
    description: string;
    doneText: string;
}

const steps = computed<OnboardingStep[]>(() => {
    const o = props.onboarding;
    if (!o) return [];
    return [
        {
            done: o.company_profile,
            href: '/company-profile',
            title: t('Complete your Company Profile'),
            description: t('Add NPWP, BPJPH registration number, and full address'),
            doneText: t('Completed'),
        },
        {
            done: o.facilities > 0,
            href: o.facilities > 0 ? '/facilities' : '/facilities/create',
            title: t('Add your first Facility'),
            description: t('Register your production site with address and capacity'),
            doneText: `${o.facilities} ${t('Facilities')}`,
        },
        {
            done: o.suppliers > 0,
            href: o.suppliers > 0 ? '/suppliers' : '/suppliers/create',
            title: t('Add your Suppliers'),
            description: t('Register companies that supply your raw materials'),
            doneText: `${o.suppliers} ${t('Suppliers')}`,
        },
        {
            done: o.ingredients > 0,
            href: o.ingredients > 0 ? '/ingredients' : '/ingredients/create',
            title: t('Add your Ingredients'),
            description: t('List all raw materials, additives, and processing aids'),
            doneText: `${o.ingredients} ${t('Ingredients')}`,
        },
        {
            done: o.certificates > 0,
            href: o.certificates > 0 ? '/certificates' : '/certificates/create',
            title: t('Upload Halal Certificates'),
            description: t('Add SH certificates for each ingredient from MUI, LPH, or Foreign HCB'),
            doneText: `${o.certificates} ${t('Certificates')}`,
        },
        {
            done: o.products > 0,
            href: o.products > 0 ? '/products' : '/products/create',
            title: t('Create your Products'),
            description: t('Link ingredients to products and see your Halal Health Score'),
            doneText: `${o.products} ${t('Products')}`,
        },
    ];
});

const completedSteps = computed(() => steps.value.filter(s => s.done).length);
const showOnboarding = computed(() => steps.value.length > 0 && completedSteps.value < steps.value.length);
</script>

<template>
    <Head :title="t('Dashboard')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-4 p-4">
            <DeadlineBanner />

            <!-- Owner without company context -->
            <div v-if="isOwner && !hasCompanyContext" class="flex flex-col items-center justify-center rounded-xl border border-dashed border-gray-300 py-16 dark:border-gray-600">
                <div class="mb-4 inline-flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900">
                    <svg class="h-8 w-8 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                </div>
                <h2 class="mb-2 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ t('Select a Company') }}</h2>
                <p class="mb-4 text-sm text-gray-500">{{ t('Choose a company to manage its data.') }}</p>
                <Link href="/companies" class="rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-medium text-white hover:bg-emerald-700">
                    {{ t('View Companies') }}
                </Link>
            </div>

            <!-- Company context exists -->
            <template v-else>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                    {{ t('Welcome back, :name', { name: user?.name ?? '' }) }}
                </h2>

                <!-- Onboarding -->
                <div v-if="showOnboarding" class="rounded-xl border border-emerald-200 bg-emerald-50 p-6 dark:border-emerald-800 dark:bg-emerald-950">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-emerald-800 dark:text-emerald-200">{{ t('Getting Started') }}</h3>
                            <p class="text-sm text-emerald-700 dark:text-emerald-300">{{ t('Follow these steps to set up your halal compliance system.') }}</p>
                        </div>
                        <span class="rounded-full bg-emerald-200 px-3 py-1 text-sm font-semibold text-emerald-800 dark:bg-emerald-800 dark:text-emerald-200">
                            {{ completedSteps }}/{{ steps.length }}
                        </span>
                    </div>

                    <div class="mb-4 h-2 overflow-hidden rounded-full bg-emerald-200 dark:bg-emerald-800">
                        <div class="h-full rounded-full bg-emerald-600 transition-all duration-500" :style="{ width: `${(completedSteps / steps.length) * 100}%` }" />
                    </div>

                    <div class="space-y-2">
                        <Link
                            v-for="(step, index) in steps"
                            :key="index"
                            :href="step.href"
                            :class="[
                                'flex items-center gap-4 rounded-lg border px-4 py-3 transition hover:shadow-md',
                                step.done
                                    ? 'border-emerald-300 bg-emerald-100/50 dark:border-emerald-700 dark:bg-emerald-900/30'
                                    : 'border-emerald-200 bg-white dark:border-emerald-800 dark:bg-emerald-900/50'
                            ]"
                        >
                            <div :class="[
                                'flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-sm font-bold',
                                step.done
                                    ? 'bg-emerald-600 text-white'
                                    : 'bg-emerald-200 text-emerald-700 dark:bg-emerald-800 dark:text-emerald-300'
                            ]">
                                <svg v-if="step.done" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                <span v-else>{{ index + 1 }}</span>
                            </div>

                            <div class="flex-1">
                                <p :class="['font-medium', step.done ? 'text-emerald-700 dark:text-emerald-400' : 'text-gray-900 dark:text-gray-100']">
                                    {{ step.title }}
                                </p>
                                <p v-if="!step.done" class="text-xs text-gray-500">{{ step.description }}</p>
                                <p v-else class="text-xs text-emerald-600 dark:text-emerald-400">✓ {{ step.doneText }}</p>
                            </div>

                            <svg class="h-5 w-5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </Link>
                    </div>
                </div>

                <!-- Dashboard cards -->
                <template v-if="productSummary && certSummary">
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="flex items-center gap-4 rounded-xl border border-sidebar-border/70 bg-white p-5 dark:border-sidebar-border dark:bg-gray-900">
                            <ScoreRing :score="productSummary?.average_score ?? 0" :size="56" />
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('Average Score') }}</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ productSummary?.average_score ?? 0 }}/100</p>
                            </div>
                        </div>

                        <div class="rounded-xl border border-sidebar-border/70 bg-white p-5 dark:border-sidebar-border dark:bg-gray-900">
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('Total Products') }}</p>
                            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-gray-100">{{ productSummary?.total_products }}</p>
                            <div class="mt-2 flex gap-2">
                                <HalalStatusBadge status="compliant" size="sm" />
                                <span class="text-xs text-gray-500">{{ productSummary?.compliant }}</span>
                                <HalalStatusBadge status="at_risk" size="sm" />
                                <span class="text-xs text-gray-500">{{ productSummary?.at_risk }}</span>
                                <HalalStatusBadge status="non_compliant" size="sm" />
                                <span class="text-xs text-gray-500">{{ productSummary?.non_compliant }}</span>
                            </div>
                        </div>

                        <div class="rounded-xl border border-sidebar-border/70 bg-white p-5 dark:border-sidebar-border dark:bg-gray-900">
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('Halal Certificates') }}</p>
                            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-gray-100">{{ certSummary?.total }}</p>
                            <div class="mt-2 flex gap-2">
                                <HalalStatusBadge status="valid" size="sm" />
                                <span class="text-xs text-gray-500">{{ certSummary?.valid }}</span>
                                <HalalStatusBadge status="expiring_soon" size="sm" />
                                <span class="text-xs text-gray-500">{{ certSummary?.expiring_soon }}</span>
                                <HalalStatusBadge status="expired" size="sm" />
                                <span class="text-xs text-gray-500">{{ certSummary?.expired }}</span>
                            </div>
                        </div>

                        <div class="rounded-xl border border-sidebar-border/70 bg-white p-5 dark:border-sidebar-border dark:bg-gray-900">
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('Compliant') }}</p>
                            <p class="mt-1 text-2xl font-bold" :class="productSummary?.compliant === productSummary?.total_products ? 'text-emerald-600' : 'text-amber-600'">
                                {{ productSummary?.total_products ? Math.round(((productSummary?.compliant ?? 0) / productSummary.total_products) * 100) : 0 }}%
                            </p>
                            <p class="mt-1 text-xs text-gray-500">{{ productSummary?.compliant }} / {{ productSummary?.total_products }} {{ t('Products') }}</p>
                        </div>
                    </div>

                    <!-- Expiry Radar -->
                    <div class="rounded-xl border border-sidebar-border/70 bg-white dark:border-sidebar-border dark:bg-gray-900">
                        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ t('Expiry Radar') }}</h3>
                            <span class="text-sm text-gray-500">{{ t('Certificates expiring within :days days', { days: 90 }) }}</span>
                        </div>
                        <div v-if="expiringSoon.length === 0" class="px-5 py-8 text-center text-sm text-gray-500">
                            {{ t('No expiring certificates') }}
                        </div>
                        <div v-else class="divide-y divide-gray-100 dark:divide-gray-800">
                            <div v-for="cert in expiringSoon" :key="cert.id" class="flex items-center justify-between px-5 py-3">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-medium text-gray-900 dark:text-gray-100">{{ cert.ingredient.name }}</p>
                                    <p class="text-xs text-gray-500">{{ cert.sh_number }} &middot; {{ cert.ingredient.supplier?.name ?? '-' }}</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-sm tabular-nums" :class="cert.days_until_expiry <= 30 ? 'font-semibold text-red-600' : 'text-amber-600'">
                                        {{ cert.days_until_expiry }} {{ t('days left') }}
                                    </span>
                                    <HalalStatusBadge :status="cert.status" size="sm" />
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </template>
        </div>

        <FlashMessage />
    </AppLayout>
</template>