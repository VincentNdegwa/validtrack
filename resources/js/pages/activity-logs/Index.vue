<script setup lang="ts">
import { DataTable } from '@/components/ui/data-table';
import { ActionMenu, ActionMenuButton } from '@/components/ui/dropdown-menu';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Eye } from 'lucide-vue-next';
import { computed, ref } from 'vue';

// Define props for parent-driven data loading mode
interface ActivityLog {
    id: number;
    user_id: number;
    company_id: number;
    action_type: string;
    target_type: string;
    target_id: number;
    payload: any;
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
    activityLogs?: {
        data: ActivityLog[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters?: {
        sort: string;
        direction: string;
        search: string;
        per_page: number;
    };
}

const props = defineProps<Props>();
const search = ref(props.filters?.search || '');
const sortField = ref(props.filters?.sort || 'created_at');
const sortDirection = ref<'asc' | 'desc'>((props.filters?.direction as 'asc' | 'desc') || 'desc');
const perPage = ref(props.filters?.per_page || 10);

// Computed pagination object for parent-driven mode
const pagination = computed(() => {
    if (!props.activityLogs) return null;
    return {
        currentPage: props.activityLogs.current_page,
        lastPage: props.activityLogs.last_page,
        perPage: props.activityLogs.per_page,
        total: props.activityLogs.total,
    };
});

// Define columns for the DataTable
const columns = computed(() => [
    {
        key: 'created_at',
        label: 'Date/Time',
        sortable: true,
        sortDirection: sortField.value === 'created_at' ? sortDirection.value : null,
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
        sortable: true,
        sortDirection: sortField.value === 'action_type' ? sortDirection.value : null,
    },
    {
        key: 'target_type',
        label: 'Target',
        sortable: true,
        sortDirection: sortField.value === 'target_type' ? sortDirection.value : null,
    },
    { key: '_actions', label: 'Actions' },
]);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Activity Logs',
        href: '/activity-logs',
    },
];

const formatDate = (dateString: string): string => {
    const date = new Date(dateString);
    return date.toLocaleString();
};

const formatTargetType = (targetType: string): string => {
    const parts = targetType.split('\\');
    return parts[parts.length - 1];
};

const formatActionType = (actionType: string): string => {
    return actionType.charAt(0).toUpperCase() + actionType.slice(1);
};

const handlePageChange = (page: number) => {
    router.get(
        '/activity-logs',
        {
            page: page,
            sort: sortField.value,
            direction: sortDirection.value,
            search: search.value,
            per_page: perPage.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['activityLogs'],
        },
    );
};

// Handle sort change
const handleSort = (field: string) => {
    if (field === sortField.value) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = field;
        sortDirection.value = 'asc';
    }

    router.get(
        '/activity-logs',
        {
            sort: sortField.value,
            direction: sortDirection.value,
            search: search.value,
            per_page: perPage.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['activityLogs'],
        },
    );
};

// Handle search
const handleSearch = () => {
    router.get(
        '/activity-logs',
        {
            search: search.value,
            sort: sortField.value,
            direction: sortDirection.value,
            per_page: perPage.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['activityLogs'],
        },
    );
};

// Handle per page change
const handlePerPageChange = (value: number) => {
    perPage.value = value;
    router.get(
        '/activity-logs',
        {
            search: search.value,
            sort: sortField.value,
            direction: sortDirection.value,
            per_page: perPage.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['activityLogs'],
        },
    );
};

// Handle menu action selection
const handleMenuAction = (action: string, logId: string | number) => {
    const activityLog = props.activityLogs?.data.find((l) => l.id === logId);

    if (!activityLog) return;

    if (action === 'view') {
        router.visit(`/activity-logs/${activityLog.slug}`);
    }
};
</script>

<template>
    <Head title="Activity Logs" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Activity Logs</h1>
                    <p class="text-muted-foreground">View system activity and audit trail</p>
                </div>
                <div class="flex gap-2">
                    <div class="mb-0 flex items-center">
                        <div class="relative mr-2">
                            <input
                                type="text"
                                v-model="search"
                                placeholder="Search logs..."
                                class="rounded-md border px-4 py-2 focus:ring-2 focus:ring-primary focus:outline-none"
                                @keyup.enter="handleSearch"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <DataTable
                :data="props.activityLogs?.data || []"
                :columns="columns"
                :pagination="pagination || undefined"
                :show-pagination="!!pagination"
                empty-message="No activity logs found"
                @page-change="handlePageChange"
                @sort="handleSort"
                @per-page-change="handlePerPageChange"
            >
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
                        <span
                            :class="{
                                'rounded-full px-2 py-1 text-xs': true,
                                'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400': log.action_type === 'created',
                                'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400': log.action_type === 'updated',
                                'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400': log.action_type === 'deleted',
                            }"
                        >
                            {{ formatActionType(log.action_type) }}
                        </span>
                    </div>
                </template>

                <template #target_type="{ item: log }">
                    <div>{{ formatTargetType(log.target_type) }}</div>
                </template>

                <template #actions="{ item: log }">
                    <ActionMenu :item-id="log.id" @select="handleMenuAction">
                        <template #menu-items="{ handleAction }">
                            <ActionMenuButton :icon="Eye" text="View Details" @click="(e) => handleAction('view', e)" />
                        </template>
                    </ActionMenu>
                </template>
            </DataTable>
        </div>
    </AppLayout>
</template>
