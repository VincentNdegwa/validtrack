<template>
    <Head title="Company Details" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">{{ company.name }}</h1>
                    <p class="text-muted-foreground">Company details and user management</p>
                </div>
                <div class="flex space-x-2">
                    <Link :href="`/companies/${company.slug}/edit`">
                        <Button variant="outline">Edit Company</Button>
                    </Link>
                    <Button @click="confirmSwitch(company)" class="bg-primary text-primary-foreground">
                        Impersonate User
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <!-- Company details card -->
                <Card class="md:col-span-1">
                    <CardHeader>
                        <CardTitle>Company Details</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Status</dt>
                                <dd>
                                    <span
                                        class="inline-flex items-center rounded-full px-2 py-1 text-xs"
                                        :class="company.is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'"
                                    >
                                        {{ company.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Email</dt>
                                <dd>{{ company.email || 'N/A' }}</dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Phone</dt>
                                <dd>{{ company.phone || 'N/A' }}</dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Website</dt>
                                <dd>
                                    <a
                                        v-if="company.website"
                                        :href="company.website"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="text-blue-600 hover:underline"
                                    >
                                        {{ company.website }}
                                    </a>
                                    <span v-else>N/A</span>
                                </dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Address</dt>
                                <dd>{{ company.address || 'N/A' }}</dd>
                            </div>
                            
                            <div v-if="company.description">
                                <dt class="text-sm font-medium text-muted-foreground">Description</dt>
                                <dd class="whitespace-pre-line">{{ company.description }}</dd>
                            </div>
                        </dl>
                    </CardContent>
                </Card>

                <!-- Users list card -->
                <Card class="md:col-span-2">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle>Users</CardTitle>
                        <Link :href="route('users.create')">
                            <Button size="sm">Add User</Button>
                        </Link>
                    </CardHeader>
                    <CardContent>
                        <DataTable
                            :data="company.users"
                            :columns="userColumns"
                            empty-message="No users found"
                        >
                            <template #role="{ item: user }">
                                <span v-if="user.roles && user.roles.length > 0">
                                    {{ user.roles[0].display_name }}
                                </span>
                                <span v-else>-</span>
                            </template>
                            
                            <template #status="{ item: user }">
                                <StatusBadge :active="user.is_active" />
                            </template>
                            
                            <template #actions="{ item: user }">
                                <div class="flex items-center space-x-2">
                                    <Link :href="`/users/${user.slug}`" class="text-blue-600 hover:underline">View</Link>
                                    <Link :href="`/users/${user.slug}/edit`" class="text-amber-600 hover:underline">Edit</Link>
                                    <button @click="impersonateSpecificUser(user.id)" class="text-indigo-600 hover:underline">Impersonate</button>
                                </div>
                            </template>
                        </DataTable>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- Switch Dialog -->
        <Dialog :open="showSwitchDialog" @update:open="showSwitchDialog = $event">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Select User</DialogTitle>
                </DialogHeader>
                <div class="py-4">
                    <p>
                        Switch to view the system as a user from <span class="font-semibold">{{ companyToSwitch?.name }}</span>?
                    </p>
                    <p class="text-sm text-muted-foreground mt-2">
                        This will take you to a page where you can select a specific user to impersonate.
                    </p>
                </div>
                <div class="flex justify-end space-x-2">
                    <Button variant="outline" @click="cancelSwitch">Cancel</Button>
                    <Button @click="switchToCompany">Continue</Button>
                </div>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { DataTable } from '@/components/ui/data-table';
import { StatusBadge } from '@/components/ui/status-badge';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type Company, type User } from '@/types/models';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Props {
    company: Company & { 
        users: Array<User & { roles?: Array<{ name: string, display_name: string }> }> 
    };
}

const props = defineProps<Props>();
const showSwitchDialog = ref(false);
const companyToSwitch = ref<Company | null>(null);

// Define columns for users DataTable
const userColumns = [
    { key: 'name', label: 'Name' },
    { key: 'email', label: 'Email' },
    { key: 'role', label: 'Role' },
    { key: 'status', label: 'Status' },
    { key: '_actions', label: 'Actions', class: 'w-[100px]' }
];

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
        title: props.company.name,
        href: `/companies/${props.company.slug}`,
    },
];

const confirmSwitch = (company: Company) => {
    companyToSwitch.value = company;
    showSwitchDialog.value = true;
};

const cancelSwitch = () => {
    showSwitchDialog.value = false;
    companyToSwitch.value = null;
};

const switchToCompany = () => {
    if (companyToSwitch.value) {
        router.post('/companies/switch', {
            company_id: companyToSwitch.value.id
        });
    }
};

const impersonateSpecificUser = (userId: number) => {
    router.post('/impersonate', {
        user_id: userId
    });
};
</script>
