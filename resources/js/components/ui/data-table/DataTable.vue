<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { computed, ref, type PropType } from 'vue';

interface Column {
  key: string;
  label: string;
  class?: string;
  sortable?: boolean;
  sortDirection?: 'asc' | 'desc' | null;
  render?: (item: any) => any;
}

interface Pagination {
  currentPage: number;
  lastPage: number;
  perPage: number;
  total: number;
}

const props = defineProps({
  data: {
    type: Array as PropType<any[]>,
    required: true
  },
  columns: {
    type: Array as PropType<Column[]>,
    required: true
  },
  pagination: {
    type: Object as PropType<Pagination>,
    default: null
  },
  showPagination: {
    type: Boolean,
    default: false
  },
  emptyMessage: {
    type: String,
    default: 'No data found'
  },
  actionsSlotName: {
    type: String,
    default: 'actions'
  }
});

const emit = defineEmits<{
  (e: 'page-change', page: number): void;
  (e: 'sort', field: string): void;
  (e: 'per-page-change', perPage: number): void;
}>();

const sortColumn = ref('');
const sortDirection = ref('asc');

const paginatedData = computed(() => {
  return props.data;
});

// Pagination object from props
const tablePagination = computed(() => {
  return props.pagination;
});

// Display columns with sort indicators
const displayColumns = computed(() => {
  return props.columns.map(column => {
    if (!column.sortable) return column;
    
    return {
      ...column,
      sortDirection: column.sortDirection
    };
  });
});

// Change page - emit to parent
const changePage = (page: number) => {
  emit('page-change', page);
};

// Handle sort - emit to parent
const handleSort = (field: string) => {
  emit('sort', field);
};

// Handle per page change - emit to parent
const handlePerPageChange = (value: number) => {
  emit('per-page-change', value);
};

const paginationRange = computed(() => {
  const pagination = tablePagination.value;
  if (!pagination) return [];
  
  const { currentPage, lastPage } = pagination;
  const delta = 1; 
  
  let range: (number | string)[] = [];
  
  range.push(1);
  
  const rangeStart = Math.max(2, currentPage - delta);
  const rangeEnd = Math.min(lastPage - 1, currentPage + delta);
  
  if (rangeStart > 2) {
    range.push('...');
  }
  
  // Add page numbers in the middle
  for (let i = rangeStart; i <= rangeEnd; i++) {
    range.push(i);
  }
  
  // Add ellipsis if needed before last page
  if (rangeEnd < lastPage - 1) {
    range.push('...');
  }
  
  // Always include last page if it's not the same as first page
  if (lastPage > 1) {
    range.push(lastPage);
  }
  
  return range;
});

// Get cell content either from direct property or using a render function
const getCellContent = (item: any, column: Column) => {
  if (column.render) {
    return column.render(item);
  }
  
  // Handle nested properties with dot notation (e.g. "user.name")
  const keys = column.key.split('.');
  let value = item;
  
  for (const key of keys) {
    if (value === null || value === undefined) return '';
    value = value[key];
  }
  
  // Add special formatting for specific types
  if (value === null || value === undefined) return '';
  if (typeof value === 'boolean') return value ? 'Yes' : 'No';
  if (value instanceof Date) return value.toLocaleDateString();
  
  return value;
};

import { useSlots } from 'vue';

// Check if column has a custom slot
const slots = useSlots();
const hasCustomSlot = (column: Column) => {
  return !!column.key && !!slots[column.key];
};

</script>

<template>
  <div class="rounded-xl border border-border bg-card">
    <!-- Custom header slot -->
    <div v-if="$slots.header" class="border-b border-border">
      <slot name="header" />
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left text-sm">
        <thead class="bg-muted/50 text-xs uppercase">
          <tr>
            <th
              v-for="column in displayColumns"
              :key="column.key"
              scope="col"
              class="px-6 py-3"
              :class="[column.class, column.sortable ? 'cursor-pointer select-none' : '']"
              @click="column.sortable && handleSort(column.key)"
            >
              <div class="flex items-center">
                {{ column.label }}
                <template v-if="column.sortable && column.sortDirection">
                  <svg 
                    class="ml-1 h-4 w-4" 
                    :class="column.sortDirection === 'asc' ? '' : 'transform rotate-180'"
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                  </svg>
                </template>
              </div>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="props.data.length === 0">
            <td :colspan="columns.length" class="px-6 py-4 text-center">
              {{ emptyMessage }}
            </td>
          </tr>
          <tr
            v-for="(item, index) in paginatedData"
            :key="index"
            class="border-t border-border hover:bg-muted/30"
          >
            <td
              v-for="column in columns"
              :key="column.key"
              class="px-6 py-4"
              :class="column.class"
            >
              <template v-if="column.key === '_actions'">
                <slot name="actions" :item="item" />
              </template>
              <template v-else-if="$slots[column.key]">
                <slot :name="column.key" :item="item" />
              </template>
              <template v-else>
                {{ getCellContent(item, column) }}
              </template>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div
      v-if="props.showPagination && tablePagination"
      class="flex flex-col md:flex-row md:items-center justify-between border-t border-border bg-card px-6 py-3 gap-4"
    >
      <div class="flex flex-col md:flex-row md:items-center gap-2">
        <p class="text-sm text-muted-foreground">
          Showing {{ (tablePagination.currentPage - 1) * tablePagination.perPage + 1 }} to
          {{ Math.min(tablePagination.currentPage * tablePagination.perPage, tablePagination.total) }}
          of {{ tablePagination.total }} entries
        </p>
        
        <div class="flex items-center">
          <select 
            class="px-2 py-1 border border-border rounded-md text-sm bg-card text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
            :value="tablePagination.perPage"
            @change="handlePerPageChange(parseInt(($event.target as HTMLSelectElement).value))"
          >
            <option :value="5" class="bg-background text-foreground">5</option>
            <option :value="10" class="bg-background text-foreground">10</option>
            <option :value="25" class="bg-background text-foreground">25</option>
            <option :value="50" class="bg-background text-foreground">50</option>
          </select>
          <span class="text-sm text-muted-foreground ml-2">per page</span>
        </div>
      </div>
      <div class="flex items-center space-x-2">
        <Button
          variant="outline"
          size="sm"
          :disabled="tablePagination.currentPage === 1"
          @click="changePage(tablePagination.currentPage - 1)"
        >
          Previous
        </Button>

        <div class="flex items-center space-x-1">
          <Button
            v-for="(page, index) in paginationRange"
            :key="index"
            size="sm"
            :variant="page === tablePagination.currentPage ? 'default' : 'outline'"
            :disabled="page === '...'"
            @click="page !== '...' && changePage(page as number)"
          >
            {{ page }}
          </Button>
        </div>

        <Button
          variant="outline"
          size="sm"
          :disabled="tablePagination.currentPage === tablePagination.lastPage"
          @click="changePage(tablePagination.currentPage + 1)"
        >
          Next
        </Button>
      </div>
    </div>
  </div>
</template>
