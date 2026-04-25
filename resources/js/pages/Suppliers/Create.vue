<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { useTrans } from '@/composables/useTrans';
import type { BreadcrumbItem } from '@/types';
import { computed } from 'vue';

const { t } = useTrans();
const props = defineProps<{ supplier?: { id: number; name: string; code: string | null; address: string | null; city: string | null; country: string; phone: string | null; email: string | null; pic_name: string | null; pic_phone: string | null; status: string; notes: string | null } }>();
const isEditing = computed(() => !!props.supplier);
const form = useForm({
    name: props.supplier?.name ?? '', code: props.supplier?.code ?? '', address: props.supplier?.address ?? '', city: props.supplier?.city ?? '',
    country: props.supplier?.country ?? 'ID', phone: props.supplier?.phone ?? '', email: props.supplier?.email ?? '',
    pic_name: props.supplier?.pic_name ?? '', pic_phone: props.supplier?.pic_phone ?? '', status: props.supplier?.status ?? 'active', notes: props.supplier?.notes ?? '',
});
const breadcrumbs = computed<BreadcrumbItem[]>(() => [{ title: 'Dashboard', href: '/dashboard' }, { title: 'Suppliers', href: '/suppliers' }, { title: isEditing.value ? 'Edit' : 'Create', href: '#' }]);
function submit() { isEditing.value ? form.put(`/suppliers/${props.supplier!.id}`) : form.post('/suppliers'); }
</script>

<template>
    <Head :title="isEditing ? t('Edit Supplier') : t('Add Supplier')" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="lg:mx-auto p-4">
            <h2 class="mb-6 text-xl font-semibold text-gray-900 dark:text-gray-100">{{ isEditing ? t('Edit Supplier') : t('Add Supplier') }}</h2>
            <form @submit.prevent="submit" class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2"><label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Supplier Name') }} *</label><input v-model="form.name" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" /><p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p></div>
                    <div><label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Code') }}</label><input v-model="form.code" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" /></div>
                    <div><label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Country') }}</label><input v-model="form.country" maxlength="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" /></div>
                    <div class="sm:col-span-2"><label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Address') }}</label><input v-model="form.address" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" /></div>
                    <div><label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('City') }}</label><input v-model="form.city" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" /></div>
                    <div><label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Phone') }}</label><input v-model="form.phone" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" /></div>
                    <div><label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Email') }}</label><input v-model="form.email" type="email" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" /></div>
                    <div><label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Contact Person') }}</label><input v-model="form.pic_name" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" /></div>
                    <div><label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Contact Phone') }}</label><input v-model="form.pic_phone" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" /></div>
                    <div><label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Status') }}</label><select v-model="form.status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"><option value="active">{{ t('Active') }}</option><option value="inactive">{{ t('Inactive') }}</option><option value="blacklisted">{{ t('Blacklisted') }}</option></select></div>
                    <div class="sm:col-span-2"><label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Notes') }}</label><textarea v-model="form.notes" rows="3" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" /></div>
                </div>
                <div class="flex gap-3 pt-4">
                    <button type="submit" :disabled="form.processing" class="rounded-lg bg-emerald-600 px-6 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50">{{ form.processing ? t('Loading...') : (isEditing ? t('Update') : t('Create')) }}</button>
                    <a href="/suppliers" class="rounded-lg border border-gray-300 px-6 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300">{{ t('Cancel') }}</a>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
