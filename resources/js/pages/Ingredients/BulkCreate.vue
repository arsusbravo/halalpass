<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { useTrans } from '@/composables/useTrans';
import { type BreadcrumbItem } from '@/types';
import { computed } from 'vue';

const { t } = useTrans();

const form = useForm({
    names: ''
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Ingredients', href: '/ingredients' },
    { title: 'Bulk Add', href: '#' },
];

const ingredientCount = computed(() => {
    if (!form.names.trim()) return 0;
    return form.names.trim().split('\n').filter(line => line.trim()).length;
});

const previewNames = computed(() => {
    if (!form.names.trim()) return [];
    return form.names.trim().split('\n').filter(line => line.trim()).map(line => line.trim());
});

function submit() {
    form.post('/ingredients/bulk');
}
</script>

<template>
    <Head :title="t('Bulk Add Ingredients')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="lg:mx-auto lg:max-w-xl p-4">
            <h2 class="mb-2 text-xl font-semibold text-gray-900 dark:text-gray-100">{{ t('Bulk Add Ingredients') }}</h2>
            <p class="mb-6 text-sm text-gray-500">{{ t('Add multiple ingredients at once. Enter one ingredient name per line.') }}</p>

            <form @submit.prevent="submit" class="space-y-5">
                <!-- Textarea -->
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Ingredient Names') }} *</label>
                    <textarea
                        v-model="form.names"
                        rows="10"
                        required
                        :placeholder="t('Tepung Terigu\nMinyak Goreng\nGaram\nGula Pasir\nMSG\nKecap Manis\nBawang Putih\nCabai Merah')"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 font-mono text-sm leading-relaxed dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                    />
                    <p v-if="form.errors.names" class="mt-1 text-xs text-red-600">{{ form.errors.names }}</p>
                    <p class="mt-1 text-xs text-gray-400">{{ t('One ingredient per line. Empty lines are ignored.') }}</p>
                </div>

                <!-- Preview -->
                <div v-if="ingredientCount > 0" class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
                    <div class="mb-3 flex items-center justify-between">
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ t('Preview') }}</p>
                        <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-900 dark:text-emerald-300">
                            {{ ingredientCount }} {{ t('ingredients') }}
                        </span>
                    </div>
                    <div class="max-h-48 space-y-1 overflow-y-auto">
                        <div v-for="(name, i) in previewNames" :key="i" class="flex items-center gap-2 text-sm">
                            <span class="w-6 shrink-0 text-right text-xs text-gray-400">{{ i + 1 }}.</span>
                            <span class="text-gray-700 dark:text-gray-300">{{ name }}</span>
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <div class="flex gap-3 pt-2">
                    <button type="submit" :disabled="form.processing || ingredientCount === 0" class="rounded-lg bg-emerald-600 px-6 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50">
                        {{ form.processing ? t('Creating...') : t('Create :count Ingredients', { count: ingredientCount }) }}
                    </button>
                    <a href="/ingredients" class="rounded-lg border border-gray-300 px-6 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300">{{ t('Cancel') }}</a>
                </div>
            </form>
        </div>
    </AppLayout>
</template>