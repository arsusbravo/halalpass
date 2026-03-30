<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { useTrans } from '@/composables/useTrans';
import LanguageSwitcher from '@/components/LanguageSwitcher.vue';

const { t } = useTrans();

const form = useForm({
    // Company fields
    company_name: '',
    company_city: '',
    company_province: '',
    company_phone: '',
    company_npwp: '',
    // User fields
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

function submit() {
    form.post('/register', {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
}
</script>

<template>
    <AuthBase
        :title="t('Create an account')"
        :description="t('Register your company and create your admin account')"
    >
        <Head :title="t('Register')" />

        <div class="mb-4 flex justify-end">
            <LanguageSwitcher />
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <!-- Company Section -->
                <div class="border-b border-gray-200 pb-4 dark:border-gray-700">
                    <p class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-300">{{ t('Company Information') }}</p>

                    <div class="grid gap-3">
                        <div class="grid gap-2">
                            <Label for="company_name">{{ t('Company Name') }} *</Label>
                            <Input
                                id="company_name"
                                v-model="form.company_name"
                                type="text"
                                required
                                autofocus
                                :tabindex="1"
                                :placeholder="t('e.g. PT Berkah Pangan Nusantara')"
                            />
                            <InputError :message="form.errors.company_name" />
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="grid gap-2">
                                <Label for="company_city">{{ t('City') }} *</Label>
                                <Input
                                    id="company_city"
                                    v-model="form.company_city"
                                    type="text"
                                    required
                                    :tabindex="2"
                                    :placeholder="t('e.g. Jakarta')"
                                />
                                <InputError :message="form.errors.company_city" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="company_province">{{ t('Province') }} *</Label>
                                <Input
                                    id="company_province"
                                    v-model="form.company_province"
                                    type="text"
                                    required
                                    :tabindex="3"
                                    :placeholder="t('e.g. DKI Jakarta')"
                                />
                                <InputError :message="form.errors.company_province" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="grid gap-2">
                                <Label for="company_phone">{{ t('Phone') }}</Label>
                                <Input
                                    id="company_phone"
                                    v-model="form.company_phone"
                                    type="text"
                                    :tabindex="4"
                                    placeholder="021-12345678"
                                />
                                <InputError :message="form.errors.company_phone" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="company_npwp">{{ t('NPWP') }}</Label>
                                <Input
                                    id="company_npwp"
                                    v-model="form.company_npwp"
                                    type="text"
                                    :tabindex="5"
                                    placeholder="00.000.000.0-000.000"
                                />
                                <InputError :message="form.errors.company_npwp" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Section -->
                <div>
                    <p class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-300">{{ t('Admin Account') }}</p>

                    <div class="grid gap-3">
                        <div class="grid gap-2">
                            <Label for="name">{{ t('Name') }} *</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                :tabindex="6"
                                autocomplete="name"
                                :placeholder="t('Full name')"
                            />
                            <InputError :message="form.errors.name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="email">{{ t('Email') }} *</Label>
                            <Input
                                id="email"
                                v-model="form.email"
                                type="email"
                                required
                                :tabindex="7"
                                autocomplete="email"
                                placeholder="email@example.com"
                            />
                            <InputError :message="form.errors.email" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="password">{{ t('Password') }} *</Label>
                            <PasswordInput
                                id="password"
                                v-model="form.password"
                                required
                                :tabindex="8"
                                autocomplete="new-password"
                                :placeholder="t('Password')"
                            />
                            <InputError :message="form.errors.password" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="password_confirmation">{{ t('Confirm Password') }} *</Label>
                            <PasswordInput
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                required
                                :tabindex="9"
                                autocomplete="new-password"
                                :placeholder="t('Confirm Password')"
                            />
                            <InputError :message="form.errors.password_confirmation" />
                        </div>
                    </div>
                </div>

                <Button
                    type="submit"
                    class="mt-2 w-full"
                    :tabindex="10"
                    :disabled="form.processing"
                    data-test="register-user-button"
                >
                    <Spinner v-if="form.processing" />
                    {{ t('Create account') }}
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                {{ t('Already have an account?') }}
                <TextLink
                    :href="login()"
                    class="underline underline-offset-4"
                    :tabindex="11"
                >{{ t('Log in') }}</TextLink>
            </div>
        </form>
    </AuthBase>
</template>