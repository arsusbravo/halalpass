<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { useTrans } from '@/composables/useTrans';
import HalalStatusBadge from '@/components/HalalStatusBadge.vue';
import { type BreadcrumbItem } from '@/types';

const { t } = useTrans();

const props = defineProps<{
    ingredient: {
        id: number;
        name: string;
        code: string | null;
        type: string;
        brand: string | null;
        manufacturer: string | null;
        origin_country: string | null;
        category: string;
        halal_risk_level: string;
        sh_number: string | null;
        cert_status: string | null;
        notes: string | null;
        supplier?: { id: number; name: string } | null;
        children?: Array<{
            id: number;
            name: string;
            code: string | null;
            halal_risk_level: string;
            sh_number: string | null;
            cert_status: string | null;
        }>;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Ingredients', href: '/ingredients' },
    { title: props.ingredient.name, href: '#' },
];

const riskLabels: Record<string, string> = {
    no_risk: 'No Risk',
    low_risk: 'Low Risk',
    medium_risk: 'Medium Risk',
    high_risk: 'High Risk',
};

const categoryLabels: Record<string, string> = {
    bahan_baku: 'Raw Material',
    bahan_tambahan: 'Additive',
    bahan_penolong: 'Processing Aid',
};

function deleteIngredient() {
    if (confirm(t('Are you sure?'))) {
        router.delete(`/ingredients/${props.ingredient.id}`);
    }
}
</script>

<template>
    <Head :title="ingredient.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="lg:mx-auto p-4">
            <!-- Header -->
            <div class="mb-6 flex items-start justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ ingredient.name }}</h2>
                    <p class="mt-1 text-sm text-gray-500">{{ ingredient.code }}</p>
                </div>
                <div class="flex gap-2">
                    <Link :href="`/ingredients/${ingredient.id}/edit`" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                        {{ t('Edit') }}
                    </Link>
                    <button @click="deleteIngredient" class="rounded-lg border border-red-300 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:border-red-700 dark:text-red-400">
                        {{ t('Delete') }}
                    </button>
                </div>
            </div>

            <!-- Info grid -->
            <div class="mb-6 rounded-xl border border-sidebar-border/70 bg-white dark:border-sidebar-border dark:bg-gray-900">
                <div class="grid grid-cols-2 gap-px bg-gray-100 dark:bg-gray-800">
                    <div class="bg-white px-5 py-4 dark:bg-gray-900">
                        <p class="text-xs text-gray-500">{{ t('Category') }}</p>
                        <p class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">{{ t(categoryLabels[ingredient.category] || ingredient.category) }}</p>
                    </div>
                    <div class="bg-white px-5 py-4 dark:bg-gray-900">
                        <p class="text-xs text-gray-500">{{ t('Halal Risk Level') }}</p>
                        <p class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">{{ t(riskLabels[ingredient.halal_risk_level] || ingredient.halal_risk_level) }}</p>
                    </div>
                    <div class="bg-white px-5 py-4 dark:bg-gray-900">
                        <p class="text-xs text-gray-500">{{ t('Brand / Producer') }}</p>
                        <p class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">{{ ingredient.brand || '-' }}</p>
                    </div>
                    <div class="bg-white px-5 py-4 dark:bg-gray-900">
                        <p class="text-xs text-gray-500">{{ t('Ingredient Type') }}</p>
                        <p class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">{{ ingredient.type === 'composite' ? t('Composite Material') : t('Simple Material') }}</p>
                    </div>
                    <div v-if="ingredient.supplier" class="bg-white px-5 py-4 dark:bg-gray-900">
                        <p class="text-xs text-gray-500">{{ t('Supplier') }}</p>
                        <p class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">{{ ingredient.supplier.name }}</p>
                    </div>
                    <div v-if="ingredient.origin_country" class="bg-white px-5 py-4 dark:bg-gray-900">
                        <p class="text-xs text-gray-500">{{ t('Origin Country') }}</p>
                        <p class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">{{ ingredient.origin_country }}</p>
                    </div>
                    <div v-if="ingredient.manufacturer" class="bg-white px-5 py-4 dark:bg-gray-900">
                        <p class="text-xs text-gray-500">{{ t('Manufacturer') }}</p>
                        <p class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">{{ ingredient.manufacturer }}</p>
                    </div>
                </div>
            </div>

            <!-- Certificate -->
            <div v-if="ingredient.halal_risk_level !== 'no_risk'" class="mb-6 rounded-xl border border-sidebar-border/70 bg-white dark:border-sidebar-border dark:bg-gray-900">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ t('Halal Certificate') }}</h3>
                    <HalalStatusBadge v-if="ingredient.cert_status" :status="ingredient.cert_status" size="sm" />
                </div>

                <div v-if="ingredient.sh_number" class="p-5">
                    <div>
                        <p class="text-xs text-gray-500">{{ t('Certificate Number (SH)') }}</p>
                        <p class="mt-1 font-mono text-sm font-medium text-gray-900 dark:text-gray-100">{{ ingredient.sh_number }}</p>
                        <p class="mt-1 text-xs text-emerald-600">{{ t('Valid for life (GR 42/2024)') }}</p>
                    </div>
                    <div class="mt-3">
                        <a :href="`https://bpjph.halal.go.id/sertifikat-halal/sertifikat?nama_produk=${encodeURIComponent(ingredient.name)}`" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-emerald-600 hover:underline">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            {{ t('Verify on BPJPH Halal Directory') }}
                        </a>
                    </div>
                </div>

                <div v-else class="px-5 py-8 text-center">
                    <p class="mb-3 text-sm text-gray-500">{{ t('No certificate added yet.') }}</p>
                    <div class="flex items-center justify-center gap-3">
                        <Link :href="`/ingredients/${ingredient.id}/edit`" class="text-sm font-medium text-emerald-600 hover:underline">{{ t('Add certificate') }}</Link>
                        <span class="text-gray-300">|</span>
                        <a :href="`https://bpjph.halal.go.id/sertifikat-halal/sertifikat?nama_produk=${encodeURIComponent(ingredient.name)}`" target="_blank" class="inline-flex items-center gap-1 text-sm font-medium text-emerald-600 hover:underline">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            {{ t('Search on BPJPH') }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- No cert needed -->
            <div v-else class="mb-6 rounded-lg border border-gray-200 bg-gray-50 px-5 py-4 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-800/50">
                {{ t('No halal certificate needed for naturally halal ingredients.') }}
            </div>

            <!-- Children -->
            <div v-if="ingredient.children && ingredient.children.length > 0" class="mb-6 rounded-xl border border-sidebar-border/70 bg-white dark:border-sidebar-border dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ t('Sub-Ingredients') }}</h3>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    <Link v-for="child in ingredient.children" :key="child.id" :href="`/ingredients/${child.id}`" class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ child.name }}</p>
                            <p class="text-xs text-gray-500">{{ child.code }} · {{ child.sh_number || t('No certificate') }}</p>
                        </div>
                        <HalalStatusBadge v-if="child.cert_status" :status="child.cert_status" size="sm" />
                    </Link>
                </div>
            </div>

            <!-- Notes -->
            <div v-if="ingredient.notes" class="rounded-xl border border-sidebar-border/70 bg-white p-5 dark:border-sidebar-border dark:bg-gray-900">
                <h3 class="mb-2 font-semibold text-gray-900 dark:text-gray-100">{{ t('Notes') }}</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ ingredient.notes }}</p>
            </div>
        </div>
    </AppLayout>
</template>