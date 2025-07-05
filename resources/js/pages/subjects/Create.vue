<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { type SubjectType } from '@/types/models';

interface Props {
  subjectTypes?: SubjectType[];
}

const props = defineProps<Props>();

const form = useForm({
  name: '',
  subject_type_id: '',
  email: '',
  phone: '',
  address: '',
  category: '',
  notes: '',
  status: 1,
});

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
    title: 'Create Subject',
    href: '/subjects/create',
  },
];

const submit = () => {
  form.post('/subjects', {
    onSuccess: () => {
      form.reset();
    },
  });
};
</script>

<template>
  <Head title="Create Subject" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-foreground">Create Subject</h1>
      </div>

      <div class="rounded-xl border border-border bg-card p-6">
        <form @submit.prevent="submit" class="space-y-6">
          <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div class="space-y-2">
              <label for="name" class="block text-sm font-medium">Name</label>
              <input 
                id="name" 
                v-model="form.name" 
                type="text" 
                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                :class="{ 'border-red-500': form.errors.name }"
                required
              >
              <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
            </div>

            <div class="space-y-2">
              <div class="flex justify-between items-center">
                <label for="subject_type_id" class="block text-sm font-medium">Subject Type</label>
                <Link href="/subject-types" class="text-xs text-primary hover:underline">
                  Manage Types
                </Link>
              </div>
              <div class="flex space-x-2">
                <select 
                  id="subject_type_id" 
                  v-model="form.subject_type_id" 
                  class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                  :class="{ 'border-red-500': form.errors.subject_type_id }"
                >
                  <option value="">Select Type</option>
                  <option v-for="type in props.subjectTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
                </select>
                <Link href="/subject-types/create">
                  <Button type="button" variant="outline" size="icon" class="h-10 w-10">+</Button>
                </Link>
              </div>
              <p v-if="form.errors.subject_type_id" class="mt-1 text-xs text-red-500">{{ form.errors.subject_type_id }}</p>
            </div>

            <div class="space-y-2">
              <label for="email" class="block text-sm font-medium">Email</label>
              <input 
                id="email" 
                v-model="form.email" 
                type="email" 
                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                :class="{ 'border-red-500': form.errors.email }"
              >
              <p v-if="form.errors.email" class="mt-1 text-xs text-red-500">{{ form.errors.email }}</p>
            </div>

            <div class="space-y-2">
              <label for="phone" class="block text-sm font-medium">Phone</label>
              <input 
                id="phone" 
                v-model="form.phone" 
                type="text" 
                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                :class="{ 'border-red-500': form.errors.phone }"
              >
              <p v-if="form.errors.phone" class="mt-1 text-xs text-red-500">{{ form.errors.phone }}</p>
            </div>

            <div class="space-y-2">
              <label for="address" class="block text-sm font-medium">Address</label>
              <input 
                id="address" 
                v-model="form.address" 
                type="text" 
                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                :class="{ 'border-red-500': form.errors.address }"
              >
              <p v-if="form.errors.address" class="mt-1 text-xs text-red-500">{{ form.errors.address }}</p>
            </div>

            <div class="space-y-2">
              <label for="category" class="block text-sm font-medium">Category</label>
              <input 
                id="category" 
                v-model="form.category" 
                type="text" 
                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                :class="{ 'border-red-500': form.errors.category }"
              >
              <p v-if="form.errors.category" class="mt-1 text-xs text-red-500">{{ form.errors.category }}</p>
            </div>

            <div class="space-y-2">
              <label for="status" class="block text-sm font-medium">Status</label>
              <select 
                id="status" 
                v-model="form.status" 
                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                :class="{ 'border-red-500': form.errors.status }"
              >
                <option :value="1">Active</option>
                <option :value="2">Pending</option>
                <option :value="3">Inactive</option>
              </select>
              <p v-if="form.errors.status" class="mt-1 text-xs text-red-500">{{ form.errors.status }}</p>
            </div>
          </div>

          <div class="space-y-2">
            <label for="notes" class="block text-sm font-medium">Notes</label>
            <textarea 
              id="notes" 
              v-model="form.notes" 
              rows="4" 
              class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary"
              :class="{ 'border-red-500': form.errors.notes }"
            ></textarea>
            <p v-if="form.errors.notes" class="mt-1 text-xs text-red-500">{{ form.errors.notes }}</p>
          </div>

          <div class="flex justify-end space-x-3">
            <Button type="button" variant="outline" :href="route('subjects.index')">Cancel</Button>
            <Button type="submit" class="bg-primary text-primary-foreground hover:bg-primary/90" :disabled="form.processing">
              <span v-if="form.processing">Creating...</span>
              <span v-else>Create Subject</span>
            </Button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
