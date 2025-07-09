<script setup lang="ts">
import Can from '@/components/auth/Can.vue';
import { Button } from '@/components/ui/button';
import { DataTable } from '@/components/ui/data-table';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { ActionMenu, ActionMenuButton } from '@/components/ui/dropdown-menu';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type Role } from '@/types/models';
import { Head, Link, router } from '@inertiajs/vue3';
import { Edit, Eye, Trash } from 'lucide-vue-next';
import { computed, ref } from 'vue';

// Define props for parent-driven data loading mode
interface Props {
    roles?: {
        data: Role[];
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
const sortDirection = ref<'asc' | 'desc'>((props.filters?.direction as 'asc' | 'desc') || 'asc');
const perPage = ref(props.filters?.per_page || 10);
const showDeleteDialog = ref(false);
const roleToDelete = ref<Role | null>(null);

// Computed pagination object for parent-driven mode
const pagination = computed(() => {
    if (!props.roles) return null;
    return {
        currentPage: props.roles.current_page,
        lastPage: props.roles.last_page,
        perPage: props.roles.per_page,
        total: props.roles.total,
    };
});

// Define columns for the DataTable
const columns = computed(() => [
    {
        key: 'name',
        label: 'Name',
        class: 'font-medium',
        sortable: true,
        sortDirection: sortField.value === 'name' ? sortDirection.value : null,
    },
    {
        key: 'display_name',
        label: 'Display Name',
        sortable: true,
        sortDirection: sortField.value === 'display_name' ? sortDirection.value : null,
    },
    {
        key: 'users_count',
        label: 'Users',
        sortable: true,
        sortDirection: sortField.value === 'users_count' ? sortDirection.value : null,
    },
    {
        key: 'permissions_count',
        label: 'Permissions',
        sortable: true,
        sortDirection: sortField.value === 'permissions_count' ? sortDirection.value : null,
    },
    { key: '_actions', label: 'Actions' },
]);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Roles',
        href: '/roles',
    },
];

const confirmDelete = (role: Role) => {
    roleToDelete.value = role;
    showDeleteDialog.value = true;
};

const handlePageChange = (page: number) => {
    router.get(
        '/roles',
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
            only: ['roles'],
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
        '/roles',
        {
            sort: sortField.value,
            direction: sortDirection.value,
            search: search.value,
            per_page: perPage.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['roles'],
        },
    );
};

// Handle search
const handleSearch = () => {
    router.get(
        '/roles',
        {
            search: search.value,
            sort: sortField.value,
            direction: sortDirection.value,
            per_page: perPage.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['roles'],
        },
    );
};

// Handle per page change
const handlePerPageChange = (value: number) => {
    perPage.value = value;
    router.get(
        '/roles',
        {
            search: search.value,
            sort: sortField.value,
            direction: sortDirection.value,
            per_page: perPage.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['roles'],
        },
    );
};

// Handle menu action selection
const handleMenuAction = (action: string, roleId: string | number) => {
    const role = props.roles?.data.find((r) => r.id === roleId);

    if (!role) return;

    switch (action) {
        case 'view':
            router.visit(`/roles/${role.slug}`);
            break;
        case 'edit':
            router.visit(`/roles/${role.slug}/edit`);
            break;
        case 'delete':
            confirmDelete(role);
            break;
    }
};

const cancelDelete = () => {
    showDeleteDialog.value = false;
    roleToDelete.value = null;
};

const deleteRole = () => {
    if (roleToDelete.value) {
        router.delete(`/roles/${roleToDelete.value.slug}`, {
            onSuccess: () => {
                showDeleteDialog.value = false;
                roleToDelete.value = null;
            },
        });
    }
};
</script>

<template>
    <Head title="Roles" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Roles</h1>
                    <p class="text-muted-foreground">Manage user roles and permissions</p>
                </div>
                <div class="flex gap-2">
                    <div class="mb-0 flex items-center">
                        <div class="relative mr-2">
                            <input
                                type="text"
                                v-model="search"
                                placeholder="Search roles..."
                                class="rounded-md border px-4 py-2 focus:ring-2 focus:ring-primary focus:outline-none"
                                @keyup.enter="handleSearch"
                            />
                        </div>
                    </div>
                    <Can permission="roles-view">
                        <Link href="/permissions">
                            <Button variant="outline" class="mr-2">Manage Permissions</Button>
                        </Link>
                    </Can>
                    <Can permission="roles-create">
                        <Link href="/roles/create">
                            <Button class="bg-primary text-primary-foreground hover:bg-primary/90">Add Role</Button>
                        </Link>
                    </Can>
                </div>
            </div>

            <DataTable
                :data="props.roles?.data || []"
                :columns="columns"
                :pagination="pagination || undefined"
                :show-pagination="!!pagination"
                empty-message="No roles found"
                @page-change="handlePageChange"
                @sort="handleSort"
                @per-page-change="handlePerPageChange"
            >
                <template #actions="{ item: role }">
                    <ActionMenu :item-id="role.id" @select="handleMenuAction">
                        <template #menu-items="{ handleAction }">
                            <Can permission="roles-view">
                                <ActionMenuButton :icon="Eye" text="View" @click="(e) => handleAction('view', e)" />
                            </Can>
                            <Can permission="roles-edit">
                                <ActionMenuButton :icon="Edit" text="Edit" @click="(e) => handleAction('edit', e)" />
                            </Can>
                            <Can permission="roles-delete">
                                <ActionMenuButton :icon="Trash" text="Delete" variant="destructive" @click="(e) => handleAction('delete', e)" />
                            </Can>
                        </template>
                    </ActionMenu>
                </template>
            </DataTable>
        </div>

        <Dialog :open="showDeleteDialog" @update:open="showDeleteDialog = $event">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete Role</DialogTitle>
                </DialogHeader>
                <div class="py-4">
                    <p>
                        Are you sure you want to delete role <span class="font-semibold">{{ roleToDelete?.name }}</span
                        >? This action cannot be undone.
                    </p>
                </div>
                <div class="flex justify-end space-x-2">
                    <Button variant="outline" @click="cancelDelete">Cancel</Button>
                    <Button variant="destructive" @click="deleteRole">Delete</Button>
                </div>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
