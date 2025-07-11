<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Check, LoaderCircle, Lock, Mail, User } from 'lucide-vue-next';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthBase title="Get Started with ValidTrack" description="Create your account to start managing compliance documents">
        <Head title="Register" />

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="name" class="flex items-center gap-1">
                        <User class="h-4 w-4 text-muted-foreground" />
                        <span>Full Name</span>
                    </Label>
                    <div class="relative">
                        <Input
                            id="name"
                            type="text"
                            required
                            autofocus
                            :tabindex="1"
                            autocomplete="name"
                            v-model="form.name"
                            placeholder="John Doe"
                            class="pr-10"
                        />
                    </div>
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email" class="flex items-center gap-1">
                        <Mail class="h-4 w-4 text-muted-foreground" />
                        <span>Email Address</span>
                    </Label>
                    <div class="relative">
                        <Input
                            id="email"
                            type="email"
                            required
                            :tabindex="2"
                            autocomplete="email"
                            v-model="form.email"
                            placeholder="name@company.com"
                            class="pr-10"
                        />
                    </div>
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="password" class="flex items-center gap-1">
                        <Lock class="h-4 w-4 text-muted-foreground" />
                        <span>Password</span>
                    </Label>
                    <div class="relative">
                        <Input
                            id="password"
                            type="password"
                            required
                            :tabindex="3"
                            autocomplete="new-password"
                            v-model="form.password"
                            placeholder="••••••••"
                            class="pr-10"
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
                            required
                            :tabindex="4"
                            autocomplete="new-password"
                            v-model="form.password_confirmation"
                            placeholder="••••••••"
                            class="pr-10"
                        />
                    </div>
                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <div class="mt-1 text-xs text-muted-foreground">
                    By clicking "Create Account", you agree to our
                    <a href="#" class="text-primary hover:underline">Terms of Service</a> and
                    <a href="#" class="text-primary hover:underline">Privacy Policy</a>.
                </div>

                <Button
                    type="submit"
                    class="mt-2 w-full transition-all hover:shadow-md"
                    :class="{ 'opacity-90': form.processing }"
                    tabindex="5"
                    :disabled="form.processing"
                >
                    <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                    <span>Create Account</span>
                </Button>
            </div>

            <!-- <div class="relative my-2">
                <div class="absolute inset-0 flex items-center">
                    <span class="w-full border-t"></span>
                </div>
                <div class="relative flex justify-center text-xs uppercase">
                    <span class="bg-card px-2 text-muted-foreground">Or sign up with</span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <Button variant="outline" type="button" class="hover:bg-secondary/20 transition-colors">
                    <svg class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                    </svg>
                    Facebook
                </Button>
                <Button variant="outline" type="button" class="hover:bg-secondary/20 transition-colors">
                    <svg class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path fill="currentColor" d="M21.35,11.1H12.18V13.83H18.69C18.36,17.64 15.19,19.27 12.19,19.27C8.36,19.27 5,16.25 5,12C5,7.9 8.2,4.73 12.2,4.73C15.29,4.73 17.1,6.7 17.1,6.7L19,4.72C19,4.72 16.56,2 12.1,2C6.42,2 2.03,6.8 2.03,12C2.03,17.05 6.16,22 12.25,22C17.6,22 21.5,18.33 21.5,12.91C21.5,11.76 21.35,11.1 21.35,11.1V11.1Z" />
                    </svg>
                    Google
                </Button>
            </div> -->

            <div class="mt-2 text-center text-sm text-muted-foreground">
                Already have an account?
                <TextLink :href="route('login')" :tabindex="6" class="font-medium text-primary transition-colors hover:text-primary/80">
                    Sign in
                </TextLink>
            </div>
        </form>
    </AuthBase>
</template>
