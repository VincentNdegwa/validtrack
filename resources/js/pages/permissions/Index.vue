<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { DataTable } from '@/components/ui/data-table';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type Permission } from '@/types/models';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Can from '@/components/auth/Can.vue';

interface Props {
    permissions?: {
        data: Permission[];
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
const sortField = ref(props.filters?.sort || 'name');
const sortDirection = ref<'asc' | 'desc'>(props.filters?.direction as 'asc' | 'desc' || 'asc');
const perPage = ref(props.filters?.per_page || 10);

// Computed pagination object for parent-driven mode
const pagination = computed(() => {
    if (!props.permissions) return null;
    return {
        currentPage: props.permissions.current_page,
        lastPage: props.permissions.last_page,
        perPage: props.permissions.per_page,
        total: props.permissions.total
    };
});

// Define columns for the DataTable
const columns = computed(() => [
    { 
        key: 'name', 
        label: 'Permission Name',
        class: 'font-medium',
        sortable: true,
        sortDirection: sortField.value === 'name' ? sortDirection.value : null
    },
    { 
        key: 'display_name', 
        label: 'Display Name',
        sortable: true,
        sortDirection: sortField.value === 'display_name' ? sortDirection.value : null
    },
    { 
        key: 'description', 
        label: 'Description',
        sortable: false
    },
    { 
        key: 'scope', 
        label: 'Scope',
        sortable: false
    },
    { 
        key: 'roles_count', 
        label: 'Roles',
        sortable: true,
        sortDirection: sortField.value === 'roles_count' ? sortDirection.value : null
    },
    { key: 'status', label: 'Status' }
]);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Permissions',
        href: '/permissions',
    },
];

const handlePageChange = (page: number) => {
    router.get('/permissions', {
        page: page,
        sort: sortField.value,
        direction: sortDirection.value,
        search: search.value,
        per_page: perPage.value
    }, {
        preserveState: true,
        preserveScroll: true,
        only: ['permissions']
    });
};

// Handle sort change
const handleSort = (field: string) => {
    if (field === sortField.value) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = field;
        sortDirection.value = 'asc';
    }
    
    router.get('/permissions', {
        sort: sortField.value,
        direction: sortDirection.value,
        search: search.value,
        per_page: perPage.value
    }, {
        preserveState: true,
        preserveScroll: true,
        only: ['permissions']
    });
};

// Handle search
const handleSearch = () => {
    router.get('/permissions', {
        search: search.value,
        sort: sortField.value,
        direction: sortDirection.value,
        per_page: perPage.value
    }, {
        preserveState: true,
        preserveScroll: true,
        only: ['permissions']
    });
};

// Handle per page change
const handlePerPageChange = (value: number) => {
    perPage.value = value;
    router.get('/permissions', {
        search: search.value,
        sort: sortField.value,
        direction: sortDirection.value,
        per_page: perPage.value
    }, {
        preserveState: true,
        preserveScroll: true,
        only: ['permissions']
    });
};
</script>

<template>
    <Head title="Permissions" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Permissions</h1>
                    <p class="text-muted-foreground">Available permissions for assignment to roles</p>
                </div>
                <div class="flex gap-2">
                    <div class="flex items-center mb-0">
                        <div class="relative mr-2">
                            <input type="text" v-model="search" placeholder="Search permissions..."
                                class="px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                @keyup.enter="handleSearch" />
                        </div>
                    </div>
                    <Can permission="roles-view">
                        <Link href="/roles">
                            <Button variant="outline" class="mr-2">Manage Roles</Button>
                        </Link>
                    </Can>
                </div>
            </div>

            <DataTable 
                :data="props.permissions?.data || []" 
                :columns="columns" 
                :pagination="pagination || undefined"
                :show-pagination="!!pagination" 
                empty-message="No permissions found" 
                @page-change="handlePageChange"
                @sort="handleSort" 
                @per-page-change="handlePerPageChange"
            >
                <template #description="{ item: permission }">
                    <span class="line-clamp-1">{{ permission.description || '-' }}</span>
                </template>
                
                <template #scope="{ item: permission }">
                    <span
                        class="inline-flex items-center rounded-full px-2 py-1 text-xs"
                        :class="permission.company_id === null ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'"
                    >
                        {{ permission.company_id === null ? 'Global' : 'Company' }}
                    </span>
                </template>
                
                <template #status="{  }">
                    <div class="text-muted-foreground text-xs italic">
                        Managed by system
                    </div>
                </template>
            </DataTable>
        </div>
    </AppLayout>
</template>
