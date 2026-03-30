<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { useTrans } from '@/composables/useTrans';
import FlashMessage from '@/components/FlashMessage.vue';
import HalalStatusBadge from '@/components/HalalStatusBadge.vue';
import type { BreadcrumbItem } from '@/types';
import { ref } from 'vue';

const { t } = useTrans();
const props = defineProps<{
    facility: { id: number; name: string };
    document: { id: number; version: string; status: string; approved_by: number | null; approved_at: string | null };
    progress: { document_id: number; version: string; status: string; completion_percentage: number; incomplete_criteria: string[]; sections: Array<{ key: string; label: string; filled: boolean; data: any }> };
    criteriaLabels: Record<string, string>;
}>();
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: '/dashboard' }, { title: 'Facilities', href: '/facilities' }, { title: props.facility.name, href: `/facilities/${props.facility.id}` }, { title: 'SJPH', href: '#' }];

const activeSection = ref<string | null>(null);
const sectionForm = useForm({ criterion: '', section_data: {} as Record<string, any> });

function openSection(key: string, existingData: any) {
    activeSection.value = key;
    sectionForm.criterion = key;
    sectionForm.section_data = existingData ? { ...existingData } : {};
}

function saveSection() {
    sectionForm.transform((formData) => ({
        criterion: formData.criterion,
        data: formData.section_data,
    })).post(`/sjph/${props.document.id}/section`, {
        preserveScroll: true,
        onSuccess: () => { activeSection.value = null; },
    });
}

function submitForReview() { router.post(`/sjph/${props.document.id}/submit`); }
function approve() { router.post(`/sjph/${props.document.id}/approve`); }
function reject() { router.post(`/sjph/${props.document.id}/reject`); }
function newVersion() { router.post(`/sjph/${props.document.id}/new-version`); }
</script>

<template>
    <Head :title="`SJPH — ${facility.name}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-3xl p-4">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ t('SJPH Document') }}</h2>
                    <p class="text-sm text-gray-500">{{ facility.name }} &middot; {{ t('Version') }} {{ progress.version }}</p>
                </div>
                <HalalStatusBadge :status="document.status" />
            </div>

            <!-- Progress Bar -->
            <div class="mb-6 rounded-xl border border-sidebar-border/70 bg-white p-5 dark:border-sidebar-border dark:bg-gray-900">
                <div class="mb-2 flex items-center justify-between text-sm">
                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ t('Completion') }}</span>
                    <span class="font-bold" :class="progress.completion_percentage === 100 ? 'text-emerald-600' : 'text-amber-600'">{{ progress.completion_percentage }}%</span>
                </div>
                <div class="h-2 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                    <div class="h-full rounded-full transition-all duration-500" :class="progress.completion_percentage === 100 ? 'bg-emerald-500' : 'bg-amber-500'" :style="{ width: `${progress.completion_percentage}%` }" />
                </div>
            </div>

            <!-- Sections -->
            <div class="space-y-2">
                <div v-for="section in progress.sections" :key="section.key" class="rounded-xl border border-sidebar-border/70 bg-white dark:border-sidebar-border dark:bg-gray-900">
                    <button @click="openSection(section.key, section.data)" class="flex w-full items-center justify-between px-5 py-4 text-left hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <div class="flex items-center gap-3">
                            <span :class="section.filled ? 'bg-emerald-500' : 'bg-gray-300 dark:bg-gray-600'" class="flex h-6 w-6 items-center justify-center rounded-full text-xs text-white">
                                <svg v-if="section.filled" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                <span v-else>&bull;</span>
                            </span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ section.label }}</span>
                        </div>
                        <span class="text-xs" :class="section.filled ? 'text-emerald-600' : 'text-gray-400'">{{ section.filled ? t('Completed') : t('Not Started') }}</span>
                    </button>

                    <!-- Inline Edit -->
                    <div v-if="activeSection === section.key && document.status === 'draft'" class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">
                        <p class="mb-3 text-sm text-gray-500">{{ t('Enter data for') }}: {{ section.label }}</p>
                        <textarea v-model="sectionForm.section_data.content" rows="6" :placeholder="`${section.label} content (JSON or text)`" class="mb-3 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                        <div class="flex gap-2">
                            <button @click="saveSection" :disabled="sectionForm.processing" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50">{{ t('Save Section') }}</button>
                            <button @click="activeSection = null" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300">{{ t('Cancel') }}</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-6 flex gap-3">
                <button v-if="document.status === 'draft' && progress.completion_percentage === 100" @click="submitForReview" class="rounded-lg bg-blue-600 px-6 py-2 text-sm font-medium text-white hover:bg-blue-700">{{ t('Submit for Review') }}</button>
                <button v-if="document.status === 'in_review'" @click="approve" class="rounded-lg bg-emerald-600 px-6 py-2 text-sm font-medium text-white hover:bg-emerald-700">{{ t('Approve') }}</button>
                <button v-if="document.status === 'in_review'" @click="reject" class="rounded-lg border border-gray-300 px-6 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300">{{ t('Reject to Draft') }}</button>
                <button v-if="document.status === 'approved'" @click="newVersion" class="rounded-lg border border-emerald-600 px-6 py-2 text-sm font-medium text-emerald-600 hover:bg-emerald-50">{{ t('Create New Version') }}</button>
            </div>
        </div>
        <FlashMessage />
    </AppLayout>
</template>
