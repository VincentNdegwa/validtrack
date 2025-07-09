<script setup lang="ts">
import Can from '@/components/auth/Can.vue';
import { Button } from '@/components/ui/button';
import { DataTable } from '@/components/ui/data-table';
import { ActionMenu, ActionMenuButton } from '@/components/ui/dropdown-menu';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type SubjectType } from '@/types/models';
import { Head, Link, router } from '@inertiajs/vue3';
import { Edit, Eye, Trash } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Props {
    subjectTypes?: {
        data: SubjectType[];
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
    if (!props.subjectTypes) return null;
    return {
        currentPage: props.subjectTypes.current_page,
        lastPage: props.subjectTypes.last_page,
        perPage: props.subjectTypes.per_page,
        total: props.subjectTypes.total,
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
        key: 'subjects_count',
        label: 'Subjects Count',
        sortable: false,
    },
    {
        key: 'created_at',
        label: 'Created At',
        sortable: true,
        sortDirection: sortField.value === 'created_at' ? sortDirection.value : null,
    },
    { key: '_actions', label: 'Actions' },
]);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Subject Types',
        href: '/subject-types',
    },
];

const handlePageChange = (page: number) => {
    router.get(
        '/subject-types',
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
            only: ['subjectTypes'],
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
        '/subject-types',
        {
            sort: sortField.value,
            direction: sortDirection.value,
            search: search.value,
            per_page: perPage.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['subjectTypes'],
        },
    );
};

// Handle search
const handleSearch = () => {
    router.get(
        '/subject-types',
        {
            search: search.value,
            sort: sortField.value,
            direction: sortDirection.value,
            per_page: perPage.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['subjectTypes'],
        },
    );
};

// Handle per page change
const handlePerPageChange = (value: number) => {
    perPage.value = value;
    router.get(
        '/subject-types',
        {
            search: search.value,
            sort: sortField.value,
            direction: sortDirection.value,
            per_page: perPage.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['subjectTypes'],
        },
    );
};

// Handle menu action selection
const handleMenuAction = (action: string, typeId: string | number) => {
    const type = props.subjectTypes?.data.find((t) => t.id === typeId);

    if (!type) return;

    switch (action) {
        case 'view':
            router.visit(`/subject-types/${type.id}`);
            break;
        case 'edit':
            router.visit(`/subject-types/${type.id}/edit`);
            break;
        case 'delete':
            if ((type.subjects_count || 0) === 0) {
                router.delete(`/subject-types/${type.id}`);
            }
            break;
    }
};
</script>

<template>
    <Head title="Subject Types" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-foreground">Subject Types</h1>
                <div class="flex gap-2">
                    <div class="mb-0 flex items-center">
                        <div class="relative mr-2">
                            <input
                                type="text"
                                v-model="search"
                                placeholder="Search subject types..."
                                class="rounded-md border px-4 py-2 focus:ring-2 focus:ring-primary focus:outline-none"
                                @keyup.enter="handleSearch"
                            />
                        </div>
                    </div>
                    <Can permission="subject-types-create">
                        <Link href="/subject-types/create">
                            <Button class="bg-primary text-primary-foreground hover:bg-primary/90"> Add Subject Type </Button>
                        </Link>
                    </Can>
                </div>
            </div>

            <DataTable
                :data="props.subjectTypes?.data || []"
                :columns="columns"
                :pagination="pagination || undefined"
                :show-pagination="!!pagination"
                empty-message="No subject types found"
                @page-change="handlePageChange"
                @sort="handleSort"
                @per-page-change="handlePerPageChange"
            >
                <template #created_at="{ item: type }">
                    {{ new Date(type.created_at).toLocaleDateString() }}
                </template>

                <template #actions="{ item: type }">
                    <ActionMenu :item-id="type.id" @select="handleMenuAction">
                        <template #menu-items="{ handleAction }">
                            <Can permission="subject-types-view">
                                <ActionMenuButton :icon="Eye" text="View" @click="(e) => handleAction('view', e)" />
                            </Can>
                            <Can permission="subject-types-edit">
                                <ActionMenuButton :icon="Edit" text="Edit" @click="(e) => handleAction('edit', e)" />
                            </Can>
                            <Can permission="subject-types-delete">
                                <ActionMenuButton
                                    v-if="(type.subjects_count || 0) === 0"
                                    :icon="Trash"
                                    text="Delete"
                                    variant="destructive"
                                    @click="(e) => handleAction('delete', e)"
                                />
                                <span v-else class="px-2 py-1.5 text-xs text-muted-foreground">In use</span>
                            </Can>
                        </template>
                    </ActionMenu>
                </template>
            </DataTable>
        </div>
    </AppLayout>
</template>
