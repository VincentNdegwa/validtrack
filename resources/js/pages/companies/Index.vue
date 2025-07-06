<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type Company } from '@/types/models';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Props {
    companies?: Company[];
}

const props = defineProps<Props>();
const companies = ref(props.companies || []);
const showDeleteDialog = ref(false);
const companyToDelete = ref<Company | null>(null);
const showSwitchDialog = ref(false);
const companyToSwitch = ref<Company | null>(null);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Companies',
        href: '/companies',
    },
];

const confirmDelete = (company: Company) => {
    companyToDelete.value = company;
    showDeleteDialog.value = true;
};

const cancelDelete = () => {
    showDeleteDialog.value = false;
    companyToDelete.value = null;
};

const deleteCompany = () => {
    if (companyToDelete.value) {
        router.delete(`/companies/${companyToDelete.value.slug}`, {
            onSuccess: () => {
                showDeleteDialog.value = false;
                companyToDelete.value = null;
            }
        });
    }
};

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

const clearCompanyContext = () => {
    router.post('/companies/switch', {
        company_id: null
    });
};
</script>

<template>
    <Head title="Companies" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Companies</h1>
                    <p class="text-muted-foreground">Manage companies in the platform</p>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" @click="clearCompanyContext">Clear Company Context</Button>
                    <Link href="/companies/create">
                        <Button class="bg-primary text-primary-foreground hover:bg-primary/90">Add Company</Button>
                    </Link>
                </div>
            </div>

            <div class="rounded-xl border border-border bg-card">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-muted/50 text-xs uppercase">
                            <tr>
                                <th scope="col" class="px-6 py-3">Name</th>
                                <th scope="col" class="px-6 py-3">Email</th>
                                <th scope="col" class="px-6 py-3">Users</th>
                                <th scope="col" class="px-6 py-3">Status</th>
                                <th scope="col" class="px-6 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="companies.length === 0">
                                <td colspan="5" class="px-6 py-4 text-center">No companies found</td>
                            </tr>
                            <tr v-for="company in companies" :key="company.id" class="border-t border-border hover:bg-muted/30">
                                <td class="px-6 py-4 font-medium">{{ company.name }}</td>
                                <td class="px-6 py-4">{{ company.email }}</td>
                                <td class="px-6 py-4">{{ company.users_count || 0 }}</td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center rounded-full px-2 py-1 text-xs"
                                        :class="company.is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'"
                                    >
                                        {{ company.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex space-x-2">
                                        <Link :href="`/companies/${company.slug}`" class="text-blue-600 hover:underline">View</Link>
                                        <Link :href="`/companies/${company.slug}/edit`" class="text-amber-600 hover:underline">Edit</Link>
                                        <button @click="confirmSwitch(company)" class="text-indigo-600 hover:underline">Impersonate</button>
                                        <button @click="confirmDelete(company)" class="text-red-600 hover:underline">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Delete Dialog -->
        <Dialog :open="showDeleteDialog" @update:open="showDeleteDialog = $event">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete Company</DialogTitle>
                </DialogHeader>
                <div class="py-4">
                    <p>
                        Are you sure you want to delete company <span class="font-semibold">{{ companyToDelete?.name }}</span>? This
                        action cannot be undone.
                    </p>
                </div>
                <div class="flex justify-end space-x-2">
                    <Button variant="outline" @click="cancelDelete">Cancel</Button>
                    <Button variant="destructive" @click="deleteCompany">Delete</Button>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Switch Dialog -->
        <Dialog :open="showSwitchDialog" @update:open="showSwitchDialog = $event">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Select Company</DialogTitle>
                </DialogHeader>
                <div class="py-4">
                    <p>
                        Switch to <span class="font-semibold">{{ companyToSwitch?.name }}</span>?
                    </p>
                    <p class="text-sm text-muted-foreground mt-2">
                        This will show you a list of users in this company that you can impersonate.
                    </p>
                </div>
                <div class="flex justify-end space-x-2">
                    <Button variant="outline" @click="cancelSwitch">Cancel</Button>
                    <Button @click="switchToCompany">Switch</Button>
                </div>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
