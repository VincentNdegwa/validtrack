<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { type DocumentType } from '@/types/models';

interface Props {
  documentTypes?: DocumentType[];
}

const props = defineProps<Props>();
const documentTypes = ref(props.documentTypes || []);

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
</script>

<template>
  <Head title="Document Types" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-foreground">Document Types</h1>
        <Link href="/document-types/create">
          <Button class="bg-primary text-primary-foreground hover:bg-primary/90">
            Add Document Type
          </Button>
        </Link>
      </div>

      <div class="rounded-xl border border-border bg-card">
        <div class="overflow-x-auto">
          <table class="w-full text-sm text-left">
            <thead class="text-xs uppercase bg-muted/50">
              <tr>
                <th scope="col" class="px-6 py-3">Name</th>
                <th scope="col" class="px-6 py-3">Description</th>
                <th scope="col" class="px-6 py-3">Documents Count</th>
                <th scope="col" class="px-6 py-3">Status</th>
                <th scope="col" class="px-6 py-3">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="documentTypes.length === 0">
                <td colspan="5" class="px-6 py-4 text-center">No document types found</td>
              </tr>
              <tr v-for="type in documentTypes" :key="type.id" class="border-t border-border hover:bg-muted/30">
                <td class="px-6 py-4 font-medium">{{ type.name }}</td>
                <td class="px-6 py-4">{{ type.description || 'No description' }}</td>
                <td class="px-6 py-4">{{ type.documents?.length || 0 }}</td>
                <td class="px-6 py-4">
                  <span
                    :class="{
                      'px-2 py-1 rounded-full text-xs': true,
                      'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400': type.is_active,
                      'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400': !type.is_active
                    }"
                  >
                    {{ type.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="flex space-x-2">
                    <Link :href="`/document-types/${type.slug}`" class="text-blue-600 hover:underline">View</Link>
                    <Link :href="`/document-types/${type.slug}/edit`" class="text-amber-600 hover:underline">Edit</Link>
                    <button 
                      @click.prevent="$inertia.delete(`/document-types/${type.id}`)" 
                      class="text-red-600 hover:underline"
                      v-if="(type.documents?.length || 0) === 0"
                    >
                      Delete
                    </button>
                    <span v-else class="text-muted-foreground text-xs">In use</span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
