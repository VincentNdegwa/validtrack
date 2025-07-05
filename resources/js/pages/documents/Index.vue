<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { type Document } from '@/types/models';
import { route } from 'ziggy-js';

interface Props {
  documents?: Document[];
}

const props = defineProps<Props>();
const documents = ref(props.documents || []);

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
    case 1: return 'bg-green-500';
    case 2: return 'bg-yellow-500';
    case 3: return 'bg-red-500';
    default: return 'bg-gray-500';
  }
};

const getStatusText = (status: number) => {
  switch (status) {
    case 1: return 'Valid';
    case 2: return 'Pending';
    case 3: return 'Expired';
    default: return 'Unknown';
  }
};

const formatDate = (dateString: string | undefined) => {
  if (!dateString) return 'N/A';
  return new Date(dateString).toLocaleDateString();
};

const isExpired = (dateString: string | undefined) => {
  if (!dateString) return false;
  return new Date(dateString) < new Date();
};
</script>

<template>
  <Head title="Documents" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-foreground">Documents</h1>
        <div class="flex gap-2">
          <Link :href="route('document-types.index')">
            <Button variant="outline" class="mr-2">
              Document Types
            </Button>
          </Link>
          <Link :href="route('documents.create')">
            <Button class="bg-primary text-primary-foreground hover:bg-primary/90">
              Upload Document
            </Button>
          </Link>
        </div>
      </div>

      <div class="rounded-xl border border-border bg-card">
        <div class="overflow-x-auto">
          <table class="w-full text-sm text-left">
            <thead class="text-xs uppercase bg-muted/50">
              <tr>
                <th scope="col" class="px-6 py-3">Document</th>
                <th scope="col" class="px-6 py-3">Subject</th>
                <th scope="col" class="px-6 py-3">Type</th>
                <th scope="col" class="px-6 py-3">Issue Date</th>
                <th scope="col" class="px-6 py-3">Expiry Date</th>
                <th scope="col" class="px-6 py-3">Status</th>
                <th scope="col" class="px-6 py-3">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="documents.length === 0">
                <td colspan="7" class="px-6 py-4 text-center">No documents found</td>
              </tr>
              <tr v-for="document in documents" :key="document.id" class="border-t border-border hover:bg-muted/30">
                <td class="px-6 py-4 font-medium">
                  <div>{{ document.document_type?.name || 'Document' }}</div>
                  <div class="text-xs text-muted-foreground" v-if="document.notes">{{ document.notes }}</div>
                </td>
                <td class="px-6 py-4">{{ document.subject?.name || 'N/A' }}</td>
                <td class="px-6 py-4">{{ document.document_type?.name || 'N/A' }}</td>
                <td class="px-6 py-4">{{ formatDate(document.issue_date) }}</td>
                <td class="px-6 py-4" :class="{ 'text-red-500': isExpired(document.expiry_date) }">
                  {{ formatDate(document.expiry_date) }}
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center">
                    <span :class="[getStatusColor(document.status), 'w-2 h-2 mr-2 rounded-full']"></span>
                    {{ getStatusText(document.status) }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="flex space-x-2">
                    <Link :href="`/documents/${document.id}`" class="text-blue-600 hover:underline">View</Link>
                    <Link :href="`/documents/${document.id}/edit`" class="text-amber-600 hover:underline">Edit</Link>
                    <button class="text-red-600 hover:underline">Delete</button>
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
