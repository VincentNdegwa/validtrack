<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { type SubjectType } from '@/types/models';

interface Props {
  subjectTypes?: SubjectType[];
}

const props = defineProps<Props>();
const subjectTypes = ref(props.subjectTypes || []);

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
</script>

<template>
  <Head title="Subject Types" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-foreground">Subject Types</h1>
        <Link href="/subject-types/create">
          <Button class="bg-primary text-primary-foreground hover:bg-primary/90">
            Add Subject Type
          </Button>
        </Link>
      </div>

      <div class="rounded-xl border border-border bg-card">
        <div class="overflow-x-auto">
          <table class="w-full text-sm text-left">
            <thead class="text-xs uppercase bg-muted/50">
              <tr>
                <th scope="col" class="px-6 py-3">Name</th>
                <th scope="col" class="px-6 py-3">Subjects Count</th>
                <th scope="col" class="px-6 py-3">Created At</th>
                <th scope="col" class="px-6 py-3">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="subjectTypes.length === 0">
                <td colspan="4" class="px-6 py-4 text-center">No subject types found</td>
              </tr>
              <tr v-for="type in subjectTypes" :key="type.id" class="border-t border-border hover:bg-muted/30">
                <td class="px-6 py-4 font-medium">{{ type.name }}</td>
                <td class="px-6 py-4">{{ type.subjects?.length || 0 }}</td>
                <td class="px-6 py-4">{{ new Date(type.created_at).toLocaleDateString() }}</td>
                <td class="px-6 py-4">
                  <div class="flex space-x-2">
                    <Link :href="`/subject-types/${type.id}`" class="text-blue-600 hover:underline">View</Link>
                    <Link :href="`/subject-types/${type.id}/edit`" class="text-amber-600 hover:underline">Edit</Link>
                    <button 
                      @click.prevent="$inertia.delete(`/subject-types/${type.id}`)" 
                      class="text-red-600 hover:underline"
                      v-if="(type.subjects?.length || 0) === 0"
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
