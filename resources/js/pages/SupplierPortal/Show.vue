<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { useTrans } from '@/composables/useTrans';

const { t } = useTrans();
const props = defineProps<{
    token: string;
    portalData: {
        supplier_name: string;
        company_name: string;
        ingredient: { id: number; name: string; code: string | null } | null;
        ingredients_requiring_certs: Array<{ id: number; name: string; code: string | null; current_cert_status: string }>;
        expires_at: string;
    };
}>();

const form = useForm({
    ingredient_id: props.portalData.ingredient?.id ?? (props.portalData.ingredients_requiring_certs[0]?.id ?? ''),
    sh_number: '', issuing_body: 'MUI' as string, issuing_body_name: '', issue_date: '', expiry_date: '',
    document: null as File | null,
});

function onFileChange(e: Event) { const files = (e.target as HTMLInputElement).files; if (files?.length) form.document = files[0]; }
function submit() { form.post(`/supplier-portal/${props.token}/upload`, { forceFormData: true }); }
</script>

<template>
    <Head :title="t('Upload Halal Certificate')" />
    <div class="flex min-h-screen items-center justify-center bg-gray-50 p-4 dark:bg-gray-950">
        <div class="w-full max-w-lg">
            <!-- Header -->
            <div class="mb-6 text-center">
                <div class="mb-2 inline-flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900">
                    <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                </div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ t('Supplier Portal') }}</h1>
                <p class="mt-1 text-sm text-gray-500">{{ portalData.company_name }}</p>
            </div>

            <!-- Form -->
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                    {{ t('Welcome') }}, <strong>{{ portalData.supplier_name }}</strong>. {{ t('Upload Halal Certificate') }}:
                </p>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Ingredient') }} *</label>
                        <select v-model="form.ingredient_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                            <option v-for="ing in portalData.ingredients_requiring_certs" :key="ing.id" :value="ing.id">
                                {{ ing.name }} {{ ing.code ? `(${ing.code})` : '' }}
                                <template v-if="ing.current_cert_status !== 'valid'"> — {{ ing.current_cert_status }}</template>
                            </option>
                        </select>
                    </div>
                    <div><label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('SH Number') }} *</label><input v-model="form.sh_number" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" /><p v-if="form.errors.sh_number" class="mt-1 text-xs text-red-600">{{ form.errors.sh_number }}</p></div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Issuing Body') }} *</label>
                            <select v-model="form.issuing_body" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                                <option value="MUI">MUI</option><option value="LPH">LPH</option><option value="FOREIGN_HCB">Foreign HCB</option>
                            </select>
                        </div>
                        <div><label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Issuing Body Name') }}</label><input v-model="form.issuing_body_name" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" /></div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Issue Date') }}</label><input v-model="form.issue_date" type="date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" /></div>
                        <div><label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Expiry Date') }} *</label><input v-model="form.expiry_date" type="date" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" /><p v-if="form.errors.expiry_date" class="mt-1 text-xs text-red-600">{{ form.errors.expiry_date }}</p></div>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Upload Document') }} * (PDF, JPG, PNG)</label>
                        <input type="file" accept=".pdf,.jpg,.jpeg,.png" @change="onFileChange" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm file:mr-3 file:rounded file:border-0 file:bg-emerald-50 file:px-3 file:py-1 file:text-sm file:text-emerald-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                        <p v-if="form.errors.document" class="mt-1 text-xs text-red-600">{{ form.errors.document }}</p>
                    </div>
                    <button type="submit" :disabled="form.processing" class="w-full rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50">
                        {{ form.processing ? t('Loading...') : t('Upload Halal Certificate') }}
                    </button>
                </form>

                <p class="mt-4 text-center text-xs text-gray-400">{{ t('Expires at') }}: {{ portalData.expires_at }}</p>
            </div>
        </div>
    </div>
</template>
