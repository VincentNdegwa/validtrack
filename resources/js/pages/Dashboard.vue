<script setup lang="ts">
import StatsCard from '@/components/dashboard/StatsCard.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type ActivityLog, type Company, type Document, type Subject } from '@/types/models';
import { Head, Link } from '@inertiajs/vue3';
import { Activity, AlertCircle, BookType, Calendar, ChevronRight, Clock, FileText, Tags, User, Users } from 'lucide-vue-next';
import { ref } from 'vue';

interface Stats {
    subjects: number;
    documents: number;
    subjectTypes: number;
    documentTypes: number;
    users: number;
    expiringDocuments: number;
}

interface Props {
    stats?: Stats;
    recentSubjects?: Subject[];
    recentDocuments?: Document[];
    recentActivities?: ActivityLog[];
    expiringDocuments?: Document[];
    company?: Company;
}

const props = defineProps<Props>();

const stats = ref(
    props.stats || {
        subjects: 0,
        documents: 0,
        subjectTypes: 0,
        documentTypes: 0,
        users: 0,
        expiringDocuments: 0,
    },
);

const recentSubjects = ref(props.recentSubjects || []);
const recentDocuments = ref(props.recentDocuments || []);
const recentActivities = ref(props.recentActivities || []);
const expiringDocuments = ref(props.expiringDocuments || []);
const company = ref(props.company);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString();
};

const formatDateTime = (date: string) => {
    return new Date(date).toLocaleString();
};

const getStatusLabel = (status: number) => {
    switch (status) {
        case 0:
            return 'Draft';
        case 1:
            return 'Active';
        case 2:
            return 'Expired';
        case 3:
            return 'Archived';
        default:
            return 'Unknown';
    }
};

const getStatusClass = (status: number) => {
    switch (status) {
        case 0:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400';
        case 1:
            return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
        case 2:
            return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
        case 3:
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400';
    }
};

const getDaysUntilExpiry = (expiryDate: string) => {
    const today = new Date();
    const expiry = new Date(expiryDate);
    const diffTime = expiry.getTime() - today.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays;
};

const getActivityTypeIcon = (actionType: string) => {
    switch (actionType) {
        case 'create':
            return 'plus-circle';
        case 'update':
            return 'edit';
        case 'delete':
            return 'trash';
        default:
            return 'activity';
    }
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- Company Info Section -->
            <div v-if="company" class="mb-4 flex flex-col items-start justify-between sm:flex-row sm:items-center">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">{{ company.name }}</h1>
                    <p class="text-muted-foreground">{{ company.location || 'No location set' }}</p>
                </div>
                <div class="mt-2 sm:mt-0">
                    <span class="text-muted-foreground">{{ formatDate(company.created_at) }}</span>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-3 lg:grid-cols-6">
                <StatsCard title="Subjects" :value="stats.subjects" description="Total subjects" :icon="User" color="primary" href="/subjects" />

                <StatsCard
                    title="Documents"
                    :value="stats.documents"
                    description="Total documents"
                    :icon="FileText"
                    color="success"
                    href="/documents"
                />

                <StatsCard
                    title="Subject Types"
                    :value="stats.subjectTypes"
                    description="Subject classifications"
                    :icon="Tags"
                    color="default"
                    href="/subject-types"
                />

                <StatsCard
                    title="Document Types"
                    :value="stats.documentTypes"
                    description="Document classifications"
                    :icon="BookType"
                    color="default"
                    href="/document-types"
                />

                <StatsCard title="Users" :value="stats.users" description="Team members" :icon="Users" color="primary" />

                <StatsCard
                    title="Expiring Soon"
                    :value="stats.expiringDocuments"
                    description="Expiring in 30 days"
                    :icon="AlertCircle"
                    color="warning"
                />
            </div>

            <!-- Two column layout for lists -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <!-- Recent Subjects -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <CardTitle>Recent Subjects</CardTitle>
                            <Link href="/subjects">
                                <Button variant="ghost" size="sm" class="text-xs">
                                    View All
                                    <ChevronRight class="ml-1 h-4 w-4" />
                                </Button>
                            </Link>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-2">
                            <div v-if="recentSubjects.length === 0" class="py-4 text-center text-sm text-muted-foreground">No subjects found</div>
                            <div
                                v-for="subject in recentSubjects"
                                :key="subject.id"
                                class="flex items-center justify-between border-b border-border pb-2 last:border-0 last:pb-0"
                            >
                                <div class="space-y-1">
                                    <p class="text-sm leading-none font-medium">
                                        <Link :href="`/subjects/${subject.id}`" class="hover:underline">
                                            {{ subject.name }}
                                        </Link>
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ subject.subject_type?.name || 'No type' }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <p class="text-xs text-muted-foreground">{{ formatDate(subject.created_at) }}</p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Recent Documents -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <CardTitle>Recent Documents</CardTitle>
                            <Link href="/documents">
                                <Button variant="ghost" size="sm" class="text-xs">
                                    View All
                                    <ChevronRight class="ml-1 h-4 w-4" />
                                </Button>
                            </Link>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-2">
                            <div v-if="recentDocuments.length === 0" class="py-4 text-center text-sm text-muted-foreground">No documents found</div>
                            <div
                                v-for="document in recentDocuments"
                                :key="document.id"
                                class="flex items-center justify-between border-b border-border pb-2 last:border-0 last:pb-0"
                            >
                                <div class="space-y-1">
                                    <p class="text-sm leading-none font-medium">
                                        <Link :href="`/documents/${document.slug}`" class="hover:underline">
                                            {{ document.subject?.name || 'Unknown Subject' }}
                                        </Link>
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ document.document_type?.name || 'No type' }}
                                    </p>
                                </div>
                                <div class="flex flex-col items-end">
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-full px-2 py-1 text-xs" :class="getStatusClass(document.status)">
                                            {{ getStatusLabel(document.status) }}
                                        </span>
                                    </div>
                                    <p class="mt-1 text-xs text-muted-foreground">{{ formatDate(document.created_at) }}</p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Expiring Documents -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <CardTitle>Expiring Documents</CardTitle>
                            <Calendar class="h-4 w-4 text-muted-foreground" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-2">
                            <div v-if="expiringDocuments.length === 0" class="py-4 text-center text-sm text-muted-foreground">
                                No documents expiring soon
                            </div>
                            <div
                                v-for="document in expiringDocuments"
                                :key="document.id"
                                class="flex items-center justify-between border-b border-border pb-2 last:border-0 last:pb-0"
                            >
                                <div class="space-y-1">
                                    <p class="text-sm leading-none font-medium">
                                        <Link :href="`/documents/${document.slug}`" class="hover:underline">
                                            {{ document.subject?.name || 'Unknown Subject' }}
                                        </Link>
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ document.document_type?.name || 'No type' }}
                                    </p>
                                </div>
                                <div v-if="document.expiry_date" class="flex flex-col items-end">
                                    <div class="rounded-full bg-amber-100 px-2 py-1 text-xs text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                                        {{ getDaysUntilExpiry(document.expiry_date) }} days left
                                    </div>
                                    <p class="mt-1 text-xs text-muted-foreground">{{ formatDate(document.expiry_date) }}</p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Recent Activity -->
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <CardTitle>Recent Activity</CardTitle>
                        <Activity class="h-4 w-4 text-muted-foreground" />
                    </div>
                    <CardDescription>Recent actions performed by users in your company</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div v-if="recentActivities.length === 0" class="py-4 text-center text-sm text-muted-foreground">
                            No recent activity found
                        </div>
                        <div v-for="activity in recentActivities" :key="activity.id" class="flex">
                            <div class="mr-4 flex-shrink-0">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10">
                                    <component :is="getActivityTypeIcon(activity.action_type)" class="h-4 w-4 text-primary" />
                                </div>
                            </div>
                            <div class="flex-grow">
                                <p class="text-sm">
                                    <span class="font-medium">{{ activity.user?.name || 'Unknown User' }}</span>
                                    <span> {{ activity.action_type }}</span>
                                    <span> a {{ activity.target_type }}</span>
                                </p>
                                <div class="flex items-center text-xs text-muted-foreground">
                                    <Clock class="mr-1 h-3 w-3" />
                                    <span>{{ formatDateTime(activity.created_at) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
