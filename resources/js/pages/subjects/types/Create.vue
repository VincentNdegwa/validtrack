<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
  name: '',
});

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
  {
    title: 'Subject Types',
    href: '/subject-types',
  },
  {
    title: 'Create Subject Type',
    href: '/subject-types/create',
  },
];

const submit = () => {
  form.post('/subject-types', {
    onSuccess: () => {
      form.reset();
    },
  });
};
</script>

<template>
  <Head title="Create Subject Type" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-foreground">Create Subject Type</h1>
      </div>

      <div class="rounded-xl border border-border bg-card p-6">
        <form @submit.prevent="submit" class="space-y-6">
          <div class="space-y-2">
            <label for="name" class="block text-sm font-medium">Name</label>
            <input 
              id="name" 
              v-model="form.name" 
              type="text" 
              class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary"
              :class="{ 'border-red-500': form.errors.name }"
              placeholder="Enter subject type name"
              required
            >
            <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
          </div>

          <div class="flex justify-end space-x-3">
            <Button type="button" variant="outline" :href="route('subject-types.index')">Cancel</Button>
            <Button type="submit" class="bg-primary text-primary-foreground hover:bg-primary/90" :disabled="form.processing">
              <span v-if="form.processing">Creating...</span>
              <span v-else>Create Subject Type</span>
            </Button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
