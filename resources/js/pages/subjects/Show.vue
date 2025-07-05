<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { type Subject, type Document } from '@/types/models';

interface Props {
  subject: Subject;
  documents?: Document[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
  {
    title: 'Subjects',
    href: '/subjects',
  },
  {
    title: props.subject?.name || 'Subject Detail',
    href: `/subjects/${props.subject?.id}`,
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
    case 1: return 'Active';
    case 2: return 'Pending';
    case 3: return 'Inactive';
    default: return 'Unknown';
  }
};
</script>

<template>
  <Head :title="'Subject: ' + subject.name" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-foreground">{{ subject.name }}</h1>
        <div class="flex space-x-3">
          <Link :href="`/subjects/${subject.id}/edit`">
            <Button variant="outline">Edit</Button>
          </Link>
          <Link href="/subjects">
            <Button variant="ghost">Back to Subjects</Button>
          </Link>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Subject Info Card -->
        <div class="rounded-xl border border-border bg-card p-6 col-span-2">
          <h2 class="text-xl font-semibold mb-4">Subject Information</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4">
            <div>
              <p class="text-sm text-muted-foreground">Name</p>
              <p class="font-medium">{{ subject.name }}</p>
            </div>
            
            <div>
              <p class="text-sm text-muted-foreground">Type</p>
              <p class="font-medium">{{ subject.subject_type?.name || 'N/A' }}</p>
            </div>
            
            <div>
              <p class="text-sm text-muted-foreground">Email</p>
              <p class="font-medium">{{ subject.email || 'N/A' }}</p>
            </div>
            
            <div>
              <p class="text-sm text-muted-foreground">Phone</p>
              <p class="font-medium">{{ subject.phone || 'N/A' }}</p>
            </div>
            
            <div>
              <p class="text-sm text-muted-foreground">Address</p>
              <p class="font-medium">{{ subject.address || 'N/A' }}</p>
            </div>
            
            <div>
              <p class="text-sm text-muted-foreground">Category</p>
              <p class="font-medium">{{ subject.category || 'N/A' }}</p>
            </div>
            
            <div>
              <p class="text-sm text-muted-foreground">Status</p>
              <p class="font-medium flex items-center">
                <span :class="[getStatusColor(subject.status), 'w-2 h-2 mr-2 rounded-full']"></span>
                {{ getStatusText(subject.status) }}
              </p>
            </div>
            
            <div>
              <p class="text-sm text-muted-foreground">Created</p>
              <p class="font-medium">{{ new Date(subject.created_at).toLocaleDateString() }}</p>
            </div>
          </div>

          <div class="mt-6">
            <p class="text-sm text-muted-foreground">Notes</p>
            <p class="font-medium whitespace-pre-line">{{ subject.notes || 'No notes available' }}</p>
          </div>
        </div>

        <!-- Associated Documents Card -->
        <div class="rounded-xl border border-border bg-card p-6">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Documents</h2>
            <Link :href="`/documents/create?subject_id=${subject.id}`">
              <Button size="sm" class="bg-primary text-primary-foreground hover:bg-primary/90">
                Add Document
              </Button>
            </Link>
          </div>
          
          <div v-if="documents && documents.length > 0" class="space-y-3">
            <div v-for="document in documents" :key="document.id" class="p-3 border border-border rounded-lg hover:bg-muted/30">
              <div class="flex justify-between items-start">
                <div>
                  <p class="font-medium">{{ document.document_type?.name || 'Document' }}</p>
                  <p class="text-sm text-muted-foreground">
                    Issued: {{ new Date(document.issue_date).toLocaleDateString() }}
                  </p>
                  <p v-if="document.expiry_date" class="text-sm" :class="new Date(document.expiry_date) < new Date() ? 'text-red-500' : 'text-muted-foreground'">
                    Expires: {{ new Date(document.expiry_date).toLocaleDateString() }}
                  </p>
                </div>
                <Link :href="`/documents/${document.id}`" class="text-primary hover:underline text-sm">View</Link>
              </div>
            </div>
          </div>
          <div v-else class="text-center p-4 text-muted-foreground">
            No documents available
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
