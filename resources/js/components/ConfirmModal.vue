<script setup lang="ts">
import { useTrans } from '@/composables/useTrans';

const props = withDefaults(defineProps<{
    show: boolean;
    title?: string;
    message?: string;
    confirmLabel?: string;
    cancelLabel?: string;
    destructive?: boolean;
}>(), { destructive: true });

const emit = defineEmits<{ confirm: []; cancel: [] }>();
const { t } = useTrans();
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-from-class="opacity-0"
            enter-active-class="transition duration-200"
            leave-to-class="opacity-0"
            leave-active-class="transition duration-200"
        >
            <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="emit('cancel')">
                <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        {{ title ?? t('Are you sure?') }}
                    </h3>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        {{ message ?? t('This action cannot be undone.') }}
                    </p>
                    <div class="mt-4 flex justify-end gap-3">
                        <button
                            @click="emit('cancel')"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800"
                        >
                            {{ cancelLabel ?? t('Cancel') }}
                        </button>
                        <button
                            @click="emit('confirm')"
                            :class="[
                                'rounded-lg px-4 py-2 text-sm font-medium text-white',
                                destructive
                                    ? 'bg-red-600 hover:bg-red-700'
                                    : 'bg-emerald-600 hover:bg-emerald-700'
                            ]"
                        >
                            {{ confirmLabel ?? t('Delete') }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
