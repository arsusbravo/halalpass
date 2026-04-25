<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { useTrans } from '@/composables/useTrans';
import FlashMessage from '@/components/FlashMessage.vue';
import HalalStatusBadge from '@/components/HalalStatusBadge.vue';
import { type BreadcrumbItem } from '@/types';
import { ref, computed } from 'vue';

const { t } = useTrans();

const props = defineProps<{
    products: Array<{ id: number; name: string; code: string | null; halal_status: string; halal_health_score: number }>;
    facilities: Array<{ id: number; name: string; code: string | null }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Export', href: '/export' },
];

const selectedProducts = ref<number[]>([]);
const selectedFacilities = ref<number[]>([]);
const includeCertificates = ref(true);
const includeMaterialMatrix = ref(true);
const generating = ref(false);

const allProductsSelected = computed(() => selectedProducts.value.length === props.products.length);
const allFacilitiesSelected = computed(() => selectedFacilities.value.length === props.facilities.length);

function toggleAllProducts() {
    selectedProducts.value = allProductsSelected.value ? [] : props.products.map(p => p.id);
}

function toggleAllFacilities() {
    selectedFacilities.value = allFacilitiesSelected.value ? [] : props.facilities.map(f => f.id);
}

function generateExport() {
    generating.value = true;

    const params = new URLSearchParams();

    if (selectedProducts.value.length > 0 && selectedProducts.value.length < props.products.length) {
        params.set('products', selectedProducts.value.join(','));
    }
    if (selectedFacilities.value.length > 0 && selectedFacilities.value.length < props.facilities.length) {
        params.set('facilities', selectedFacilities.value.join(','));
    }
    if (!includeCertificates.value) {
        params.set('include_certificates', '0');
    }
    if (!includeMaterialMatrix.value) {
        params.set('include_material_matrix', '0');
    }

    const query = params.toString();
    window.location.href = `/export/generate${query ? '?' + query : ''}`;

    // Reset generating after a delay (download starts in browser)
    setTimeout(() => { generating.value = false; }, 3000);
}
</script>

<template>
    <Head :title="t('Export')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="lg:mx-auto lg:max-w-3xl p-4">
            <h2 class="mb-2 text-xl font-semibold text-gray-900 dark:text-gray-100">{{ t('Export for SIHALAL') }}</h2>
            <p class="mb-6 text-sm text-gray-500">{{ t('Generate a ZIP file with all documents needed for your SIHALAL submission.') }}</p>

            <!-- Products -->
            <div class="mb-6 rounded-xl border border-sidebar-border/70 bg-white dark:border-sidebar-border dark:bg-gray-900">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ t('Products') }}</h3>
                    <label class="flex items-center gap-2 text-sm text-gray-500">
                        <input type="checkbox" :checked="allProductsSelected" @change="toggleAllProducts" class="rounded border-gray-300 text-emerald-600" />
                        {{ t('Select All') }}
                    </label>
                </div>
                <div v-if="products.length > 0" class="divide-y divide-gray-100 dark:divide-gray-800">
                    <label v-for="product in products" :key="product.id" class="flex cursor-pointer items-center justify-between px-5 py-3 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" :value="product.id" v-model="selectedProducts" class="rounded border-gray-300 text-emerald-600" />
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ product.name }}</p>
                                <p class="text-xs text-gray-500">{{ product.code }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-400">{{ product.halal_health_score }}/100</span>
                            <HalalStatusBadge :status="product.halal_status" size="sm" />
                        </div>
                    </label>
                </div>
                <div v-else class="px-5 py-8 text-center text-sm text-gray-500">{{ t('No products yet.') }}</div>
            </div>

            <!-- Facilities -->
            <div class="mb-6 rounded-xl border border-sidebar-border/70 bg-white dark:border-sidebar-border dark:bg-gray-900">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ t('Facilities') }}</h3>
                    <label class="flex items-center gap-2 text-sm text-gray-500">
                        <input type="checkbox" :checked="allFacilitiesSelected" @change="toggleAllFacilities" class="rounded border-gray-300 text-emerald-600" />
                        {{ t('Select All') }}
                    </label>
                </div>
                <div v-if="facilities.length > 0" class="divide-y divide-gray-100 dark:divide-gray-800">
                    <label v-for="facility in facilities" :key="facility.id" class="flex cursor-pointer items-center gap-3 px-5 py-3 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <input type="checkbox" :value="facility.id" v-model="selectedFacilities" class="rounded border-gray-300 text-emerald-600" />
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ facility.name }}</p>
                            <p class="text-xs text-gray-500">{{ facility.code }}</p>
                        </div>
                    </label>
                </div>
                <div v-else class="px-5 py-8 text-center text-sm text-gray-500">{{ t('No facilities yet.') }}</div>
            </div>

            <!-- Options -->
            <div class="mb-6 rounded-xl border border-sidebar-border/70 bg-white p-5 dark:border-sidebar-border dark:bg-gray-900">
                <h3 class="mb-3 font-semibold text-gray-900 dark:text-gray-100">{{ t('Include in export') }}</h3>
                <div class="space-y-3">
                    <label class="flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300">
                        <input type="checkbox" v-model="includeMaterialMatrix" class="rounded border-gray-300 text-emerald-600" />
                        {{ t('Material Matrix (Matriks Bahan)') }}
                    </label>
                    <label class="flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300">
                        <input type="checkbox" v-model="includeCertificates" class="rounded border-gray-300 text-emerald-600" />
                        {{ t('Certificate PDF files') }}
                    </label>
                </div>
            </div>

            <!-- Generate -->
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-5 dark:border-emerald-800 dark:bg-emerald-950/30">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-semibold text-emerald-800 dark:text-emerald-200">{{ t('Download Export ZIP') }}</p>
                        <p class="mt-1 text-xs text-emerald-700 dark:text-emerald-300">{{ t('Contains Daftar Bahan, Matriks Bahan, and certificate files for SIHALAL.') }}</p>
                    </div>
                    <button
                        @click="generateExport"
                        :disabled="generating"
                        class="rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50"
                    >
                        <span v-if="generating" class="flex items-center gap-2">
                            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" /></svg>
                            {{ t('Generating...') }}
                        </span>
                        <span v-else class="flex items-center gap-2">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3" /></svg>
                            {{ t('Generate & Download') }}
                        </span>
                    </button>
                </div>
            </div>
        </div>
        <FlashMessage />
    </AppLayout>
</template>