<script setup lang="ts">
import { useTrans } from '@/composables/useTrans';
import { computed } from 'vue';

const props = defineProps<{ status: string; size?: 'sm' | 'md' }>();
const { t } = useTrans();

const config = computed(() => {
    const map: Record<string, { label: string; classes: string }> = {
        compliant: { label: t('Compliant'), classes: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200' },
        at_risk: { label: t('At Risk'), classes: 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200' },
        non_compliant: { label: t('Non-Compliant'), classes: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' },
        pending: { label: t('Pending'), classes: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200' },
        valid: { label: t('Valid'), classes: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200' },
        expiring_soon: { label: t('Expiring Soon'), classes: 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200' },
        expired: { label: t('Expired'), classes: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' },
        missing: { label: t('Missing'), classes: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' },
        active: { label: t('Active'), classes: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200' },
        inactive: { label: t('Inactive'), classes: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200' },
        draft: { label: t('Draft'), classes: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200' },
        in_review: { label: t('In Review'), classes: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' },
        approved: { label: t('Approved'), classes: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200' },
        not_started: { label: t('Not Started'), classes: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200' },
        in_progress: { label: t('In Progress'), classes: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' },
        completed: { label: t('Completed'), classes: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200' },
    };
    return map[props.status] ?? { label: props.status, classes: 'bg-gray-100 text-gray-800' };
});

const sizeClasses = computed(() => props.size === 'sm' ? 'px-1.5 py-0.5 text-xs' : 'px-2 py-1 text-xs');
</script>

<template>
    <span :class="[config.classes, sizeClasses, 'inline-flex items-center rounded-full font-medium whitespace-nowrap']">
        {{ config.label }}
    </span>
</template>
