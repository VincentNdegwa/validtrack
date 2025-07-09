<script setup lang="ts">
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { computed } from 'vue';

interface ActivityLog {
    id: number;
    user_id: number;
    company_id: number;
    action_type: string;
    target_type: string;
    target_id: number;
    payload: any;
    changes?: any;
    created_at: string;
    updated_at: string;
    slug: string;
    message: string;
    friendly_target_name: string;
    friendly_date: string;
    user?: {
        id: number;
        name: string;
        email: string;
    };
}

interface Props {
    activityLog: ActivityLog;
}

const props = defineProps<Props>();

const formattedDateTime = computed(() => {
    const date = new Date(props.activityLog.created_at);
    return date.toLocaleString();
});

const targetType = computed(() => {
    const parts = props.activityLog.target_type.split('\\');
    return parts[parts.length - 1];
});

const actionType = computed(() => {
    return props.activityLog.action_type.charAt(0).toUpperCase() + 
           props.activityLog.action_type.slice(1);
});
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Activity Logs',
        href: '/activity-logs',
    },
    {
        title: `Log #${props.activityLog.id}`,
        href: `/activity-logs/${props.activityLog.id}`,
    },
];

// Handle back button
const goBack = () => {
    router.visit('/activity-logs');
};
</script>

<template>
    <Head title="Activity Log Details" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Activity Log Details</h1>
                    <p class="text-muted-foreground">View details of system activity</p>
                </div>
                <div>
                    <Button variant="outline" @click="goBack">Back to Logs</Button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6">
                <!-- Log Header Info -->
                <div class="rounded-lg border border-border bg-card p-6">
                    <!-- User-friendly message -->
                    <div class="mb-6 text-lg font-medium">
                        {{ activityLog.message }}
                        <div class="mt-1 text-sm text-muted-foreground">{{ activityLog.friendly_date }}</div>
                    </div>

                    <div class="mb-4 flex flex-col gap-1">
                        <div class="flex justify-between">
                            <div>
                                <span class="text-muted-foreground">Log ID:</span>
                                <span class="ml-2 font-medium">{{ activityLog.id }}</span>
                            </div>
                            <div>
                                <span class="text-muted-foreground">Date/Time:</span>
                                <span class="ml-2 font-medium">{{ formattedDateTime }}</span>
                            </div>
                        </div>
                        <div class="flex justify-between">
                            <div>
                                <span class="text-muted-foreground">User:</span>
                                <span class="ml-2 font-medium">{{ activityLog.user?.name || 'Unknown' }}</span>
                            </div>
                            <div>
                                <span class="text-muted-foreground">Action:</span>
                                <span 
                                    class="ml-2 rounded-full px-2 py-0.5 text-xs font-medium"
                                    :class="{
                                        'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400': activityLog.action_type === 'created',
                                        'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400': activityLog.action_type === 'updated',
                                        'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400': activityLog.action_type === 'deleted'
                                    }"
                                >
                                    {{ actionType }}
                                </span>
                            </div>
                        </div>
                        <div>
                            <span class="text-muted-foreground">Target:</span>
                            <span class="ml-2 font-medium">{{ targetType }} #{{ activityLog.target_id }}</span>
                        </div>
                    </div>
                </div>

                <!-- Log Payload -->
                <div class="rounded-lg border border-border bg-card p-6">
                    <h2 class="mb-4 text-xl font-semibold">Details</h2>
                    
                    <!-- Created or Deleted -->
                    <div v-if="activityLog.action_type === 'created' || activityLog.action_type === 'deleted'">
                        <div v-if="activityLog.payload?.attributes" class="space-y-2">
                            <div v-for="(value, key) in activityLog.payload.attributes" :key="key" class="grid grid-cols-3 gap-4">
                                <div class="font-medium text-muted-foreground">{{ key }}</div>
                                <div class="col-span-2">{{ value }}</div>
                            </div>
                        </div>
                        <div v-else class="text-muted-foreground">No additional details available.</div>
                    </div>
                    
                    <!-- Updated -->
                    <div v-else-if="activityLog.action_type === 'updated' && activityLog.changes">
                        <div class="space-y-4">
                            <div v-for="(change, key) in activityLog.changes" :key="key" class="grid grid-cols-3 gap-4 border-b border-border pb-2">
                                <div class="font-medium text-muted-foreground">{{ key }}</div>
                                <div class="line-through text-red-500">
                                    {{ change.from }}
                                </div>
                                <div class="text-green-500">
                                    {{ change.to }}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Fallback -->
                    <div v-else class="text-muted-foreground">No change details available.</div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
