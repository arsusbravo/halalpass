<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { useTrans } from '@/composables/useTrans';
import FlashMessage from '@/components/FlashMessage.vue';
import type { BreadcrumbItem } from '@/types';

const { t } = useTrans();
const props = defineProps<{
    products: Array<{ id: number; name: string; code: string | null; halal_status: string }>;
    facilities: Array<{ id: number; name: string; code: string | null }>;
}>();
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: '/dashboard' }, { title: 'Export', href: '/export' }];
const form = useForm({
    product_ids: [] as number[],
    facility_ids: [] as number[],
    include_certificates: true,
    include_material_matrix: true,
});
function submit() {
    // Use window.location for file download (Inertia doesn't handle file responses)
    const params = new URLSearchParams();
    form.product_ids.forEach(id => params.append('product_ids[]', String(id)));
    form.facility_ids.forEach(id => params.append('facility_ids[]', String(id)));
    params.set('include_certificates', form.include_certificates ? '1' : '0');
    params.set('include_material_matrix', form.include_material_matrix ? '1' : '0');

    window.location.href = `/export/generate?${params.toString()}`;
}
</script>

<template>
    <Head :title="t('Audit Export')" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-2xl p-4">
            <h2 class="mb-6 text-xl font-semibold text-gray-900 dark:text-gray-100">{{ t('Audit Export') }}</h2>
            <p class="mb-6 text-sm text-gray-500">{{ t('Generate a ZIP file containing the Materials List and Material Matrix formatted for SIHALAL upload.') }}</p>

            <div class="space-y-6">
                <!-- Facilities -->
                <div class="rounded-xl border border-sidebar-border/70 bg-white p-5 dark:border-sidebar-border dark:bg-gray-900">
                    <h3 class="mb-3 font-semibold text-gray-900 dark:text-gray-100">{{ t('Select Facilities') }}</h3>
                    <p class="mb-3 text-xs text-gray-500">{{ t('Leave empty to include all facilities.') }}</p>
                    <div class="space-y-2">
                        <label v-for="f in facilities" :key="f.id" class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <input v-model="form.facility_ids" :value="f.id" type="checkbox" class="rounded border-gray-300 text-emerald-600" />
                            {{ f.name }} <span v-if="f.code" class="text-xs text-gray-400">({{ f.code }})</span>
                        </label>
                    </div>
                </div>

                <!-- Products -->
                <div class="rounded-xl border border-sidebar-border/70 bg-white p-5 dark:border-sidebar-border dark:bg-gray-900">
                    <h3 class="mb-3 font-semibold text-gray-900 dark:text-gray-100">{{ t('Select Products') }}</h3>
                    <p class="mb-3 text-xs text-gray-500">{{ t('Leave empty to include all products.') }}</p>
                    <div class="space-y-2">
                        <label v-for="p in products" :key="p.id" class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <input v-model="form.product_ids" :value="p.id" type="checkbox" class="rounded border-gray-300 text-emerald-600" />
                            {{ p.name }} <span v-if="p.code" class="text-xs text-gray-400">({{ p.code }})</span>
                        </label>
                    </div>
                </div>

                <!-- Options -->
                <div class="rounded-xl border border-sidebar-border/70 bg-white p-5 dark:border-sidebar-border dark:bg-gray-900">
                    <h3 class="mb-3 font-semibold text-gray-900 dark:text-gray-100">Options</h3>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300"><input v-model="form.include_certificates" type="checkbox" class="rounded border-gray-300 text-emerald-600" /> {{ t('Include Certificates') }}</label>
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300"><input v-model="form.include_material_matrix" type="checkbox" class="rounded border-gray-300 text-emerald-600" /> {{ t('Include Material Matrix') }}</label>
                    </div>
                </div>

                <button @click="submit" class="rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-medium text-white hover:bg-emerald-700">
                    {{ t('Generate Export') }}
                </button>
            </div>
        </div>
        <FlashMessage />
    </AppLayout>
</template>
