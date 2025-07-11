<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

defineProps<{
    title?: string;
    description?: string;
}>();

const loaded = ref(false);

onMounted(() => {
    setTimeout(() => {
        loaded.value = true;
    }, 100);
});
</script>

<template>
    <div class="relative flex min-h-svh w-full flex-col items-center justify-center bg-background p-6 md:p-0">
        <!-- Background elements -->
        <div class="bg-grid-pattern absolute inset-0 opacity-[0.03] dark:opacity-[0.4]" aria-hidden="true"></div>
        <div class="absolute top-0 left-0 hidden h-full w-1/2 bg-gradient-to-br from-primary/5 to-accent/5 md:block"></div>

        <!-- Content -->
        <div class="relative z-10 flex w-full items-stretch md:h-svh">
            <!-- Left panel - visible only on md+ screens -->
            <div
                :class="[
                    'hidden flex-col justify-between p-8 transition-opacity duration-700 md:flex md:w-1/2',
                    { 'opacity-100': loaded, 'opacity-0': !loaded },
                ]"
            >
                <!-- Logo and branding -->
                <div>
                    <Link :href="route('home')" class="flex items-center gap-2 font-medium">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-primary to-accent">
                            <AppLogoIcon class="h-6 w-6 fill-current text-white" />
                        </div>
                        <span class="text-xl font-bold text-foreground">ValidTrack</span>
                    </Link>
                </div>

                <!-- Hero image and text -->
                <div
                    :class="['space-y-6', { 'translate-y-0 opacity-100': loaded, 'translate-y-4 opacity-0': !loaded }]"
                    style="
                        transition:
                            opacity 0.7s ease-out 0.2s,
                            transform 0.7s ease-out 0.2s;
                    "
                >
                    <h1 class="text-3xl font-bold text-foreground md:text-4xl">Stay Compliant, <br />Stay Ahead</h1>
                    <p class="max-w-md text-lg text-muted-foreground">Track your compliance documents securely and never miss critical deadlines.</p>
                    <div class="relative mt-8 overflow-hidden rounded-xl border border-border">
                        <img
                            src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?q=80&w=2070&auto=format&fit=crop"
                            alt="ValidTrack Dashboard"
                            class="aspect-video w-full object-cover"
                        />
                        <div class="absolute inset-0 bg-gradient-to-tr from-primary/10 to-accent/10 mix-blend-overlay"></div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="text-sm text-muted-foreground">&copy; {{ new Date().getFullYear() }} ValidTrack. All rights reserved.</div>
            </div>

            <!-- Right panel - form content -->
            <div class="w-full md:w-1/2">
                <div
                    :class="[
                        'mx-auto flex h-full w-full max-w-lg flex-col justify-center transition-opacity duration-700',
                        { 'opacity-100': loaded, 'opacity-0': !loaded },
                    ]"
                >
                    <div class="mb-4 md:hidden">
                        <Link :href="route('home')" class="flex items-center gap-2 font-medium">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-primary to-accent">
                                <AppLogoIcon class="h-6 w-6 fill-current text-white" />
                            </div>
                            <span class="text-xl font-bold text-foreground">ValidTrack</span>
                        </Link>
                    </div>

                    <div class="rounded-xl border border-border bg-card p-6 shadow-lg md:p-8">
                        <div class="space-y-6">
                            <div class="space-y-2 text-center">
                                <h1 class="text-2xl font-bold">{{ title }}</h1>
                                <p class="text-center text-sm text-muted-foreground">{{ description }}</p>
                            </div>
                            <slot />
                        </div>
                    </div>

                    <div class="mt-6 text-center text-sm text-muted-foreground md:hidden">
                        &copy; {{ new Date().getFullYear() }} ValidTrack. All rights reserved.
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.bg-grid-pattern {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'%3E%3Cpath d='M0 0h100v100H0z' fill='none'/%3E%3Cpath d='M0 0h1v100H0zm25 0h1v100h-1zm25 0h1v100h-1zm25 0h1v100h-1zm25 0h1v100h-1zM0 0v1h100V0zm0 25v1h100v-1zm0 25v1h100v-1zm0 25v1h100v-1zm0 25v1h100v-1z' fill='currentColor'/%3E%3C/svg%3E");
}
</style>
