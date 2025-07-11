<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowLeft, LoaderCircle, Mail } from 'lucide-vue-next';

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <AuthLayout title="Reset Your Password" description="Enter your email to receive a password reset link">
        <Head title="Forgot Password" />

        <div class="mb-6 flex justify-center">
            <div class="rounded-full bg-primary/10 p-3">
                <Mail class="h-6 w-6 text-primary" />
            </div>
        </div>

        <div
            v-if="status"
            class="mb-6 rounded-md bg-green-50 p-3 text-center text-sm font-medium text-green-600 dark:bg-green-900/30 dark:text-green-400"
        >
            {{ status }}
        </div>

        <div class="space-y-6">
            <form @submit.prevent="submit">
                <div class="grid gap-3">
                    <Label for="email" class="flex items-center gap-1">
                        <span>Email address</span>
                    </Label>
                    <div class="relative">
                        <Input
                            id="email"
                            type="email"
                            name="email"
                            autocomplete="email"
                            v-model="form.email"
                            autofocus
                            placeholder="name@company.com"
                            class="pr-10"
                        />
                    </div>
                    <InputError :message="form.errors.email" />
                </div>

                <div class="mt-6">
                    <Button class="w-full transition-all hover:shadow-md" :class="{ 'opacity-90': form.processing }" :disabled="form.processing">
                        <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                        <span>Send Reset Link</span>
                    </Button>
                </div>
            </form>

            <div class="text-center">
                <TextLink
                    :href="route('login')"
                    class="inline-flex items-center gap-1 text-sm font-medium text-primary transition-colors hover:text-primary/80"
                >
                    <ArrowLeft class="h-4 w-4" />
                    <span>Back to login</span>
                </TextLink>
            </div>
        </div>
    </AuthLayout>
</template>
