<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { useTrans } from '@/composables/useTrans';
import type { BreadcrumbItem } from '@/types';
import { computed, ref } from 'vue';

const { t } = useTrans();
const props = defineProps<{
    certificate?: { id: number; ingredient_id: number; sh_number: string; issuing_body: string; issuing_body_name: string | null; issue_date: string | null; expiry_date: string; notes: string | null; original_filename: string | null };
    ingredients: Array<{ id: number; name: string; code: string | null }>;
    preselectedIngredientId?: string | null;
}>();
const isEditing = computed(() => !!props.certificate);
const form = useForm({
    ingredient_id: props.certificate?.ingredient_id ?? (props.preselectedIngredientId ? Number(props.preselectedIngredientId) : ''),
    sh_number: props.certificate?.sh_number ?? '', issuing_body: props.certificate?.issuing_body ?? 'MUI', issuing_body_name: props.certificate?.issuing_body_name ?? '',
    issue_date: props.certificate?.issue_date ?? '', expiry_date: props.certificate?.expiry_date ?? '', notes: props.certificate?.notes ?? '',
    document: null as File | null,
});
const breadcrumbs = computed<BreadcrumbItem[]>(() => [{ title: 'Dashboard', href: '/dashboard' }, { title: 'Certificates', href: '/certificates' }, { title: isEditing.value ? 'Edit' : 'Create', href: '#' }]);
const fileInput = ref<HTMLInputElement>();
function onFileChange(e: Event) { const files = (e.target as HTMLInputElement).files; if (files?.length) form.document = files[0]; }
function submit() {
    if (isEditing.value) {
        form.post(`/certificates/${props.certificate!.id}`, { _method: 'put', forceFormData: true } as any);
    } else {
        form.post('/certificates', { forceFormData: true });
    }
}
</script>

<template>
    <Head :title="isEditing ? t('Edit Certificate') : t('Add Certificate')" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="lg:mx-auto p-4">
            <h2 class="mb-6 text-xl font-semibold text-gray-900 dark:text-gray-100">{{ isEditing ? t('Edit Certificate') : t('Add Certificate') }}</h2>
            <form @submit.prevent="submit" class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Ingredient') }} *</label>
                        <select v-model="form.ingredient_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                            <option value="">{{ t('Select...') }}</option>
                            <option v-for="ing in ingredients" :key="ing.id" :value="ing.id">{{ ing.name }} {{ ing.code ? `(${ing.code})` : '' }}</option>
                        </select>
                        <p v-if="form.errors.ingredient_id" class="mt-1 text-xs text-red-600">{{ form.errors.ingredient_id }}</p>
                    </div>
                    <div class="sm:col-span-2"><label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('SH Number') }} *</label><input v-model="form.sh_number" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" /><p v-if="form.errors.sh_number" class="mt-1 text-xs text-red-600">{{ form.errors.sh_number }}</p>
                    <div v-if="form.ingredient_id" class="mt-1">
                        <p><a :href="'https://halalmui.org/search-product?productname=' + (ingredients.find(i => i.id === form.ingredient_id)?.name ?? '')" target="_blank" class="flex items-center gap-1 text-sm text-green-500">{{ t('Find certificate number') }}<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg></a></p>
                    </div></div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Issuing Body') }} *</label>
                        <select v-model="form.issuing_body" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                            <option value="MUI">MUI</option><option value="LPH">LPH</option><option value="FOREIGN_HCB">Foreign HCB</option>
                        </select>
                    </div>
                    <div><label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Issuing Body Name') }}</label><input v-model="form.issuing_body_name" placeholder="e.g. LPPOM MUI, JAKIM" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" /></div>
                    <div><label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Issue Date') }}</label><input v-model="form.issue_date" type="date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" /></div>
                    <div><label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Expiry Date') }} *</label><input v-model="form.expiry_date" type="date" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" /><p v-if="form.errors.expiry_date" class="mt-1 text-xs text-red-600">{{ form.errors.expiry_date }}</p></div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Upload Document') }}</label>
                        <input ref="fileInput" type="file" accept=".pdf,.jpg,.jpeg,.png" @change="onFileChange" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm file:mr-3 file:rounded file:border-0 file:bg-emerald-50 file:px-3 file:py-1 file:text-sm file:text-emerald-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                        <p v-if="isEditing && props.certificate?.original_filename" class="mt-1 text-xs text-gray-500">{{ t('Current') }}: {{ props.certificate.original_filename }}</p>
                        <p v-if="form.errors.document" class="mt-1 text-xs text-red-600">{{ form.errors.document }}</p>
                    </div>
                    <div class="sm:col-span-2"><label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Notes') }}</label><textarea v-model="form.notes" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" /></div>
                </div>
                <div class="flex gap-3 pt-4">
                    <button type="submit" :disabled="form.processing" class="rounded-lg bg-emerald-600 px-6 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50">{{ form.processing ? t('Loading...') : (isEditing ? t('Update') : t('Create')) }}</button>
                    <a href="/certificates" class="rounded-lg border border-gray-300 px-6 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300">{{ t('Cancel') }}</a>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
