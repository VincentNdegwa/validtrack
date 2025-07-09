<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

// Animation state
const loaded = ref(false);
const features = ref([
    {
        title: 'Real-Time Compliance Dashboard',
        description: 'Monitor document statuses with intuitive color-coded indicators and expiry timelines at a glance',
        icon: 'M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0 0 20.25 18V6A2.25 2.25 0 0 0 18 3.75H6A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25z',
    },
    {
        title: 'Smart Document Management',
        description: 'Get alerts before critical documents expire, track revision history, and securely store your compliance files',
        icon: 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z',
    },
    {
        title: 'Comprehensive Activity Logs',
        description: 'Track every change with detailed audit trails showing who did what and when, ensuring accountability and transparency',
        icon: 'M10.125 2.25h-4.5c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125v-9M10.125 2.25h.375a9 9 0 0 1 9 9v.375M10.125 2.25A3.375 3.375 0 0 1 13.5 5.625v1.5c0 .621.504 1.125 1.125 1.125h1.5a3.375 3.375 0 0 1 3.375 3.375M9 15l2.25 2.25L15 12',
    },
    {
        title: 'Intelligent Reminders',
        description: 'Automated notification system that alerts stakeholders when documents are approaching expiration dates',
        icon: 'M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0',
    },
]);

const testimonials = ref([
    {
        name: 'Sarah Johnson',
        role: 'Compliance Officer',
        company: 'TechSolutions Inc.',
        quote: 'ValidTrack has transformed how we manage vendor certifications. We\'ve reduced compliance gaps by 87% in just three months.',
        avatar: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80',
    },
    {
        name: 'Michael Chen',
        role: 'Operations Director',
        company: 'GlobalTrade Partners',
        quote: 'The activity logging feature saved us during our annual audit. Being able to show a complete history of document changes was invaluable.',
        avatar: 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80',
    },
    {
        name: 'Priya Patel',
        role: 'HR Manager',
        company: 'Innovate Financial',
        quote: 'Managing employee certifications used to be a nightmare. Now with ValidTrack\'s reminder system, we\'ve eliminated all expired documents.',
        avatar: 'https://images.unsplash.com/photo-1517841905240-472988babdf9?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80',
    },
]);

const stats = ref([
    { value: '98%', label: 'Compliance Rate' },
    { value: '73%', label: 'Time Saved' },
    { value: '54%', label: 'Cost Reduction' },
    { value: '3,500+', label: 'Documents Tracked' },
]);

const useCases = ref([
    {
        title: 'Vendor Management',
        description: 'Track vendor insurance certificates, contracts, and security attestations to ensure uninterrupted business operations',
        icon: 'M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72L4.318 3.44A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72m-13.5 8.65h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .415.336.75.75.75z',
    },
    {
        title: 'Employee Certifications',
        description: 'Keep track of professional licenses, training certificates, and background checks with automated renewal workflows',
        icon: 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z',
    },
    {
        title: 'Asset & Equipment Compliance',
        description: 'Monitor inspection dates, calibration certificates, and maintenance records for critical equipment and assets',
        icon: 'M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z',
    },
    {
        title: 'Regulatory Documentation',
        description: 'Stay ahead of industry regulations by tracking required filings, permits, and compliance documentation with built-in reminders',
        icon: 'M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418',
    },
]);

onMounted(() => {
    setTimeout(() => {
        loaded.value = true;
    }, 100);
});
</script>

<template>
    <Head title="ValidTrack - Never Miss a Compliance Deadline Again">
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
                    <span class="text-xl font-bold text-foreground">ValidTrack</span>
                </div>

                <nav class="hidden md:flex items-center space-x-6">
                    <a href="#features" class="text-sm font-medium text-foreground/80 hover:text-primary transition-colors">Features</a>
                    <a href="#use-cases" class="text-sm font-medium text-foreground/80 hover:text-primary transition-colors">Use Cases</a>
                    <a href="#testimonials" class="text-sm font-medium text-foreground/80 hover:text-primary transition-colors">Testimonials</a>
                    <a href="#stats" class="text-sm font-medium text-foreground/80 hover:text-primary transition-colors">Results</a>
                </nav>

                <div class="flex items-center space-x-2">
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
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="relative overflow-hidden pt-24 pb-20 md:pt-32 md:pb-28">
            <!-- Background pattern -->
            <div class="absolute inset-0 bg-gradient-to-br from-primary/5 to-accent/5"></div>
            <div class="absolute inset-0 bg-grid-pattern opacity-[0.03] dark:opacity-[0.05]" aria-hidden="true"></div>

            <div class="container mx-auto px-4">
                <div class="flex flex-col items-center gap-12 md:flex-row">
                    <div class="space-y-8 md:w-1/2">
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
                                New Activity Logging System
                            </span>
                        </div>

                        <h1
                            :class="[
                                'translate-y-4 text-4xl leading-tight font-extrabold tracking-tight opacity-0 transition-all delay-100 duration-700 md:text-5xl lg:text-6xl',
                                { 'translate-y-0 opacity-100': loaded },
                            ]"
                        >
                            Never Miss a <span class="text-primary">Compliance</span> Deadline Again
                        </h1>

                        <p
                            :class="[
                                'translate-y-4 text-lg text-muted-foreground opacity-0 transition-all delay-200 duration-700 md:text-xl',
                                { 'translate-y-0 opacity-100': loaded },
                            ]"
                        >
                            ValidTrack's advanced activity logging and document management system gives you complete visibility into your compliance status,
                            with smart reminders before critical documents expire.
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
                                class="rounded-md bg-primary px-6 py-3 text-base font-medium text-primary-foreground transition-colors hover:bg-primary/90 flex items-center justify-center"
                            >
                                Start Free Trial
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </Link>
                            <Link
                                v-else
                                :href="route('dashboard')"
                                class="rounded-md bg-primary px-6 py-3 text-base font-medium text-primary-foreground transition-colors hover:bg-primary/90 flex items-center justify-center"
                            >
                                Go to Dashboard
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </Link>
                            <a
                                href="#features"
                                class="rounded-md bg-secondary px-6 py-3 text-base font-medium text-secondary-foreground transition-colors hover:bg-secondary/90 flex items-center justify-center"
                            >
                                See Features
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        </div>
                    </div>

                    <div :class="['translate-x-4 opacity-0 transition-all delay-300 duration-700 md:w-1/2', { 'translate-x-0 opacity-100': loaded }]">
                        <div class="relative rounded-xl border border-border shadow-2xl shadow-primary/10 overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-tr from-primary/10 to-accent/10 mix-blend-overlay"></div>
                            <img
                                src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?q=80&w=2070&auto=format&fit=crop"
                                alt="ValidTrack Dashboard"
                                class="relative z-10 aspect-[16/9] w-full object-cover"
                            />
                            <!-- Activity Log Alert Overlay -->
                            <div class="absolute bottom-4 right-4 max-w-xs p-3 bg-card/95 backdrop-blur-sm border border-border rounded-lg shadow-lg z-20">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0 h-9 w-9 rounded-full bg-primary/20 flex items-center justify-center text-primary">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-medium text-sm">Insurance Certificate Expiring</h3>
                                        <p class="text-xs text-muted-foreground mt-1">Vendor "TechSupply Inc." has a document expiring in 14 days.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="py-24 bg-secondary/20">
            <div class="container mx-auto px-4">
                <div class="mb-16 text-center">
                    <span class="inline-block rounded-full bg-primary/20 px-3 py-1 text-sm font-medium text-primary mb-4">Features</span>
                    <h2 class="mb-4 text-3xl font-bold tracking-tight md:text-4xl">Stay Compliant With Powerful Tools</h2>
                    <p class="mx-auto max-w-2xl text-lg text-muted-foreground">
                        Comprehensive monitoring with detailed activity logs and document tracking to eliminate compliance gaps.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                    <div
                        v-for="(feature, index) in features"
                        :key="index"
                        :class="[
                            'group translate-y-4 rounded-xl border border-border bg-card p-6 opacity-0 shadow-sm transition-all duration-500 hover:border-primary/20 hover:shadow-md',
                            { 'translate-y-0 opacity-100': loaded },
                        ]"
                        :style="{ transitionDelay: `${400 + index * 100}ms` }"
                    >
                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 group-hover:bg-primary/20 transition-colors">
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

        <!-- Activity Logs Highlight Section -->
        <section class="py-24 relative overflow-hidden">
            <div class="container mx-auto px-4">
                <div class="flex flex-col md:flex-row gap-12 items-center">
                    <div class="md:w-1/2 space-y-6">
                        <span class="inline-block rounded-full bg-primary/20 px-3 py-1 text-sm font-medium text-primary">New Feature</span>
                        <h2 class="text-3xl font-bold tracking-tight md:text-4xl">Complete Audit Trail With Activity Logs</h2>
                        <p class="text-lg text-muted-foreground">
                            Our new activity logging system tracks every change across your organization. Know exactly who did what and when, with detailed 
                            before-and-after comparisons for all document updates.
                        </p>
                        <ul class="space-y-3">
                            <li class="flex items-start gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 flex-shrink-0 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Track creation, updates, and deletion of all records</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 flex-shrink-0 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Search and filter activity by user, action type, or target</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 flex-shrink-0 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Generate audit reports for compliance reviews</span>
                            </li>
                        </ul>
                        <div>
                            <Link
                                v-if="$page.props.auth.user"
                                :href="route('activity-logs.index')"
                                class="rounded-md bg-primary px-5 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90 inline-flex items-center"
                            >
                                View Activity Logs
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </Link>
                        </div>
                    </div>
                    <div class="md:w-1/2">
                        <div class="relative rounded-xl border border-border shadow-lg overflow-hidden">
                            <img
                                src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?q=80&w=2070&auto=format&fit=crop"
                                alt="Activity Logs"
                                class="w-full aspect-video object-cover"
                            />
                            <div class="absolute inset-0 bg-gradient-to-b from-transparent to-background/80"></div>
                            <div class="absolute bottom-0 left-0 right-0 p-6">
                                <div class="bg-card/95 backdrop-blur-sm border border-border rounded-lg shadow-lg p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <h3 class="font-medium">Recent Activity</h3>
                                        <span class="text-xs text-muted-foreground">Today</span>
                                    </div>
                                    <div class="space-y-3">
                                        <div class="flex items-center gap-3 p-2 rounded-md hover:bg-accent/10">
                                            <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                                                <span class="text-green-700 text-xs">JD</span>
                                            </div>
                                            <div>
                                                <p class="text-sm"><span class="font-medium">John Doe</span> created a new document</p>
                                                <p class="text-xs text-muted-foreground">5 minutes ago</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3 p-2 rounded-md hover:bg-accent/10">
                                            <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                                <span class="text-blue-700 text-xs">AS</span>
                                            </div>
                                            <div>
                                                <p class="text-sm"><span class="font-medium">Alice Smith</span> updated vendor information</p>
                                                <p class="text-xs text-muted-foreground">2 hours ago</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Use Cases -->
        <section id="use-cases" class="py-24 bg-accent/5">
            <div class="container mx-auto px-4">
                <div class="mb-16 text-center">
                    <span class="inline-block rounded-full bg-primary/20 px-3 py-1 text-sm font-medium text-primary mb-4">Use Cases</span>
                    <h2 class="mb-4 text-3xl font-bold tracking-tight md:text-4xl">Tailored Solutions for Every Need</h2>
                    <p class="mx-auto max-w-2xl text-lg text-muted-foreground">
                        ValidTrack adapts to your specific compliance and documentation requirements across multiple industries.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div
                        v-for="(useCase, index) in useCases"
                        :key="index"
                        class="rounded-xl border border-border bg-card p-6 shadow-sm transition-all duration-300 hover:shadow-md hover:border-primary/20"
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
                                <path :d="useCase.icon" />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-xl font-semibold">{{ useCase.title }}</h3>
                        <p class="text-muted-foreground">{{ useCase.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section id="stats" class="py-24">
            <div class="container mx-auto px-4">
                <div class="mb-16 text-center">
                    <span class="inline-block rounded-full bg-primary/20 px-3 py-1 text-sm font-medium text-primary mb-4">Results</span>
                    <h2 class="mb-4 text-3xl font-bold tracking-tight md:text-4xl">Real Results from Real Clients</h2>
                    <p class="mx-auto max-w-2xl text-lg text-muted-foreground">
                        See the impact ValidTrack has had for organizations across industries.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-8 md:grid-cols-4">
                    <div 
                        v-for="(stat, index) in stats" 
                        :key="index" 
                        class="flex flex-col items-center text-center"
                    >
                        <div class="text-4xl font-bold text-primary mb-2">{{ stat.value }}</div>
                        <div class="text-muted-foreground">{{ stat.label }}</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section id="testimonials" class="py-24 bg-primary/5">
            <div class="container mx-auto px-4">
                <div class="mb-16 text-center">
                    <span class="inline-block rounded-full bg-primary/20 px-3 py-1 text-sm font-medium text-primary mb-4">Testimonials</span>
                    <h2 class="mb-4 text-3xl font-bold tracking-tight md:text-4xl">What Our Clients Say</h2>
                    <p class="mx-auto max-w-2xl text-lg text-muted-foreground">
                        Organizations across industries rely on ValidTrack to streamline their compliance processes.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                    <div
                        v-for="(testimonial, index) in testimonials"
                        :key="index"
                        class="rounded-xl border border-border bg-card p-6 shadow-sm"
                    >
                        <div class="mb-4 flex items-center gap-4">
                            <img
                                :src="testimonial.avatar"
                                :alt="testimonial.name"
                                class="h-12 w-12 rounded-full object-cover"
                            />
                            <div>
                                <h3 class="font-medium">{{ testimonial.name }}</h3>
                                <p class="text-sm text-muted-foreground">{{ testimonial.role }}, {{ testimonial.company }}</p>
                            </div>
                        </div>
                        <p class="italic text-muted-foreground">"{{ testimonial.quote }}"</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="relative py-24 overflow-hidden">
            <!-- Background gradient -->
            <div class="absolute inset-0 bg-gradient-to-br from-primary/10 to-accent/10"></div>
            <div class="absolute inset-0 bg-grid-pattern opacity-[0.03] dark:opacity-[0.05]" aria-hidden="true"></div>
            
            <div class="container relative z-10 mx-auto px-4 text-center">
                <div class="mx-auto max-w-3xl">
                    <h2 class="mb-4 text-3xl font-bold tracking-tight md:text-4xl">Ready to Transform Your Compliance Process?</h2>
                    <p class="mx-auto mb-8 max-w-2xl text-lg text-muted-foreground">
                        Join organizations that trust ValidTrack to maintain compliance and eliminate the stress of tracking critical documents.
                    </p>

                    <div class="flex flex-col sm:flex-row justify-center gap-4">
                        <Link
                            v-if="!$page.props.auth.user"
                            :href="route('register')"
                            class="rounded-md bg-primary px-8 py-3 text-base font-medium text-primary-foreground transition-colors hover:bg-primary/90 flex items-center justify-center"
                        >
                            Start Your Free Trial
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </Link>
                        <Link
                            v-else
                            :href="route('dashboard')"
                            class="rounded-md bg-primary px-8 py-3 text-base font-medium text-primary-foreground transition-colors hover:bg-primary/90 flex items-center justify-center"
                        >
                            Go to Dashboard
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </Link>
                        <a href="#features" class="rounded-md bg-secondary px-8 py-3 text-base font-medium text-secondary-foreground transition-colors hover:bg-secondary/90">
                            Learn More
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-border py-12">
            <div class="container mx-auto px-4">
                <div class="grid grid-cols-1 gap-8 md:grid-cols-4">
                    <div class="space-y-4">
                        <div class="flex items-center gap-2">
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
                            <span class="text-lg font-bold">ValidTrack</span>
                        </div>
                        <p class="text-sm text-muted-foreground">Comprehensive compliance tracking and document management for organizations of all sizes.</p>
                    </div>
                    
                    <div>
                        <h3 class="mb-3 font-semibold">Features</h3>
                        <ul class="space-y-2 text-sm">
                            <li><a href="#" class="text-muted-foreground hover:text-primary transition-colors">Activity Logs</a></li>
                            <li><a href="#" class="text-muted-foreground hover:text-primary transition-colors">Document Management</a></li>
                            <li><a href="#" class="text-muted-foreground hover:text-primary transition-colors">Smart Reminders</a></li>
                            <li><a href="#" class="text-muted-foreground hover:text-primary transition-colors">Compliance Dashboard</a></li>
                        </ul>
                    </div>
                    
                    <div>
                        <h3 class="mb-3 font-semibold">Resources</h3>
                        <ul class="space-y-2 text-sm">
                            <li><a href="#" class="text-muted-foreground hover:text-primary transition-colors">Documentation</a></li>
                            <li><a href="#" class="text-muted-foreground hover:text-primary transition-colors">API Reference</a></li>
                            <li><a href="#" class="text-muted-foreground hover:text-primary transition-colors">Help Center</a></li>
                            <li><a href="#" class="text-muted-foreground hover:text-primary transition-colors">Blog</a></li>
                        </ul>
                    </div>
                    
                    <div>
                        <h3 class="mb-3 font-semibold">Company</h3>
                        <ul class="space-y-2 text-sm">
                            <li><a href="#" class="text-muted-foreground hover:text-primary transition-colors">About Us</a></li>
                            <li><a href="#" class="text-muted-foreground hover:text-primary transition-colors">Contact</a></li>
                            <li><a href="#" class="text-muted-foreground hover:text-primary transition-colors">Privacy Policy</a></li>
                            <li><a href="#" class="text-muted-foreground hover:text-primary transition-colors">Terms of Service</a></li>
                        </ul>
                    </div>
                </div>
                
                <div class="mt-8 border-t border-border pt-8 flex flex-col md:flex-row justify-between items-center">
                    <div class="text-sm text-muted-foreground">&copy; {{ new Date().getFullYear() }} ValidTrack. All rights reserved.</div>
                    <div class="flex space-x-4 mt-4 md:mt-0">
                        <a href="#" class="text-muted-foreground hover:text-primary transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-twitter" viewBox="0 0 16 16">
                                <path d="M5.026 15c6.038 0 9.341-5.003 9.341-9.334 0-.14 0-.282-.006-.422A6.685 6.685 0 0 0 16 3.542a6.658 6.658 0 0 1-1.889.518 3.301 3.301 0 0 0 1.447-1.817 6.533 6.533 0 0 1-2.087.793A3.286 3.286 0 0 0 7.875 6.03a9.325 9.325 0 0 1-6.767-3.429 3.289 3.289 0 0 0 1.018 4.382A3.323 3.323 0 0 1 .64 6.575v.045a3.288 3.288 0 0 0 2.632 3.218 3.203 3.203 0 0 1-.865.115 3.23 3.23 0 0 1-.614-.057 3.283 3.283 0 0 0 3.067 2.277A6.588 6.588 0 0 1 .78 13.58a6.32 6.32 0 0 1-.78-.045A9.344 9.344 0 0 0 5.026 15z"/>
                            </svg>
                        </a>
                        <a href="#" class="text-muted-foreground hover:text-primary transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-linkedin" viewBox="0 0 16 16">
                                <path d="M0 1.146C0 .513.526 0 1.175 0h13.65C15.474 0 16 .513 16 1.146v13.708c0 .633-.526 1.146-1.175 1.146H1.175C.526 16 0 15.487 0 14.854V1.146zm4.943 12.248V6.169H2.542v7.225h2.401zm-1.2-8.212c.837 0 1.358-.554 1.358-1.248-.015-.709-.52-1.248-1.342-1.248-.822 0-1.359.54-1.359 1.248 0 .694.521 1.248 1.327 1.248h.016zm4.908 8.212V9.359c0-.216.016-.432.08-.586.173-.431.568-.878 1.232-.878.869 0 1.216.662 1.216 1.634v3.865h2.401V9.25c0-2.22-1.184-3.252-2.764-3.252-1.274 0-1.845.7-2.165 1.193v.025h-.016a5.54 5.54 0 0 1 .016-.025V6.169h-2.4c.03.678 0 7.225 0 7.225h2.4z"/>
                            </svg>
                        </a>
                        <a href="#" class="text-muted-foreground hover:text-primary transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-github" viewBox="0 0 16 16">
                                <path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.012 8.012 0 0 0 16 8c0-4.42-3.58-8-8-8z"/>
                            </svg>
                        </a>
                    </div>
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
