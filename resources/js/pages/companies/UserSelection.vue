<template>
    <Head title="Select User" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Select User to Impersonate</h1>
                    <p class="text-muted-foreground">
                        Select a user from {{ company.name }} to view the system as that user
                    </p>
                </div>
            </div>

            <div class="rounded-xl border border-border bg-card p-6">
                <div class="mb-4">
                    <form @submit.prevent="impersonateUser">
                        <div class="mb-6">
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div>
                                    <label for="user_id" class="mb-2 block text-sm font-medium">Select User</label>
                                    <select
                                        id="user_id"
                                        v-model="selectedUserId"
                                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                        required
                                    >
                                        <option value="" disabled>Select a user</option>
                                        <option v-for="user in users" :key="user.id" :value="user.id">
                                            {{ user.name }} ({{ user.email }})
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2">
                            <Link :href="route('companies.index')">
                                <Button type="button" variant="outline">Cancel</Button>
                            </Link>
                            <Button type="submit" :disabled="!selectedUserId">Impersonate User</Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type Company, type User } from '@/types/models';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Props {
    company: Company;
    users: User[];
}

const props = defineProps<Props>();
const selectedUserId = ref('');

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Companies',
        href: '/companies',
    },
    {
        title: 'Select User',
        href: '#',
    }
];

const impersonateUser = () => {
    router.post('/impersonate', {
        user_id: selectedUserId.value
    });
};
</script>
