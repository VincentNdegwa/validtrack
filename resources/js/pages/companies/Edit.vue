<template>
    <Head title="Edit Company" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Edit Company</h1>
                    <p class="text-muted-foreground">Update company information</p>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Company Information</CardTitle>
                    <CardDescription>Update the details for {{ company.name }}</CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="name">Company Name</Label>
                                <Input id="name" v-model="form.name" :error="form.errors.name" placeholder="Enter company name" />
                                <p v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="email">Email</Label>
                                <Input id="email" v-model="form.email" :error="form.errors.email" placeholder="company@example.com" />
                                <p v-if="form.errors.email" class="text-sm text-red-500">{{ form.errors.email }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="phone">Phone Number</Label>
                                <Input id="phone" v-model="form.phone" :error="form.errors.phone" placeholder="(123) 456-7890" />
                                <p v-if="form.errors.phone" class="text-sm text-red-500">{{ form.errors.phone }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="website">Website</Label>
                                <Input
                                    id="website"
                                    v-model="form.website"
                                    :error="form.errors.website"
                                    placeholder="https://example.com"
                                />
                                <p v-if="form.errors.website" class="text-sm text-red-500">{{ form.errors.website }}</p>
                            </div>

                            <div class="space-y-2 md:col-span-2">
                                <Label for="address">Address</Label>
                                <Input
                                    id="address"
                                    v-model="form.address"
                                    :error="form.errors.address"
                                    placeholder="Enter company address"
                                />
                                <p v-if="form.errors.address" class="text-sm text-red-500">{{ form.errors.address }}</p>
                            </div>

                            <div class="flex items-center space-x-2">
                                <Switch id="is_active" v-model="form.is_active" />
                                <Label for="is_active">Active</Label>
                                <p v-if="form.errors.is_active" class="text-sm text-red-500">{{ form.errors.is_active }}</p>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-2">
                            <Link :href="`/companies/${company.slug}`">
                                <Button type="button" variant="outline">Cancel</Button>
                            </Link>
                            <Button :disabled="form.processing" type="submit" class="bg-primary text-primary-foreground">
                                Update Company
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <!-- Danger Zone -->
            <Card class="border-red-200 dark:border-red-900">
                <CardHeader>
                    <CardTitle class="text-red-600 dark:text-red-500">Danger Zone</CardTitle>
                    <CardDescription>Perform destructive actions on this company</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between rounded-lg border border-red-200 p-4 dark:border-red-900">
                            <div>
                                <h3 class="text-base font-medium">Delete this company</h3>
                                <p class="text-sm text-muted-foreground">
                                    Once deleted, all of the company's data will be permanently removed.
                                </p>
                            </div>
                            <Button variant="destructive" @click="confirmDelete">Delete Company</Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Delete Dialog -->
        <Dialog :open="showDeleteDialog" @update:open="showDeleteDialog = $event">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete Company</DialogTitle>
                </DialogHeader>
                <div class="py-4">
                    <p>
                        Are you sure you want to delete company <span class="font-semibold">{{ company.name }}</span>? This action
                        cannot be undone.
                    </p>
                </div>
                <div class="flex justify-end space-x-2">
                    <Button variant="outline" @click="cancelDelete">Cancel</Button>
                    <Button variant="destructive" @click="deleteCompany">Delete</Button>
                </div>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type Company } from '@/types/models';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Props {
    company: Company;
}

const props = defineProps<Props>();
const showDeleteDialog = ref(false);

interface CompanyForm {
    name: string;
    email: string;
    phone: string;
    address: string;
    website: string;
    description: string;
    is_active: boolean;
}

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
    {
        title: 'Edit',
        href: `/companies/${props.company.slug}/edit`,
    },
];

const form = useForm<CompanyForm>({
    name: props.company.name,
    email: props.company.email,
    phone: props.company.phone || '',
    address: props.company.address || '',
    website: props.company.website || '',
    description: props.company.description || '',
    is_active: props.company.is_active,
});

const submit = () => {
    form.put(`/companies/${props.company.id}`, {
        preserveScroll: true,
    });
};

const confirmDelete = () => {
    showDeleteDialog.value = true;
};

const cancelDelete = () => {
    showDeleteDialog.value = false;
};

const deleteCompany = () => {
    router.delete(`/companies/${props.company.slug}`, {
        onSuccess: () => {
            showDeleteDialog.value = false;
        },
    });
};
</script>
