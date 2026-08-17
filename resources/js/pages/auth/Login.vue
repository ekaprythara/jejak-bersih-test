<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { Globe } from '@lucide/vue';
import { onMounted, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import PasskeyVerify from '@/components/PasskeyVerify.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineOptions({
    layout: {
        title: 'Log in to your account',
        description: 'Enter your username and password below to log in',
    },
});

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const backgroundImg = '/images/tracking-background.png';

const username = ref<string>('');
const password = ref<HTMLInputElement | null>(null);
const isChecked = ref<boolean>(false);

onMounted(() => {
    const localStorageUsername = localStorage.getItem('username');
    const localStorageIsChecked = localStorage.getItem('isChecked');

    if (localStorageUsername && localStorageIsChecked) {
        username.value = localStorageUsername;
        isChecked.value = true;

        password.value?.focus();
    } else {
        username.value = '';
        isChecked.value = false;
    }
});

const handleSuccess = () => {
    if (!isChecked.value) {
        localStorage.removeItem('username');
        localStorage.removeItem('isChecked');
    } else {
        localStorage.setItem('username', username.value);
        localStorage.setItem('isChecked', 'true');
    }
};
</script>

<template>
    <Head title="Log in" />

    <!-- Container Utama Full Height dengan Background -->

    <!-- Card Login Liquid Glass -->
    <div
        class="relative w-full max-w-md overflow-hidden rounded-3xl border border-white/40 bg-white/20 p-8 shadow-[0_8px_32px_0_rgba(31,38,135,0.25)] backdrop-blur-xl sm:p-10"
    >
        <!-- Refleksi Kilau Kaca Cairan -->
        <div
            class="pointer-events-none absolute -top-12 -left-12 h-40 w-40 rounded-full bg-white/30 blur-2xl"
        ></div>

        <!-- Header / Logo -->
        <div class="relative z-10 mb-6 flex flex-col items-center text-center">
            <div
                class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl border border-white/50 bg-white/30 shadow-sm backdrop-blur-md"
            >
                <Globe class="h-6 w-6 text-slate-900" />
            </div>
            <h1
                class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl"
            >
                Log in to your account
            </h1>
            <p class="mt-1 text-sm font-medium text-slate-700">
                Enter your username and password below to log in
            </p>
        </div>

        <!-- Form -->
        <Form
            v-bind="store.form()"
            :reset-on-success="['password']"
            @success="handleSuccess"
            v-slot="{ errors, processing }"
            class="relative z-10 flex flex-col gap-5"
        >
            <div class="grid gap-4">
                <!-- Username -->
                <div class="grid gap-2">
                    <Label for="username" class="font-bold text-slate-800"
                        >Username</Label
                    >
                    <Input
                        id="username"
                        type="text"
                        name="username"
                        v-model="username"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="username"
                        placeholder="chandra"
                        class="h-11 rounded-xl border-white/40 bg-white/40 text-slate-900 placeholder:text-slate-500 focus-visible:ring-slate-400"
                    />
                    <InputError :message="errors.username" />
                </div>

                <!-- Password -->
                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password" class="font-bold text-slate-800"
                            >Password</Label
                        >
                        <TextLink
                            v-if="canResetPassword"
                            :href="request()"
                            class="text-xs font-bold text-slate-700 hover:text-slate-900 hover:underline"
                            :tabindex="5"
                        >
                            Forgot your password?
                        </TextLink>
                    </div>
                    <PasswordInput
                        id="password"
                        name="password"
                        required
                        :tabindex="2"
                        ref="password"
                        autocomplete="current-password"
                        placeholder="Password"
                        class="h-11 rounded-xl border-white/40 bg-white/40 text-slate-900 placeholder:text-slate-500 focus-visible:ring-slate-400"
                    />
                    <InputError :message="errors.password" />
                </div>

                <!-- Remember Me -->
                <div class="flex items-center pt-1">
                    <Label
                        for="remember"
                        class="flex cursor-pointer items-center space-x-2.5 text-sm font-bold text-slate-800"
                    >
                        <Checkbox
                            id="remember"
                            name="remember"
                            v-model="isChecked"
                            :tabindex="3"
                            class="h-4 w-4 rounded border-white/50 bg-white/40 data-[state=checked]:bg-slate-900 data-[state=checked]:text-white"
                        />
                        <span>Remember me</span>
                    </Label>
                </div>

                <!-- Submit Button -->
                <Button
                    type="submit"
                    class="mt-2 h-11 w-full rounded-xl bg-slate-900 font-bold text-white shadow-md hover:bg-slate-800"
                    :tabindex="4"
                    :disabled="processing"
                    data-test="login-button"
                >
                    <Spinner v-if="processing" />
                    <span v-else>Log in</span>
                </Button>
            </div>

            <!-- Footer Text -->
            <div class="mt-2 text-center text-sm font-medium text-slate-700">
                Don't have an account?
                <TextLink
                    :href="register()"
                    :tabindex="5"
                    class="font-extrabold text-slate-900 hover:underline"
                >
                    Sign up
                </TextLink>
            </div>
        </Form>
    </div>
</template>
