<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import FacilityForm from '@/components/FacilityForm.vue';
import { useTrans } from '@/composables/useTrans';
import type { BreadcrumbItem } from '@/types';
import { computed } from 'vue';

const { t } = useTrans();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Facilities', href: '/facilities' },
    { title: 'Create', href: '#' },
]);
</script>

<template>
    <Head :title="t('Add Facility')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="lg:mx-auto p-4">
            <h2 class="mb-6 text-xl font-semibold text-gray-900 dark:text-gray-100">
                {{ t('Add Facility') }}
            </h2>

            <FacilityForm />
        </div>
    </AppLayout>
</template>
