<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { useTrans } from '@/composables/useTrans';
import FlashMessage from '@/components/FlashMessage.vue';
import HalalStatusBadge from '@/components/HalalStatusBadge.vue';
import { type BreadcrumbItem } from '@/types';

const { t } = useTrans();

const props = defineProps<{
    facility: { id: number; name: string; code: string | null };
    document: { id: number; status: string } | null;
    teamMembers: Array<{ name: string; position: string; role: string }>;
    trainings: Array<{ date: string; topic: string; provider: string; attendees: string }>;
    stats: { ingredients: number; ingredients_with_cert: number; products: number };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Facilities', href: '/facilities' },
    { title: props.facility.name, href: `/facilities/${props.facility.id}` },
    { title: 'SJPH', href: '#' },
];

const form = useForm({
    team_members: props.teamMembers.length > 0
        ? [...props.teamMembers]
        : [{ name: '', position: '', role: 'Koordinator Halal' }],
    trainings: props.trainings.length > 0
        ? [...props.trainings]
        : [{ date: '', topic: 'Awareness Halal & SJPH', provider: '', attendees: '' }],
});

function addMember() {
    form.team_members.push({ name: '', position: '', role: '' });
}

function removeMember(index: number) {
    form.team_members.splice(index, 1);
}

function addTraining() {
    form.trainings.push({ date: '', topic: '', provider: '', attendees: '' });
}

function removeTraining(index: number) {
    form.trainings.splice(index, 1);
}

function save() {
    form.post(`/sjph/${props.facility.id}/save`, { preserveScroll: true });
}

function download() {
    form.post(`/sjph/${props.facility.id}/save`, {
        preserveScroll: true,
        onSuccess: () => {
            window.location.href = `/sjph/${props.facility.id}/download`;
            // Reload after a delay so status updates to "approved"
            setTimeout(() => {
                window.location.href = `/sjph/${props.facility.id}`;
            }, 1500);
        },
    });
}
</script>

<template>
    <Head :title="`SJPH — ${facility.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-3xl p-4">
            <!-- Header -->
            <div class="mb-6 flex items-start justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ t('SJPH Document') }}</h2>
                    <p class="mt-1 text-sm text-gray-500">{{ facility.name }} ({{ facility.code }})</p>
                </div>
                <HalalStatusBadge v-if="document" :status="document.status" size="sm" />
            </div>

            <!-- What will be generated -->
            <div class="mb-6 rounded-xl border border-blue-200 bg-blue-50 p-5 dark:border-blue-800 dark:bg-blue-950/30">
                <h3 class="mb-2 text-sm font-semibold text-blue-800 dark:text-blue-200">{{ t('What will be in your SJPH document?') }}</h3>
                <p class="mb-3 text-sm text-blue-700 dark:text-blue-300">{{ t('The following 11 sections are auto-generated from your data. You only need to fill in team members and training records below.') }}</p>
                <div class="grid gap-2 text-xs text-blue-700 dark:text-blue-300 sm:grid-cols-2">
                    <div class="flex items-center gap-2"><svg class="h-3.5 w-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg> 1. Kebijakan Halal</div>
                    <div class="flex items-center gap-2"><svg class="h-3.5 w-3.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg> 2. Tim Manajemen Halal — <strong>{{ t('fill in below') }}</strong></div>
                    <div class="flex items-center gap-2"><svg class="h-3.5 w-3.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg> 3. Pelatihan dan Edukasi — <strong>{{ t('fill in below') }}</strong></div>
                    <div class="flex items-center gap-2"><svg class="h-3.5 w-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg> 4. Bahan ({{ stats.ingredients }} {{ t('ingredients') }})</div>
                    <div class="flex items-center gap-2"><svg class="h-3.5 w-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg> 5. Produk ({{ stats.products }} {{ t('Products') }})</div>
                    <div class="flex items-center gap-2"><svg class="h-3.5 w-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg> 6. Fasilitas Produksi</div>
                    <div class="flex items-center gap-2"><svg class="h-3.5 w-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg> 7. Prosedur Aktivitas Kritis</div>
                    <div class="flex items-center gap-2"><svg class="h-3.5 w-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg> 8. Kemampuan Telusur</div>
                    <div class="flex items-center gap-2"><svg class="h-3.5 w-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg> 9. Penanganan Produk Tidak Halal</div>
                    <div class="flex items-center gap-2"><svg class="h-3.5 w-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg> 10. Audit Internal</div>
                    <div class="flex items-center gap-2"><svg class="h-3.5 w-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg> 11. Kaji Ulang Manajemen</div>
                </div>
            </div>

            <!-- Team Members -->
            <div class="mb-6 rounded-xl border border-sidebar-border/70 bg-white dark:border-sidebar-border dark:bg-gray-900">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ t('Halal Management Team') }}</h3>
                        <p class="mt-1 text-xs text-gray-500">{{ t('Who is responsible for halal compliance in your company?') }}</p>
                    </div>
                    <button @click="addMember" type="button" class="rounded-lg border border-emerald-300 px-3 py-1.5 text-xs font-medium text-emerald-700 hover:bg-emerald-50 dark:border-emerald-700 dark:text-emerald-400">
                        + {{ t('Add Member') }}
                    </button>
                </div>
                <div class="p-5">
                    <div v-for="(member, i) in form.team_members" :key="i" class="mb-3 flex gap-3 rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                        <div class="flex-1">
                            <label class="mb-1 block text-xs text-gray-500">{{ t('Name') }}</label>
                            <input v-model="member.name" type="text" :placeholder="t('e.g. Budi Santoso')" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                        </div>
                        <div class="flex-1">
                            <label class="mb-1 block text-xs text-gray-500">{{ t('Position') }}</label>
                            <input v-model="member.position" type="text" :placeholder="t('e.g. QA Manager')" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                        </div>
                        <div class="flex-1">
                            <label class="mb-1 block text-xs text-gray-500">{{ t('Halal Team Role') }}</label>
                            <input v-model="member.role" type="text" :placeholder="t('e.g. Koordinator Halal')" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                        </div>
                        <button v-if="form.team_members.length > 1" @click="removeMember(i)" type="button" class="mt-5 shrink-0 text-red-400 hover:text-red-600">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Training Records -->
            <div class="mb-6 rounded-xl border border-sidebar-border/70 bg-white dark:border-sidebar-border dark:bg-gray-900">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ t('Training Records') }}</h3>
                        <p class="mt-1 text-xs text-gray-500">{{ t('What halal training has your team completed?') }}</p>
                    </div>
                    <button @click="addTraining" type="button" class="rounded-lg border border-emerald-300 px-3 py-1.5 text-xs font-medium text-emerald-700 hover:bg-emerald-50 dark:border-emerald-700 dark:text-emerald-400">
                        + {{ t('Add Training') }}
                    </button>
                </div>
                <div class="p-5">
                    <div v-for="(tr, i) in form.trainings" :key="i" class="mb-3 rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                        <div class="flex gap-3">
                            <div class="w-32 shrink-0">
                                <label class="mb-1 block text-xs text-gray-500">{{ t('Date') }}</label>
                                <input v-model="tr.date" type="date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                            </div>
                            <div class="flex-1">
                                <label class="mb-1 block text-xs text-gray-500">{{ t('Topic') }}</label>
                                <input v-model="tr.topic" type="text" :placeholder="t('e.g. Awareness Halal & SJPH')" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                            </div>
                            <button v-if="form.trainings.length > 1" @click="removeTraining(i)" type="button" class="mt-5 shrink-0 text-red-400 hover:text-red-600">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </div>
                        <div class="mt-2 flex gap-3">
                            <div class="flex-1">
                                <label class="mb-1 block text-xs text-gray-500">{{ t('Provider') }}</label>
                                <input v-model="tr.provider" type="text" :placeholder="t('e.g. LPPOM MUI')" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                            </div>
                            <div class="flex-1">
                                <label class="mb-1 block text-xs text-gray-500">{{ t('Attendees') }}</label>
                                <input v-model="tr.attendees" type="text" :placeholder="t('e.g. All halal team members')" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Validation errors -->
            <div v-if="Object.keys(form.errors).length > 0" class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-600">
                <p v-for="(error, key) in form.errors" :key="key">{{ error }}</p>
            </div>
            <!-- Actions -->
            <div class="rounded-xl border-2 border-emerald-300 bg-emerald-50 p-5 dark:border-emerald-700 dark:bg-emerald-950/30">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-semibold text-emerald-800 dark:text-emerald-200">{{ t('Generate SJPH Document') }}</p>
                        <p class="mt-1 text-xs text-emerald-700 dark:text-emerald-300">{{ t('Creates a complete SJPH with all 11 sections. Opens in your browser — save or print.') }}</p>
                    </div>
                    <div class="flex gap-2">
                        <button @click="save" :disabled="form.processing" class="rounded-lg border border-emerald-600 px-5 py-2.5 text-sm font-medium text-emerald-700 hover:bg-emerald-100 disabled:opacity-50 dark:text-emerald-300 dark:hover:bg-emerald-900">
                            {{ t('Save') }}
                        </button>
                        <button @click="download" :disabled="form.processing" class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50">
                            <span class="flex items-center gap-2">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3" /></svg>
                                {{ t('Generate & Download SJPH') }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <FlashMessage />
    </AppLayout>
</template>