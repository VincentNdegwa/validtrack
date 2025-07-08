<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { DataTable } from '@/components/ui/data-table';
import { ActionMenu, ActionMenuButton } from '@/components/ui/dropdown-menu';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Eye, Edit, Trash } from 'lucide-vue-next';
import { type DocumentType } from '@/types/models';

// Define props for parent-driven data loading mode
interface Props {
  documentTypes?: {
    data: DocumentType[];
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
  if (!props.documentTypes) return null;
  return {
    currentPage: props.documentTypes.current_page,
    lastPage: props.documentTypes.last_page,
    perPage: props.documentTypes.per_page,
    total: props.documentTypes.total
  };
});

// Define columns for the DataTable
const columns = computed(() => [
  { 
    key: 'name', 
    label: 'Name',
    class: 'font-medium',
    sortable: true,
    sortDirection: sortField.value === 'name' ? sortDirection.value : null
  },
  { 
    key: 'description', 
    label: 'Description',
    sortable: false
  },
  { 
    key: 'documents_count', 
    label: 'Documents Count',
    sortable: false
  },
  { 
    key: 'is_active', 
    label: 'Status',
    sortable: true,
    sortDirection: sortField.value === 'is_active' ? sortDirection.value : null
  },
  { key: '_actions', label: 'Actions' }
]);

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
  {
    title: 'Document Types',
    href: '/document-types',
  },
];

const handlePageChange = (page: number) => {
  router.get('/document-types', {
    page: page,
    sort: sortField.value,
    direction: sortDirection.value,
    search: search.value,
    per_page: perPage.value
  }, {
    preserveState: true,
    preserveScroll: true,
    only: ['documentTypes']
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
  
  router.get('/document-types', {
    sort: sortField.value,
    direction: sortDirection.value,
    search: search.value,
    per_page: perPage.value
  }, {
    preserveState: true,
    preserveScroll: true,
    only: ['documentTypes']
  });
};

// Handle search
const handleSearch = () => {
  router.get('/document-types', {
    search: search.value,
    sort: sortField.value,
    direction: sortDirection.value,
    per_page: perPage.value
  }, {
    preserveState: true,
    preserveScroll: true,
    only: ['documentTypes']
  });
};

// Handle per page change
const handlePerPageChange = (value: number) => {
  perPage.value = value;
  router.get('/document-types', {
    search: search.value,
    sort: sortField.value,
    direction: sortDirection.value,
    per_page: perPage.value
  }, {
    preserveState: true,
    preserveScroll: true,
    only: ['documentTypes']
  });
};


const handleMenuAction = (action: string, typeId: string | number) => {
  const type = props.documentTypes?.data.find(t => t.id === typeId);
  
  if (!type) return;
  
  switch (action) {
    case 'view':
      router.visit(`/document-types/${type.slug}`);
      break;
    case 'edit':
      router.visit(`/document-types/${type.slug}/edit`);
      break;
    case 'delete':
      if ((type.documents_count || 0) === 0) {
        router.delete(`/document-types/${type.slug}`);
      }
      break;
  }
};
</script>

<template>
  <Head title="Document Types" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-foreground">Document Types</h1>
        <div class="flex gap-2">
          <div class="flex items-center mb-0">
            <div class="relative mr-2">
              <input type="text" v-model="search" placeholder="Search document types..."
                class="px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                @keyup.enter="handleSearch" />
            </div>
          </div>
          <Link href="/document-types/create">
            <Button class="bg-primary text-primary-foreground hover:bg-primary/90">
              Add Document Type
            </Button>
          </Link>
        </div>
      </div>

      <DataTable 
        :data="props.documentTypes?.data || []" 
        :columns="columns" 
        :pagination="pagination || undefined"
        :show-pagination="!!pagination" 
        empty-message="No document types found" 
        @page-change="handlePageChange"
        @sort="handleSort" 
        @per-page-change="handlePerPageChange"
      >
        <template #description="{ item: type }">
          {{ type.description || 'No description' }}
        </template>
        
        <template #is_active="{ item: type }">
          <span
            :class="{
              'px-2 py-1 rounded-full text-xs': true,
              'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400': type.is_active,
              'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400': !type.is_active
            }"
          >
            {{ type.is_active ? 'Active' : 'Inactive' }}
          </span>
        </template>
        
        <template #actions="{ item: type }">
          <ActionMenu :item-id="type.id" @select="handleMenuAction">
            <template #menu-items="{ handleAction }">
              <ActionMenuButton :icon="Eye" text="View" @click="(e) => handleAction('view', e)" />
              <ActionMenuButton :icon="Edit" text="Edit" @click="(e) => handleAction('edit', e)" />
              <ActionMenuButton 
                v-if="(type.documents_count || 0) === 0"
                :icon="Trash" 
                text="Delete" 
                variant="destructive" 
                @click="(e) => handleAction('delete', e)" 
              />
              <span v-else class="px-2 py-1.5 text-xs text-muted-foreground">In use</span>
            </template>
          </ActionMenu>
        </template>
      </DataTable>
    </div>
  </AppLayout>
</template>
