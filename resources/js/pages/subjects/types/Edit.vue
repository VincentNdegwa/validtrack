<script setup lang="ts">
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type SubjectType } from '@/types/models';
import { Head, useForm } from '@inertiajs/vue3';

interface Props {
    subjectType: SubjectType;
}

const props = defineProps<Props>();

const form = useForm({
    name: props.subjectType.name,
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
        title: 'Edit Subject Type',
        href: `/subject-types/${props.subjectType.id}/edit`,
    },
];

const submit = () => {
    form.put(`/subject-types/${props.subjectType.id}`, {
        preserveScroll: true,
    });
};
const goBack = () => {
    window.history.back();
};
</script>

<template>
    <Head title="Edit Subject Type" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-foreground">Edit Subject Type</h1>
            </div>

            <div class="rounded-xl border border-border bg-card p-6">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="space-y-2">
                        <label for="name" class="block text-sm font-medium">Name</label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus:ring-2 focus:ring-primary focus:outline-none"
                            :class="{ 'border-red-500': form.errors.name }"
                            placeholder="Enter subject type name"
                            required
                        />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <Button @click="goBack" type="button" variant="outline" >Cancel</Button>
                        <Button type="submit" class="bg-primary text-primary-foreground hover:bg-primary/90" :disabled="form.processing">
                            <span v-if="form.processing">Saving...</span>
                            <span v-else>Save Changes</span>
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
