<script setup>
import { useTrans } from '@/composables/useTrans';
import { router } from '@inertiajs/vue3';

const { locale } = useTrans();

function switchLocale(newLocale) {
    if (newLocale === locale.value) return;
    router.get(`/locale/${newLocale}`, {}, { preserveState: true });
}
</script>

<template>
    <div class="flex items-center gap-1">
        <button
            @click="switchLocale('id')"
            :class="[
                'px-2 py-1 text-sm rounded transition-colors',
                locale === 'id'
                    ? 'bg-emerald-600 text-white font-medium'
                    : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'
            ]"
        >
            ID
        </button>
        <button
            @click="switchLocale('en')"
            :class="[
                'px-2 py-1 text-sm rounded transition-colors',
                locale === 'en'
                    ? 'bg-emerald-600 text-white font-medium'
                    : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'
            ]"
        >
            EN
        </button>
    </div>
</template>