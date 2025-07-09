<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { DataTable } from '@/components/ui/data-table';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { ActionMenu, ActionMenuButton } from '@/components/ui/dropdown-menu';
import { StatusBadge } from '@/components/ui/status-badge';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type Company } from '@/types/models';
import { Head, Link, router } from '@inertiajs/vue3';
import { Edit, Eye, Trash, UserRound } from 'lucide-vue-next';
import { computed, ref } from 'vue';

// Define props for parent-driven data loading mode
interface Props {
    companies?: {
        data: Company[];
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

// Computed pagination object for parent-driven mode
const pagination = computed(() => {
    if (!props.companies) return null;
    return {
        currentPage: props.companies.current_page,
        lastPage: props.companies.last_page,
        perPage: props.companies.per_page,
        total: props.companies.total,
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
        key: 'users_count',
        label: 'Users',
        sortable: true,
        sortDirection: sortField.value === 'users_count' ? sortDirection.value : null,
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
const companyToDelete = ref<Company | null>(null);
const showSwitchDialog = ref(false);
const companyToSwitch = ref<Company | null>(null);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Companies',
        href: '/companies',
    },
];

const confirmDelete = (company: Company, event?: Event) => {
    event?.preventDefault();
    companyToDelete.value = company;
    showDeleteDialog.value = true;
};

const cancelDelete = () => {
    showDeleteDialog.value = false;
    companyToDelete.value = null;
};

const deleteCompany = () => {
    if (companyToDelete.value) {
        router.delete(`/companies/${companyToDelete.value.id}`, {
            onSuccess: () => {
                showDeleteDialog.value = false;
                companyToDelete.value = null;
            },
        });
    }
};

const confirmSwitch = (company: Company, event?: Event) => {
    event?.preventDefault();
    companyToSwitch.value = company;
    showSwitchDialog.value = true;
};

const cancelSwitch = () => {
    showSwitchDialog.value = false;
    companyToSwitch.value = null;
};

const switchToCompany = () => {
    if (companyToSwitch.value) {
        router.post('/companies/switch', {
            company_id: companyToSwitch.value.id,
        });
    }
};

const handlePageChange = (page: number) => {
    router.get(
        '/companies',
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
            only: ['companies'],
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
        '/companies',
        {
            sort: sortField.value,
            direction: sortDirection.value,
            search: search.value,
            per_page: perPage.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['companies'],
        },
    );
};

// Handle search
const handleSearch = () => {
    router.get(
        '/companies',
        {
            search: search.value,
            sort: sortField.value,
            direction: sortDirection.value,
            per_page: perPage.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['companies'],
        },
    );
};

// Handle per page change
const handlePerPageChange = (value: number) => {
    perPage.value = value;
    router.get(
        '/companies',
        {
            search: search.value,
            sort: sortField.value,
            direction: sortDirection.value,
            per_page: perPage.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['companies'],
        },
    );
};

const handleMenuAction = (action: string, companyId: string | number) => {
    const company = props.companies?.data.find((c) => c.id === companyId);

    if (!company) return;

    switch (action) {
        case 'view':
            router.visit(`/companies/${company.slug}`);
            break;
        case 'edit':
            router.visit(`/companies/${company.slug}/edit`);
            break;
        case 'impersonate':
            confirmSwitch(company);
            break;
        case 'delete':
            confirmDelete(company);
            break;
    }
};
</script>

<template>
    <Head title="Companies" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Companies</h1>
                    <p class="text-muted-foreground">Manage companies in the platform</p>
                </div>
                <div class="flex gap-2">
                    <div class="relative mr-2">
                        <input
                            type="text"
                            v-model="search"
                            placeholder="Search companies..."
                            class="rounded-md border px-4 py-2 focus:ring-2 focus:ring-primary focus:outline-none"
                            @keyup.enter="handleSearch"
                        />
                    </div>

                    <Link href="/companies/create">
                        <Button class="bg-primary text-primary-foreground hover:bg-primary/90">Add Company</Button>
                    </Link>
                </div>
            </div>

            <DataTable
                :data="props.companies?.data || []"
                :columns="columns"
                :pagination="pagination || undefined"
                :show-pagination="!!pagination"
                empty-message="No companies found"
                @page-change="handlePageChange"
                @sort="handleSort"
                @per-page-change="handlePerPageChange"
            >
                <template #status="{ item: company }">
                    <StatusBadge :active="company.is_active" />
                </template>

                <template #actions="{ item: company }">
                    <ActionMenu :item-id="company.id" @select="handleMenuAction">
                        <template #menu-items="{ handleAction }">
                            <ActionMenuButton :icon="Eye" text="View" @click="(e) => handleAction('view', e)" />
                            <ActionMenuButton :icon="Edit" text="Edit" @click="(e) => handleAction('edit', e)" />
                            <ActionMenuButton :icon="UserRound" text="Impersonate" @click="(e) => handleAction('impersonate', e)" />
                            <ActionMenuButton :icon="Trash" text="Delete" variant="destructive" @click="(e) => handleAction('delete', e)" />
                        </template>
                    </ActionMenu>
                </template>
            </DataTable>
        </div>

        <!-- Delete Dialog -->
        <Dialog :open="showDeleteDialog" @update:open="showDeleteDialog = $event">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete Company</DialogTitle>
                </DialogHeader>
                <div class="py-4">
                    <p>
                        Are you sure you want to delete company <span class="font-semibold">{{ companyToDelete?.name }}</span
                        >? This action cannot be undone.
                    </p>
                </div>
                <div class="flex justify-end space-x-2">
                    <Button variant="outline" @click="cancelDelete">Cancel</Button>
                    <Button variant="destructive" @click="deleteCompany">Delete</Button>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Switch Dialog -->
        <Dialog :open="showSwitchDialog" @update:open="showSwitchDialog = $event">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Select Company</DialogTitle>
                </DialogHeader>
                <div class="py-4">
                    <p>
                        Switch to <span class="font-semibold">{{ companyToSwitch?.name }}</span
                        >?
                    </p>
                    <p class="mt-2 text-sm text-muted-foreground">This will show you a list of users in this company that you can impersonate.</p>
                </div>
                <div class="flex justify-end space-x-2">
                    <Button variant="outline" @click="cancelSwitch">Cancel</Button>
                    <Button @click="switchToCompany">Switch</Button>
                </div>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
