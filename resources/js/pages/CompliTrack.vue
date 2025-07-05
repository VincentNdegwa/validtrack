<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

// Animation state
const loaded = ref(false);
const features = ref([
  { 
    title: 'Multi-Tenant Platform', 
    description: 'Secure isolation of company data with role-based access control',
    icon: 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0 0v-8.5'
  },
  { 
    title: 'Smart Document Management', 
    description: 'Track expiry dates, set automated reminders, and maintain compliance',
    icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z'
  },
  { 
    title: 'Real-Time Compliance Dashboard', 
    description: 'Color-coded status indicators for at-a-glance monitoring',
    icon: 'M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0 0 20.25 18V6A2.25 2.25 0 0 0 18 3.75H6A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25z'
  },
  { 
    title: 'Flexible Entity Tracking', 
    description: 'Track any entity type - vendors, employees, assets, partners, and more',
    icon: 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z'
  }
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
    
    <div class="min-h-screen bg-background flex flex-col text-foreground">
        <!-- Navbar -->
        <header class="w-full border-b border-border/40 backdrop-blur-sm bg-background/80 sticky top-0 z-40">
            <div class="container mx-auto px-4 py-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="h-10 w-10 rounded-lg bg-gradient-to-br from-primary to-accent flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" 
                             class="text-white h-6 w-6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-foreground">CompliTrack</span>
                </div>
                
                <nav class="flex items-center space-x-2">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="px-5 py-2 text-sm font-medium rounded-md bg-primary text-primary-foreground hover:bg-primary/90 transition-colors"
                    >
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="px-5 py-2 text-sm font-medium rounded-md bg-secondary text-secondary-foreground hover:bg-secondary/90 transition-colors"
                        >
                            Log in
                        </Link>
                        <Link
                            :href="route('register')"
                            class="px-5 py-2 text-sm font-medium rounded-md bg-primary text-primary-foreground hover:bg-primary/90 transition-colors"
                        >
                            Register
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="pt-20 pb-16 md:pt-24 md:pb-20 relative overflow-hidden">
            <!-- Background pattern -->
            <div class="absolute inset-0 bg-grid-pattern opacity-[0.03] dark:opacity-[0.05]" aria-hidden="true"></div>
            
            <div class="container mx-auto px-4">
                <div class="flex flex-col md:flex-row gap-12 items-center">
                    <div class="md:w-1/2 space-y-6">
                        <div :class="['opacity-0 translate-y-4 transition-all duration-700', {'opacity-100 translate-y-0': loaded}]">
                            <span class="inline-flex items-center rounded-full bg-accent/20 px-3 py-1 text-sm text-accent-foreground font-medium">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-1">
                                    <path d="m9 12 2 2 4-4" />
                                    <path d="M12 3c-1.083 0-2.154.099-3.197.287-1.381.248-2.463 1.354-2.667 2.748a14.318 14.318 0 0 0 0 8.93c.204 1.394 1.286 2.5 2.667 2.748A17.32 17.32 0 0 0 12 18c1.083 0 2.154-.099 3.197-.287 1.381-.248 2.463-1.354 2.667-2.748a14.318 14.318 0 0 0 0-8.93c-.204-1.394-1.286-2.5-2.667-2.748A17.32 17.32 0 0 0 12 3Z" />
                                </svg>
                                Multi-Tenant SaaS
                            </span>
                        </div>
                        
                        <h1 :class="['text-4xl md:text-5xl lg:text-6xl font-bold leading-tight opacity-0 translate-y-4 transition-all duration-700 delay-100', {'opacity-100 translate-y-0': loaded}]">
                            Universal Compliance & Document Tracker
                        </h1>
                        
                        <p :class="['text-lg md:text-xl text-muted-foreground opacity-0 translate-y-4 transition-all duration-700 delay-200', {'opacity-100 translate-y-0': loaded}]">
                            Centralize tracking of compliance documents, set expiry reminders, and generate reports for vendors, employees, assets, and more.
                        </p>
                        
                        <div :class="['flex flex-col sm:flex-row gap-4 opacity-0 translate-y-4 transition-all duration-700 delay-300', {'opacity-100 translate-y-0': loaded}]">
                            <Link
                                v-if="!$page.props.auth.user"
                                :href="route('register')"
                                class="px-6 py-3 text-base font-medium rounded-md bg-primary text-primary-foreground hover:bg-primary/90 transition-colors"
                            >
                                Start Free Trial
                            </Link>
                            <Link
                                v-else
                                :href="route('dashboard')"
                                class="px-6 py-3 text-base font-medium rounded-md bg-primary text-primary-foreground hover:bg-primary/90 transition-colors"
                            >
                                Go to Dashboard
                            </Link>
                            <a href="#features" class="px-6 py-3 text-base font-medium rounded-md bg-secondary text-secondary-foreground hover:bg-secondary/90 transition-colors">
                                Learn More
                            </a>
                        </div>
                    </div>
                    
                    <div :class="['md:w-1/2 opacity-0 translate-x-4 transition-all duration-700 delay-300', {'opacity-100 translate-x-0': loaded}]">
                        <div class="relative rounded-xl overflow-hidden shadow-2xl shadow-primary/10 border border-border">
                            <img 
                                src="https://images.unsplash.com/photo-1575936123452-b67c3203c357?fm=jpg&q=60&w=3000&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mnx8aW1hZ2V8ZW58MHx8MHx8fDA%3D" 
                                alt="CompliTrack Dashboard" 
                                class="w-full aspect-[16/9] object-cover"
                            />
                            <!-- Overlay with gradient -->
                            <div class="absolute inset-0 bg-gradient-to-tr from-primary/40 to-accent/30 mix-blend-overlay"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="py-20 bg-secondary/30">
            <div class="container mx-auto px-4">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold mb-4">Powerful Compliance Management</h2>
                    <p class="text-lg text-muted-foreground max-w-2xl mx-auto">
                        CompliTrack provides everything you need to maintain compliance across your organization.
                    </p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div 
                        v-for="(feature, index) in features" 
                        :key="index"
                        :class="['p-6 rounded-xl bg-card border border-border shadow-sm opacity-0 translate-y-4 transition-all duration-500', 
                                {'opacity-100 translate-y-0': loaded}]"
                        :style="{ transitionDelay: `${400 + (index * 100)}ms` }"
                    >
                        <div class="h-12 w-12 rounded-lg bg-primary/10 flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" 
                                 class="text-primary h-6 w-6" stroke-linecap="round" stroke-linejoin="round">
                                <path :d="feature.icon" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">{{ feature.title }}</h3>
                        <p class="text-muted-foreground">{{ feature.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Core Entities Section -->
        <section class="py-20">
            <div class="container mx-auto px-4">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold mb-4">Core Entities</h2>
                    <p class="text-lg text-muted-foreground max-w-2xl mx-auto">
                        CompliTrack is built around these key entities that form the foundation of our platform.
                    </p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="p-6 rounded-xl bg-card border border-border shadow-sm hover:shadow-md transition-shadow">
                        <h3 class="text-xl font-semibold mb-2 flex items-center">
                            <span class="h-8 w-8 rounded-full bg-primary/20 flex items-center justify-center mr-2 text-primary">C</span>
                            Company
                        </h3>
                        <p class="text-muted-foreground">The tenant (organization) with isolated data and customizable settings</p>
                    </div>
                    
                    <div class="p-6 rounded-xl bg-card border border-border shadow-sm hover:shadow-md transition-shadow">
                        <h3 class="text-xl font-semibold mb-2 flex items-center">
                            <span class="h-8 w-8 rounded-full bg-accent/20 flex items-center justify-center mr-2 text-accent">U</span>
                            User
                        </h3>
                        <p class="text-muted-foreground">Users who belong to a company with role-based permissions</p>
                    </div>
                    
                    <div class="p-6 rounded-xl bg-card border border-border shadow-sm hover:shadow-md transition-shadow">
                        <h3 class="text-xl font-semibold mb-2 flex items-center">
                            <span class="h-8 w-8 rounded-full bg-chart-3/20 flex items-center justify-center mr-2 text-chart-3">S</span>
                            Subject
                        </h3>
                        <p class="text-muted-foreground">A tracked entity (vendor, employee, asset, etc.) with categorization</p>
                    </div>
                    
                    <div class="p-6 rounded-xl bg-card border border-border shadow-sm hover:shadow-md transition-shadow">
                        <h3 class="text-xl font-semibold mb-2 flex items-center">
                            <span class="h-8 w-8 rounded-full bg-chart-4/20 flex items-center justify-center mr-2 text-chart-4">D</span>
                            Document
                        </h3>
                        <p class="text-muted-foreground">Compliance documents linked to subjects with expiry tracking</p>
                    </div>
                    
                    <div class="p-6 rounded-xl bg-card border border-border shadow-sm hover:shadow-md transition-shadow">
                        <h3 class="text-xl font-semibold mb-2 flex items-center">
                            <span class="h-8 w-8 rounded-full bg-chart-1/20 flex items-center justify-center mr-2 text-chart-1">R</span>
                            Reminder
                        </h3>
                        <p class="text-muted-foreground">Scheduled alerts based on document expiry dates</p>
                    </div>
                    
                    <div class="p-6 rounded-xl bg-card border border-border shadow-sm hover:shadow-md transition-shadow">
                        <h3 class="text-xl font-semibold mb-2 flex items-center">
                            <span class="h-8 w-8 rounded-full bg-chart-5/20 flex items-center justify-center mr-2 text-chart-5">S</span>
                            Subscription
                        </h3>
                        <p class="text-muted-foreground">Stripe-based billing with multiple tiers and usage limits</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20 bg-gradient-to-br from-primary/10 to-accent/10">
            <div class="container mx-auto px-4 text-center">
                <h2 class="text-3xl md:text-4xl font-bold mb-4">Ready to Start Tracking Compliance?</h2>
                <p class="text-lg text-muted-foreground max-w-2xl mx-auto mb-8">
                    Join organizations that trust CompliTrack to maintain compliance and avoid costly regulatory issues.
                </p>
                
                <Link
                    v-if="!$page.props.auth.user"
                    :href="route('register')"
                    class="px-8 py-3 text-base font-medium rounded-md bg-primary text-primary-foreground hover:bg-primary/90 transition-colors"
                >
                    Start Your Free Trial
                </Link>
                <Link
                    v-else
                    :href="route('dashboard')"
                    class="px-8 py-3 text-base font-medium rounded-md bg-primary text-primary-foreground hover:bg-primary/90 transition-colors"
                >
                    Go to Dashboard
                </Link>
            </div>
        </section>

        <!-- Footer -->
        <footer class="py-10 border-t border-border">
            <div class="container mx-auto px-4">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="flex items-center gap-2 mb-4 md:mb-0">
                        <div class="h-8 w-8 rounded-lg bg-gradient-to-br from-primary to-accent flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" 
                                 class="text-white h-4 w-4" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                        <span class="text-lg font-bold">CompliTrack</span>
                    </div>
                    <div class="text-sm text-muted-foreground">
                        &copy; {{ new Date().getFullYear() }} CompliTrack. All rights reserved.
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
