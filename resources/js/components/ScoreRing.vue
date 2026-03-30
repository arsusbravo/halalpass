<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(defineProps<{
    score: number;
    size?: number;
}>(), { size: 64 });

const radius = computed(() => (props.size - 8) / 2);
const circumference = computed(() => 2 * Math.PI * radius.value);
const offset = computed(() => circumference.value - (props.score / 100) * circumference.value);
const color = computed(() => {
    if (props.score >= 100) return 'text-emerald-500';
    if (props.score >= 70) return 'text-amber-500';
    return 'text-red-500';
});
</script>

<template>
    <div class="relative inline-flex items-center justify-center" :style="{ width: `${size}px`, height: `${size}px` }">
        <svg :width="size" :height="size" class="-rotate-90">
            <circle
                :r="radius" :cx="size / 2" :cy="size / 2"
                fill="none" stroke-width="4"
                class="stroke-gray-200 dark:stroke-gray-700"
            />
            <circle
                :r="radius" :cx="size / 2" :cy="size / 2"
                fill="none" stroke-width="4" stroke-linecap="round"
                :class="color" stroke="currentColor"
                :stroke-dasharray="circumference"
                :stroke-dashoffset="offset"
                class="transition-all duration-500"
            />
        </svg>
        <span class="absolute text-sm font-semibold" :class="color">{{ score }}</span>
    </div>
</template>
