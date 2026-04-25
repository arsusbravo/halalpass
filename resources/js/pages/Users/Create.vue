<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { useTrans } from '@/composables/useTrans';
import type { BreadcrumbItem } from '@/types';
import { computed } from 'vue';

const { t } = useTrans();

const props = defineProps<{
    editUser?: { id: number; name: string; email: string; role: string };
}>();

const isEditing = computed(() => !!props.editUser);

const form = useForm({
    name: props.editUser?.name ?? '',
    email: props.editUser?.email ?? '',
    role: props.editUser?.role ?? 'viewer',
    password: '',
    password_confirmation: '',
});

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Users', href: '/users' },
    { title: isEditing.value ? 'Edit' : 'Create', href: '#' },
]);

function submit() {
    if (isEditing.value) {
        form.put(`/users/${props.editUser!.id}`);
    } else {
        form.post('/users');
    }
}
</script>

<template>
    <Head :title="isEditing ? t('Edit User') : t('Add User')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="lg:mx-auto lg:max-w-lg p-4">
            <h2 class="mb-6 text-xl font-semibold text-gray-900 dark:text-gray-100">
                {{ isEditing ? t('Edit User') : t('Add User') }}
            </h2>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Name') }} *</label>
                    <input v-model="form.name" type="text" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                    <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Email') }} *</label>
                    <input v-model="form.email" type="email" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                    <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Role') }} *</label>
                    <select v-model="form.role" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                        <option value="admin">Admin</option>
                        <option value="manager">Manager</option>
                        <option value="viewer">Viewer</option>
                    </select>
                    <p v-if="form.errors.role" class="mt-1 text-xs text-red-600">{{ form.errors.role }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ t('Password') }} {{ isEditing ? `(${t('leave empty to keep current')})` : '*' }}
                    </label>
                    <input v-model="form.password" type="password" :required="!isEditing" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                    <p v-if="form.errors.password" class="mt-1 text-xs text-red-600">{{ form.errors.password }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Confirm Password') }}</label>
                    <input v-model="form.password_confirmation" type="password" :required="!isEditing && !!form.password" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" :disabled="form.processing" class="rounded-lg bg-emerald-600 px-6 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50">
                        {{ form.processing ? t('Loading...') : (isEditing ? t('Update') : t('Create')) }}
                    </button>
                    <a href="/users" class="rounded-lg border border-gray-300 px-6 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300">{{ t('Cancel') }}</a>
                </div>
            </form>
        </div>
    </AppLayout>
</template>