<script setup lang="ts">
import Can from '@/components/auth/Can.vue';
import StatsCard from '@/components/dashboard/StatsCard.vue';
import DocumentCalendar from '@/components/dashboard/DocumentCalendar.vue';
import DocumentStatusChart from '@/components/dashboard/charts/DocumentStatusChart.vue';
import DocumentTypeChart from '@/components/dashboard/charts/DocumentTypeChart.vue';
import SubjectComplianceChart from '@/components/dashboard/charts/SubjectComplianceChart.vue';
import DocumentExpiryTimelineChart from '@/components/dashboard/charts/DocumentExpiryTimelineChart.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { DataTable } from '@/components/ui/data-table';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type ActivityLog, type Company, type Document, type Subject } from '@/types/models';
import { Head, Link } from '@inertiajs/vue3';
import { Activity, AlertCircle, BookType, Calendar, ChevronRight, FileText, Tags, User, Users } from 'lucide-vue-next';
import { ref } from 'vue';

interface Stats {
    subjects: number;
    subjectsTrend?: number;
    compliantPercentage: number;
    complianceTrend?: number;
    documents: number;
    documentsTrend?: number;
    subjectTypes: number;
    subjectTypesTrend?: number;
    documentTypes: number;
    documentTypesTrend?: number;
    users: number;
    usersTrend?: number;
    expiringDocuments: number;
    expiringDocumentsTrend?: number;
}

interface Props {
    stats?: Stats;
    recentSubjects?: Subject[];
    recentDocuments?: Document[];
    recentActivities?: ActivityLog[];
    expiringDocuments?: Document[];
    calendarDocuments?: Document[];
    allDocuments?: Document[];
    allSubjects?: Subject[];
    documentsByStatus?: Record<string, number>;
    company?: Company;
}

const props = defineProps<Props>();

const stats = ref(
    props.stats || {
        subjects: 0,
        subjectsTrend: 0,
        compliantPercentage: 0,
        complianceTrend: 0,
        documents: 0,
        documentsTrend: 0,
        subjectTypes: 0,
        subjectTypesTrend: 0,
        documentTypes: 0,
        documentTypesTrend: 0,
        users: 0,
        usersTrend: 0,
        expiringDocuments: 0,
        expiringDocumentsTrend: 0,
    },
);

const recentSubjects = ref(props.recentSubjects || []);
const recentDocuments = ref(props.recentDocuments || []);
const recentActivities = ref(props.recentActivities || []);
const expiringDocuments = ref(props.expiringDocuments || []);
const calendarDocuments = ref(props.calendarDocuments || []);
const allDocuments = ref(props.allDocuments || []);
const allSubjects = ref(props.allSubjects || []);
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

const formatTargetType = (targetType: string): string => {
    const parts = targetType.split('\\');
    return parts[parts.length - 1];
};

const formatActionType = (actionType: string): string => {
    return actionType.charAt(0).toUpperCase() + actionType.slice(1);
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
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <!-- Primary Stats (First Row) -->
                <StatsCard title="Subjects" :value="stats.subjects" description="Total managed subjects" :icon="User"
                    color="primary" :trend="stats.subjectsTrend" href="/subjects" />

                <StatsCard title="Compliance Rate" :value="stats.compliantPercentage + '%'"
                    description="Overall subject compliance" :icon="User"
                    :color="stats.compliantPercentage >= 80 ? 'success' : stats.compliantPercentage >= 50 ? 'warning' : 'danger'"
                    :trend="stats.complianceTrend" />

                <StatsCard title="Documents" :value="stats.documents" description="Total active documents"
                    :icon="FileText" color="success" :trend="stats.documentsTrend" href="/documents" />

                <StatsCard title="Expiring Soon" :value="stats.expiringDocuments"
                    description="Documents expiring in 30 days" :icon="AlertCircle" color="warning"
                    :trend="stats.expiringDocumentsTrend" />

                <!-- Secondary Stats (Second Row) -->
                <StatsCard title="Subject Types" :value="stats.subjectTypes" description="Subject classifications"
                    :icon="Tags" color="default" :trend="stats.subjectTypesTrend" href="/subject-types" />

                <StatsCard title="Document Types" :value="stats.documentTypes" description="Document classifications"
                    :icon="BookType" color="default" :trend="stats.documentTypesTrend" href="/document-types" />

                <StatsCard title="Team Members" :value="stats.users" description="Active system users" :icon="Users"
                    color="primary" :trend="stats.usersTrend" href="/users" />
            </div>

            <!-- Two column layout for lists -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <!-- Recent Subjects -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <CardTitle>Recent Subjects</CardTitle>
                            <Can permission="subjects-view">
                                <Link href="/subjects">
                                <Button variant="ghost" size="sm" class="text-xs">
                                    View All
                                    <ChevronRight class="ml-1 h-4 w-4" />
                                </Button>
                                </Link>
                            </Can>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-2">
                            <div v-if="recentSubjects.length === 0"
                                class="py-4 text-center text-sm text-muted-foreground">No subjects found</div>
                            <div v-for="subject in recentSubjects" :key="subject.id"
                                class="flex items-center justify-between border-b border-border pb-2 last:border-0 last:pb-0">
                                <div class="space-y-1">
                                    <p class="text-sm leading-none font-medium">
                                        <Can permission="subjects-view">
                                            <Link :href="`/subjects/${subject.slug}`" class="hover:underline">
                                            {{ subject.name }}
                                            </Link>

                                            <template #fallback>
                                                {{ subject.name }}>
                                            </template>
                                        </Can>
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
                            <Can permission="documents-view">
                                <Link href="/documents">
                                <Button variant="ghost" size="sm" class="text-xs">
                                    View All
                                    <ChevronRight class="ml-1 h-4 w-4" />
                                </Button>
                                </Link>
                            </Can>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-2">
                            <div v-if="recentDocuments.length === 0"
                                class="py-4 text-center text-sm text-muted-foreground">No documents found</div>
                            <div v-for="document in recentDocuments" :key="document.id"
                                class="flex items-center justify-between border-b border-border pb-2 last:border-0 last:pb-0">
                                <div class="space-y-1">
                                    <p class="text-sm leading-none font-medium">
                                        <Can permission="documents-view">
                                            <Link :href="`/documents/${document.slug}`" class="hover:underline">
                                            {{ document.subject?.name || 'Unknown Subject' }}
                                            </Link>
                                            <template #fallback>
                                                {{ document.subject?.name || 'Unknown Subject' }}
                                            </template>
                                        </Can>
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ document.document_type?.name || 'No type' }}
                                    </p>
                                </div>
                                <div class="flex flex-col items-end">
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-full px-2 py-1 text-xs"
                                            :class="getStatusClass(document.status)">
                                            {{ getStatusLabel(document.status) }}
                                        </span>
                                    </div>
                                    <p class="mt-1 text-xs text-muted-foreground">{{ formatDate(document.created_at) }}
                                    </p>
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
                            <div v-if="expiringDocuments.length === 0"
                                class="py-4 text-center text-sm text-muted-foreground">
                                No documents expiring soon
                            </div>
                            <div v-for="document in expiringDocuments" :key="document.id"
                                class="flex items-center justify-between border-b border-border pb-2 last:border-0 last:pb-0">
                                <div class="space-y-1">
                                    <p class="text-sm leading-none font-medium">
                                        <Can permission="documents-view">
                                            <Link :href="`/documents/${document.slug}`" class="hover:underline">
                                            {{ document.subject?.name || 'Unknown Subject' }}
                                            </Link>
                                            <template #fallback>
                                                {{ document.subject?.name || 'Unknown Subject' }}
                                            </template>
                                        </Can>
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ document.document_type?.name || 'No type' }}
                                    </p>
                                </div>
                                <div v-if="document.expiry_date" class="flex flex-col items-end">
                                    <div
                                        class="rounded-full bg-amber-100 px-2 py-1 text-xs text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                                        {{ getDaysUntilExpiry(document.expiry_date) }} days left
                                    </div>
                                    <p class="mt-1 text-xs text-muted-foreground">{{ formatDate(document.expiry_date) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Document Calendar -->
            <DocumentCalendar :documents="calendarDocuments" />

            <!-- Charts Section - First Row -->
            <div class="grid gap-4 md:grid-cols-12">
                <div class="md:col-span-4">
                    <DocumentStatusChart :documents="allDocuments" />
                </div>
                <div class="md:col-span-8">
                    <DocumentExpiryTimelineChart :documents="allDocuments" />
                </div>
            </div>

            <!-- Charts Section - Second Row -->
            <div class="grid gap-4 md:grid-cols-12">
                <div class="md:col-span-8">
                    <SubjectComplianceChart :subjects="allSubjects" :documents="allDocuments" />
                </div>
                <div class="md:col-span-4">
                    <DocumentTypeChart :documents="allDocuments" />
                </div>
            </div>


            <!-- Recent Activity -->
            <Can permission="activity-log-view">
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
                            <div v-if="recentActivities.length === 0"
                                class="py-4 text-center text-sm text-muted-foreground">
                                No recent activity found
                            </div>

                            <DataTable v-else :data="recentActivities" :columns="[
                                {
                                    key: 'created_at',
                                    label: 'Date/Time',
                                    sortable: false,
                                },
                                {
                                    key: 'message',
                                    label: 'Activity',
                                    sortable: false,
                                },
                                {
                                    key: 'user',
                                    label: 'User',
                                    sortable: false,
                                },
                                {
                                    key: 'action_type',
                                    label: 'Action',
                                    sortable: false,
                                },
                                {
                                    key: 'target_type',
                                    label: 'Target',
                                    sortable: false,
                                },
                            ]" :show-pagination="false" empty-message="No recent activity found">
                                <template #created_at="{ item: log }">
                                    <div>
                                        {{ formatDate(log.created_at) }}
                                        <div class="text-xs text-muted-foreground">{{ log.friendly_date }}</div>
                                    </div>
                                </template>

                                <template #message="{ item: log }">
                                    <div>{{ log.message }}</div>
                                </template>

                                <template #user="{ item: log }">
                                    <div>{{ log.user?.name || 'Unknown' }}</div>
                                </template>

                                <template #action_type="{ item: log }">
                                    <div>
                                        <span :class="{
                                            'rounded-full px-2 py-1 text-xs': true,
                                            'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400': log.action_type === 'created',
                                            'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400': log.action_type === 'updated',
                                            'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400': log.action_type === 'deleted',
                                        }">
                                            {{ formatActionType(log.action_type) }}
                                        </span>
                                    </div>
                                </template>

                                <template #target_type="{ item: log }">
                                    <div>{{ formatTargetType(log.target_type) }}</div>
                                </template>
                            </DataTable>
                        </div>
                    </CardContent>
                </Card>
            </Can>
        </div>
    </AppLayout>
</template>
