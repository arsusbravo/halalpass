<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const page = usePage();

const flash = computed(() => page.props.flash as { success?: string; error?: string });
const visible = ref(false);

watch(() => flash.value, (val) => {
    if (val?.success || val?.error) {
        visible.value = true;
        setTimeout(() => { visible.value = false; }, 4000);
    }
}, { immediate: true });
</script>

<template>
    <Transition
        enter-from-class="translate-y-2 opacity-0"
        enter-active-class="transition duration-300"
        leave-to-class="translate-y-2 opacity-0"
        leave-active-class="transition duration-300"
    >
        <div v-if="visible && flash?.success" class="fixed right-4 bottom-4 z-50 max-w-sm rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-lg dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-200">
            <div class="flex items-center gap-2">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                <span>{{ flash.success }}</span>
                <button @click="visible = false" class="ml-auto text-emerald-600 hover:text-emerald-800 dark:text-emerald-400">&times;</button>
            </div>
        </div>
        <div v-else-if="visible && flash?.error" class="fixed right-4 bottom-4 z-50 max-w-sm rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 shadow-lg dark:border-red-800 dark:bg-red-950 dark:text-red-200">
            <div class="flex items-center gap-2">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                <span>{{ flash.error }}</span>
                <button @click="visible = false" class="ml-auto text-red-600 hover:text-red-800 dark:text-red-400">&times;</button>
            </div>
        </div>
    </Transition>
</template>
