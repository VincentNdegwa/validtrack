<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';

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
        title: 'Create',
        href: '/companies/create',
    },
];

const form = useForm<CompanyForm>({
    name: '',
    email: '',
    phone: '',
    address: '',
    website: '',
    description: '',
    is_active: true,
});

const submit = () => {
    form.post('/companies', {
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <Head title="Create Company" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Create Company</h1>
                    <p class="text-muted-foreground">Add a new company to the platform</p>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Company Information</CardTitle>
                    <CardDescription>Enter the details for the new company</CardDescription>
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
                            <Button :disabled="form.processing" type="submit" class="bg-primary text-primary-foreground">
                                Create Company
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
