<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { useTrans } from '@/composables/useTrans';
import { computed } from 'vue';

const { t } = useTrans();

const props = defineProps<{
    facility?: {
        id: number;
        name: string;
        address: string;
        city: string;
        province: string;
        postal_code: string | null;
        phone: string | null;
        pic_name: string | null;
        production_capacity: string | null;
        sjph_status: string;
        status: string;
    };
}>();

const isEditing = computed(() => !!props.facility);

const form = useForm({
    name: props.facility?.name ?? '',
    address: props.facility?.address ?? '',
    city: props.facility?.city ?? '',
    province: props.facility?.province ?? '',
    postal_code: props.facility?.postal_code ?? '',
    phone: props.facility?.phone ?? '',
    pic_name: props.facility?.pic_name ?? '',
    production_capacity: props.facility?.production_capacity ?? '',
    sjph_status: props.facility?.sjph_status ?? 'not_started',
    status: props.facility?.status ?? 'active',
});

function submit() {
    if (isEditing.value) {
        form.put(`/facilities/${props.facility!.id}`);
    } else {
        form.post('/facilities');
    }
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-4">
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Facility Name') }} *</label>
                <input v-model="form.name" type="text" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Address') }} *</label>
                <input v-model="form.address" type="text" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                <p v-if="form.errors.address" class="mt-1 text-xs text-red-600">{{ form.errors.address }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('City') }} *</label>
                <input v-model="form.city" type="text" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                <p v-if="form.errors.city" class="mt-1 text-xs text-red-600">{{ form.errors.city }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Province') }} *</label>
                <input v-model="form.province" type="text" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                <p v-if="form.errors.province" class="mt-1 text-xs text-red-600">{{ form.errors.province }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Postal Code') }}</label>
                <input v-model="form.postal_code" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Person In Charge') }}</label>
                <input v-model="form.pic_name" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Phone') }}</label>
                <input v-model="form.phone" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Production Capacity') }}</label>
                <input v-model="form.production_capacity" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Status') }}</label>
                <select v-model="form.status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                    <option value="active">{{ t('Active') }}</option>
                    <option value="inactive">{{ t('Inactive') }}</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('SJPH Status') }}</label>
                <select v-model="form.sjph_status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                    <option value="not_started">{{ t('Not Started') }}</option>
                    <option value="in_progress">{{ t('In Progress') }}</option>
                    <option value="completed">{{ t('Completed') }}</option>
                    <option value="approved">{{ t('Approved') }}</option>
                </select>
            </div>
        </div>

        <div class="flex gap-3 pt-4">
            <button
                type="submit"
                :disabled="form.processing"
                class="rounded-lg bg-emerald-600 px-6 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50"
            >
                {{ form.processing ? t('Loading...') : (isEditing ? t('Update') : t('Create')) }}
            </button>
            <a href="/facilities" class="rounded-lg border border-gray-300 px-6 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">
                {{ t('Cancel') }}
            </a>
        </div>
    </form>
</template>