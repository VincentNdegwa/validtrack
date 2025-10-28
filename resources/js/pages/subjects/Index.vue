<script setup lang="ts">
import Can from '@/components/auth/Can.vue';
import RequestUploadModal from '@/components/documents/RequestUploadModal.vue';
import { Button } from '@/components/ui/button';
import { DataTable } from '@/components/ui/data-table';
import { ActionMenu, ActionMenuButton } from '@/components/ui/dropdown-menu';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type Subject, type SubjectType } from '@/types/models';
import { Head, Link, router } from '@inertiajs/vue3';
import { Edit, Eye, Import, Plus, Trash, Upload } from 'lucide-vue-next';
import { computed, ref } from 'vue';

// Define props for parent-driven data loading mode
interface Props {
    subjects?: {
        data: Subject[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    subjectTypes?: SubjectType[];
    documentTypes?: DocumentType[];
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

// Upload request modal state
const showUploadRequestModal = ref(false);
const selectedSubject = ref<Subject | null>(null);

// Open upload request modal for a subject
const openUploadRequestModal = (subject: Subject) => {
    selectedSubject.value = subject;
    showUploadRequestModal.value = true;
};

// Computed pagination object for parent-driven mode
const pagination = computed(() => {
    if (!props.subjects) return null;
    return {
        currentPage: props.subjects.current_page,
        lastPage: props.subjects.last_page,
        perPage: props.subjects.per_page,
        total: props.subjects.total,
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
        key: 'subject_type',
        label: 'Type',
        sortable: false,
    },
    {
        key: 'email',
        label: 'Email',
        sortable: true,
        sortDirection: sortField.value === 'email' ? sortDirection.value : null,
    },
    {
        key: 'status',
        label: 'Status',
        sortable: true,
        sortDirection: sortField.value === 'status' ? sortDirection.value : null,
        class: 'text-center',
    },
    {
        key: 'compliance_status',
        label: 'Compliance',
        sortable: false,
        class: 'text-center',
    },
    {
        key: 'phone',
        label: 'Phone',
        sortable: true,
        sortDirection: sortField.value === 'phone' ? sortDirection.value : null,
    },
    { key: '_actions', label: 'Actions' },
]);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Subjects',
        href: '/subjects',
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
            return 'Active';
        case 2:
            return 'Pending';
        case 3:
            return 'Inactive';
        default:
            return 'Unknown';
    }
};

const handlePageChange = (page: number) => {
    router.get(
        '/subjects',
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
            only: ['subjects'],
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
        '/subjects',
        {
            sort: sortField.value,
            direction: sortDirection.value,
            search: search.value,
            per_page: perPage.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['subjects'],
        },
    );
};

// Handle search
const handleSearch = () => {
    router.get(
        '/subjects',
        {
            search: search.value,
            sort: sortField.value,
            direction: sortDirection.value,
            per_page: perPage.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['subjects'],
        },
    );
};

// Handle per page change
const handlePerPageChange = (value: number) => {
    perPage.value = value;
    router.get(
        '/subjects',
        {
            search: search.value,
            sort: sortField.value,
            direction: sortDirection.value,
            per_page: perPage.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['subjects'],
        },
    );
};

// Handle menu action selection
const handleMenuAction = (action: string, subjectId: string | number) => {
    const subject = props.subjects?.data.find((s) => s.id === subjectId);

    if (!subject) return;

    switch (action) {
        case 'view':
            router.visit(`/subjects/${subject.slug}`);
            break;
        case 'edit':
            router.visit(`/subjects/${subject.slug}/edit`);
            break;
        case 'upload':
            openUploadRequestModal(subject);
            break;
        case 'delete':
            router.delete(`/subjects/${subject.slug}`);
            break;
    }
};
</script>

<template>
    <Head title="Subjects" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Subjects</h1>
                </div>
                <div class="flex gap-2">
                    <div class="mb-0 flex items-center">
                        <div class="relative mr-2">
                            <input
                                type="text"
                                v-model="search"
                                placeholder="Search subjects..."
                                class="rounded-md border px-4 py-2 focus:ring-2 focus:ring-primary focus:outline-none"
                                @keyup.enter="handleSearch"
                            />
                        </div>
                    </div>
                    <Can permission="subject-types-view">
                        <Link href="/subject-types">
                            <Button variant="outline" class="mr-2"> Subject Types </Button>
                        </Link>
                    </Can>
                    <Can permission="subjects-create">
                           <Link :href="route('subjects.bulk-import')"> 
                        <Button class="bg-primary text-primary-foreground hover:bg-primary/90" >
                            <Import/>  Bulk Import
                        </Button>
                        </Link>
                        <Link href="/subjects/create">
                            <Button class="bg-primary text-primary-foreground hover:bg-primary/90"> <Plus/> Add </Button>
                        </Link>
                    </Can>
                </div>
            </div>

            <DataTable
                :data="props.subjects?.data || []"
                :columns="columns"
                :pagination="pagination || undefined"
                :show-pagination="!!pagination"
                empty-message="No subjects found"
                @page-change="handlePageChange"
                @sort="handleSort"
                @per-page-change="handlePerPageChange"
            >
                <template #subject_type="{ item: subject }">
                    <div>{{ subject.subject_type?.name || 'N/A' }}</div>
                </template>

                <template #status="{ item: subject }">
                    <span class="inline-flex items-center">
                        <span :class="[getStatusColor(subject.status), 'mr-2 h-2 w-2 rounded-full']"></span>
                        {{ getStatusText(subject.status) }}
                    </span>
                </template>

                <template #compliance_status="{ item: subject }">
                    <span class="inline-flex items-center">
                        <span :class="[subject.compliance_status ? 'bg-green-500' : 'bg-red-500', 'mr-2 h-2 w-2 rounded-full']"></span>
                        {{ subject.compliance_status ? 'Compliant' : 'Non-Compliant' }}
                    </span>
                </template>

                <template #actions="{ item: subject }">
                    <ActionMenu :item-id="subject.id" @select="handleMenuAction">
                        <template #menu-items="{ handleAction }">
                            <Can permission="subjects-view">
                                <ActionMenuButton :icon="Eye" text="View" @click="(e) => handleAction('view', e)" />
                            </Can>
                            <Can permission="subjects-edit">
                                <ActionMenuButton :icon="Edit" text="Edit" @click="(e) => handleAction('edit', e)" />
                            </Can>
                            <Can permission="documents-create">
                                <ActionMenuButton :icon="Upload" text="Request Upload" @click="() => openUploadRequestModal(subject)" />
                            </Can>
                            <Can permission="subjects-delete">
                                <ActionMenuButton :icon="Trash" text="Delete" variant="destructive" @click="(e) => handleAction('delete', e)" />
                            </Can>
                        </template>
                    </ActionMenu>
                </template>
            </DataTable>
        </div>

        <RequestUploadModal
            v-if="selectedSubject"
            :subject="selectedSubject"
            :documentTypes="props.documentTypes"
            :show="showUploadRequestModal"
            @close="showUploadRequestModal = false"
        />
    </AppLayout>
</template>
