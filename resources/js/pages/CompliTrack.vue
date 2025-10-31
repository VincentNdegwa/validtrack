<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';

export interface BillingPlan {
    id: number;
    name: string;
    slug: string;
    description?: string;
    monthly_price: number;
    yearly_price: number;
    is_active: boolean;
    is_featured: boolean;
    sort_order: number;
    paddle_product_id?: string;
    paddle_monthly_price_id?: string;
    paddle_yearly_price_id?: string;
    created_at: string;
    updated_at: string;
    friendly_features?: string[];
}
const props = defineProps<{
    plans: BillingPlan[];
}>();
// Animation state
const loaded = ref(false);
const scrollY = ref(0);
const showFloatingCTA = ref(false);
ref('dashboard');

// Track scroll position for animations
const handleScroll = () => {
    scrollY.value = window.scrollY;
    showFloatingCTA.value = scrollY.value > 800;
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
    setTimeout(() => {
        loaded.value = true;
    }, 300);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});

// Intersection Observer for revealing elements
const initIntersectionObserver = () => {
    const options = {
        root: null,
        rootMargin: '0px',
        threshold: 0.1,
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, options);

    document.querySelectorAll('.reveal-element').forEach((el) => {
        observer.observe(el);
    });
};

onMounted(() => {
    setTimeout(() => {
        loaded.value = true;
        initIntersectionObserver();
    }, 300);
});

// Mouse parallax effect
const mouse = ref({ x: 0, y: 0 });
const handleMouseMove = (e) => {
    mouse.value = {
        x: e.clientX / window.innerWidth - 0.5,
        y: e.clientY / window.innerHeight - 0.5,
    };
};

onMounted(() => {
    window.addEventListener('mousemove', handleMouseMove);
});

onUnmounted(() => {
    window.removeEventListener('mousemove', handleMouseMove);
});

const features = ref([
    {
        title: 'Universal Document Tracking',
        description: 'Easily manage and track documents for employees, vendors, assets, partners, and more — all in one centralized system',
        icon: 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z',
        image: 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?q=80&w=2070&auto=format&fit=crop',
    },
    {
        title: 'Custom Subject Types',
        description: 'Track any entity in your organization by defining flexible subject types like "Employee", "Vendor", "Vehicle", or "Contractor"',
        icon: 'M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z',
        image: 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?q=80&w=2070&auto=format&fit=crop',
    },
    {
        title: 'Smart Document Status',
        description: 'Automatically flag documents that are expiring soon or already expired, so you never miss a deadline',
        icon: 'M10.125 2.25h-4.5c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125v-9M10.125 2.25h.375a9 9 0 0 1 9 9v.375M10.125 2.25A3.375 3.375 0 0 1 13.5 5.625v1.5c0 .621.504 1.125 1.125 1.125h1.5a3.375 3.375 0 0 1 3.375 3.375M9 15l2.25 2.25L15 12',
        image: 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?q=80&w=2015&auto=format&fit=crop',
    },
    {
        title: 'Automated Expiry Reminders',
        description:
            'Get notified before documents expire via email. Set reminder days (e.g. 30, 14, 7, 1 days) and stay ahead of compliance deadlines',
        icon: 'M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0',
        image: 'https://images.unsplash.com/photo-1622675363311-3e1904dc1885?q=80&w=2070&auto=format&fit=crop',
    },
    {
        title: 'Compliance Dashboard',
        description: 'Visual overview of your compliance health with real-time charts and color-coded insights across all subject types',
        icon: 'M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0 0 20.25 18V6A2.25 2.25 0 0 0 18 3.75H6A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25z',
        image: 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?q=80&w=2070&auto=format&fit=crop',
    },
    {
        title: 'Full Audit Logs',
        description: 'Track who uploaded, edited, or deleted documents with detailed activity logs for full transparency and accountability',
        icon: 'M12 11.5v5m0 0-2-2m2 2 2-2M12 7.5v-.75m0 0c-.414 0-.75-.336-.75-.75s.336-.75.75-.75.75.336.75.75-.336.75-.75.75Z',
        image: 'https://images.unsplash.com/photo-1507925921958-8a62f3d1a50d?q=80&w=2076&auto=format&fit=crop',
    },
    {
        title: 'Secure Document Storage',
        description: 'Safely upload and store your files with secure access — backed by encrypted file links and permission controls',
        icon: 'M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z',
        image: 'https://images.unsplash.com/photo-1563013544-824ae1b704d3?q=80&w=2070&auto=format&fit=crop',
    },
    {
        title: 'Team Management with Roles',
        description: 'Add team members with roles like Admin, Manager, or Viewer. Control who can view or manage documents and settings',
        icon: 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z',
        image: 'https://images.unsplash.com/photo-1600880292203-757bb62b4baf?q=80&w=2070&auto=format&fit=crop',
    },
    {
        title: 'Reports & Exports',
        description: 'Generate downloadable reports filtered by subject type, document status, expiry dates, and more. Export as PDF or Excel',
        icon: 'M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3',
        image: 'https://images.unsplash.com/photo-1543286386-713bdd548da4?q=80&w=2070&auto=format&fit=crop',
    },
    {
        title: 'Real-Time Activity Tracking',
        description: 'See document activity as it happens. Know which documents were added or updated, and by whom',
        icon: 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        image: 'https://images.unsplash.com/photo-1557804506-669a67965ba0?q=80&w=2074&auto=format&fit=crop',
    },
    {
        title: 'Access Anywhere, Anytime',
        description: "100% cloud-based. Manage your compliance from anywhere — whether you're in the office or on the go",
        icon: 'M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418',
        image: 'https://images.unsplash.com/photo-1499951360447-b19be8fe80f5?q=80&w=2070&auto=format&fit=crop',
    },
]);

const testimonials = ref([
    {
        name: 'Sarah Johnson',
        role: 'Compliance Officer',
        company: 'TechSolutions Inc.',
        quote: "ValidTrack has transformed how we manage vendor certifications. We've reduced compliance gaps by 87% in just three months.",
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
        quote: "Managing employee certifications used to be a nightmare. Now with ValidTrack's reminder system, we've eliminated all expired documents.",
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

                <nav class="hidden items-center space-x-5 md:flex">
                    <a href="#features" class="text-sm font-medium text-foreground/80 transition-colors hover:text-primary">Features</a>
                    <a href="#demo" class="text-sm font-medium text-foreground/80 transition-colors hover:text-primary">Demo</a>
                    <a href="#use-cases" class="text-sm font-medium text-foreground/80 transition-colors hover:text-primary">Use Cases</a>
                    <a href="#pricing" class="text-sm font-medium text-foreground/80 transition-colors hover:text-primary">Pricing</a>
                    <a href="#faq" class="text-sm font-medium text-foreground/80 transition-colors hover:text-primary">FAQ</a>
                    <a href="#testimonials" class="text-sm font-medium text-foreground/80 transition-colors hover:text-primary">Testimonials</a>
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
            <div class="bg-grid-pattern absolute inset-0 opacity-[0.03] dark:opacity-[0.05]" aria-hidden="true"></div>

            <div class="container mx-auto px-4">
                <div class="flex max-w-7xl flex-col items-center gap-12 justify-self-center md:flex-row">
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
                            Track All Your <span class="text-primary">Critical Documents</span> Without the Chaos
                        </h1>

                        <p
                            :class="[
                                'translate-y-4 text-lg text-muted-foreground opacity-0 transition-all delay-200 duration-700 md:text-xl',
                                { 'translate-y-0 opacity-100': loaded },
                            ]"
                        >
                            ValidTrack's comprehensive document management platform combines universal tracking, automated reminders, and robust
                            reporting to keep your organization 100% compliant with zero effort.
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
                                class="flex items-center justify-center rounded-md bg-primary px-6 py-3 text-base font-medium text-primary-foreground transition-colors hover:bg-primary/90"
                            >
                                Start Free Trial
                                <svg xmlns="http://www.w3.org/2000/svg" class="ml-2 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        fill-rule="evenodd"
                                        d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </Link>
                            <Link
                                v-else
                                :href="route('dashboard')"
                                class="flex items-center justify-center rounded-md bg-primary px-6 py-3 text-base font-medium text-primary-foreground transition-colors hover:bg-primary/90"
                            >
                                Go to Dashboard
                                <svg xmlns="http://www.w3.org/2000/svg" class="ml-2 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        fill-rule="evenodd"
                                        d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </Link>
                            <a
                                href="#features"
                                class="flex items-center justify-center rounded-md bg-secondary px-6 py-3 text-base font-medium text-secondary-foreground transition-colors hover:bg-secondary/90"
                            >
                                See Features
                                <svg xmlns="http://www.w3.org/2000/svg" class="ml-2 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        fill-rule="evenodd"
                                        d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </a>
                            <!-- <a href="#demo"
                                class="flex items-center justify-center rounded-md border border-primary bg-transparent px-6 py-3 text-base font-medium text-primary transition-colors hover:bg-primary/5">
                                Watch Demo
                                <svg xmlns="http://www.w3.org/2000/svg" class="ml-2 h-5 w-5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"
                                        clip-rule="evenodd" />
                                </svg>
                            </a> -->
                        </div>
                    </div>

                    <div
                        :class="[
                            'translate-x-4 opacity-0 transition-all delay-300 duration-700 md:absolute md:right-0 md:w-1/2',
                            { 'translate-x-0 opacity-100': loaded },
                        ]"
                    >
                        <div class="relative overflow-hidden rounded-xl border border-border shadow-2xl shadow-primary/10">
                            <div class="absolute inset-0 bg-gradient-to-tr from-primary/10 to-accent/10 mix-blend-overlay"></div>
                            <img
                                src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?q=80&w=2070&auto=format&fit=crop"
                                alt="ValidTrack Dashboard"
                                class="relative z-10 aspect-[16/9] w-full object-cover"
                            />
                            <!-- Activity Log Alert Overlay -->
                            <div
                                class="absolute right-4 bottom-4 z-20 max-w-xs rounded-lg border border-border bg-card/95 p-3 shadow-lg backdrop-blur-sm"
                            >
                                <div class="flex items-start gap-3">
                                    <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-primary/20 text-primary">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke-width="1.5"
                                            stroke="currentColor"
                                            class="h-5 w-5"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"
                                            />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-medium">Insurance Certificate Expiring</h3>
                                        <p class="mt-1 text-xs text-muted-foreground">Vendor "TechSupply Inc." has a document expiring in 14 days.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Key Benefits Overview -->
        <section class="py-16">
            <div class="container mx-auto px-4">
                <div class="grid max-w-6xl grid-cols-1 gap-8 justify-self-center sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Benefit 1 -->
                    <div
                        class="flex flex-col items-center rounded-lg border border-border bg-card p-6 text-center shadow-sm transition-all hover:shadow-md"
                    >
                        <div class="mb-4 rounded-full bg-primary/10 p-3">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="h-8 w-8 text-primary"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"
                                />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-xl font-bold">Track Any Entity</h3>
                        <p class="text-muted-foreground">Employees, vendors, assets, and more — all in one place.</p>
                    </div>

                    <!-- Benefit 2 -->
                    <div
                        class="flex flex-col items-center rounded-lg border border-border bg-card p-6 text-center shadow-sm transition-all hover:shadow-md"
                    >
                        <div class="mb-4 rounded-full bg-primary/10 p-3">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="h-8 w-8 text-primary"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"
                                />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-xl font-bold">Get Expiry Reminders</h3>
                        <p class="text-muted-foreground">Automated email alerts so you never miss a deadline.</p>
                    </div>

                    <!-- Benefit 3 -->
                    <div
                        class="flex flex-col items-center rounded-lg border border-border bg-card p-6 text-center shadow-sm transition-all hover:shadow-md"
                    >
                        <div class="mb-4 rounded-full bg-primary/10 p-3">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="h-8 w-8 text-primary"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0 0 20.25 18V6A2.25 2.25 0 0 0 18 3.75H6A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25z"
                                />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-xl font-bold">See Compliance at a Glance</h3>
                        <p class="text-muted-foreground">Real-time dashboards and smart insights.</p>
                    </div>

                    <!-- Benefit 4 -->
                    <div
                        class="flex flex-col items-center rounded-lg border border-border bg-card p-6 text-center shadow-sm transition-all hover:shadow-md"
                    >
                        <div class="mb-4 rounded-full bg-primary/10 p-3">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="h-8 w-8 text-primary"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"
                                />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-xl font-bold">Built for Teams</h3>
                        <p class="text-muted-foreground">Add users, assign roles, and stay in control.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Problem Statement + Solution -->
        <section class="bg-primary/5 py-16">
            <div class="container mx-auto px-4">
                <div class="mx-auto max-w-3xl text-center">
                    <h2 class="mb-8 text-3xl font-bold tracking-tight md:text-4xl">
                        Managing compliance manually is messy, risky, and time-consuming
                    </h2>

                    <div class="mb-10 grid gap-6 md:grid-cols-3">
                        <div class="rounded-lg border border-border bg-card p-6 shadow-sm">
                            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-red-100 text-red-600">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                    class="h-6 w-6"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m6.75 12H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"
                                    />
                                </svg>
                            </div>
                            <p>Most businesses track compliance in Excel or email.</p>
                        </div>

                        <div class="rounded-lg border border-border bg-card p-6 shadow-sm">
                            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                    class="h-6 w-6"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"
                                    />
                                </svg>
                            </div>
                            <p>That leads to missed expiries, penalties, and confusion.</p>
                        </div>

                        <div class="rounded-lg border border-border bg-card p-6 shadow-sm">
                            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-green-100 text-green-600">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                    class="h-6 w-6"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </div>
                            <p>Our platform solves this by centralizing everything and automating the process.</p>
                        </div>
                    </div>

                    <Link
                        v-if="!$page.props.auth.user"
                        :href="route('register')"
                        class="inline-flex items-center justify-center rounded-md bg-primary px-6 py-2 text-base font-medium text-primary-foreground transition-colors hover:bg-primary/90"
                    >
                        Start tracking with zero setup
                        <svg xmlns="http://www.w3.org/2000/svg" class="ml-2 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path
                                fill-rule="evenodd"
                                d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="relative overflow-hidden py-24">
            <div class="absolute inset-0 bg-gradient-to-br from-primary/5 via-background to-accent/5"></div>
            <div
                class="absolute top-0 left-0 h-full w-full bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] [mask-image:radial-gradient(ellipse_50%_50%_at_50%_50%,#000_70%,transparent_100%)] [background-size:20px_20px] opacity-20"
            ></div>
            <div class="absolute right-0 bottom-0 h-[500px] w-[500px] rounded-full bg-primary/5 blur-[100px]"></div>

            <div class="relative z-10 container mx-auto px-4">
                <div class="mb-16 text-center">
                    <span class="mb-4 inline-flex items-center gap-1 rounded-full bg-primary/20 px-3 py-1 text-sm font-medium text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                            <path
                                d="M10 3.75a2 2 0 1 0-4 0 2 2 0 0 0 4 0ZM17.25 4.5a.75.75 0 0 0 0-1.5h-5.5a.75.75 0 0 0 0 1.5h5.5ZM5 3.75a.75.75 0 0 1-.75.75h-1.5a.75.75 0 0 1 0-1.5h1.5a.75.75 0 0 1 .75.75ZM4.25 17a.75.75 0 0 0 0-1.5h-1.5a.75.75 0 0 0 0 1.5h1.5ZM17.25 17a.75.75 0 0 0 0-1.5h-5.5a.75.75 0 0 0 0 1.5h5.5ZM9 10a.75.75 0 0 1-.75.75h-5.5a.75.75 0 0 1 0-1.5h5.5A.75.75 0 0 1 9 10ZM17.25 10.75a.75.75 0 0 0 0-1.5h-1.5a.75.75 0 0 0 0 1.5h1.5ZM14 10a2 2 0 1 0-4 0 2 2 0 0 0 4 0ZM10 16.25a2 2 0 1 0-4 0 2 2 0 0 0 4 0Z"
                            />
                        </svg>
                        Features
                    </span>
                    <h2 class="mb-4 text-3xl font-bold tracking-tight md:text-4xl lg:text-5xl">
                        Everything You Need For
                        <span class="bg-gradient-to-r from-primary to-accent bg-clip-text text-transparent">Document Compliance</span>
                    </h2>
                    <p class="mx-auto max-w-2xl text-lg text-muted-foreground">
                        A comprehensive solution for all your document tracking and compliance management needs.
                    </p>
                </div>

                <div class="grid max-w-6xl gap-8 justify-self-center md:grid-cols-2 lg:grid-cols-3">
                    <!-- Feature cards with images -->
                    <div
                        v-for="(feature, index) in features"
                        :key="index"
                        class="reveal-element"
                        :class="[
                            'group relative transform overflow-hidden rounded-2xl border border-border/50 bg-card/80 p-6 opacity-0 transition-all duration-700 hover:-translate-y-1 hover:border-primary/50 hover:shadow-xl hover:shadow-primary/10',
                            { 'is-visible opacity-100': loaded },
                        ]"
                        :style="{ transitionDelay: `${400 + (index % 6) * 100}ms` }"
                    >
                        <!-- Animated glow effect -->
                        <div
                            class="absolute transition-all duration-700 ease-in-out"
                            :class="[
                                index % 2 === 0
                                    ? '-top-16 -right-16 group-hover:-top-8 group-hover:-right-8'
                                    : '-bottom-16 -left-16 group-hover:-bottom-8 group-hover:-left-8',
                                'bg- h-40 w-40 rounded-full' + (index % 2 === 0 ? 'primary' : 'accent') + '/10 blur-3xl',
                            ]"
                        ></div>

                        <div class="relative z-10">
                            <!-- Feature icon with floating effect -->
                            <div
                                class="mb-6 flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-primary/20 to-accent/20 p-3 shadow-inner transition-all duration-500 ease-in-out group-hover:shadow-lg group-hover:shadow-primary/20 hover:scale-110"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                    class="h-8 w-8 text-primary"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path :d="feature.icon" />
                                </svg>
                            </div>

                            <!-- Feature image with enhanced hover effect -->
                            <div class="relative mb-4 aspect-[16/9] overflow-hidden rounded-lg bg-muted">
                                <!-- Overlay gradient on hover -->
                                <div
                                    class="absolute inset-0 z-10 flex items-end bg-gradient-to-t from-black/70 to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-100"
                                >
                                    <div class="p-4 text-white">
                                        <span class="text-sm font-medium">View details</span>
                                    </div>
                                </div>
                                <img
                                    :src="
                                        feature.image ||
                                        'https://images.unsplash.com/photo-1568992688065-536aad8a12f6?q=80&w=2832&auto=format&fit=crop'
                                    "
                                    :alt="feature.title + ' Screenshot'"
                                    class="h-full w-full object-cover transition-all duration-500 group-hover:scale-110 group-hover:brightness-110"
                                />
                            </div>

                            <!-- Feature title and description -->
                            <h3 class="mb-2 text-xl font-semibold transition-colors duration-300 group-hover:text-primary">
                                {{ feature.title }}
                            </h3>
                            <p class="text-muted-foreground">{{ feature.description }}</p>

                            <!-- Learn more link with enhanced animation -->
                            <div
                                class="relative mt-4 flex items-center gap-2 overflow-hidden text-sm font-medium text-primary transition-colors duration-300 group-hover:text-accent"
                            >
                                <span class="relative">
                                    Learn more
                                    <span
                                        class="absolute bottom-0 left-0 h-0.5 w-0 bg-primary transition-all duration-300 group-hover:w-full group-hover:bg-accent"
                                    ></span>
                                </span>
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                    class="h-4 w-4 transition-all duration-300 group-hover:translate-x-1.5"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Interactive Product Demo Section -->
        <section id="demo" class="relative overflow-hidden py-24">
            <!-- Futuristic background elements -->
            <div class="absolute inset-0 bg-gradient-to-br from-primary/5 via-background to-accent/5"></div>
            <div class="bg-grid-pattern absolute inset-0 opacity-[0.04]"></div>
            <div class="absolute top-0 left-0 h-64 w-full bg-gradient-to-b from-primary/10 to-transparent blur-3xl"></div>

            <div class="relative z-10 container mx-auto px-4">
                <div class="mb-16 text-center">
                    <span class="mb-4 inline-flex items-center gap-1 rounded-full bg-primary/20 px-3 py-1 text-sm font-medium text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                            <path
                                fill-rule="evenodd"
                                d="M1 4.75C1 3.784 1.784 3 2.75 3h14.5c.966 0 1.75.784 1.75 1.75v10.5A1.75 1.75 0 0 1 17.25 17H2.75A1.75 1.75 0 0 1 1 15.25V4.75Z"
                                clip-rule="evenodd"
                            />
                            <path
                                fill-rule="evenodd"
                                d="M3.5 6.25a.75.75 0 0 1 .75-.75h11.5a.75.75 0 0 1 0 1.5H4.25a.75.75 0 0 1-.75-.75Z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        Interactive Demo
                    </span>
                    <h2 class="mb-4 text-3xl font-bold tracking-tight md:text-4xl lg:text-5xl">
                        Experience <span class="bg-gradient-to-r from-primary to-accent bg-clip-text text-transparent">Next-Gen</span>
                        Compliance Management
                    </h2>
                    <p class="mx-auto max-w-2xl text-lg text-muted-foreground">
                        Our intuitive interface turns complex compliance processes into simple, visual workflows. See it in action.
                    </p>
                </div>

                <div class="mx-auto max-w-6xl">
                    <!-- Interactive Demo Tabs -->
                    <div class="mb-8 flex flex-wrap justify-center gap-4">
                        <button
                            class="rounded-full bg-primary px-6 py-2 text-sm font-medium text-primary-foreground transition-all hover:shadow-lg hover:shadow-primary/20"
                        >
                            Dashboard
                        </button>
                        <button
                            class="rounded-full bg-card px-6 py-2 text-sm font-medium text-foreground transition-all hover:bg-primary hover:text-primary-foreground"
                        >
                            Document Manager
                        </button>
                        <button
                            class="rounded-full bg-card px-6 py-2 text-sm font-medium text-foreground transition-all hover:bg-primary hover:text-primary-foreground"
                        >
                            Reports & Analytics
                        </button>
                    </div>

                    <!-- Interactive Dashboard Demo -->
                    <div class="relative rounded-2xl border border-border bg-card/80 p-6 shadow-xl backdrop-blur-sm">
                        <!-- Mock navigation -->
                        <div class="mb-8 flex items-center justify-between">
                            <div class="flex items-center gap-6">
                                <div class="flex items-center gap-2">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-primary to-accent">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            class="h-4 w-4 text-white"
                                        >
                                            <path d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                    </div>
                                    <span class="font-medium">ValidTrack</span>
                                </div>
                                <div class="hidden gap-4 md:flex">
                                    <span class="text-primary">Dashboard</span>
                                    <span class="text-muted-foreground">Documents</span>
                                    <span class="text-muted-foreground">Subjects</span>
                                    <span class="text-muted-foreground">Reports</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="h-8 w-8 rounded-full bg-muted"></div>
                            </div>
                        </div>

                        <!-- Demo content -->
                        <div class="grid gap-6 lg:grid-cols-4">
                            <!-- Stats cards -->
                            <div class="col-span-4 grid gap-4 sm:grid-cols-2 lg:col-span-1 lg:grid-cols-1">
                                <div
                                    class="group rounded-xl border border-border bg-background p-4 transition-all hover:border-primary/50 hover:shadow-md"
                                >
                                    <div class="mb-2 text-sm font-medium text-muted-foreground">Documents</div>
                                    <div class="text-2xl font-bold">126</div>
                                    <div class="mt-2 flex items-center gap-1 text-xs text-green-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3 w-3">
                                            <path
                                                fill-rule="evenodd"
                                                d="M12 7a1 1 0 1 1 0-2h5a1 1 0 0 1 1 1v5a1 1 0 1 1-2 0V8.414l-4.293 4.293a1 1 0 0 1-1.414 0L8 10.414l-4.293 4.293a1 1 0 0 1-1.414-1.414l5-5a1 1 0 0 1 1.414 0L11 10.586l3.293-3.293A1 1 0 0 1 12 7Z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                        <span>12% increase</span>
                                    </div>
                                </div>
                                <div
                                    class="group rounded-xl border border-border bg-background p-4 transition-all hover:border-primary/50 hover:shadow-md"
                                >
                                    <div class="mb-2 text-sm font-medium text-muted-foreground">Expiring Soon</div>
                                    <div class="text-2xl font-bold">8</div>
                                    <div class="mt-2 flex items-center gap-1 text-xs text-amber-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3 w-3">
                                            <path
                                                fill-rule="evenodd"
                                                d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM8.5 7.5a.5.5 0 0 1 1 0v5a.5.5 0 0 1-1 0v-5Z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                        <span>Action required</span>
                                    </div>
                                </div>
                                <div
                                    class="group rounded-xl border border-border bg-background p-4 transition-all hover:border-primary/50 hover:shadow-md"
                                >
                                    <div class="mb-2 text-sm font-medium text-muted-foreground">Compliance Rate</div>
                                    <div class="text-2xl font-bold">94%</div>
                                    <div class="mt-2 flex items-center gap-1 text-xs text-green-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3 w-3">
                                            <path
                                                fill-rule="evenodd"
                                                d="M12 7a1 1 0 1 1 0-2h5a1 1 0 0 1 1 1v5a1 1 0 1 1-2 0V8.414l-4.293 4.293a1 1 0 0 1-1.414 0L8 10.414l-4.293 4.293a1 1 0 0 1-1.414-1.414l5-5a1 1 0 0 1 1.414 0L11 10.586l3.293-3.293A1 1 0 0 1 12 7Z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                        <span>3.2% increase</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Main content -->
                            <div class="col-span-4 lg:col-span-3">
                                <div class="grid gap-6">
                                    <!-- Main graph -->
                                    <div class="rounded-xl border border-border bg-background p-4">
                                        <div class="mb-4 flex items-center justify-between">
                                            <h3 class="text-lg font-medium">Document Status Overview</h3>
                                            <div class="flex gap-2">
                                                <div class="flex items-center gap-1">
                                                    <div class="h-3 w-3 rounded-full bg-primary"></div>
                                                    <span class="text-xs">Valid</span>
                                                </div>
                                                <div class="flex items-center gap-1">
                                                    <div class="h-3 w-3 rounded-full bg-amber-500"></div>
                                                    <span class="text-xs">Expiring</span>
                                                </div>
                                                <div class="flex items-center gap-1">
                                                    <div class="h-3 w-3 rounded-full bg-red-500"></div>
                                                    <span class="text-xs">Expired</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div
                                            class="flex h-64 w-full items-end rounded-lg bg-gradient-to-r from-primary/10 via-transparent to-accent/10 p-4"
                                        >
                                            <!-- Mock chart bars -->
                                            <div class="flex h-full flex-1 items-end justify-around gap-2">
                                                <div class="w-6 rounded-t-md bg-primary" style="height: 60%"></div>
                                                <div class="w-6 rounded-t-md bg-primary" style="height: 75%"></div>
                                                <div class="w-6 rounded-t-md bg-primary" style="height: 85%"></div>
                                                <div class="w-6 rounded-t-md bg-primary" style="height: 65%"></div>
                                                <div class="w-6 rounded-t-md bg-amber-500" style="height: 45%"></div>
                                                <div class="w-6 rounded-t-md bg-primary" style="height: 80%"></div>
                                                <div class="w-6 rounded-t-md bg-primary" style="height: 90%"></div>
                                                <div class="w-6 rounded-t-md bg-primary" style="height: 70%"></div>
                                                <div class="w-6 rounded-t-md bg-amber-500" style="height: 50%"></div>
                                                <div class="w-6 rounded-t-md bg-red-500" style="height: 30%"></div>
                                                <div class="w-6 rounded-t-md bg-primary" style="height: 75%"></div>
                                                <div class="w-6 rounded-t-md bg-primary" style="height: 85%"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Recent activities -->
                                    <div class="rounded-xl border border-border bg-background p-4">
                                        <h3 class="mb-4 text-lg font-medium">Recent Activities</h3>
                                        <div class="space-y-3">
                                            <div class="flex items-center gap-3 rounded-lg p-2 hover:bg-muted/50">
                                                <div
                                                    class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-green-100 text-green-600"
                                                >
                                                    <svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke-width="1.5"
                                                        stroke="currentColor"
                                                        class="h-5 w-5"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                                                        />
                                                    </svg>
                                                </div>
                                                <div class="flex-1">
                                                    <div class="flex items-center justify-between">
                                                        <p class="text-sm font-medium">Insurance certificate uploaded</p>
                                                        <span class="text-xs text-muted-foreground">2 hours ago</span>
                                                    </div>
                                                    <p class="text-xs text-muted-foreground">TechSupply Inc. - Expires July 15, 2026</p>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-3 rounded-lg p-2 hover:bg-muted/50">
                                                <div
                                                    class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-600"
                                                >
                                                    <svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke-width="1.5"
                                                        stroke="currentColor"
                                                        class="h-5 w-5"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"
                                                        />
                                                    </svg>
                                                </div>
                                                <div class="flex-1">
                                                    <div class="flex items-center justify-between">
                                                        <p class="text-sm font-medium">Training certificate expiring</p>
                                                        <span class="text-xs text-muted-foreground">5 hours ago</span>
                                                    </div>
                                                    <p class="text-xs text-muted-foreground">Employee ID 10234 - Expires in 14 days</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Interactive Elements -->
                    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div class="flex justify-center">
                            <div class="flex h-14 w-14 animate-pulse items-center justify-center rounded-full bg-primary/20">
                                <div class="h-10 w-10 rounded-full bg-primary/30"></div>
                            </div>
                        </div>
                        <div class="flex justify-center">
                            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-primary/10">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-6 w-6 text-primary"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V13.5Zm0 2.25h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V18Zm2.498-6.75h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V13.5Zm0 2.25h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V18Zm2.504-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5Zm0 2.25h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V18Zm2.498-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5Z"
                                    />
                                </svg>
                            </div>
                        </div>
                        <div class="flex justify-center">
                            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-accent/10">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-6 w-6 text-accent"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z"
                                    />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Use Cases -->
        <section id="use-cases" class="bg-accent/5 py-24">
            <div class="container mx-auto px-4">
                <div class="mb-16 text-center">
                    <span class="mb-4 inline-block rounded-full bg-primary/20 px-3 py-1 text-sm font-medium text-primary">Use Cases</span>
                    <h2 class="mb-4 text-3xl font-bold tracking-tight md:text-4xl">Tailored Solutions for Every Need</h2>
                    <p class="mx-auto max-w-2xl text-lg text-muted-foreground">
                        ValidTrack adapts to your specific compliance and documentation requirements across multiple industries.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div
                        v-for="(useCase, index) in useCases"
                        :key="index"
                        class="rounded-xl border border-border bg-card p-6 shadow-sm transition-all duration-300 hover:border-primary/20 hover:shadow-md"
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

        <!-- Who It's For Section -->
        <section class="py-24">
            <div class="container mx-auto px-4">
                <div class="mb-16 text-center">
                    <span class="mb-4 inline-block rounded-full bg-primary/20 px-3 py-1 text-sm font-medium text-primary">Target Personas</span>
                    <h2 class="mb-4 text-3xl font-bold tracking-tight md:text-4xl">Perfect for HR, Procurement, Compliance, and Operations Teams</h2>
                    <p class="mx-auto max-w-2xl text-lg text-muted-foreground">
                        ValidTrack adapts to your team's specific document tracking needs across departments and industries.
                    </p>
                </div>

                <div class="mx-auto max-w-5xl">
                    <div class="overflow-hidden rounded-xl border border-border">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-border bg-muted/50">
                                    <th class="p-4 text-left font-semibold">Role</th>
                                    <th class="p-4 text-left font-semibold">How They Use It</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b border-border">
                                    <td class="p-4 font-medium">HR</td>
                                    <td class="p-4">Track employee IDs, contracts, training certifications, and background checks</td>
                                </tr>
                                <tr class="border-b border-border bg-muted/20">
                                    <td class="p-4 font-medium">Procurement</td>
                                    <td class="p-4">Manage vendor tax documents, insurance certificates, and business registrations</td>
                                </tr>
                                <tr class="border-b border-border">
                                    <td class="p-4 font-medium">Asset Manager</td>
                                    <td class="p-4">Monitor inspection certificates, maintenance records, and equipment calibrations</td>
                                </tr>
                                <tr class="bg-muted/20">
                                    <td class="p-4 font-medium">NGOs/Partners</td>
                                    <td class="p-4">Handle partnership agreements, grant documents, and compliance requirements</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section id="stats" class="py-24">
            <div class="container mx-auto px-4">
                <div class="mb-16 text-center">
                    <span class="mb-4 inline-block rounded-full bg-primary/20 px-3 py-1 text-sm font-medium text-primary">Results</span>
                    <h2 class="mb-4 text-3xl font-bold tracking-tight md:text-4xl">Real Results from Real Clients</h2>
                    <p class="mx-auto max-w-2xl text-lg text-muted-foreground">
                        See the impact ValidTrack has had for organizations across industries.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-8 md:grid-cols-4">
                    <div v-for="(stat, index) in stats" :key="index" class="flex flex-col items-center text-center">
                        <div class="mb-2 text-4xl font-bold text-primary">{{ stat.value }}</div>
                        <div class="text-muted-foreground">{{ stat.label }}</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section id="testimonials" class="bg-primary/5 py-24">
            <div class="container mx-auto px-4">
                <div class="mb-16 text-center">
                    <span class="mb-4 inline-block rounded-full bg-primary/20 px-3 py-1 text-sm font-medium text-primary">Testimonials</span>
                    <h2 class="mb-4 text-3xl font-bold tracking-tight md:text-4xl">What Our Clients Say</h2>
                    <p class="mx-auto max-w-2xl text-lg text-muted-foreground">
                        Organizations across industries rely on ValidTrack to streamline their compliance processes.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                    <div v-for="(testimonial, index) in testimonials" :key="index" class="rounded-xl border border-border bg-card p-6 shadow-sm">
                        <div class="mb-4 flex items-center gap-4">
                            <img :src="testimonial.avatar" :alt="testimonial.name" class="h-12 w-12 rounded-full object-cover" />
                            <div>
                                <h3 class="font-medium">{{ testimonial.name }}</h3>
                                <p class="text-sm text-muted-foreground">{{ testimonial.role }}, {{ testimonial.company }}</p>
                            </div>
                        </div>
                        <p class="text-muted-foreground italic">"{{ testimonial.quote }}"</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section id="faq" class="bg-muted/20 py-24">
            <div class="container mx-auto px-4">
                <div class="mb-16 text-center">
                    <span class="mb-4 inline-block rounded-full bg-primary/20 px-3 py-1 text-sm font-medium text-primary">FAQ</span>
                    <h2 class="mb-4 text-3xl font-bold tracking-tight md:text-4xl">Got Questions?</h2>
                    <p class="mx-auto max-w-2xl text-lg text-muted-foreground">Find answers to our most frequently asked questions.</p>
                </div>

                <div class="mx-auto max-w-3xl">
                    <div class="space-y-4">
                        <!-- Question 1 -->
                        <div class="rounded-lg border border-border bg-card">
                            <button class="flex w-full items-center justify-between p-4 text-left font-medium">
                                Can I track documents for employees and vendors?
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </button>
                            <div class="border-t border-border p-4">
                                <p>
                                    Yes! ValidTrack is designed to manage documents for any entity type. You can create custom subject types like
                                    "Employee", "Vendor", "Asset", or any other category specific to your organization's needs.
                                </p>
                            </div>
                        </div>

                        <!-- Question 2 -->
                        <div class="rounded-lg border border-border bg-card">
                            <button class="flex w-full items-center justify-between p-4 text-left font-medium">
                                Can I get reminders before documents expire?
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </button>
                            <div class="border-t border-border p-4">
                                <p>
                                    Absolutely! Our automated notification system allows you to set custom reminder periods (30, 14, 7, 1 days before
                                    expiry) and will send email alerts to designated team members to ensure you never miss a compliance deadline.
                                </p>
                            </div>
                        </div>

                        <!-- Question 3 -->
                        <div class="rounded-lg border border-border bg-card">
                            <button class="flex w-full items-center justify-between p-4 text-left font-medium">
                                How secure is my data?
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </button>
                            <div class="border-t border-border p-4">
                                <p>
                                    ValidTrack employs bank-level security measures including encryption at rest and in transit, secure access
                                    controls, and regular security audits. Your data is stored in secure, compliant data centers, and we never share
                                    your information with third parties.
                                </p>
                            </div>
                        </div>

                        <!-- Question 4 -->
                        <div class="rounded-lg border border-border bg-card">
                            <button class="flex w-full items-center justify-between p-4 text-left font-medium">
                                Can I invite my team?
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </button>
                            <div class="border-t border-border p-4">
                                <p>
                                    Yes, ValidTrack is built for team collaboration. You can add team members with different roles such as Admin,
                                    Manager, or Viewer, and control what each role can access or modify within the system.
                                </p>
                            </div>
                        </div>

                        <!-- Question 5 -->
                        <div class="rounded-lg border border-border bg-card">
                            <button class="flex w-full items-center justify-between p-4 text-left font-medium">
                                Do you offer a free trial?
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </button>
                            <div class="border-t border-border p-4">
                                <p>
                                    Yes! We offer a completely free plan for small teams, and you can upgrade to our paid plans at any time. No credit
                                    card is required to get started, and you can cancel anytime.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing Section -->
        <section id="pricing" class="py-24">
            <div class="container mx-auto px-4">
                <div class="mb-16 text-center">
                    <span class="mb-4 inline-block rounded-full bg-primary/20 px-3 py-1 text-sm font-medium text-primary">Pricing</span>
                    <h2 class="mb-4 text-3xl font-bold tracking-tight md:text-4xl">Simple Pricing That Scales With You</h2>
                    <p class="mx-auto max-w-2xl text-lg text-muted-foreground">
                        Choose the plan that fits your organization's needs, with flexible options for teams of all sizes.
                    </p>
                </div>

                <div class="mx-auto grid max-w-5xl grid-cols-1 gap-8 md:grid-cols-3">
                    <!-- Free Plan -->
                    <div
                        v-for="plan in props.plans"
                        :key="plan.id"
                        class="flex flex-col rounded-xl border border-border bg-card p-6 shadow-sm transition-all hover:shadow-md"
                    >
                        <div class="mb-5">
                            <h3 class="text-2xl font-bold">{{ plan.name }}</h3>
                            <p class="mt-2 text-muted-foreground">{{ plan.description }}</p>
                        </div>

                        <div class="mb-5">
                            <span class="text-3xl font-bold">${{ plan.monthly_price }}</span>
                            <span class="text-muted-foreground">/month</span>
                        </div>
                        <!-- <div class="mb-5">
                            <span class="text-3xl font-bold">${{ plan.yearly_price }}</span>
                            <span class="text-muted-foreground">/year</span>
                        </div> -->

                        <ul class="mb-8 space-y-3">
                            <li v-for="feature in plan.friendly_features" :key="feature" class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-5 w-5 text-primary" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                <span>{{ feature }}</span>
                            </li>
                        </ul>

                        <div class="mt-auto">
                            <Link
                                v-if="!$page.props.auth.user"
                                :href="route('register')"
                                class="inline-flex w-full items-center justify-center rounded-md border border-primary bg-transparent px-5 py-2 text-sm font-medium text-primary transition-colors hover:bg-primary/5"
                            >
                                Start Free
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="relative overflow-hidden py-24">
            <!-- Background gradient -->
            <div class="absolute inset-0 bg-gradient-to-br from-primary/10 to-accent/10"></div>
            <div class="bg-grid-pattern absolute inset-0 opacity-[0.03] dark:opacity-[0.05]" aria-hidden="true"></div>

            <div class="relative z-10 container mx-auto px-4 text-center">
                <div class="mx-auto max-w-3xl">
                    <span class="mb-4 inline-block rounded-full bg-primary/20 px-3 py-1 text-sm font-medium text-primary"
                        >Why Teams Choose ValidTrack</span
                    >
                    <h2 class="mb-4 text-3xl font-bold tracking-tight md:text-4xl">Your Compliance Hub — Simple, Secure, Reliable</h2>
                    <p class="mx-auto mb-8 max-w-2xl text-lg text-muted-foreground">
                        Built for Compliance Teams, HR Managers, and Procurement Officers to streamline document management and ensure regulatory
                        compliance.
                    </p>

                    <div class="flex flex-col justify-center gap-4 sm:flex-row">
                        <Link
                            v-if="!$page.props.auth.user"
                            :href="route('register')"
                            class="flex items-center justify-center rounded-md bg-primary px-8 py-3 text-base font-medium text-primary-foreground transition-colors hover:bg-primary/90"
                        >
                            Start Your Free Trial
                            <svg xmlns="http://www.w3.org/2000/svg" class="ml-2 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path
                                    fill-rule="evenodd"
                                    d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </Link>
                        <Link
                            v-else
                            :href="route('dashboard')"
                            class="flex items-center justify-center rounded-md bg-primary px-8 py-3 text-base font-medium text-primary-foreground transition-colors hover:bg-primary/90"
                        >
                            Go to Dashboard
                            <svg xmlns="http://www.w3.org/2000/svg" class="ml-2 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path
                                    fill-rule="evenodd"
                                    d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </Link>
                        <a
                            href="#features"
                            class="rounded-md bg-secondary px-8 py-3 text-base font-medium text-secondary-foreground transition-colors hover:bg-secondary/90"
                        >
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
                        <p class="text-sm text-muted-foreground">
                            Comprehensive compliance tracking and document management for organizations of all sizes.
                        </p>
                    </div>

                    <div>
                        <h3 class="mb-3 font-semibold">Features</h3>
                        <ul class="space-y-2 text-sm">
                            <li>
                                <a href="#features" class="text-muted-foreground transition-colors hover:text-primary">Universal Document Tracking</a>
                            </li>
                            <li>
                                <a href="#features" class="text-muted-foreground transition-colors hover:text-primary">Automated Expiry Reminders</a>
                            </li>
                            <li><a href="#features" class="text-muted-foreground transition-colors hover:text-primary">Compliance Dashboard</a></li>
                            <li><a href="#features" class="text-muted-foreground transition-colors hover:text-primary">Full Audit Logs</a></li>
                            <li><a href="#features" class="text-muted-foreground transition-colors hover:text-primary">Team Management</a></li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="mb-3 font-semibold">Resources</h3>
                        <ul class="space-y-2 text-sm">
                            <li><a href="#demo" class="text-muted-foreground transition-colors hover:text-primary">Product Demo</a></li>
                            <li><a href="#pricing" class="text-muted-foreground transition-colors hover:text-primary">Pricing</a></li>
                            <li><a href="#faq" class="text-muted-foreground transition-colors hover:text-primary">FAQ</a></li>
                            <li><a href="#use-cases" class="text-muted-foreground transition-colors hover:text-primary">Use Cases</a></li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="mb-3 font-semibold">Legal & Policies</h3>
                        <ul class="space-y-2 text-sm">
                            <li>
                                <a :href="route('legal')" class="text-muted-foreground transition-colors hover:text-primary">Terms & Conditions</a>
                            </li>
                            <li>
                                <a :href="route('legal', { tab: 'privacy' })" class="text-muted-foreground transition-colors hover:text-primary"
                                    >Privacy Policy</a
                                >
                            </li>
                            <li>
                                <a :href="route('legal', { tab: 'refund' })" class="text-muted-foreground transition-colors hover:text-primary"
                                    >Refund Policy</a
                                >
                            </li>
                            <li><a :href="route('security')" class="text-muted-foreground transition-colors hover:text-primary">Security</a></li>
                            <li>
                                <a :href="route('acceptable-use')" class="text-muted-foreground transition-colors hover:text-primary"
                                    >Acceptable Use Policy</a
                                >
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="mt-8 flex flex-col items-center justify-between border-t border-border pt-8 md:flex-row">
                    <div class="text-sm text-muted-foreground">&copy; {{ new Date().getFullYear() }} Tech360 Systems. All rights reserved.</div>
                    <div class="mt-4 flex space-x-4 md:mt-0">
                        <a href="#" class="text-muted-foreground transition-colors hover:text-primary">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="20"
                                height="20"
                                fill="currentColor"
                                class="bi bi-twitter"
                                viewBox="0 0 16 16"
                            >
                                <path
                                    d="M5.026 15c6.038 0 9.341-5.003 9.341-9.334 0-.14 0-.282-.006-.422A6.685 6.685 0 0 0 16 3.542a6.658 6.658 0 0 1-1.889.518 3.301 3.301 0 0 0 1.447-1.817 6.533 6.533 0 0 1-2.087.793A3.286 3.286 0 0 0 7.875 6.03a9.325 9.325 0 0 1-6.767-3.429 3.289 3.289 0 0 0 1.018 4.382A3.323 3.323 0 0 1 .64 6.575v.045a3.288 3.288 0 0 0 2.632 3.218 3.203 3.203 0 0 1-.865.115 3.23 3.23 0 0 1-.614-.057 3.283 3.283 0 0 0 3.067 2.277A6.588 6.588 0 0 1 .78 13.58a6.32 6.32 0 0 1-.78-.045A9.344 9.344 0 0 0 5.026 15z"
                                />
                            </svg>
                        </a>
                        <a href="#" class="text-muted-foreground transition-colors hover:text-primary">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="20"
                                height="20"
                                fill="currentColor"
                                class="bi bi-linkedin"
                                viewBox="0 0 16 16"
                            >
                                <path
                                    d="M0 1.146C0 .513.526 0 1.175 0h13.65C15.474 0 16 .513 16 1.146v13.708c0 .633-.526 1.146-1.175 1.146H1.175C.526 16 0 15.487 0 14.854V1.146zm4.943 12.248V6.169H2.542v7.225h2.401zm-1.2-8.212c.837 0 1.358-.554 1.358-1.248-.015-.709-.52-1.248-1.342-1.248-.822 0-1.359.54-1.359 1.248 0 .694.521 1.248 1.327 1.248h.016zm4.908 8.212V9.359c0-.216.016-.432.08-.586.173-.431.568-.878 1.232-.878.869 0 1.216.662 1.216 1.634v3.865h2.401V9.25c0-2.22-1.184-3.252-2.764-3.252-1.274 0-1.845.7-2.165 1.193v.025h-.016a5.54 5.54 0 0 1 .016-.025V6.169h-2.4c.03.678 0 7.225 0 7.225h2.4z"
                                />
                            </svg>
                        </a>
                        <a href="#" class="text-muted-foreground transition-colors hover:text-primary">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="20"
                                height="20"
                                fill="currentColor"
                                class="bi bi-github"
                                viewBox="0 0 16 16"
                            >
                                <path
                                    d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.012 8.012 0 0 0 16 8c0-4.42-3.58-8-8-8z"
                                />
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
