<script setup lang="ts">
import { useTrans } from '@/composables/useTrans';
import { computed } from 'vue';

const { t } = useTrans();
const deadline = new Date('2026-10-17');
const now = new Date();
const daysRemaining = computed(() => Math.ceil((deadline.getTime() - now.getTime()) / (1000 * 60 * 60 * 24)));

const urgency = computed(() => {
    if (daysRemaining.value <= 90) return 'bg-red-50 border-red-200 text-red-800 dark:bg-red-950 dark:border-red-800 dark:text-red-200';
    if (daysRemaining.value <= 180) return 'bg-amber-50 border-amber-200 text-amber-800 dark:bg-amber-950 dark:border-amber-800 dark:text-amber-200';
    return 'bg-blue-50 border-blue-200 text-blue-800 dark:bg-blue-950 dark:border-blue-800 dark:text-blue-200';
});
</script>

<template>
    <div :class="[urgency, 'flex items-center justify-between rounded-lg border px-4 py-3 text-sm']">
        <div class="flex items-center gap-2">
            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span class="font-medium">{{ t('Deadline Alert') }}:</span>
            <span>{{ t(':days days remaining until the mandatory halal deadline.', { days: daysRemaining }) }}</span>
        </div>
        <span class="font-bold tabular-nums">17 Oct 2026</span>
    </div>
</template>
