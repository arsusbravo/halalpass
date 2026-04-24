<script setup lang="ts">
import { useTrans } from '@/composables/useTrans';
import { ref, watch } from 'vue';

const { t } = useTrans();
const showAdvanced = ref(false);
const suggestions = ref<Array<{ name: string; brand: string | null; sh_number: string; halal_risk_level: string; category: string }>>([]);
const showSuggestions = ref(false);
const searchTimeout = ref<ReturnType<typeof setTimeout> | null>(null);

const props = defineProps<{
    form: {
        name: string;
        brand: string;
        halal_risk_level: string;
        sh_number: string;
        type: string;
        parent_id: string | number;
        supplier_id: string | number;
        category: string;
        origin_country: string;
        manufacturer: string;
        notes: string;
        processing: boolean;
        errors: Record<string, string>;
    };
    suppliers: Array<{ id: number; name: string; code: string | null }>;
    parentIngredients: Array<{ id: number; name: string }>;
    submitLabel: string;
}>();

const emit = defineEmits<{
    submit: [];
}>();

const riskHelp: Record<string, string> = {
    no_risk: 'E.g. eggs, water, fresh vegetables, rice',
    low_risk: 'E.g. salt, sugar, wheat flour',
    medium_risk: 'E.g. MSG, coloring, preservatives, cooking oil',
    high_risk: 'E.g. gelatin, enzymes, emulsifiers, animal-derived ingredients',
};

function onNameInput() {
    if (searchTimeout.value) clearTimeout(searchTimeout.value);

    const query = props.form.name.trim();
    if (query.length < 2) {
        suggestions.value = [];
        showSuggestions.value = false;
        return;
    }

    searchTimeout.value = setTimeout(async () => {
        try {
            const response = await fetch(`/ingredients/search?q=${encodeURIComponent(query)}`);
            const data = await response.json();
            suggestions.value = data;
            showSuggestions.value = data.length > 0;
        } catch {
            suggestions.value = [];
            showSuggestions.value = false;
        }
    }, 300);
}

function pickSuggestion(item: typeof suggestions.value[0]) {
    props.form.name = item.name;
    props.form.brand = item.brand ?? '';
    props.form.sh_number = item.sh_number;
    props.form.halal_risk_level = item.halal_risk_level;
    props.form.category = item.category;
    showSuggestions.value = false;
    suggestions.value = [];
}

function hideSuggestions() {
    setTimeout(() => { showSuggestions.value = false; }, 200);
}
</script>

<template>
    <form @submit.prevent="emit('submit')" class="space-y-5">
        <!-- Ingredient Name with autocomplete -->
        <div class="relative">
            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Ingredient Name') }} *</label>
            <input
                v-model="form.name"
                @input="onNameInput"
                @focus="onNameInput"
                @blur="hideSuggestions"
                type="text"
                required
                autocomplete="off"
                :placeholder="t('e.g. Tepung Terigu Cakra Kembar')"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
            />
            <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>

            <!-- Autocomplete dropdown -->
            <div v-if="showSuggestions && suggestions.length > 0" class="absolute left-0 right-0 top-full z-50 mt-1 max-h-64 overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800">
                <button
                    v-for="(item, i) in suggestions"
                    :key="i"
                    type="button"
                    @mousedown.prevent="pickSuggestion(item)"
                    class="flex w-full items-center justify-between px-4 py-3 text-left text-sm hover:bg-emerald-50 dark:hover:bg-emerald-900/30"
                >
                    <div>
                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ item.name }}</p>
                        <p class="text-xs text-gray-500">{{ item.brand ?? '-' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-mono text-xs text-emerald-600">{{ item.sh_number }}</p>
                        <p class="text-xs text-gray-400">✓ {{ t('Certified') }}</p>
                    </div>
                </button>
            </div>
        </div>

        <!-- Risk Level -->
        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Halal Risk Level') }} *</label>
            <select v-model="form.halal_risk_level" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                <option value="no_risk">{{ t('No Risk — Naturally Halal') }}</option>
                <option value="low_risk">{{ t('Low Risk — Certificate Recommended') }}</option>
                <option value="medium_risk">{{ t('Medium Risk — Certificate Required') }}</option>
                <option value="high_risk">{{ t('High Risk — Certificate Mandatory') }}</option>
            </select>
            <p class="mt-1 text-xs text-gray-400">{{ t(riskHelp[form.halal_risk_level]) }}</p>
        </div>

        <!-- ============================================ -->
        <!-- CERTIFICATE SECTION (not for no_risk)        -->
        <!-- ============================================ -->
        <div v-if="form.halal_risk_level !== 'no_risk'" class="space-y-4">

            <!-- Step 1: Search BPJPH -->
            <div class="rounded-xl border-2 border-emerald-300 bg-linear-to-br from-emerald-50 to-green-50 p-5 dark:border-emerald-700 dark:from-emerald-950/40 dark:to-green-950/40">
                <div class="mb-2 flex items-center gap-2">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-600 text-xs font-bold text-white">1</span>
                    <p class="text-sm font-semibold text-emerald-800 dark:text-emerald-200">{{ t('Find the halal certificate') }}</p>
                </div>
                <p class="mb-4 text-sm text-emerald-700 dark:text-emerald-300">
                    {{ t('Search the official BPJPH directory using your ingredient name, then copy the details below.') }}
                </p>
                <a
                    :href="`https://bpjph.halal.go.id/sertifikat-halal/sertifikat?nama_produk=${encodeURIComponent(form.name || '')}`"
                    target="_blank"
                    class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 hover:shadow-md"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    {{ t('Search on BPJPH Halal Directory') }}
                    <svg class="h-3.5 w-3.5 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                </a>
            </div>

            <!-- Step 2: Paste results -->
            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-900">
                <div class="mb-4 flex items-center gap-2">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-600 text-xs font-bold text-white">2</span>
                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ t('Copy the details from BPJPH') }}</p>
                </div>
                <div class="space-y-3">
                    <div>
                        <label class="mb-1 block text-xs text-gray-500 dark:text-gray-400">
                            {{ t('Brand / Producer') }}
                            <span class="ml-1 text-gray-300">— Nama Produsen</span>
                        </label>
                        <input v-model="form.brand" type="text" :placeholder="t('e.g. PT Bogasari Flour Mills')" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-gray-500 dark:text-gray-400">
                            {{ t('Certificate Number (SH)') }}
                            <span class="ml-1 text-gray-300">— Nomor Sertifikat</span>
                        </label>
                        <input v-model="form.sh_number" type="text" placeholder="00220270910125" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 font-mono text-sm tracking-wide dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                    </div>
                </div>
                <p class="mt-3 text-xs text-gray-400">
                    {{ t('Valid for life under GR 42/2024. No expiry date needed.') }}
                </p>
                <p v-if="form.halal_risk_level === 'low_risk'" class="mt-1 text-xs text-amber-600">
                    {{ t('Certificate is optional for low-risk ingredients, but recommended.') }}
                </p>
            </div>
        </div>

        <!-- No cert needed -->
        <div v-else class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 dark:border-emerald-800 dark:bg-emerald-950/30">
            <p class="flex items-center gap-2 text-sm text-emerald-700 dark:text-emerald-300">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                {{ t('No halal certificate needed for naturally halal ingredients.') }}
            </p>
        </div>

        <!-- Advanced -->
        <div>
            <button type="button" @click="showAdvanced = !showAdvanced" class="flex items-center gap-2 text-sm text-gray-400 hover:text-gray-600">
                <svg :class="['h-4 w-4 transition-transform', showAdvanced ? 'rotate-90' : '']" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                {{ t('More details (optional)') }}
            </button>
            <div v-if="showAdvanced" class="mt-3 space-y-3 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs text-gray-600 dark:text-gray-400">{{ t('Ingredient Type') }}</label>
                        <select v-model="form.type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                            <option value="simple">{{ t('Simple Material') }}</option>
                            <option value="composite">{{ t('Composite Material') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-gray-600 dark:text-gray-400">{{ t('Category') }}</label>
                        <select v-model="form.category" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                            <option value="bahan_baku">{{ t('Raw Material') }}</option>
                            <option value="bahan_tambahan">{{ t('Additive') }}</option>
                            <option value="bahan_penolong">{{ t('Processing Aid') }}</option>
                        </select>
                    </div>
                    <div v-if="form.type === 'composite' || parentIngredients.length > 0">
                        <label class="mb-1 block text-xs text-gray-600 dark:text-gray-400">{{ t('Part of (composite parent)') }}</label>
                        <select v-model="form.parent_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                            <option value="">{{ t('None') }}</option>
                            <option v-for="p in parentIngredients" :key="p.id" :value="p.id">{{ p.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-gray-600 dark:text-gray-400">{{ t('Supplier') }}</label>
                        <select v-model="form.supplier_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                            <option value="">{{ t('None') }}</option>
                            <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-gray-600 dark:text-gray-400">{{ t('Origin Country') }}</label>
                        <input v-model="form.origin_country" type="text" maxlength="2" placeholder="ID" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-gray-600 dark:text-gray-400">{{ t('Manufacturer') }}</label>
                        <input v-model="form.manufacturer" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-xs text-gray-600 dark:text-gray-400">{{ t('Notes') }}</label>
                    <textarea v-model="form.notes" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                </div>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex gap-3 pt-2">
            <button type="submit" :disabled="form.processing" class="rounded-lg bg-emerald-600 px-6 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50">
                {{ form.processing ? t('Loading...') : submitLabel }}
            </button>
            <a href="/ingredients" class="rounded-lg border border-gray-300 px-6 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300">{{ t('Cancel') }}</a>
        </div>
    </form>
</template>