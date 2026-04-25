<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { useTrans } from '@/composables/useTrans';
import FlashMessage from '@/components/FlashMessage.vue';
import HalalStatusBadge from '@/components/HalalStatusBadge.vue';
import type { BreadcrumbItem } from '@/types';

const { t } = useTrans();
const props = defineProps<{
    supplier: {
        id: number; name: string; code: string | null; address: string | null; city: string | null; country: string; phone: string | null; email: string | null; pic_name: string | null; status: string; notes: string | null;
        ingredients: Array<{ id: number; name: string; code: string | null; halal_certificates: Array<{ status: string }> }>;
        access_tokens: Array<{ id: number; token: string; expires_at: string; ingredient?: { name: string } | null }>;
    };
}>();
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: '/dashboard' }, { title: 'Suppliers', href: '/suppliers' }, { title: props.supplier.name, href: '#' }];
const tokenForm = useForm({ ingredient_id: null as number | null, valid_days: 30 });
function generateToken() { tokenForm.post(`/suppliers/${props.supplier.id}/generate-token`, { preserveScroll: true }); }
function revokeToken(tokenId: number) { if (confirm(t('Are you sure?'))) router.delete(`/supplier-tokens/${tokenId}`); }
</script>

<template>
    <Head :title="supplier.name" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="lg:mx-auto lg:max-w-4xl p-4">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ supplier.name }}</h2>
                <Link :href="`/suppliers/${supplier.id}/edit`" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">{{ t('Edit') }}</Link>
            </div>

            <!-- Info -->
            <div class="mb-6 grid gap-4 rounded-xl border border-sidebar-border/70 bg-white p-5 sm:grid-cols-2 dark:border-sidebar-border dark:bg-gray-900">
                <div><p class="text-xs text-gray-500">{{ t('Code') }}</p><p class="text-sm text-gray-900 dark:text-gray-100">{{ supplier.code ?? '-' }}</p></div>
                <div><p class="text-xs text-gray-500">{{ t('Country') }}</p><p class="text-sm text-gray-900 dark:text-gray-100">{{ supplier.country }}</p></div>
                <div><p class="text-xs text-gray-500">{{ t('City') }}</p><p class="text-sm text-gray-900 dark:text-gray-100">{{ supplier.city ?? '-' }}</p></div>
                <div><p class="text-xs text-gray-500">{{ t('Phone') }}</p><p class="text-sm text-gray-900 dark:text-gray-100">{{ supplier.phone ?? '-' }}</p></div>
                <div><p class="text-xs text-gray-500">{{ t('Email') }}</p><p class="text-sm text-gray-900 dark:text-gray-100">{{ supplier.email ?? '-' }}</p></div>
                <div><p class="text-xs text-gray-500">{{ t('Contact Person') }}</p><p class="text-sm text-gray-900 dark:text-gray-100">{{ supplier.pic_name ?? '-' }}</p></div>
                <div><p class="text-xs text-gray-500">{{ t('Status') }}</p><HalalStatusBadge :status="supplier.status" /></div>
            </div>

            <!-- Ingredients from this supplier -->
            <h3 class="mb-3 font-semibold text-gray-900 dark:text-gray-100">{{ t('Ingredients') }} ({{ supplier.ingredients.length }})</h3>
            <div class="mb-6 overflow-x-auto rounded-xl border border-sidebar-border/70 bg-white dark:border-sidebar-border dark:bg-gray-900">
                <table class="w-full text-left text-sm">
                    <thead class="border-b bg-gray-50 dark:bg-gray-800/50"><tr><th class="px-4 py-3 font-medium text-gray-500">{{ t('Ingredient Name') }}</th><th class="px-4 py-3 font-medium text-gray-500">{{ t('Code') }}</th><th class="px-4 py-3 font-medium text-gray-500">{{ t('Certificate Status') }}</th></tr></thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-for="ing in supplier.ingredients" :key="ing.id">
                            <td class="px-4 py-3"><Link :href="`/ingredients/${ing.id}`" class="font-medium text-emerald-600 hover:text-emerald-800">{{ ing.name }}</Link></td>
                            <td class="px-4 py-3 text-gray-500">{{ ing.code ?? '-' }}</td>
                            <td class="px-4 py-3"><HalalStatusBadge v-if="ing.halal_certificates.length" :status="ing.halal_certificates[0].status" size="sm" /><span v-else class="text-xs text-red-500">{{ t('Missing') }}</span></td>
                        </tr>
                        <tr v-if="!supplier.ingredients.length"><td colspan="3" class="px-4 py-6 text-center text-gray-500">{{ t('No data available') }}</td></tr>
                    </tbody>
                </table>
            </div>

            <!-- Portal Token Generation -->
            <h3 class="mb-3 font-semibold text-gray-900 dark:text-gray-100">{{ t('Supplier Portal') }}</h3>
            <div class="mb-4 rounded-xl border border-sidebar-border/70 bg-white p-5 dark:border-sidebar-border dark:bg-gray-900">
                <div class="flex items-end gap-3">
                    <div class="flex-1">
                        <label class="mb-1 block text-sm text-gray-500">{{ t('Ingredient') }} ({{ t('optional') }})</label>
                        <select v-model="tokenForm.ingredient_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                            <option :value="null">{{ t('All') }} — {{ t('Ingredients') }}</option>
                            <option v-for="ing in supplier.ingredients" :key="ing.id" :value="ing.id">{{ ing.name }}</option>
                        </select>
                    </div>
                    <button @click="generateToken" :disabled="tokenForm.processing" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50">{{ t('Generate Portal Link') }}</button>
                </div>
            </div>

            <!-- Active Tokens -->
            <div v-if="supplier.access_tokens.length" class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-white dark:border-sidebar-border dark:bg-gray-900">
                <div class="border-b border-gray-100 px-4 py-3 dark:border-gray-800"><h4 class="text-sm font-medium text-gray-500">{{ t('Active Tokens') }}</h4></div>
                <div v-for="token in supplier.access_tokens" :key="token.id" class="flex items-center justify-between border-b border-gray-100 px-4 py-3 last:border-0 dark:border-gray-800">
                    <div>
                        <p class="font-mono text-xs text-gray-600 dark:text-gray-400">{{ `/supplier-portal/${token.token.substring(0, 12)}...` }}</p>
                        <p class="text-xs text-gray-500">{{ token.ingredient?.name ?? t('All') }} &middot; {{ t('Expires at') }}: {{ token.expires_at }}</p>
                    </div>
                    <button @click="revokeToken(token.id)" class="text-xs text-red-600 hover:text-red-800">{{ t('Revoke Token') }}</button>
                </div>
            </div>
        </div>
        <FlashMessage />
    </AppLayout>
</template>
