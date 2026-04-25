<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { useTrans } from '@/composables/useTrans';
import FlashMessage from '@/components/FlashMessage.vue';
import HalalStatusBadge from '@/components/HalalStatusBadge.vue';
import ScoreRing from '@/components/ScoreRing.vue';
import type { BreadcrumbItem } from '@/types';

const { t } = useTrans();

const props = defineProps<{
    product: {
        id: number;
        name: string;
        code: string | null;
        brand: string | null;
        description: string | null;
        category: string | null;
        halal_status: string;
        halal_health_score: number;
        status: string;
        facility: { id: number; name: string };
        ingredients: Array<{
            id: number;
            name: string;
            code: string | null;
            type: string;
            supplier: { name: string } | null;
            halal_certificates: Array<{ id: number; sh_number: string; status: string; expiry_date: string }>;
            pivot: { percentage: number | null; is_critical: boolean; usage_purpose: string | null; sort_order: number };
        }>;
    };
    scoreResult: {
        score: number;
        status: string;
        total_ingredients: number;
        compliant_count: number;
        at_risk_count: number;
        non_compliant_count: number;
        missing_count: number;
        ingredient_details: Array<{
            ingredient_id: number;
            name: string;
            score: number;
            status: string;
            certificate_sh: string | null;
            expiry_date: string | null;
            days_until_expiry: number | null;
        }>;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Products', href: '/products' },
    { title: props.product.name, href: '#' },
];

function recalculate() {
    router.post(`/products/${props.product.id}/recalculate`);
}

function removeIngredient(ingredientId: number) {
    if (confirm(t('Are you sure?'))) {
        router.delete(`/products/${props.product.id}/ingredients/${ingredientId}`);
    }
}
</script>

<template>
    <Head :title="product.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="lg:mx-auto lg:max-w-5xl p-4">
            <!-- Header -->
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ product.name }}</h2>
                    <p class="text-sm text-gray-500">{{ product.brand }} &middot; {{ product.code }} &middot; {{ product.facility.name }}</p>
                </div>
                <div class="flex gap-2">
                    <button @click="recalculate" class="rounded-lg border border-emerald-600 px-4 py-2 text-sm font-medium text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950">
                        {{ t('Recalculate Score') }}
                    </button>
                    <Link :href="`/products/${product.id}/edit`" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                        {{ t('Edit') }}
                    </Link>
                </div>
            </div>

            <!-- Score Card -->
            <div class="mb-6 flex items-center gap-6 rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-900">
                <ScoreRing :score="scoreResult.score" :size="80" />
                <div class="flex-1">
                    <div class="flex items-center gap-3">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ t('Halal Health Score') }}</h3>
                        <HalalStatusBadge :status="scoreResult.status" />
                    </div>
                    <p class="mt-1 text-sm text-gray-500">{{ scoreResult.total_ingredients }} {{ t('Ingredients') }} {{ t('checked') }}</p>
                    <div class="mt-3 flex gap-4 text-sm">
                        <span class="text-emerald-600">{{ scoreResult.compliant_count }} {{ t('Compliant') }}</span>
                        <span class="text-amber-600">{{ scoreResult.at_risk_count }} {{ t('At Risk') }}</span>
                        <span class="text-red-600">{{ scoreResult.non_compliant_count + scoreResult.missing_count }} {{ t('Non-Compliant') }}/{{ t('Missing') }}</span>
                    </div>
                </div>
            </div>

            <!-- Ingredient Score Breakdown -->
            <h3 class="mb-3 font-semibold text-gray-900 dark:text-gray-100">{{ t('Ingredient Details') }}</h3>
            <div class="overflow-x-auto rounded-xl border border-sidebar-border/70 bg-white dark:border-sidebar-border dark:bg-gray-900">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-100 bg-gray-50 dark:border-gray-800 dark:bg-gray-800/50">
                        <tr>
                            <th class="px-4 py-3 font-medium text-gray-500">{{ t('Ingredient Name') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-500">{{ t('Composition (%)') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-500">{{ t('Critical Point') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-500">{{ t('SH Number') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-500">{{ t('Expiry Date') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-500">{{ t('Certificate Status') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-500">{{ t('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-for="detail in scoreResult.ingredient_details" :key="detail.ingredient_id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-4 py-3">
                                <Link :href="`/ingredients/${detail.ingredient_id}`" class="font-medium text-gray-900 hover:text-emerald-600 dark:text-gray-100">
                                    {{ detail.name }}
                                </Link>
                            </td>
                            <td class="px-4 py-3 text-gray-500">
                                {{ product.ingredients.find(i => i.id === detail.ingredient_id)?.pivot?.percentage
                                    ? product.ingredients.find(i => i.id === detail.ingredient_id)!.pivot.percentage + '%'
                                    : '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <span v-if="product.ingredients.find(i => i.id === detail.ingredient_id)?.pivot?.is_critical" class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900 dark:text-red-200">
                                    {{ t('Critical Point') }}
                                </span>
                                <span v-else class="text-gray-400">-</span>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ detail.certificate_sh ?? t('Missing') }}</td>
                            <td class="px-4 py-3 text-gray-500">
                                <span v-if="detail.expiry_date">
                                    {{ detail.expiry_date }}
                                    <span v-if="detail.days_until_expiry !== null" class="ml-1 text-xs" :class="detail.days_until_expiry <= 30 ? 'text-red-600 font-semibold' : detail.days_until_expiry <= 90 ? 'text-amber-600' : 'text-gray-400'">
                                        ({{ detail.days_until_expiry }}d)
                                    </span>
                                </span>
                                <span v-else class="text-red-500">-</span>
                            </td>
                            <td class="px-4 py-3"><HalalStatusBadge :status="detail.status" size="sm" /></td>
                            <td class="px-4 py-3">
                                <button @click="removeIngredient(detail.ingredient_id)" class="text-xs text-red-600 hover:text-red-800">{{ t('Remove from Product') }}</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Description -->
            <div v-if="product.description" class="mt-6 rounded-xl border border-sidebar-border/70 bg-white p-5 dark:border-sidebar-border dark:bg-gray-900">
                <h3 class="mb-2 font-semibold text-gray-900 dark:text-gray-100">{{ t('Description') }}</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ product.description }}</p>
            </div>
        </div>

        <FlashMessage />
    </AppLayout>
</template>
