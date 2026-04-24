<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import FacilityForm from '@/components/FacilityForm.vue';
import { useTrans } from '@/composables/useTrans';
import { type BreadcrumbItem } from '@/types';

const { t } = useTrans();

const props = defineProps<{
    facility: {
        id: number;
        name: string;
        address: string;
        city: string;
        province: string;
        postal_code: string | null;
        phone: string | null;
        pic_name: string | null;
        production_capacity: string | null;
        sjph_status: string;
        status: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Facilities', href: '/facilities' },
    { title: 'Edit', href: '#' },
];
</script>

<template>
    <Head :title="t('Edit Facility')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-2xl p-4">
            <h2 class="mb-6 text-xl font-semibold text-gray-900 dark:text-gray-100">{{ t('Edit Facility') }}</h2>

            <FacilityForm :facility="props.facility" />
        </div>
    </AppLayout>
</template>