<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

// Animation state
const loaded = ref(false);
const features = ref([
    {
        title: 'Multi-Tenant Platform',
        description: 'Secure isolation of company data with role-based access control',
        icon: 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0 0v-8.5',
    },
    {
        title: 'Smart Document Management',
        description: 'Track expiry dates, set automated reminders, and maintain compliance',
        icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z',
    },
    {
        title: 'Real-Time Compliance Dashboard',
        description: 'Color-coded status indicators for at-a-glance monitoring',
        icon: 'M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0 0 20.25 18V6A2.25 2.25 0 0 0 18 3.75H6A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25z',
    },
    {
        title: 'Flexible Entity Tracking',
        description: 'Track any entity type - vendors, employees, assets, partners, and more',
        icon: 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z',
    },
]);

onMounted(() => {
    setTimeout(() => {
        loaded.value = true;
    }, 100);
});
</script>

<template>
    <Head title="CompliTrack - Universal Compliance & Document Tracker">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="true" />
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    </Head>

    <div class="flex min-h-screen flex-col bg-background text-foreground">
        <!-- Navbar -->
        <header class="sticky top-0 z-40 w-full border-b border-border/40 bg-background/80 backdrop-blur-sm">
            <div class="container mx-auto flex items-center justify-between px-4 py-4">
                <div class="flex items-center gap-2">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-primary to-accent">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-6 w-6 text-white"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-foreground">CompliTrack</span>
                </div>

                <nav class="flex items-center space-x-2">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="rounded-md bg-primary px-5 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90"
                    >
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="rounded-md bg-secondary px-5 py-2 text-sm font-medium text-secondary-foreground transition-colors hover:bg-secondary/90"
                        >
                            Log in
                        </Link>
                        <Link
                            :href="route('register')"
                            class="rounded-md bg-primary px-5 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90"
                        >
                            Register
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="relative overflow-hidden pt-20 pb-16 md:pt-24 md:pb-20">
            <!-- Background pattern -->
            <div class="bg-grid-pattern absolute inset-0 opacity-[0.03] dark:opacity-[0.05]" aria-hidden="true"></div>

            <div class="container mx-auto px-4">
                <div class="flex flex-col items-center gap-12 md:flex-row">
                    <div class="space-y-6 md:w-1/2">
                        <div :class="['translate-y-4 opacity-0 transition-all duration-700', { 'translate-y-0 opacity-100': loaded }]">
                            <span class="inline-flex items-center rounded-full bg-accent/20 px-3 py-1 text-sm font-medium text-accent-foreground">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="16"
                                    height="16"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="mr-1"
                                >
                                    <path d="m9 12 2 2 4-4" />
                                    <path
                                        d="M12 3c-1.083 0-2.154.099-3.197.287-1.381.248-2.463 1.354-2.667 2.748a14.318 14.318 0 0 0 0 8.93c.204 1.394 1.286 2.5 2.667 2.748A17.32 17.32 0 0 0 12 18c1.083 0 2.154-.099 3.197-.287 1.381-.248 2.463-1.354 2.667-2.748a14.318 14.318 0 0 0 0-8.93c-.204-1.394-1.286-2.5-2.667-2.748A17.32 17.32 0 0 0 12 3Z"
                                    />
                                </svg>
                                Multi-Tenant SaaS
                            </span>
                        </div>

                        <h1
                            :class="[
                                'translate-y-4 text-4xl leading-tight font-bold opacity-0 transition-all delay-100 duration-700 md:text-5xl lg:text-6xl',
                                { 'translate-y-0 opacity-100': loaded },
                            ]"
                        >
                            Universal Compliance & Document Tracker
                        </h1>

                        <p
                            :class="[
                                'translate-y-4 text-lg text-muted-foreground opacity-0 transition-all delay-200 duration-700 md:text-xl',
                                { 'translate-y-0 opacity-100': loaded },
                            ]"
                        >
                            Centralize tracking of compliance documents, set expiry reminders, and generate reports for vendors, employees, assets,
                            and more.
                        </p>

                        <div
                            :class="[
                                'flex translate-y-4 flex-col gap-4 opacity-0 transition-all delay-300 duration-700 sm:flex-row',
                                { 'translate-y-0 opacity-100': loaded },
                            ]"
                        >
                            <Link
                                v-if="!$page.props.auth.user"
                                :href="route('register')"
                                class="rounded-md bg-primary px-6 py-3 text-base font-medium text-primary-foreground transition-colors hover:bg-primary/90"
                            >
                                Start Free Trial
                            </Link>
                            <Link
                                v-else
                                :href="route('dashboard')"
                                class="rounded-md bg-primary px-6 py-3 text-base font-medium text-primary-foreground transition-colors hover:bg-primary/90"
                            >
                                Go to Dashboard
                            </Link>
                            <a
                                href="#features"
                                class="rounded-md bg-secondary px-6 py-3 text-base font-medium text-secondary-foreground transition-colors hover:bg-secondary/90"
                            >
                                Learn More
                            </a>
                        </div>
                    </div>

                    <div :class="['translate-x-4 opacity-0 transition-all delay-300 duration-700 md:w-1/2', { 'translate-x-0 opacity-100': loaded }]">
                        <div class="relative overflow-hidden rounded-xl border border-border shadow-2xl shadow-primary/10">
                            <img
                                src="https://images.unsplash.com/photo-1575936123452-b67c3203c357?fm=jpg&q=60&w=3000&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mnx8aW1hZ2V8ZW58MHx8MHx8fDA%3D"
                                alt="CompliTrack Dashboard"
                                class="aspect-[16/9] w-full object-cover"
                            />
                            <!-- Overlay with gradient -->
                            <div class="absolute inset-0 bg-gradient-to-tr from-primary/40 to-accent/30 mix-blend-overlay"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="bg-secondary/30 py-20">
            <div class="container mx-auto px-4">
                <div class="mb-16 text-center">
                    <h2 class="mb-4 text-3xl font-bold md:text-4xl">Powerful Compliance Management</h2>
                    <p class="mx-auto max-w-2xl text-lg text-muted-foreground">
                        CompliTrack provides everything you need to maintain compliance across your organization.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                    <div
                        v-for="(feature, index) in features"
                        :key="index"
                        :class="[
                            'translate-y-4 rounded-xl border border-border bg-card p-6 opacity-0 shadow-sm transition-all duration-500',
                            { 'translate-y-0 opacity-100': loaded },
                        ]"
                        :style="{ transitionDelay: `${400 + index * 100}ms` }"
                    >
                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.5"
                                class="h-6 w-6 text-primary"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path :d="feature.icon" />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-xl font-semibold">{{ feature.title }}</h3>
                        <p class="text-muted-foreground">{{ feature.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Core Entities Section -->
        <section class="py-20">
            <div class="container mx-auto px-4">
                <div class="mb-16 text-center">
                    <h2 class="mb-4 text-3xl font-bold md:text-4xl">Core Entities</h2>
                    <p class="mx-auto max-w-2xl text-lg text-muted-foreground">
                        CompliTrack is built around these key entities that form the foundation of our platform.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div class="rounded-xl border border-border bg-card p-6 shadow-sm transition-shadow hover:shadow-md">
                        <h3 class="mb-2 flex items-center text-xl font-semibold">
                            <span class="mr-2 flex h-8 w-8 items-center justify-center rounded-full bg-primary/20 text-primary">C</span>
                            Company
                        </h3>
                        <p class="text-muted-foreground">The tenant (organization) with isolated data and customizable settings</p>
                    </div>

                    <div class="rounded-xl border border-border bg-card p-6 shadow-sm transition-shadow hover:shadow-md">
                        <h3 class="mb-2 flex items-center text-xl font-semibold">
                            <span class="mr-2 flex h-8 w-8 items-center justify-center rounded-full bg-accent/20 text-accent">U</span>
                            User
                        </h3>
                        <p class="text-muted-foreground">Users who belong to a company with role-based permissions</p>
                    </div>

                    <div class="rounded-xl border border-border bg-card p-6 shadow-sm transition-shadow hover:shadow-md">
                        <h3 class="mb-2 flex items-center text-xl font-semibold">
                            <span class="mr-2 flex h-8 w-8 items-center justify-center rounded-full bg-chart-3/20 text-chart-3">S</span>
                            Subject
                        </h3>
                        <p class="text-muted-foreground">A tracked entity (vendor, employee, asset, etc.) with categorization</p>
                    </div>

                    <div class="rounded-xl border border-border bg-card p-6 shadow-sm transition-shadow hover:shadow-md">
                        <h3 class="mb-2 flex items-center text-xl font-semibold">
                            <span class="mr-2 flex h-8 w-8 items-center justify-center rounded-full bg-chart-4/20 text-chart-4">D</span>
                            Document
                        </h3>
                        <p class="text-muted-foreground">Compliance documents linked to subjects with expiry tracking</p>
                    </div>

                    <div class="rounded-xl border border-border bg-card p-6 shadow-sm transition-shadow hover:shadow-md">
                        <h3 class="mb-2 flex items-center text-xl font-semibold">
                            <span class="mr-2 flex h-8 w-8 items-center justify-center rounded-full bg-chart-1/20 text-chart-1">R</span>
                            Reminder
                        </h3>
                        <p class="text-muted-foreground">Scheduled alerts based on document expiry dates</p>
                    </div>

                    <div class="rounded-xl border border-border bg-card p-6 shadow-sm transition-shadow hover:shadow-md">
                        <h3 class="mb-2 flex items-center text-xl font-semibold">
                            <span class="mr-2 flex h-8 w-8 items-center justify-center rounded-full bg-chart-5/20 text-chart-5">S</span>
                            Subscription
                        </h3>
                        <p class="text-muted-foreground">Stripe-based billing with multiple tiers and usage limits</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="bg-gradient-to-br from-primary/10 to-accent/10 py-20">
            <div class="container mx-auto px-4 text-center">
                <h2 class="mb-4 text-3xl font-bold md:text-4xl">Ready to Start Tracking Compliance?</h2>
                <p class="mx-auto mb-8 max-w-2xl text-lg text-muted-foreground">
                    Join organizations that trust CompliTrack to maintain compliance and avoid costly regulatory issues.
                </p>

                <Link
                    v-if="!$page.props.auth.user"
                    :href="route('register')"
                    class="rounded-md bg-primary px-8 py-3 text-base font-medium text-primary-foreground transition-colors hover:bg-primary/90"
                >
                    Start Your Free Trial
                </Link>
                <Link
                    v-else
                    :href="route('dashboard')"
                    class="rounded-md bg-primary px-8 py-3 text-base font-medium text-primary-foreground transition-colors hover:bg-primary/90"
                >
                    Go to Dashboard
                </Link>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-border py-10">
            <div class="container mx-auto px-4">
                <div class="flex flex-col items-center justify-between md:flex-row">
                    <div class="mb-4 flex items-center gap-2 md:mb-0">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-primary to-accent">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="h-4 w-4 text-white"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                        <span class="text-lg font-bold">CompliTrack</span>
                    </div>
                    <div class="text-sm text-muted-foreground">&copy; {{ new Date().getFullYear() }} CompliTrack. All rights reserved.</div>
                </div>
            </div>
        </footer>
    </div>
</template>

<style>
.bg-grid-pattern {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'%3E%3Cpath d='M0 0h100v100H0z' fill='none'/%3E%3Cpath d='M0 0h1v100H0zm25 0h1v100h-1zm25 0h1v100h-1zm25 0h1v100h-1zm25 0h1v100h-1zM0 0v1h100V0zm0 25v1h100v-1zm0 25v1h100v-1zm0 25v1h100v-1zm0 25v1h100v-1z' fill='currentColor'/%3E%3C/svg%3E");
}
</style>
