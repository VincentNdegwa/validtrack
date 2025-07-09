<script setup lang="ts">
import Can from '@/components/auth/Can.vue';
import { Button } from '@/components/ui/button';
import { DataTable } from '@/components/ui/data-table';
import { ActionMenu, ActionMenuButton } from '@/components/ui/dropdown-menu';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type Document } from '@/types/models';
import { Head, Link, router } from '@inertiajs/vue3';
import { Download, Edit, Eye, Trash } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { route } from 'ziggy-js';

// Define props for parent-driven data loading mode
interface Props {
    documents?: {
        data: Document[];
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
    if (!props.documents) return null;
    return {
        currentPage: props.documents.current_page,
        lastPage: props.documents.last_page,
        perPage: props.documents.per_page,
        total: props.documents.total,
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
        key: 'subject',
        label: 'Subject',
        sortable: false,
    },
    {
        key: 'document_type',
        label: 'Type',
        sortable: false,
    },
    {
        key: 'expiry_date',
        label: 'Expiry Date',
        sortable: true,
    },
    {
        key: 'created_at',
        label: 'Date Added',
        sortable: true,
        sortDirection: sortField.value === 'created_at' ? sortDirection.value : null,
    },
    {
        key: 'status',
        label: 'Status',
        sortable: false,
    },
    { key: '_actions', label: 'Actions' },
]);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Documents',
        href: '/documents',
    },
];

const getStatusColor = (status: number) => {
    switch (status) {
        case 1:
            return 'bg-green-500';
        case 2:
            return 'bg-yellow-500';
        case 3:
            return 'bg-red-500';
        default:
            return 'bg-gray-500';
    }
};

const getStatusText = (status: number) => {
    switch (status) {
        case 1:
            return 'Valid';
        case 2:
            return 'Pending';
        case 3:
            return 'Expired';
        default:
            return 'Unknown';
    }
};

const formatDate = (dateString: string | undefined) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString();
};

const handlePageChange = (page: number) => {
    router.get(
        '/documents',
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
            only: ['documents'],
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
        '/documents',
        {
            sort: sortField.value,
            direction: sortDirection.value,
            search: search.value,
            per_page: perPage.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['documents'],
        },
    );
};

// Handle search
const handleSearch = () => {
    router.get(
        '/documents',
        {
            search: search.value,
            sort: sortField.value,
            direction: sortDirection.value,
            per_page: perPage.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['documents'],
        },
    );
};

// Handle per page change
const handlePerPageChange = (value: number) => {
    perPage.value = value;
    router.get(
        '/documents',
        {
            search: search.value,
            sort: sortField.value,
            direction: sortDirection.value,
            per_page: perPage.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['documents'],
        },
    );
};

// Handle menu action selection
const handleMenuAction = (action: string, documentId: string | number) => {
    const document = props.documents?.data.find((d) => d.id === documentId);

    if (!document) return;

    switch (action) {
        case 'view':
            router.visit(`/documents/${document.slug}`);
            break;
        case 'edit':
            router.visit(`/documents/${document.slug}/edit`);
            break;
        case 'download':
            window.open(route('documents.download', document.id), '_blank');
            break;
        case 'delete':
            router.delete(`/documents/${document.slug}`);
            break;
    }
};
</script>

<template>
    <Head title="Documents" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-foreground">Documents</h1>
                <div class="flex gap-2">
                    <div class="mb-0 flex items-center">
                        <div class="relative mr-2">
                            <input
                                type="text"
                                v-model="search"
                                placeholder="Search documents..."
                                class="rounded-md border px-4 py-2 focus:ring-2 focus:ring-primary focus:outline-none"
                                @keyup.enter="handleSearch"
                            />
                        </div>
                    </div>
                    <Can permission="document-types-view">
                        <Link :href="route('document-types.index')">
                            <Button variant="outline" class="mr-2"> Document Types </Button>
                        </Link>
                    </Can>
                    <Can permission="documents-create">
                        <Link :href="route('documents.create')">
                            <Button class="bg-primary text-primary-foreground hover:bg-primary/90"> Upload Document </Button>
                        </Link>
                    </Can>
                </div>
            </div>

            <DataTable
                :data="props.documents?.data || []"
                :columns="columns"
                :pagination="pagination || undefined"
                :show-pagination="!!pagination"
                empty-message="No documents found"
                @page-change="handlePageChange"
                @sort="handleSort"
                @per-page-change="handlePerPageChange"
            >
                <template #name="{ item: document }">
                    <div>
                        <div>{{ document.name }}</div>
                        <div class="text-xs text-muted-foreground" v-if="document.notes">{{ document.notes }}</div>
                    </div>
                </template>

                <template #subject="{ item: document }">
                    <div>{{ document.subject?.name || 'N/A' }}</div>
                </template>

                <template #document_type="{ item: document }">
                    <div>{{ document.document_type?.name || 'N/A' }}</div>
                </template>

                <template #expiry_date="{ item: document }">
                    <div>
                        {{ document.expiry_date ? formatDate(document.expiry_date) : 'N/A' }}
                    </div>
                </template>

                <template #created_at="{ item: document }">
                    <div>{{ formatDate(document.created_at) }}</div>
                </template>

                <template #status="{ item: document }">
                    <span class="inline-flex items-center">
                        <span :class="[getStatusColor(document.status), 'mr-2 h-2 w-2 rounded-full']"></span>
                        {{ getStatusText(document.status) }}
                    </span>
                </template>

                <template #actions="{ item: document }">
                    <ActionMenu :item-id="document.id" @select="handleMenuAction">
                        <template #menu-items="{ handleAction }">
                            <Can permission="documents-view">
                                <ActionMenuButton :icon="Eye" text="View" @click="(e) => handleAction('view', e)" />
                            </Can>
                            <Can permission="documents-edit">
                                <ActionMenuButton :icon="Edit" text="Edit" @click="(e) => handleAction('edit', e)" />
                            </Can>
                            <Can permission="documents-view">
                                <ActionMenuButton :icon="Download" text="Download" @click="(e) => handleAction('download', e)" />
                            </Can>
                            <Can permission="documents-delete">
                                <ActionMenuButton :icon="Trash" text="Delete" variant="destructive" @click="(e) => handleAction('delete', e)" />
                            </Can>
                        </template>
                    </ActionMenu>
                </template>
            </DataTable>
        </div>
    </AppLayout>
</template>
