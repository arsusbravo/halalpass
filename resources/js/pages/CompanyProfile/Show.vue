<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { useTrans } from '@/composables/useTrans';
import FlashMessage from '@/components/FlashMessage.vue';
import { type BreadcrumbItem } from '@/types';
import { ref } from 'vue';

const { t } = useTrans();

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
        signature_path: string | null;
        status: string;
    };
    signatureUrl: string | null;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Company Profile', href: '/company-profile' },
];

const signaturePreview = ref<string | null>(props.signatureUrl);
const signatureFile = ref<File | null>(null);
const signatureMode = ref<'draw' | 'upload'>('draw');
const isDrawing = ref(false);
const canvasRef = ref<HTMLCanvasElement | null>(null);
const drawing = ref(false);

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
    signature: null as File | null,
});

function onSignatureChange(event: Event) {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files[0]) {
        const file = input.files[0];
        signatureFile.value = file;
        form.signature = file;
        // Preview
        const reader = new FileReader();
        reader.onload = (e) => {
            signaturePreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
}

function removeSignature() {
    if (confirm(t('Remove signature?'))) {
        router.delete('/company-profile/signature', { preserveScroll: true });
        signaturePreview.value = null;
        signatureFile.value = null;
        form.signature = null;
        isDrawing.value = false;
    }
}

// --- Canvas drawing ---
function getCanvasCtx(): CanvasRenderingContext2D | null {
    const canvas = canvasRef.value;
    if (!canvas) return null;
    const ctx = canvas.getContext('2d');
    if (ctx) {
        ctx.strokeStyle = '#1a1a1a';
        ctx.lineWidth = 2.5;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
    }
    return ctx;
}

function getCanvasPos(e: MouseEvent): { x: number; y: number } {
    const canvas = canvasRef.value!;
    const rect = canvas.getBoundingClientRect();
    return {
        x: (e.clientX - rect.left) * (canvas.width / rect.width),
        y: (e.clientY - rect.top) * (canvas.height / rect.height),
    };
}

function getTouchPos(e: TouchEvent): { x: number; y: number } {
    const canvas = canvasRef.value!;
    const rect = canvas.getBoundingClientRect();
    const touch = e.touches[0];
    return {
        x: (touch.clientX - rect.left) * (canvas.width / rect.width),
        y: (touch.clientY - rect.top) * (canvas.height / rect.height),
    };
}

function startDrawing(e: MouseEvent) {
    drawing.value = true;
    const ctx = getCanvasCtx();
    if (!ctx) return;
    const pos = getCanvasPos(e);
    ctx.beginPath();
    ctx.moveTo(pos.x, pos.y);
}

function draw(e: MouseEvent) {
    if (!drawing.value) return;
    const ctx = getCanvasCtx();
    if (!ctx) return;
    const pos = getCanvasPos(e);
    ctx.lineTo(pos.x, pos.y);
    ctx.stroke();
}

function startDrawingTouch(e: TouchEvent) {
    drawing.value = true;
    const ctx = getCanvasCtx();
    if (!ctx) return;
    const pos = getTouchPos(e);
    ctx.beginPath();
    ctx.moveTo(pos.x, pos.y);
}

function drawTouch(e: TouchEvent) {
    if (!drawing.value) return;
    const ctx = getCanvasCtx();
    if (!ctx) return;
    const pos = getTouchPos(e);
    ctx.lineTo(pos.x, pos.y);
    ctx.stroke();
}

function stopDrawing() {
    drawing.value = false;
}

function clearCanvas() {
    const canvas = canvasRef.value;
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    if (ctx) ctx.clearRect(0, 0, canvas.width, canvas.height);
}

function saveCanvas() {
    const canvas = canvasRef.value;
    if (!canvas) return;
    canvas.toBlob((blob) => {
        if (!blob) return;
        const file = new File([blob], 'signature.png', { type: 'image/png' });
        form.signature = file;
        signaturePreview.value = canvas.toDataURL('image/png');
        isDrawing.value = false;
    }, 'image/png');
}

function submit() {
    form.post('/company-profile', {
        forceFormData: true,
        preserveScroll: true,
    });
}
</script>

<template>
    <Head :title="t('Company Profile')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="lg:mx-auto p-4">
            <h2 class="mb-6 text-xl font-semibold text-gray-900 dark:text-gray-100">{{ t('Company Profile') }}</h2>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Required fields notice -->
                <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700 dark:border-amber-800 dark:bg-amber-950/30 dark:text-amber-300">
                    {{ t('Fields marked with * are required for SJPH document generation and SIHALAL submission.') }}
                </div>

                <!-- Company Name -->
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Company Name') }} *</label>
                    <input v-model="form.name" type="text" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                    <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                </div>

                <!-- NPWP + BPJPH -->
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('NPWP') }} *</label>
                        <input v-model="form.npwp" type="text" required placeholder="01.234.567.8-901.000" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                        <p v-if="form.errors.npwp" class="mt-1 text-xs text-red-600">{{ form.errors.npwp }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('BPJPH Registration') }}</label>
                        <input v-model="form.bpjph_registration_number" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                    </div>
                </div>

                <!-- Address -->
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Address') }}</label>
                    <input v-model="form.address" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('City') }}</label>
                        <input v-model="form.city" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Province') }}</label>
                        <input v-model="form.province" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Postal Code') }}</label>
                        <input v-model="form.postal_code" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                    </div>
                </div>

                <!-- Contact -->
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Phone') }}</label>
                        <input v-model="form.phone" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Email') }}</label>
                        <input v-model="form.email" type="email" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                    </div>
                </div>

                <!-- Person in Charge (required for SJPH) -->
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Person In Charge (Director/Owner)') }} *</label>
                        <input v-model="form.pic_name" type="text" required :placeholder="t('Full name of company director')" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                        <p v-if="form.errors.pic_name" class="mt-1 text-xs text-red-600">{{ form.errors.pic_name }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('Contact Phone') }}</label>
                        <input v-model="form.pic_phone" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                    </div>
                </div>

                <!-- Signature -->
                <div class="rounded-xl border border-sidebar-border/70 bg-white p-5 dark:border-sidebar-border dark:bg-gray-900">
                    <h3 class="mb-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ t('Director Signature') }} *</h3>
                    <p class="mb-4 text-xs text-gray-500">{{ t('Draw your signature or upload an image. This will appear on your SJPH document.') }}</p>

                    <!-- Preview (existing signature) -->
                    <div v-if="signaturePreview && !isDrawing" class="mb-4">
                        <div class="inline-block rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                            <img :src="signaturePreview" alt="Signature" class="h-20 max-w-xs object-contain" />
                        </div>
                        <div class="mt-2 flex gap-3">
                            <button type="button" @click="removeSignature" class="text-xs text-red-500 hover:text-red-700">{{ t('Remove signature') }}</button>
                        </div>
                    </div>

                    <!-- Tabs: Draw / Upload -->
                    <div v-if="!signaturePreview || isDrawing" class="space-y-3">
                        <div class="flex gap-2">
                            <button type="button" @click="signatureMode = 'draw'" :class="['rounded-lg px-3 py-1.5 text-xs font-medium', signatureMode === 'draw' ? 'bg-emerald-600 text-white' : 'border border-gray-300 text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-400']">
                                {{ t('Draw') }}
                            </button>
                            <button type="button" @click="signatureMode = 'upload'" :class="['rounded-lg px-3 py-1.5 text-xs font-medium', signatureMode === 'upload' ? 'bg-emerald-600 text-white' : 'border border-gray-300 text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-400']">
                                {{ t('Upload') }}
                            </button>
                        </div>

                        <!-- Canvas -->
                        <div v-if="signatureMode === 'draw'">
                            <div class="rounded-lg border-2 border-dashed border-gray-300 bg-white dark:border-gray-600 dark:bg-gray-800">
                                <canvas
                                    ref="canvasRef"
                                    width="500"
                                    height="150"
                                    class="w-full cursor-crosshair touch-none"
                                    @mousedown="startDrawing"
                                    @mousemove="draw"
                                    @mouseup="stopDrawing"
                                    @mouseleave="stopDrawing"
                                    @touchstart.prevent="startDrawingTouch"
                                    @touchmove.prevent="drawTouch"
                                    @touchend="stopDrawing"
                                />
                            </div>
                            <div class="mt-2 flex gap-2">
                                <button type="button" @click="clearCanvas" class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-400">
                                    {{ t('Clear') }}
                                </button>
                                <button type="button" @click="saveCanvas" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-emerald-700">
                                    {{ t('Use this signature') }}
                                </button>
                            </div>
                        </div>

                        <!-- Upload -->
                        <div v-if="signatureMode === 'upload'">
                            <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                {{ t('Choose file') }}
                                <input type="file" accept="image/png,image/jpeg" @change="onSignatureChange" class="hidden" />
                            </label>
                            <p class="mt-1 text-xs text-gray-400">PNG or JPG, max 2MB</p>
                        </div>
                    </div>

                    <!-- Change button when signature exists -->
                    <div v-if="signaturePreview && !isDrawing" class="mt-3">
                        <button type="button" @click="isDrawing = true; signatureMode = 'draw'" class="text-xs text-emerald-600 hover:text-emerald-800">
                            {{ t('Change signature') }}
                        </button>
                    </div>

                    <p v-if="form.errors.signature" class="mt-2 text-xs text-red-600">{{ form.errors.signature }}</p>
                </div>

                <!-- Submit -->
                <div class="flex gap-3 pt-2">
                    <button type="submit" :disabled="form.processing" class="rounded-lg bg-emerald-600 px-6 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50">
                        {{ form.processing ? t('Loading...') : t('Save Profile') }}
                    </button>
                </div>
            </form>
        </div>
        <FlashMessage />
    </AppLayout>
</template>