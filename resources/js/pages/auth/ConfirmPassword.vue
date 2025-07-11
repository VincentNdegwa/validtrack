<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle, Shield } from 'lucide-vue-next';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <AuthLayout title="Confirm your password" description="This is a secure area of the application. Please confirm your password before continuing.">
        <Head title="Confirm password" />

        <div class="mb-6 flex justify-center">
            <div class="rounded-full bg-primary/10 p-3">
                <Shield class="h-6 w-6 text-primary" />
            </div>
        </div>

        <form @submit.prevent="submit">
            <div class="space-y-6">
                <div class="grid gap-2">
                    <Label htmlFor="password">Password</Label>
                    <div class="relative">
                        <Input
                            id="password"
                            type="password"
                            class="mt-1 block w-full pr-10"
                            v-model="form.password"
                            required
                            autocomplete="current-password"
                            autofocus
                            placeholder="Enter your password"
                        />
                    </div>
                    <InputError :message="form.errors.password" />
                </div>

                <div class="flex flex-col gap-4">
                    <Button class="w-full transition-all hover:shadow-md" :class="{ 'opacity-90': form.processing }" :disabled="form.processing">
                        <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                        <span>Confirm Password</span>
                    </Button>
                </div>
            </div>
        </form>
    </AuthLayout>
</template>
