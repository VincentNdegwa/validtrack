<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { type Subject, type SubjectType } from '@/types/models';

interface Props {
  subjects?: Subject[];
  subjectTypes?: SubjectType[];
}

const props = defineProps<Props>();
const subjects = ref(props.subjects || []);

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
  <Head title="Subjects" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-foreground">Subjects</h1>
          <Link href="/subject-types" class="text-sm text-primary hover:underline">
            Manage Subject Types
          </Link>
        </div>
        <Link href="/subjects/create">
          <Button class="bg-primary text-primary-foreground hover:bg-primary/90">
            Add Subject
          </Button>
        </Link>
      </div>

      <div class="rounded-xl border border-border bg-card">
        <div class="overflow-x-auto">
          <table class="w-full text-sm text-left">
            <thead class="text-xs uppercase bg-muted/50">
              <tr>
                <th scope="col" class="px-6 py-3">Name</th>
                <th scope="col" class="px-6 py-3">Type</th>
                <th scope="col" class="px-6 py-3">Email</th>
                <th scope="col" class="px-6 py-3">Phone</th>
                <th scope="col" class="px-6 py-3">Status</th>
                <th scope="col" class="px-6 py-3">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="subjects.length === 0">
                <td colspan="6" class="px-6 py-4 text-center">No subjects found</td>
              </tr>
              <tr v-for="subject in subjects" :key="subject.id" class="border-t border-border hover:bg-muted/30">
                <td class="px-6 py-4 font-medium">{{ subject.name }}</td>
                <td class="px-6 py-4">{{ subject.subject_type?.name || 'N/A' }}</td>
                <td class="px-6 py-4">{{ subject.email || 'N/A' }}</td>
                <td class="px-6 py-4">{{ subject.phone || 'N/A' }}</td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center">
                    <span :class="[getStatusColor(subject.status), 'w-2 h-2 mr-2 rounded-full']"></span>
                    {{ getStatusText(subject.status) }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="flex space-x-2">
                    <Link :href="`/subjects/${subject.id}`" class="text-blue-600 hover:underline">View</Link>
                    <Link :href="`/subjects/${subject.id}/edit`" class="text-amber-600 hover:underline">Edit</Link>
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
