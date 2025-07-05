<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
  name: '',
  description: '',
  icon: '',
});

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
  {
    title: 'Document Types',
    href: '/document-types',
  },
  {
    title: 'Create',
    href: '/document-types/create',
  },
];

const submit = () => {
  form.post('/document-types');
};
</script>

<template>
  <Head title="Create Document Type" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-foreground">Create Document Type</h1>
      </div>

      <div class="rounded-xl border border-border bg-card p-6">
        <form @submit.prevent="submit" class="grid gap-6">
          <div class="grid gap-2">
            <Label for="name" class="text-sm font-medium">Name</Label>
            <Input id="name" v-model="form.name" type="text" placeholder="Enter document type name" />
            <InputError :message="form.errors.name" />
          </div>

          <div class="grid gap-2">
            <Label for="description" class="text-sm font-medium">Description (Optional)</Label>
            <Textarea id="description" v-model="form.description" placeholder="Enter description" />
            <InputError :message="form.errors.description" />
          </div>

          <div class="grid gap-2">
            <Label for="icon" class="text-sm font-medium">Icon (Optional)</Label>
            <Input id="icon" v-model="form.icon" type="text" placeholder="Icon name or CSS class" />
            <InputError :message="form.errors.icon" />
          </div>

          <div class="flex justify-end gap-2">
            <Button type="button" variant="outline" :href="route('document-types.index')">Cancel</Button>
            <Button type="submit" :disabled="form.processing">Create Document Type</Button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
