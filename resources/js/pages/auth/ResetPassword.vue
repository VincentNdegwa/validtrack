<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Check, KeyRound, LoaderCircle, Lock, Mail } from 'lucide-vue-next';

interface Props {
    token: string;
    email: string;
}

const props = defineProps<Props>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <AuthLayout title="Set New Password" description="Create a new secure password for your account">
        <Head title="Reset Password" />

        <div class="mb-6 flex justify-center">
            <div class="rounded-full bg-primary/10 p-3">
                <KeyRound class="h-6 w-6 text-primary" />
            </div>
        </div>

        <form @submit.prevent="submit">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="email" class="flex items-center gap-1">
                        <Mail class="h-4 w-4 text-muted-foreground" />
                        <span>Email</span>
                    </Label>
                    <div class="relative">
                        <Input id="email" type="email" name="email" autocomplete="email" v-model="form.email" class="bg-muted/50 pr-10" readonly />
                    </div>
                    <InputError :message="form.errors.email" class="mt-1" />
                </div>

                <div class="grid gap-2">
                    <Label for="password" class="flex items-center gap-1">
                        <Lock class="h-4 w-4 text-muted-foreground" />
                        <span>New Password</span>
                    </Label>
                    <div class="relative">
                        <Input
                            id="password"
                            type="password"
                            name="password"
                            autocomplete="new-password"
                            v-model="form.password"
                            class="pr-10"
                            autofocus
                            placeholder="••••••••"
                        />
                    </div>
                    <p class="text-xs text-muted-foreground">Password must be at least 8 characters</p>
                    <InputError :message="form.errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation" class="flex items-center gap-1">
                        <Check class="h-4 w-4 text-muted-foreground" />
                        <span>Confirm Password</span>
                    </Label>
                    <div class="relative">
                        <Input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            autocomplete="new-password"
                            v-model="form.password_confirmation"
                            class="pr-10"
                            placeholder="••••••••"
                        />
                    </div>
                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <Button
                    type="submit"
                    class="mt-4 w-full transition-all hover:shadow-md"
                    :class="{ 'opacity-90': form.processing }"
                    :disabled="form.processing"
                >
                    <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                    <span>Reset Password</span>
                </Button>
            </div>
        </form>
    </AuthLayout>
</template>
