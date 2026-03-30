<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { useTrans } from '@/composables/useTrans';
import { useAuth } from '@/composables/useAuth';
import FlashMessage from '@/components/FlashMessage.vue';
import type { BreadcrumbItem } from '@/types';

const { t } = useTrans();
const { isAdmin } = useAuth();

const props = defineProps<{
    company: {
        id: number;
        name: string;
        npwp: string | null;
        bpjph_registration_number: string | null;
        address: string | null;
        city: string | null;
        province: string | null;
        postal_code: string | null;
        phone: string | null;
        email: string | null;
        pic_name: string | null;
        pic_phone: string | null;
        status: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Company Profile', href: '/company-profile' },
];

const form = useForm({
    name: props.company.name,
    npwp: props.company.npwp ?? '',
    bpjph_registration_number: props.company.bpjph_registration_number ?? '',
    address: props.company.address ?? '',
    city: props.company.city ?? '',
    province: props.company.province ?? '',
    postal_code: props.company.postal_code ?? '',
    phone: props.company.phone ?? '',
    email: props.company.email ?? '',
    pic_name: props.company.pic_name ?? '',
    pic_phone: props.company.pic_phone ?? '',
});

function submit() {
    form.put('/company-profile');
}
</script>

<template>
    <Head :title="t('Company Profile')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-2xl p-4">
            <h2 class="mb-6 text-xl font-semibold text-gray-900 dark:text-gray-100">{{ t('Company Profile') }}</h2>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Company Name') }} *</label>
                        <input v-model="form.name" :disabled="!isAdmin" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm disabled:bg-gray-100 disabled:text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:disabled:bg-gray-900" />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('NPWP') }}</label>
                        <input v-model="form.npwp" :disabled="!isAdmin" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm disabled:bg-gray-100 disabled:text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:disabled:bg-gray-900" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('BPJPH Registration') }}</label>
                        <input v-model="form.bpjph_registration_number" :disabled="!isAdmin" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm disabled:bg-gray-100 disabled:text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:disabled:bg-gray-900" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Address') }}</label>
                        <input v-model="form.address" :disabled="!isAdmin" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm disabled:bg-gray-100 disabled:text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:disabled:bg-gray-900" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('City') }}</label>
                        <input v-model="form.city" :disabled="!isAdmin" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm disabled:bg-gray-100 disabled:text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:disabled:bg-gray-900" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Province') }}</label>
                        <input v-model="form.province" :disabled="!isAdmin" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm disabled:bg-gray-100 disabled:text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:disabled:bg-gray-900" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Postal Code') }}</label>
                        <input v-model="form.postal_code" :disabled="!isAdmin" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm disabled:bg-gray-100 disabled:text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:disabled:bg-gray-900" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Phone') }}</label>
                        <input v-model="form.phone" :disabled="!isAdmin" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm disabled:bg-gray-100 disabled:text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:disabled:bg-gray-900" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Email') }}</label>
                        <input v-model="form.email" :disabled="!isAdmin" type="email" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm disabled:bg-gray-100 disabled:text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:disabled:bg-gray-900" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Person In Charge') }}</label>
                        <input v-model="form.pic_name" :disabled="!isAdmin" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm disabled:bg-gray-100 disabled:text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:disabled:bg-gray-900" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Contact Phone') }}</label>
                        <input v-model="form.pic_phone" :disabled="!isAdmin" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm disabled:bg-gray-100 disabled:text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:disabled:bg-gray-900" />
                    </div>
                </div>

                <div v-if="isAdmin" class="flex gap-3 pt-4">
                    <button type="submit" :disabled="form.processing" class="rounded-lg bg-emerald-600 px-6 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50">
                        {{ form.processing ? t('Loading...') : t('Update') }}
                    </button>
                </div>
            </form>
        </div>
        <FlashMessage />
    </AppLayout>
</template>