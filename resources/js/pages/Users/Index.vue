<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { useTrans } from '@/composables/useTrans';
import { useAuth } from '@/composables/useAuth';
import FlashMessage from '@/components/FlashMessage.vue';
import HalalStatusBadge from '@/components/HalalStatusBadge.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';
import type { BreadcrumbItem } from '@/types';
import { ref } from 'vue';

const { t } = useTrans();
const { isAdmin, user: currentUser } = useAuth();

const props = defineProps<{
    users: Array<{
        id: number;
        name: string;
        email: string;
        role: string;
        created_at: string;
    }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Users', href: '/users' },
];

const deleteId = ref<number | null>(null);

function handleDelete() {
    if (deleteId.value) {
        router.delete(`/users/${deleteId.value}`);
        deleteId.value = null;
    }
}

const roleLabels: Record<string, string> = {
    owner: 'Owner',
    admin: 'Admin',
    manager: 'Manager',
    viewer: 'Viewer',
};
</script>

<template>
    <Head :title="t('Users')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ t('Users') }}</h2>
                <Link v-if="isAdmin" href="/users/create" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                    {{ t('Add User') }}
                </Link>
            </div>

            <div class="overflow-x-auto rounded-xl border border-sidebar-border/70 bg-white dark:border-sidebar-border dark:bg-gray-900">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-100 bg-gray-50 dark:border-gray-800 dark:bg-gray-800/50">
                        <tr>
                            <th class="px-4 py-3 font-medium text-gray-500">{{ t('Name') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-500">{{ t('Email') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-500">{{ t('Role') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-500">{{ t('Joined') }}</th>
                            <th v-if="isAdmin" class="px-4 py-3 font-medium text-gray-500">{{ t('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-for="u in users" :key="u.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">
                                {{ u.name }}
                                <span v-if="u.id === currentUser?.id" class="ml-1 text-xs text-gray-400">({{ t('You') }})</span>
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ u.email }}</td>
                            <td class="px-4 py-3">
                                <span :class="[
                                    'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium',
                                    u.role === 'owner' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' :
                                    u.role === 'admin' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' :
                                    u.role === 'manager' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200' :
                                    'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200'
                                ]">
                                    {{ roleLabels[u.role] ?? u.role }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ new Date(u.created_at).toLocaleDateString() }}</td>
                            <td v-if="isAdmin" class="px-4 py-3">
                                <div v-if="u.role !== 'owner' && u.id !== currentUser?.id" class="flex gap-2">
                                    <Link :href="`/users/${u.id}/edit`" class="text-sm text-blue-600 hover:text-blue-800">{{ t('Edit') }}</Link>
                                    <button @click="deleteId = u.id" class="text-sm text-red-600 hover:text-red-800">{{ t('Delete') }}</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <ConfirmModal :show="deleteId !== null" @confirm="handleDelete" @cancel="deleteId = null" />
        <FlashMessage />
    </AppLayout>
</template>