<script setup lang="ts">
import Can from '@/components/auth/Can.vue';
import { Button } from '@/components/ui/button';
import { DataTable } from '@/components/ui/data-table';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { ActionMenu, ActionMenuButton } from '@/components/ui/dropdown-menu';
import { StatusBadge } from '@/components/ui/status-badge';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type Role, type User } from '@/types/models';
import { Head, Link, router } from '@inertiajs/vue3';
import { Edit, Eye, Trash } from 'lucide-vue-next';
import { computed, ref } from 'vue';

// Define props for parent-driven data loading mode
interface Props {
    users?: {
        data: User[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    roles?: Role[];
    filters?: {
        sort: string;
        direction: string;
        search: string;
        per_page: number;
    };
}

const props = defineProps<Props>();
ref(props.roles || []);

const search = ref(props.filters?.search || '');
const sortField = ref(props.filters?.sort || 'name');
const sortDirection = ref<'asc' | 'desc'>((props.filters?.direction as 'asc' | 'desc') || 'asc');
const perPage = ref(props.filters?.per_page || 10);

// Computed pagination object for parent-driven mode
const pagination = computed(() => {
    if (!props.users) return null;
    return {
        currentPage: props.users.current_page,
        lastPage: props.users.last_page,
        perPage: props.users.per_page,
        total: props.users.total,
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
        key: 'email',
        label: 'Email',
        sortable: true,
        sortDirection: sortField.value === 'email' ? sortDirection.value : null,
    },
    {
        key: 'roles',
        label: 'Roles',
        sortable: false,
    },
    {
        key: 'status',
        label: 'Status',
        sortable: true,
        sortDirection: sortField.value === 'is_active' ? sortDirection.value : null,
    },
    { key: '_actions', label: 'Actions' },
]);

const showDeleteDialog = ref(false);
const userToDelete = ref<User | null>(null);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Users',
        href: '/users',
    },
];

const getRoleNames = (user: User) => {
    if (!user.roles || user.roles.length === 0) {
        return 'No roles assigned';
    }
    return user.roles.map((role) => role.name).join(', ');
};

const confirmDelete = (user: User) => {
    userToDelete.value = user;
    showDeleteDialog.value = true;
};

const cancelDelete = () => {
    showDeleteDialog.value = false;
    userToDelete.value = null;
};

const deleteUser = () => {
    if (userToDelete.value) {
        router.delete(`/users/${userToDelete.value.slug}`, {
            onSuccess: () => {
                showDeleteDialog.value = false;
                userToDelete.value = null;
            },
        });
    }
};

const handlePageChange = (page: number) => {
    router.get(
        '/users',
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
            only: ['users'],
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
        '/users',
        {
            sort: sortField.value,
            direction: sortDirection.value,
            search: search.value,
            per_page: perPage.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['users'],
        },
    );
};

// Handle search
const handleSearch = () => {
    router.get(
        '/users',
        {
            search: search.value,
            sort: sortField.value,
            direction: sortDirection.value,
            per_page: perPage.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['users'],
        },
    );
};

// Handle per page change
const handlePerPageChange = (value: number) => {
    perPage.value = value;
    router.get(
        '/users',
        {
            search: search.value,
            sort: sortField.value,
            direction: sortDirection.value,
            per_page: perPage.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['users'],
        },
    );
};

// Handle menu action selection
const handleMenuAction = (action: string, userId: string | number) => {
    const user = props.users?.data.find((u) => u.id === userId);

    if (!user) return;

    switch (action) {
        case 'view':
            router.visit(`/users/${user.slug}`);
            break;
        case 'edit':
            router.visit(`/users/${user.slug}/edit`);
            break;
        case 'delete':
            confirmDelete(user);
            break;
    }
};
</script>

<template>
    <Head title="Users" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Users</h1>
                    <p class="text-muted-foreground">Manage users in your organization</p>
                </div>
                <div class="flex gap-2">
                    <div class="relative mr-2">
                        <input
                            type="text"
                            v-model="search"
                            placeholder="Search users..."
                            class="rounded-md border px-4 py-2 focus:ring-2 focus:ring-primary focus:outline-none"
                            @keyup.enter="handleSearch"
                        />
                    </div>

                    <Can permission="roles-view">
                        <Link href="/roles">
                            <Button variant="outline" class="mr-2">Manage Roles</Button>
                        </Link>
                    </Can>
                    <Can permission="roles-view">
                        <Link href="/permissions">
                            <Button variant="outline" class="mr-2">Manage Permissions</Button>
                        </Link>
                    </Can>
                    <Can permission="users-create">
                        <Link href="/users/create">
                            <Button class="bg-primary text-primary-foreground hover:bg-primary/90">Add User</Button>
                        </Link>
                    </Can>
                </div>
            </div>

            <DataTable
                :data="props.users?.data || []"
                :columns="columns"
                :pagination="pagination || undefined"
                :show-pagination="!!pagination"
                empty-message="No users found"
                @page-change="handlePageChange"
                @sort="handleSort"
                @per-page-change="handlePerPageChange"
            >
                <template #roles="{ item: user }">
                    <div>{{ getRoleNames(user) }}</div>
                </template>

                <template #status="{ item: user }">
                    <StatusBadge :active="user.is_active" />
                </template>

                <template #actions="{ item: user }">
                    <ActionMenu :item-id="user.id" @select="handleMenuAction">
                        <template #menu-items="{ handleAction }">
                            <Can permission="users-view">
                                <ActionMenuButton :icon="Eye" text="View" @click="(e) => handleAction('view', e)" />
                            </Can>
                            <Can permission="users-edit">
                                <ActionMenuButton :icon="Edit" text="Edit" @click="(e) => handleAction('edit', e)" />
                            </Can>
                            <Can permission="users-delete">
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
                    <DialogTitle>Delete User</DialogTitle>
                </DialogHeader>
                <div class="py-4">
                    <p>
                        Are you sure you want to delete user <span class="font-semibold">{{ userToDelete?.name }}</span
                        >? This action cannot be undone.
                    </p>
                </div>
                <div class="flex justify-end space-x-2">
                    <Button variant="outline" @click="cancelDelete">Cancel</Button>
                    <Button variant="destructive" @click="deleteUser">Delete</Button>
                </div>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
